<?php

namespace App\Models\modules\oms;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class BilledOrderLine extends BaseOmsModel
{
    protected $table = 'oms_billed_order_lines';

    protected $fillable = [
        'billed_order_id',
        'order_note_line_id',
        'product_id',
        'product_attribute_id',
        'qty_billed',
        'qty_received',
    ];

    protected $casts = [
        'billed_order_id' => 'integer',
        'order_note_line_id' => 'integer',
        'product_id' => 'integer',
        'product_attribute_id' => 'integer',
        'qty_billed' => 'integer',
        'qty_received' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function billedOrder(): BelongsTo
    {
        return $this->belongsTo(BilledOrder::class, 'billed_order_id', 'id');
    }

    public function orderNoteLine(): BelongsTo
    {
        return $this->belongsTo(OrderNoteLine::class, 'order_note_line_id', 'id');
    }

    public function getQtyReceivedCalculatedAttribute(): int
    {
        $fromReceptions = (int) DB::table('oms_reception_lines as rl')
            ->where('rl.billed_order_line_id', $this->id)
            ->sum('rl.qty_received');

        return max((int) ($this->qty_received ?? 0), $fromReceptions);
    }

    public function getQtyMissingToReceiveAttribute(): int
    {
        $received = $this->qty_received ?? $this->qty_received_calculated ?? 0;
        return max(0, (int) $this->qty_billed - (int) $received);
    }
}
