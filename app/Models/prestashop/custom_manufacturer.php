<?php

namespace App\Models\prestashop;

class custom_manufacturer extends PrestashopModel
{
    protected $connection = 'mysql2';
    protected $table = 'ps_custom_manufacturer';
    protected $primaryKey = 'id_manufacturer';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_manufacturer',
        'site_url',
        'youtube',
        'id_currency',
        'warranty_by_them',
        'claims_link',
    ];

    protected $casts = [
        'id_manufacturer' => 'integer',
        'id_currency' => 'integer',
        'warranty_by_them' => 'boolean',
    ];
}
