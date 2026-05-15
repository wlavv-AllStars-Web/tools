<?php

namespace App\Models\modules\oms;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceptionLine extends BaseOmsModel
{
    protected $table = 'oms_reception_lines';

    protected $fillable = [
        'reception_id',
        'billed_order_line_id',
        'qty_received',
    ];

    protected $casts = [
        'reception_id' => 'integer',
        'billed_order_line_id' => 'integer',
        'qty_received' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function reception(): BelongsTo
    {
        return $this->belongsTo(Reception::class, 'reception_id', 'id');
    }

    public function billedOrderLine(): BelongsTo
    {
        return $this->belongsTo(BilledOrderLine::class, 'billed_order_line_id', 'id');
    }
}
