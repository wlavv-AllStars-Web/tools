<?php

namespace App\Models\modules\team;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\modules\tasks\task;

class team extends Model
{
    use HasFactory;

    protected $table = 'teams';

    protected $fillable = [
        'name',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'id_team');
    }

    public function managers()
    {
        return $this->hasMany(User::class, 'id_team')->where('role', 'manager');
    }

    public function tasks()
    {
        return $this->hasMany(task::class, 'id_team');
    }
}
