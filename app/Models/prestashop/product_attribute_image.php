<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class product_attribute_image extends PrestashopModel
{
    use HasFactory;

    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('product_attribute_image');
    }

    public function productAttribute()
    {
        return $this->belongsTo(product_attribute::class, 'id_product_attribute', 'id_product_attribute');
    }

    public function image()
    {
        return $this->belongsTo(image::class, 'id_image', 'id_image');
    }
}