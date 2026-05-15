<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class currency_shop extends PrestashopModel{
    
    use HasFactory;

    protected $fillable = ['name'];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = self::tableName('currency_shop');
    }

    public function currency(){
        return $this->belongsTo(currency::class, 'id_currency', 'id_currency');
    }
}