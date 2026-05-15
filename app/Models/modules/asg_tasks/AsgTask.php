<?php

namespace App\Models\modules\asg_tasks;

use Illuminate\Database\Eloquent\Model;

class AsgTask extends Model
{
    protected $table = 'asg_tasks';

    protected $fillable = [
        'title',
        'comment',
        'id_team',
        'task_date',
        'id_week',
        'status',
        'status_changed',
        'description',
        'time_allowed',
    ];

    protected $casts = [
        'task_date' => 'date',
        'id_team' => 'integer',
        'id_week' => 'integer',
        'time_allowed' => 'integer',
        'status_changed' => 'integer',
    ];
}