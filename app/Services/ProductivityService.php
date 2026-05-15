<?php
namespace App\Services;

use App\Models\modules\tasks\Task;

class ProductivityService
{
    public static function calculateDepartment($departmentId, $from, $to)
    {
        $tasks = Task::where('department_id',$departmentId)
            ->whereBetween('created_at',[$from,$to])->get();

        $score = 0;
        $valid = 0;

        foreach($tasks as $task){
            if(in_array($task->admin_status,['DONE','FAIL','DELAYED'])){
                $valid++;
                if($task->admin_status==='DONE') $score++;
                if($task->admin_status==='FAIL') $score--;
            }
        }

        return $valid ? round(($score/$valid)*100,2) : 0;
    }
}
