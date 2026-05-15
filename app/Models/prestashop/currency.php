<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class currency extends PrestashopModel{
    use HasFactory;

    protected $fillable = ['name'];
    protected $primaryKey = 'id_currency';

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = self::tableName('currency');
    }

    public function shops(){
        return $this->hasMany(currency_shop::class, 'id_currency', 'id_currency');
    }
}