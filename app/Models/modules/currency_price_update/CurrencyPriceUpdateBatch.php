<?php

namespace App\Models\modules\currency_price_update;

use Illuminate\Database\Eloquent\Model;

class CurrencyPriceUpdateBatch extends Model
{
    protected $table = 'currency_price_update_batches';

    protected $guarded = [];

    protected $casts = [
        'reverted_at' => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(CurrencyPriceUpdateLine::class, 'batch_id');
    }
}
