<?php

namespace App\Models\modules\currency_price_update;

use Illuminate\Database\Eloquent\Model;

class CurrencyPriceUpdateLine extends Model
{
    protected $table = 'currency_price_update_lines';

    protected $guarded = [];

    protected $casts = [
        'purchase_updated' => 'boolean',
        'sale_updated' => 'boolean',
        'old_snapshot' => 'array',
        'new_snapshot' => 'array',
    ];
}
