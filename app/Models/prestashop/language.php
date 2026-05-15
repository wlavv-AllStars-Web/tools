<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class language extends PrestashopModel{
    
    use HasFactory;

    protected $primaryKey = 'id_lang';
    protected $fillable = ['name'];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = self::tableName('lang');
    }

    public function countries(){
        return $this->hasMany(country_lang::class, 'id_lang', 'id_lang');
    }

    public function categories(){
        return $this->hasMany(category_lang::class, 'id_lang', 'id_lang');
    }

    public function products(){
        return $this->hasMany(product_lang::class, 'id_lang', 'id_lang');
    }
}