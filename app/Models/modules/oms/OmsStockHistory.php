<?php

namespace App\Models\modules\oms;

class OmsStockHistory extends Model
{
    protected $table = 'oms_stock_history';

    public $timestamps = false;

    protected $fillable = [
        'source_type',
        'source_id',

        'order_note_id',
        'billed_order_id',
        'supplier_invoice_id',
        'reception_id',

        'product_id',
        'product_attribute_id',

        'ps_quantity_before',
        'ps_quantity_delta',
        'ps_quantity_after',

        'ps_quantity_arrive_before',
        'ps_quantity_arrive_delta',
        'ps_quantity_arrive_after',

        'user_id',
        'user_name_snapshot',
        'user_email_snapshot',

        'created_at',
    ];
}