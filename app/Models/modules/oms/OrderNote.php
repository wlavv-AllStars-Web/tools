<?php

namespace App\Models\modules\oms;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\prestashop\suppliers;

class OrderNote extends BaseOmsModel
{
    protected $table = 'oms_order_notes';

    protected $fillable = [
        'supplier_id',
        'reference',
        'status',
        'internal_note',
        'logistic_note',
    ];

    protected $casts = [
        'supplier_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(suppliers::class, 'supplier_id', 'id_supplier');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(OrderNoteLine::class, 'order_note_id', 'id');
    }

    public function billedOrders(): HasMany
    {
        return $this->hasMany(BilledOrder::class, 'order_note_id', 'id');
    }

    public function shippingLinks(): HasMany
    {
        return $this->hasMany(ShippingErp::class, 'id_erp', 'id');
    }

    public function getTotalOrderedAttribute(): int
    {
        return (int) $this->lines->sum('qty_ordered');
    }

    public function getTotalBilledAttribute(): int
    {
        return (int) $this->lines->sum(fn ($line) => (int) ($line->qty_billed_total ?? 0));
    }

    public function getTotalReceivedAttribute(): int
    {
        return (int) $this->lines->sum(fn ($line) => (int) ($line->qty_received_total ?? 0));
    }

    public function getTotalRemainingToBillAttribute(): int
    {
        return max(0, $this->total_ordered - $this->total_billed);
    }

    public function getCanDeleteAttribute(): bool
    {
        return $this->status === 'order_note'
            && $this->lines()->count() === 0
            && $this->billedOrders()->count() === 0;
    }

    public function getHasAnyNoteAttribute(): bool
    {
        return !empty($this->internal_note) || !empty($this->logistic_note);
    }
}
