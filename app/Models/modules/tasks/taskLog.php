<?php

namespace App\Models\modules\tasks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class taskLog extends Model
{
    use HasFactory;

    protected $table = 'task_logs';
    public $timestamps = false;

    protected $fillable = [
        'task_id',
        'user_id',
        'old_status_user',
        'new_status_user',
        'old_status_manager',
        'new_status_manager',
        'old_status_admin',
        'new_status_admin',
        'comment',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(task::class, 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
