<?php

namespace App\Models\modules\oms;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\prestashop\suppliers;
use App\Models\modules\shipping\shipping;

class SupplierInvoice extends BaseOmsModel
{
    protected $table = 'oms_supplier_invoices';

    protected $fillable = [
        'supplier_id',
        'shipment_id',
        'invoice_reference',
        'invoice_date',
        'due_date',
        'currency_id',
        'currency_iso',
        'conversion_rate',
        'status',
        'internal_note',
        'logistic_note',
    ];

    protected $casts = [
        'supplier_id' => 'integer',
        'shipment_id' => 'integer',
        'currency_id' => 'integer',
        'conversion_rate' => 'decimal:6',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(suppliers::class, 'supplier_id', 'id_supplier');
    }

    public function billedOrders(): HasMany
    {
        return $this->hasMany(BilledOrder::class, 'supplier_invoice_id', 'id');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(shipping::class, 'shipment_id');
    }

    public function getHasAnyNoteAttribute(): bool
    {
        return !empty($this->internal_note) || !empty($this->logistic_note);
    }

    public function getIsDraftAttribute(): bool
    {
        return ($this->status ?? 'draft') === 'draft';
    }
}
