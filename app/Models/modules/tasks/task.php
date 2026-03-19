<?php

namespace App\Models\modules\tasks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\modules\team\team;
use App\Models\User;

class task extends Model
{
    use HasFactory;

    protected $table = 'tasks';

    protected $fillable = [
        'title','description','id_team','task_date','time_allowed',
        'status_user','status_manager','status_admin',
        'observations_user','observations_manager','observations_admin',
    ];

    protected $casts = [
        'task_date' => 'date',
    ];

    public const STATUS_USER = ['new','pending','done'];
    public const STATUS_MANAGER = ['new','pending','delayed','done','fail'];
    public const STATUS_ADMIN = ['new','pending','done','fail','delayed','ok','extra','hold'];

    public function team(){
        
        return $this->belongsTo(team::class, 'id_team');
    }

    public function assignedUser(){
        
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function logs(){
        
        return $this->hasMany(taskLog::class, 'task_id')->orderBy('created_at', 'desc');
    }

    public function files(){
        
        return $this->hasMany(taskFile::class, 'task_id')->orderBy('created_at', 'desc');
    }

    public function effectiveStatus(): string{
        
        if ($this->status_admin !== 'new') return $this->status_admin;
        if ($this->status_manager !== 'new') return $this->status_manager;
        return $this->status_user;
    }
}
