<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class country extends PrestashopModel{
    
    use HasFactory;

    protected $fillable = ['name'];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = self::tableName('country');
    }
    
    public function lang(){
        return $this->hasMany(country_lang::class, 'id_country', 'id_country');
    }

    public function lang_en(){
        return $this->hasOne(country_lang::class, 'id_country', 'id_country')->where('id_lang', 2);
    }
}