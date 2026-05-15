<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class customers_group extends PrestashopModel{
    
    use HasFactory;
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = self::tableName('customer_group');
    }

    public function customer(){
        return $this->belongsTo(customer::class, 'id_customer', 'id_customer');
    }

    public function group(){
        return $this->belongsTo(group::class, 'id_group', 'id_group');
    }
}