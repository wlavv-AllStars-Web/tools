<?php

namespace App\Models\modules\tasks\Traits;

use App\Models\modules\team\team;

trait HasTaskRole
{
    public function team()
    {
        return $this->belongsTo(team::class, 'id_team');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }
}
