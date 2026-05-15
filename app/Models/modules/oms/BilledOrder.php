<?php

namespace App\Models\modules\oms;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BilledOrder extends BaseOmsModel
{
    protected $table = 'oms_billed_orders';

    protected $fillable = [
        'order_note_id',
        'supplier_invoice_id',
        'reference',
        'status',
        'internal_note',
        'logistic_note',
    ];

    protected $casts = [
        'order_note_id' => 'integer',
        'supplier_invoice_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function orderNote(): BelongsTo
    {
        return $this->belongsTo(OrderNote::class, 'order_note_id', 'id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class, 'supplier_invoice_id', 'id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(BilledOrderLine::class, 'billed_order_id', 'id');
    }

    public function receptions(): HasMany
    {
        return $this->hasMany(Reception::class, 'billed_order_id', 'id');
    }

    public function getHasAnyNoteAttribute(): bool
    {
        return !empty($this->internal_note) || !empty($this->logistic_note);
    }
}
