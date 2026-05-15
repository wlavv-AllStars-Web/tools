<?php

namespace App\Http\Controllers\CustomTools\Tasks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\modules\tasks\task;
use App\Models\modules\tasks\taskLog;
use App\Models\modules\team\team;
use Carbon\Carbon;

class taskController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('create', task::class);
        $adminIndexRoute = $request->routeIs('admin.tools.tasks.admin.*') ? 'admin.tools.tasks.admin.index' : 'tasks.admin.index';

        $year   = (int) $request->input('year', now()->year);
        $month  = (int) $request->input('month', now()->month);
        $teamId = $request->input('team_id'); // null/'' => todos

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end   = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $q = task::with(['team'])->whereBetween('task_date', [$start, $end]);

        if (!empty($teamId)) {
            $q->where('id_team', $teamId);
        }

        $tasks = $q->orderByDesc('task_date')->get();
        $teams = team::orderBy('name')->get();

        $breadcrumbs = [
            ['name' => 'administration', 'url' => route('administration.index')],
            ['name' => 'Tasks', 'url' => route($adminIndexRoute), 'no_translation' => 1],
        ];

        return view('customTools.tasks.admin.index', compact('tasks','teams','year','month','teamId', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', task::class);

        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'id_team'      => 'required|exists:teams,id',
            'task_date'    => 'nullable|date',
            'time_allowed' => 'nullable|integer|min:1',
        ]);

        $task = task::create($data);

        taskLog::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'comment' => 'Task created',
            'new_status_admin' => $task->status_admin,
        ]);

        // ✅ Excel UI usa AJAX
        if ($request->ajax()) {
            $routePrefix = $request->routeIs('admin.tools.tasks.admin.*') ? 'admin.tools.tasks.admin.' : 'tasks.admin.';
            $task->load('team');

            return response()->json([
                'ok' => true,
                'task' => [
                    'id' => $task->id,
                    'created_at' => $task->created_at->format('d/m/Y'),
                    'title' => $task->title,
                    'id_team' => $task->id_team,
                    'team_name' => $task->team?->name,
                    'task_date' => $task->task_date?->format('Y-m-d'),
                    'task_date_display' => $task->task_date?->format('d/m/Y') ?? '',
                    'time_allowed' => $task->time_allowed,
                    'status_admin' => $task->status_admin ?? 'new',
                    'observations_admin' => $task->observations_admin ?? '',
                    'description' => $task->description ?? '',
                    'field_url' => route($routePrefix . 'field', $task->id),
                    'comments_url' => route($routePrefix . 'comments', $task->id),
                    'update_url' => route($routePrefix . 'update', $task->id),
                ]
            ]);
        }

        return back()->with('success','Task created');
    }

    /**
     * Mantive o update “antigo” porque tens rota /{id}/update.
     * Pode continuar a existir, mas a UI Excel vai chamar updateField().
     */
    public function update(Request $request, int $id)
    {
        $this->authorize('create', task::class);

        $task = task::findOrFail($id);
        $oldStatus = $task->status_admin;

        $data = $request->validate([
            'status_admin'       => 'required|in:'.implode(',', task::STATUS_ADMIN),
            'observations_admin' => 'nullable|string',
        ]);

        $task->update($data);

        taskLog::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'comment' => 'Admin updated task',
            'old_status_admin' => $oldStatus,
            'new_status_admin' => $task->status_admin,
        ]);

        if ($oldStatus !== 'fail' && $task->status_admin === 'fail' && $request->boolean('duplicate_next_week')) {
            $newTask = $task->replicate();
            $newTask->status_admin = 'new';
            $newTask->created_at = now()->addWeek()->startOfWeek();
            $newTask->updated_at = now()->addWeek()->startOfWeek();
            $newTask->save();

            taskLog::create([
                'task_id' => $newTask->id,
                'user_id' => Auth::id(),
                'comment' => 'Task duplicated due to FAIL',
                'new_status_admin' => 'new',
            ]);
        }

        return back()->with('success','Task updated');
    }

    /**
     * ✅ Excel inline: update campo-a-campo
     * POST /web/tasks/admin/{id}/field
     */
    public function updateField(Request $request, int $id)
    {
        $this->authorize('create', task::class);

        $task = task::with('team')->findOrFail($id);

        $field = $request->input('field');
        $value = $request->input('value');

        $allowed = [
            'title',
            'id_team',
            'task_date',
            'time_allowed',
            'status_admin',
            'description',
            'observations_admin',
        ];

        if (!in_array($field, $allowed, true)) {
            return response()->json(['ok' => false, 'message' => 'Field not allowed'], 422);
        }

        // Validação por campo
        $rules = match ($field) {
            'title' => ['value' => 'required|string|max:255'],
            'id_team' => ['value' => 'required|exists:teams,id'],
            'task_date' => ['value' => 'nullable|date'],
            'time_allowed' => ['value' => 'nullable|integer|min:1'],
            'status_admin' => ['value' => 'required|in:'.implode(',', task::STATUS_ADMIN)],
            'description' => ['value' => 'nullable|string'],
            'observations_admin' => ['value' => 'nullable|string'],
            default => ['value' => 'nullable'],
        };

        $validated = $request->validate($rules);

        $oldStatus = $task->status_admin;

        $task->{$field} = $validated['value'];
        $task->save();

        // reload relations if needed
        if ($field === 'id_team') {
            $task->load('team');
        }

        // log
        if ($field === 'status_admin') {
            taskLog::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'comment' => 'Admin changed status',
                'old_status_admin' => $oldStatus,
                'new_status_admin' => $task->status_admin,
            ]);

            // FAIL duplication (Excel)
            if ($oldStatus !== 'fail' && $task->status_admin === 'fail' && $request->boolean('duplicate_next_week')) {
                $newTask = $task->replicate();
                $newTask->status_admin = 'new';
                $newTask->created_at = now()->addWeek()->startOfWeek();
                $newTask->updated_at = now()->addWeek()->startOfWeek();
                $newTask->save();

                taskLog::create([
                    'task_id' => $newTask->id,
                    'user_id' => Auth::id(),
                    'comment' => 'Task duplicated due to FAIL',
                    'new_status_admin' => 'new',
                ]);
            }
        } else {
            taskLog::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'comment' => "Admin updated {$field}",
                'new_status_admin' => $task->status_admin,
            ]);
        }

        return response()->json([
            'ok' => true,
            'field' => $field,
            'value' => $task->{$field},
            'team_name' => $field === 'id_team' ? ($task->team?->name ?? '') : null,
            'task_date_display' => $field === 'task_date' ? ($task->task_date?->format('d/m/Y') ?? '') : null,
            'status_admin' => $field === 'status_admin' ? ($task->status_admin ?? 'new') : null,
        ]);
    }

    /**
     * ✅ Excel inline: comentários + histórico para "row child"
     * GET /web/tasks/admin/{id}/comments
     */
    public function comments(int $id)
    {
        $this->authorize('create', task::class);
    
        $task = task::with(['files'])->findOrFail($id);
    
return response()->json([
    'comments' => [
        'admin'   => $task->observations_admin,
        'manager' => $task->observations_manager,
        'user'    => $task->observations_user,
    ],
    'status' => [
        'admin'   => $task->status_admin,
        'manager' => $task->status_manager,
        'user'    => $task->status_user,
    ],
    'files' => $task->files->map(fn($f) => [
        'filename' => $f->filename,
        'size' => $f->size,
        'download_url' => route(request()->routeIs('admin.tools.tasks.admin.*') ? 'admin.tools.tasks.files.download' : 'tasks.files.download', $f->id),
    ])->values(),
]);

    }

}
