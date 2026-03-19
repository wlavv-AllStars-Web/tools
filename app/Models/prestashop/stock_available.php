<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\prestashop\product;
use App\Models\prestashop\product_attribute;

use Illuminate\Support\Facades\Config;

class stock_available extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."stock_available";
    }

    public function product(){
        return $this->hasOne(product::class, "id_product", 'id_product');
    }

    public function product_attribute(){
        return $this->hasOne(product_attribute::class, "id_product_attribute", 'id_product_attribute');
    }

    public static function getStock($id_product, $id_product_attribute=0){
        return self::where('id_product', $id_product)->where('id_product_attribute', $id_product_attribute)->value('quantity');
    }

    public static function counterNegativeStock(){

        $tempData = DB::table(env('DB2_DB_prefix') . 'stock_available')
            ->join(       env('DB2_DB_prefix') . 'product',            env('DB2_DB_prefix') . 'stock_available.id_product', '=', env('DB2_DB_prefix') . 'product.id_product')
            ->select(     env('DB2_DB_prefix') . 'product.reference',  env('DB2_DB_prefix') . 'stock_available.quantity')
            ->where(      env('DB2_DB_prefix') . 'stock_available.quantity', '<', 0 )
            ->orderBy(    env('DB2_DB_prefix') . 'product.reference')
            ->groupBy(    env('DB2_DB_prefix') . 'product.reference')
            ->get();

        $data = json_decode($tempData, true);

        return [
            'col'     => 2,
            'item_id' => 'counter_negativeStock',
            'name'    => trans('dashboard.Negative stock'),
            'counter' => count($data),
            'columns' => [trans('tags.reference'), trans('tags.quantity') ],
            'data'    => $data
        ];
    }

    public static function dashboard_negative_stock($type){

        $data = array();

        $bd_data_product = stock_available::select( 'ps_product.reference',  'ps_stock_available.quantity', 'ps_stock_available.id_product')
            ->join( 'ps_product', 'ps_stock_available.id_product', '=', 'ps_product.id_product')
            ->where('id_product_attribute', 0 )
            ->where('ps_stock_available.quantity', '<', 0 )
            ->where('ps_product.reference', 'NOT LIKE', "%-Z" )
            ->orderBy('ps_stock_available.quantity', 'ASC')
            ->groupBy('ps_product.reference')
            ->get();

        foreach($bd_data_product AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'quantity' => $item->quantity];

        $bd_data_attr = stock_available::select('ps_stock_available.quantity', 'ps_stock_available.id_product', 'ps_product_attribute.reference')
            ->join('ps_product_attribute', 'ps_stock_available.id_product_attribute', '=', 'ps_product_attribute.id_product_attribute')
            ->where('ps_stock_available.quantity', '<', 0 )
            ->where('ps_stock_available.id_product_attribute', '<>', 0 )
            ->orderBy('ps_stock_available.quantity', 'ASC')
            ->groupBy('ps_product_attribute.reference')
            ->get();

        foreach($bd_data_attr AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'quantity' => $item->quantity];

        return [
            'name'              => trans('dashboard.Negative stock'),
            'col'               => 4,
            'item_id'           => $type . '_negative_stock',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'quantity'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_out_of_stock($type){

        $data = array();

        $bd_data = self::select('ps_stock_available.id_product', 'id_product_attribute', 'ps_stock_available.quantity')->with('product.manufacturer', 'product_attribute')
            ->join( 'ps_product', 'ps_stock_available.id_product', '=', 'ps_product.id_product')
            ->where('ps_stock_available.quantity', '<', 1)
            ->where('wmdeprecated', 0)
            ->get();

        foreach($bd_data AS $item){
            
            if( isset($item->product) ){
                    
                $attr_reference = '';
                $prod_reference = $item->product->reference;
                
                if( isset($item->product_attribute) ) $attr_reference = $item->product_attribute->reference;
                
                if($item->product->location <> 'ZZ-ZZ-ZZ') $data[] = ['id_product' => $item->id_product, 'reference' => (strlen($attr_reference)) ? $attr_reference : $prod_reference, 'quantity' => $item->quantity];

            }

        }
        return [
            'name'              => trans('dashboard.Out of stock'),
            'col'               => 4,
            'item_id'           => $type . '_out_of_stock',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'quantity'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_no_sales($type){

        $data = array();

        $bd_data = stock_available::select( 'ps_stock_available.id_product',  'ps_stock_available.id_product_attribute', 'ps_product.reference', 'ps_product.cache_is_pack', DB::RAW('ps_product_attribute.reference AS attr_reference'), DB::RAW('ps_manufacturer.name AS brand'), 'ps_stock_available.quantity')
            ->leftJoin( 'ps_product', 'ps_stock_available.id_product', '=', 'ps_product.id_product')
            ->leftJoin('ps_product_attribute', 'ps_stock_available.id_product_attribute', '=', 'ps_product_attribute.id_product_attribute')
            ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->where( 'ps_stock_available.out_of_stock', '=', 0 )
            ->where( 'ps_product.visibility', '!=', 'none' )
            ->whereNotIn('ps_product.id_manufacturer', [ 27, 47, 51, 76, 86, 89, 91, 115, 116, 120, 126, 127, 133, 135, 136, 140, 144, 151, 152, 156, 159, 162, 164, 170, 171, 172, 187 ])
            ->orderBy( 'ps_stock_available.quantity', 'ASC')
            ->get();

        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => ( isset($item->attr_reference) ) ? $item->attr_reference : $item->reference, 'quantity' => $item->quantity, 'brand' => $item->brand];

        return [
            'name'              => trans('dashboard.No Sales'),
            'col'               => 4,
            'item_id'           => $type . '_no_sales',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'quantity', 'reference', 'brand'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }
}