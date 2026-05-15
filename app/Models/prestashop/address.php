<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class address extends PrestashopModel
{
    use HasFactory;

    protected $fillable = ['name'];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = self::tableName('address');
    }

    public function country(){
        return $this->hasOne(country::class, 'id_country', 'id_country');
    }
}