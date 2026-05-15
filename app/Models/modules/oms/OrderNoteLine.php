<?php

namespace App\Models\modules\oms;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class OrderNoteLine extends BaseOmsModel
{
    protected $table = 'oms_order_note_lines';

    protected $fillable = [
        'order_note_id',
        'product_id',
        'product_attribute_id',
        'qty_ordered',
    ];

    protected $casts = [
        'order_note_id' => 'integer',
        'product_id' => 'integer',
        'product_attribute_id' => 'integer',
        'qty_ordered' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function orderNote(): BelongsTo
    {
        return $this->belongsTo(OrderNote::class, 'order_note_id', 'id');
    }

    public function getQtyBilledTotalAttribute(): int
    {
        return (int) DB::table('oms_billed_order_lines as bol')
            ->join('oms_billed_orders as bo', 'bo.id', '=', 'bol.billed_order_id')
            ->leftJoin('oms_supplier_invoices as si', 'si.id', '=', 'bo.supplier_invoice_id')
            ->where('bo.order_note_id', $this->order_note_id)
            ->where(function ($query) {
                $query->where('bol.order_note_line_id', $this->id)
                    ->orWhere(function ($fallback) {
                        $fallback->whereNull('bol.order_note_line_id')
                            ->where('bol.product_id', $this->product_id)
                            ->where(function ($q) {
                                if (is_null($this->product_attribute_id)) {
                                    $q->whereNull('bol.product_attribute_id');
                                } else {
                                    $q->where('bol.product_attribute_id', $this->product_attribute_id);
                                }
                            });
                    });
            })
            ->where(function ($query) {
                $query->whereNull('si.status')
                    ->orWhere('si.status', '!=', 'cancelled');
            })
            ->sum('bol.qty_billed');
    }

    public function getQtyReceivedTotalAttribute(): int
    {
        return (int) DB::table('oms_reception_lines as rl')
            ->join('oms_billed_order_lines as bol', 'bol.id', '=', 'rl.billed_order_line_id')
            ->join('oms_billed_orders as bo', 'bo.id', '=', 'bol.billed_order_id')
            ->leftJoin('oms_supplier_invoices as si', 'si.id', '=', 'bo.supplier_invoice_id')
            ->where('bo.order_note_id', $this->order_note_id)
            ->where(function ($query) {
                $query->where('bol.order_note_line_id', $this->id)
                    ->orWhere(function ($fallback) {
                        $fallback->whereNull('bol.order_note_line_id')
                            ->where('bol.product_id', $this->product_id)
                            ->where(function ($q) {
                                if (is_null($this->product_attribute_id)) {
                                    $q->whereNull('bol.product_attribute_id');
                                } else {
                                    $q->where('bol.product_attribute_id', $this->product_attribute_id);
                                }
                            });
                    });
            })
            ->where(function ($query) {
                $query->whereNull('si.status')
                    ->orWhere('si.status', '!=', 'cancelled');
            })
            ->sum('rl.qty_received');
    }

    public function getRemainingToBillAttribute(): int
    {
        return max(0, (int) $this->qty_ordered - (int) $this->qty_billed_total);
    }
}
