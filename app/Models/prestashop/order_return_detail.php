<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class order_return_detail extends PrestashopModel{
    use HasFactory;

    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = ['files_required'];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = self::tableName('order_return_detail');
    }

    public function return(){
        return $this->belongsTo(order_return::class, 'id_order_return', 'id_order_return');
    }

    public function orderDetail(){
        return $this->belongsTo(orders_details::class, 'id_order_detail', 'id_order_detail');
    }
    
}