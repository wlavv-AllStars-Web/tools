<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class customer extends PrestashopModel
{
    use HasFactory;
    protected $primaryKey = 'id_customer';
    protected $fillable = [];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = self::tableName('customer');
    }
    
    public function orders(){
        return $this->hasMany(orders::class, 'id_customer', 'id_customer');
    }
    
    public function carts(){
        return $this->hasMany(cart::class, 'id_customer', 'id_customer');
    }
}