<?php

namespace App\Http\Controllers\CustomTools\Tasks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\modules\tasks\task;
use App\Models\modules\tasks\taskLog;
use App\Models\User;

class managerTaskController extends Controller
{
    public function index(Request $request)
    {

        $year  = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        $user = $request->user();

        $tasks = task::query()
            ->whereNotNull('task_date')
            ->whereYear('task_date', $year)
            ->whereMonth('task_date', $month)
            ->with(['team','assignedUser', 'files'])
            ->where('id_team', $user->id_team)
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('customTools.tasks.manager.index', compact('tasks', 'year', 'month'));
    }

    public function show(int $id, Request $request)
    {
        $task = task::with(['team','assignedUser','files.user','logs.user'])->findOrFail($id);
        $this->authorize('view', $task);

        // hide user comments from managers
        $logs = $task->logs->filter(function($log) {
            // log->user is loaded
            if (!$log->user) return true;
            return $log->user->role !== 'user' || ($log->comment && str_starts_with($log->comment, 'SYSTEM:'));
        });

        $teamUsers = User::query()
            ->where('role','user')
            ->where('id_team', $request->user()->id_team)
            ->orderBy('name')
            ->get();

        return view('customTools.tasks.manager.show', [
            'task' => $task,
            'logs' => $logs,
            'teamUsers' => $teamUsers,
        ]);
    }

    public function assignUser(Request $request, int $id)
    {
        $task = task::findOrFail($id);
        $this->authorize('assign', $task);

        $data = $request->validate([
            'assigned_user_id' => ['required','integer','exists:users,id'],
        ]);

        $assignee = User::findOrFail($data['assigned_user_id']);
        if ($assignee->id_team != $request->user()->id_team || $assignee->role !== 'user') {
            abort(403);
        }

        $old = $task->assigned_user_id;
        $task->update(['assigned_user_id' => $assignee->id]);

        taskLog::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'comment' => 'Manager assigned user',
        ]);

        return back()->with('success', 'User atribuído.');
    }

    public function updateStatus(Request $request, int $id)
    {
        $task = task::findOrFail($id);
        $this->authorize('updateManager', $task);

        $data = $request->validate([
            'status_manager' => ['required','in:'.implode(',', task::STATUS_MANAGER)],
        ]);

        $old = $task->status_manager;
        $task->update(['status_manager' => $data['status_manager']]);

        taskLog::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'old_status_manager' => $old,
            'new_status_manager' => $task->status_manager,
            'comment' => 'Manager status update',
        ]);

        return back()->with('success', 'Status atualizado.');
    }

    public function updateObservations(Request $request, int $id)
    {
        $task = task::findOrFail($id);
        $this->authorize('updateManager', $task);

        $data = $request->validate([
            'observations_manager' => ['nullable','string'],
        ]);

        $task->update(['observations_manager' => $data['observations_manager']]);

        taskLog::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'comment' => 'Manager updated observations',
        ]);

        return back()->with('success', 'Observações guardadas.');
    }
}
