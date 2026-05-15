<?php
namespace App\Observers;

use App\Models\modules\tasks\Task;
use App\Models\modules\tasks\TaskLog;

class TaskObserver
{
    public function created(Task $task)
    {
        TaskLog::create([
            'task_id'=>$task->id,
            'action_type'=>'CREATED',
            'level'=>'admin',
            'performed_by'=>auth()->id(),
            'created_at'=>now()
        ]);
    }
}
