<?php

namespace App\Models\modules\tasks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class taskFile extends Model
{
    use HasFactory;

    protected $table = 'task_files';
    public $timestamps = false;

    protected $fillable = [
        'task_id',
        'user_id',
        'filename',
        'filepath',
        'size',
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
