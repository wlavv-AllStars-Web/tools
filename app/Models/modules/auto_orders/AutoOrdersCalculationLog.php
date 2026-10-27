<?php

namespace App\Models\modules\auto_orders;

use Illuminate\Database\Eloquent\Model;

class AutoOrdersCalculationLog extends Model
{
    protected $table = 'auto_orders_calculation_logs';

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];
}
