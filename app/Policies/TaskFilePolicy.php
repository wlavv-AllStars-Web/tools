<?php

namespace App\Policies;

use App\Models\User;
use App\Models\modules\tasks\taskFile;

class TaskFilePolicy
{
    public function download(User $user, taskFile $file): bool
    {
        if ($user->role === 'admin') return true;

        if ($user->role === 'manager') {
            return (int)$user->id_team === (int)$file->task->id_team;
        }

        // user
        return (int)$file->user_id === (int)$user->id && (int)$file->task->assigned_user_id === (int)$user->id;
    }

    public function upload(User $user): bool
    {
        return in_array($user->role, ['admin','manager','user'], true);
    }
}
