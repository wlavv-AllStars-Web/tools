<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class order_state_lang extends PrestashopModel{
    use HasFactory;

    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = self::tableName('order_state_lang');
    }

    public function state(){
        return $this->belongsTo(order_state::class, 'id_order_state', 'id_order_state');
    }

    public function language(){
        return $this->belongsTo(language::class, 'id_lang', 'id_lang');
    }
}