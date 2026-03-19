<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\prestashop\order_return_state_lang;

class order_return_history extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';
    public $timestamps = false;
    protected $primaryKey = 'id_order_return_history';
    protected $fillable = [];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = env('DB2_prefix') . 'order_return_history';
    }

}
