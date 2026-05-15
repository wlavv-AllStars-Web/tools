<?php

namespace App\Models\modules\oms;

use Illuminate\Database\Eloquent\Model;

class OmsDocumentLineHistory extends Model
{
    protected $table = 'oms_document_line_history';

    public $timestamps = false;

    protected $fillable = [
        'context_type',
        'context_id',

        'order_note_id',
        'billed_order_id',
        'supplier_invoice_id',
        'reception_id',

        'product_id',
        'product_attribute_id',

        'product_reference_snapshot',
        'attribute_reference_snapshot',
        'display_reference_snapshot',

        'invoice_currency_id',
        'invoice_currency_iso',
        'conversion_rate_used',
        'purchase_conversion_rate_used',
        'sale_conversion_rate_used',

        'unit_price_invoice_currency',
        'unit_price_eur',
        'qty',

        'old_purchase_supplier_currency',
        'new_purchase_supplier_currency',
        'old_purchase_eur',
        'new_purchase_eur',

        'old_sale_supplier_currency',
        'new_sale_supplier_currency',
        'old_sale_eur',
        'new_sale_eur',

        'old_wholesale_price_eur',
        'new_wholesale_price_eur',

        'user_id',
        'user_name_snapshot',
        'user_email_snapshot',

        'created_at',
    ];

    protected $casts = [
        'context_id' => 'integer',
        'order_note_id' => 'integer',
        'billed_order_id' => 'integer',
        'supplier_invoice_id' => 'integer',
        'reception_id' => 'integer',
        'product_id' => 'integer',
        'product_attribute_id' => 'integer',
        'invoice_currency_id' => 'integer',
        'qty' => 'integer',
        'user_id' => 'integer',

        'conversion_rate_used' => 'decimal:6',
        'purchase_conversion_rate_used' => 'decimal:6',
        'sale_conversion_rate_used' => 'decimal:6',
        'unit_price_invoice_currency' => 'decimal:6',
        'unit_price_eur' => 'decimal:6',
        'old_purchase_supplier_currency' => 'decimal:6',
        'new_purchase_supplier_currency' => 'decimal:6',
        'old_purchase_eur' => 'decimal:6',
        'new_purchase_eur' => 'decimal:6',
        'old_sale_supplier_currency' => 'decimal:6',
        'new_sale_supplier_currency' => 'decimal:6',
        'old_sale_eur' => 'decimal:6',
        'new_sale_eur' => 'decimal:6',
        'old_wholesale_price_eur' => 'decimal:6',
        'new_wholesale_price_eur' => 'decimal:6',

        'created_at' => 'datetime',
    ];
}
