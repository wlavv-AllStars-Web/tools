<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class cart_product extends PrestashopModel{
    
    use HasFactory;

    protected $fillable = [];

    public function __construct(array $attributes = []){
        
        parent::__construct($attributes);
        $this->table = self::tableName('cart_product');
    }
}