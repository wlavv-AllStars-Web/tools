<?php

namespace App\Models\prestashop;

class custom_product_attribute extends PrestashopModel
{
    protected $connection = 'mysql2';
    protected $table = 'ps_custom_product_attribute';
    protected $primaryKey = 'id_product_attribute';
    public $incrementing = false;

    protected $casts = [
        'stock_arrive' => 'integer',
    ];
}
