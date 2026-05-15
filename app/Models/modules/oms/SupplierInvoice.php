<?php

namespace App\Models\modules\oms;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\prestashop\suppliers;

class SupplierInvoice extends BaseOmsModel
{
    protected $table = 'oms_supplier_invoices';

    protected $fillable = [
        'supplier_id',
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

    public function getHasAnyNoteAttribute(): bool
    {
        return !empty($this->internal_note) || !empty($this->logistic_note);
    }

    public function getIsDraftAttribute(): bool
    {
        return ($this->status ?? 'draft') === 'draft';
    }
}
