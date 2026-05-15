<?php

namespace App\Models\prestashop;

use App\Models\modules\ToolsModel;

class CurrencyVariation extends ToolsModel
{
    protected $table = 'currency_variation';

    protected $fillable = [
        'usd',
        'pound',
        'yen',
        'yuan',
        'date',
    ];

    protected $casts = [
        'usd' => 'float',
        'pound' => 'float',
        'yen' => 'float',
        'yuan' => 'float',
        'date' => 'date',
    ];
}
