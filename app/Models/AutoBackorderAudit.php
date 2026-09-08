<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoBackorderAudit extends Model
{
    protected $fillable = [
        'id_order',
        'order_reference',
        'original_state',
        'target_state',
        'audit_date',
        'detected_at',
        'reason',
        'unpicked_products',
        'state_changed',
        'state_change_attempted_at',
        'state_changed_at',
        'state_change_error',
    ];

    protected $casts = [
        'audit_date' => 'date',
        'detected_at' => 'datetime',
        'unpicked_products' => 'array',
        'state_changed' => 'boolean',
        'state_change_attempted_at' => 'datetime',
        'state_changed_at' => 'datetime',
    ];
}
