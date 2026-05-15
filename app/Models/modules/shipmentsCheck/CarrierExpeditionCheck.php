<?php

namespace App\Models\modules\shipmentsCheck;

use Illuminate\Database\Eloquent\Model;

class CarrierExpeditionCheck extends Model
{
    protected $table = 'carrier_expedition_check';

    protected $fillable = [
        'carrier_name',
        'shipments',
        'non_standard',
        'qty_checked',
        'note',
        'user_id',
        'check_date'
    ];
}