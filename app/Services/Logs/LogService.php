<?php

namespace App\Services\Logs;

use App\Models\modules\logs\logs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class LogService
{
    public static function create(
        string $action,
        string $module,
        string $severity = 'info',
        ?string $description = null,
        ?int $userId = null
    ): void {
        logs::create([
            'user_id'     => $userId ?? Auth::id(),
            'action'      => $action,
            'module'      => $module,
            'route'       => Request::path(),
            'method'      => Request::method(),
            'ip_address'  => Request::ip(),
            'user_agent'  => Request::userAgent(),
            'severity'    => $severity,
            'description' => $description,
            'created_at'  => now(),
        ]);
    }
}
