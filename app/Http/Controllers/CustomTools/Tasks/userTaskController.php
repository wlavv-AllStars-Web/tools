<?php

namespace App\Http\Controllers\CustomTools\Tasks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\modules\tasks\task;
use App\Models\modules\tasks\taskLog;

class userTaskController extends Controller
{
    public function index(Request $request)
    {
    
        $year  = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $tasks = task::query()
            ->with(['team','files'])
            ->where('assigned_user_id', $request->user()->id)
            ->whereNotNull('task_date')
            ->whereYear('task_date', $year)
            ->whereMonth('task_date', $month)
            ->orderByDesc('task_date')
            ->orderByDesc('id')
            ->get();
    
        return view('customTools.tasks.user.index', compact('tasks', 'year', 'month'));
        
    }

    public function show(int $id)
    {
        $task = task::with(['team','assignedUser','files.user','logs.user'])->findOrFail($id);
        $this->authorize('view', $task);
        $logs = $task->logs;

        return view('customTools.tasks.user.show', compact('task','logs'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $task = task::findOrFail($id);
        $this->authorize('updateUser', $task);

        $data = $request->validate([
            'status_user' => ['required','in:'.implode(',', task::STATUS_USER)],
        ]);

        $old = $task->status_user;
        $task->update(['status_user' => $data['status_user']]);

        taskLog::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'old_status_user' => $old,
            'new_status_user' => $task->status_user,
            'comment' => 'User status update',
        ]);

        return back()->with('success', 'Status atualizado.');
    }

    public function addComment(Request $request, int $id)
    {
        $task = task::findOrFail($id);
        $this->authorize('updateUser', $task);

        $data = $request->validate([
            'comment' => ['required','string','max:5000'],
            'observations_user' => ['nullable','string'],
        ]);

        if (array_key_exists('observations_user', $data)) {
            $task->update(['observations_user' => $data['observations_user']]);
        }

        taskLog::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'comment' => $data['comment'],
        ]);

        return back()->with('success', 'Comentário guardado.');
    }
}
