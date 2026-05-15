<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class categories_lang extends PrestashopModel{
    
    use HasFactory;

    protected $fillable = [];

    public function __construct(array $attributes = []){
        
        parent::__construct($attributes);
        $this->table = self::tableName('category_lang');
    }
}