<?php

namespace App\Models\modules\logs;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class logs extends Model{
    
    protected $table = 'logs';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'route',
        'method',
        'ip_address',
        'user_agent',
        'severity',
        'description',
        'created_at',
    ];

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }
}