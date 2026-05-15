<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class product_attribute_shop extends PrestashopModel
{
    use HasFactory;

    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = ['wholesale_price'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('product_attribute_shop');
    }

    public function productAttribute()
    {
        return $this->belongsTo(product_attribute::class, 'id_product_attribute', 'id_product_attribute');
    }

    public function shop()
    {
        return $this->belongsTo(shop::class, 'id_shop', 'id_shop');
    }
}