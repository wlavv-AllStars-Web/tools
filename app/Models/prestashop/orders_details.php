<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\prestashop\orders;

use Illuminate\Support\Facades\Config;

class orders_details extends Model
{   
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."order_detail";
    }

    public function product(){
        return $this->hasOne(product::class, "id_product", 'product_id');
    }

    public function product_attribute(){
        return $this->hasMany(product_attribute::class, "id_product_attribute", 'product_attribute_id');
    }


    public static function getSoldOf($product_reference, $attr_reference = ''){

        $reference = (strlen($attr_reference) > 0) ? $attr_reference : $product_reference;

        return DB::table(env('DB2_DB_prefix') . 'order_detail')
        ->join(       env('DB2_DB_prefix') . 'orders',     env('DB2_DB_prefix') . 'orders.id_order', '=', env('DB2_DB_prefix') . 'order_detail.id_order')
        ->where(      env('DB2_DB_prefix') . 'orders.date_add', '>', date('Y-m-d', strtotime('-1 year')) )
        ->where(      env('DB2_DB_prefix') . 'order_detail.product_reference', $reference )
        ->whereIn(      env('DB2_DB_prefix') . 'orders.current_state', [2, 3, 4, 5, 15, 16, 28] )
        ->sum('product_quantity');

    }

    public static function getSoldByIDOf($id_product, $id_product_attribute = 0){

        return DB::table(env('DB2_DB_prefix') . 'order_detail')
        ->join(       env('DB2_DB_prefix') . 'orders',     env('DB2_DB_prefix') . 'orders.id_order', '=', env('DB2_DB_prefix') . 'order_detail.id_order')
        ->where(      env('DB2_DB_prefix') . 'orders.date_add', '>', date('Y-m-d', strtotime('-1 year')) )
        ->where(      env('DB2_DB_prefix') . 'order_detail.product_id', $id_product )
        ->where(      env('DB2_DB_prefix') . 'order_detail.product_attribute_id', $id_product_attribute )
        ->whereIn(      env('DB2_DB_prefix') . 'orders.current_state', [2, 3, 4, 5, 15, 16, 28] )
        ->sum('product_quantity');

    }

    public static function getSoldByRefOf($reference){

        return DB::table(env('DB2_DB_prefix') . 'order_detail')
        ->join(       env('DB2_DB_prefix') . 'orders',     env('DB2_DB_prefix') . 'orders.id_order', '=', env('DB2_DB_prefix') . 'order_detail.id_order')
        ->where(      env('DB2_DB_prefix') . 'orders.date_add', '>', date('Y-m-d', strtotime('-1 year')) )
        ->where(      env('DB2_DB_prefix') . 'order_detail.product_reference', $reference )
        ->whereIn(      env('DB2_DB_prefix') . 'orders.current_state', [2, 3, 4, 5, 15, 16, 28] )
        ->sum('product_quantity');

    }

    public static function getProductsOfOrder($id_order){
        
        $list = '';
        $products = self::select('product_reference')->where('id_order', $id_order)->get();
        
        foreach($products AS $product) $list .= $product->product_reference . ', ';
        
        return substr($list, 0, -2);
    }

    public static function dashboard_order_with_voucher($type){

        $data = array();

        $prefix = env('DB2_DB_prefix');

        $array = asm_dashboard::getExceptions('order_with_voucher');
        
        $bd_data = self::select(
                "{$prefix}orders.id_order",
                "{$prefix}orders.reference",
                "{$prefix}order_detail.product_reference"
            )
            ->join("{$prefix}orders", "{$prefix}orders.id_order", '=', "{$prefix}order_detail.id_order")
            ->where("{$prefix}order_detail.product_reference", 'LIKE', '%voucher%')
            ->whereNotIn('ps_orders.id_order', $array)
            ->get();


        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_order, 'id_order' => $item->id_order, 'reference' => $item->reference, 'product_reference' => $item->product_reference];
        
        return [
            'name'              => trans('dashboard.ORDERS - WAITING INFO'),
            'col'               => 4,
            'item_id'           => $type . '_order_with_voucher',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder' ] : [],
            'columns'           => ['clean', 'id_order', 'reference', 'product_reference'],
            'counter'           => count($data),
            'exception_fields'  => ['order_with_voucher', 'id_order', 'reference', 'product_reference'],
            'data'              => $data
        ];        
    } 

    public static function dashboard_order_with_voucher_sales($type){

        $data = array();

        $prefix = env('DB2_DB_prefix');

        $array = asm_dashboard::getExceptions('order_with_voucher_sales');
        
        $bd_data = self::select(
                "{$prefix}orders.id_order",
                "{$prefix}orders.reference",
                "{$prefix}order_detail.product_reference"
            )
            ->join("{$prefix}orders", "{$prefix}orders.id_order", '=', "{$prefix}order_detail.id_order")
            ->where("{$prefix}order_detail.product_reference", 'LIKE', '%voucher%')
            ->whereNotIn('ps_orders.id_order', $array)
            ->get();


        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_order, 'id_order' => $item->id_order, 'reference' => $item->reference, 'product_reference' => $item->product_reference];
        
        return [
            'name'              => trans('dashboard.ORDERS - WAITING INFO'),
            'col'               => 4,
            'item_id'           => $type . '_order_with_voucher',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder' ] : [],
            'columns'           => ['clean', 'id_order', 'reference', 'product_reference'],
            'counter'           => count($data),
            'exception_fields'  => ['order_with_voucher_sales', 'id_order', 'reference', 'product_reference'],
            'data'              => $data
        ];        
    } 
    
}
