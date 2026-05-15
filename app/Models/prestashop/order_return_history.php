<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class order_return_history extends PrestashopModel{
    use HasFactory;

    protected $primaryKey = 'id_order_return_history';
    protected $fillable = [];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = self::tableName('order_return_history');
    }

    public function orderReturn(){
        return $this->belongsTo(order_return::class, 'id_order_return', 'id_order_return');
    }

    public function state(){
        return $this->belongsTo(order_return_state::class, 'id_order_return_state', 'id_order_return_state');
    }
}