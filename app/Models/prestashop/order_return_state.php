<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class order_return_state extends PrestashopModel{
    use HasFactory;

    protected $primaryKey = 'id_order_return_state';
    protected $fillable = [];
    
    protected $fallBackLang = 2; /** EN **/

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = self::tableName('order_return_state');
    }

    public function langs(){        return $this->hasMany(order_return_state_lang::class, 'id_order_return_state', 'id_order_return_state'); }
    public function lang(){         return $this->hasOne(order_return_state_lang::class, 'id_order_return_state', 'id_order_return_state')->where('id_lang', $this->fallBackLang); }
    public function histories(){    return $this->hasMany(order_return_history::class, 'id_order_return_state', 'id_order_return_state'); }
}