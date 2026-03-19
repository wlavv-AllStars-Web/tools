<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class order_history extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."order_history";
    }
    
    public static function getPanelInfo($array){

        return self::select('ps_order_history.id_order', 'ps_orders.reference',  DB::raw('count(id_order_state) AS total'), 'firstname', 'lastname')
            ->join('ps_orders', 'ps_order_history.id_order', '=', 'ps_orders.id_order')
            ->join('ps_customer', 'ps_orders.id_customer', '=', 'ps_customer.id_customer')
            ->where('ps_order_history.id_order_state', 2)
            ->whereNotIn('ps_order_history.id_order', $array)
            ->groupBy('ps_order_history.id_order')
            ->get();
    }
}
