<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class orders extends Model
{   
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."orders";
    }

    public static function counterPartialOrders(){

        $data = Orders::select('id_order')->where('current_state', 28)->get();

        return [
            'col'     => 2,
            'item_id' => 'counter_partials',
            'name'    => trans('Partial orders'),
            'counter' => count($data),
            'columns' => [trans('id_order'), trans('reference') ],
            'data'    => $data
        ];
    }

    public static function panelPartialOrders(){

        $data = Orders::select('id_order', 'reference')->where('current_state', 28)->get();

        return [
            'item_id' => 'panel_partials',
            'name'    => trans('Partial orders'),
            'counter' => count($data),
            'columns' => [trans('id_order'), trans('reference') ],
            'data'    => $data
        ];
    }

    public static function getParcials($product_reference){

        $tempData = DB::table(env('DB2_DB_prefix') . 'orders')
            ->join(       env('DB2_DB_prefix') . 'order_detail',     env('DB2_DB_prefix') . 'orders.id_order', '=', env('DB2_DB_prefix') . 'order_detail.id_order')
            ->where(      env('DB2_DB_prefix') . 'orders.current_state', 28 )
            ->where(      env('DB2_DB_prefix') . 'order_detail.product_reference', $product_reference )
            ->get();

        return json_decode($tempData, true);

    }

    public static function getPreparations($product_reference){

        $tempData = DB::table(env('DB2_DB_prefix') . 'orders')
            ->join(       env('DB2_DB_prefix') . 'order_detail',     env('DB2_DB_prefix') . 'orders.id_order', '=', env('DB2_DB_prefix') . 'order_detail.id_order')
            ->where(      env('DB2_DB_prefix') . 'orders.current_state', 3 )
            ->where(      env('DB2_DB_prefix') . 'order_detail.product_reference', $product_reference )
            ->get();

        return json_decode($tempData, true);

    }

    public static function getBackorders($product_reference){

        $tempData = DB::table(env('DB2_DB_prefix') . 'orders')
            ->join(       env('DB2_DB_prefix') . 'order_detail',     env('DB2_DB_prefix') . 'orders.id_order', '=', env('DB2_DB_prefix') . 'order_detail.id_order')
            ->where(      env('DB2_DB_prefix') . 'orders.current_state', 15 )
            ->where(      env('DB2_DB_prefix') . 'order_detail.product_reference', $product_reference )
            ->get();

        return json_decode($tempData, true);

    }
}
