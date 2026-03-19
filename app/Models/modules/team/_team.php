<?php

namespace App\Models\Modules\team;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\modules\tasks\Task;

class Team extends Model
{
    protected $table = 'teams';

    protected $fillable = ['name'];

    public function users()
    {
        return $this->hasMany(User::class, 'id_team');
    }

    public function manager()
    {
        return $this->hasOne(User::class, 'id_team')->where('role','manager');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'id_team');
    }
}
