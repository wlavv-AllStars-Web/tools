<?php

namespace App\Policies;

use App\Models\User;
use App\Models\modules\tasks\task;

class TaskPolicy
{
    public function view(User $user, task $task): bool
    {
        if ($user->role === 'admin') return true;

        if ($user->role === 'manager') {
            return (int)$user->id_team === (int)$task->id_team;
        }

        // user
        return (int)$task->assigned_user_id === (int)$user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function updateAdmin(User $user, task $task): bool
    {
        return $user->role === 'admin';
    }

    public function updateManager(User $user, task $task): bool
    {
        return $user->role === 'manager' && (int)$user->id_team === (int)$task->id_team;
    }

    public function updateUser(User $user, task $task): bool
    {
        return $user->role === 'user' && (int)$task->assigned_user_id === (int)$user->id;
    }

    public function assign(User $user, task $task): bool
    {
        return $this->updateManager($user, $task);
    }
}
