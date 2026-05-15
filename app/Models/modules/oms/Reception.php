<?php

namespace App\Models\modules\oms;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reception extends BaseOmsModel
{
    protected $table = 'oms_receptions';

    protected $fillable = [
        'billed_order_id',
        'created_by',
    ];

    protected $casts = [
        'billed_order_id' => 'integer',
        'created_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function billedOrder(): BelongsTo
    {
        return $this->belongsTo(BilledOrder::class, 'billed_order_id', 'id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ReceptionLine::class, 'reception_id', 'id');
    }
}
