<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class country_lang extends PrestashopModel{
    
    use HasFactory;

    protected $fillable = ['name'];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = self::tableName('country_lang');
    }

    public function country(){
        return $this->belongsTo(country::class, 'id_country', 'id_country');
    }
}