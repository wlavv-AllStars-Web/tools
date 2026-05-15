<?php

namespace App\Models\prestashop;

class custom_product extends PrestashopModel
{
    protected $connection = 'mysql2';
    protected $table = 'ps_custom_product';
    protected $primaryKey = 'id_product';
    public $incrementing = false;

    protected $casts = [
        'stock_arrive' => 'integer',
    ];
}
