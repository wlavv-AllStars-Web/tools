<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\prestashop\pack;
use App\Models\prestashop\stock_available;
use App\Models\prestashop\product_attribute_image;
use App\Models\modules\bms_procurement\bms_procurement_purchase_order_product;

use Illuminate\Support\Facades\Config;

class product_attribute extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['wholesale_price'];
    
    public function __construct()
    {
        $this->table = env('DB2_prefix')."product_attribute";
    }

    public function product()
    {
        return $this->hasOne(product::class, "id_product", 'id_product');
    }

    public function erp_invoiced()
    {

        return $this->hasOne(bms_procurement_purchase_order_product::class, 'sku', 'reference')
            ->selectRaw('sku, SUM(qty_wmfaturado) as qty_wmfaturado')
            ->groupBy('sku')
            ->where('qty_expected', '>', 0)
            ->groupBy('sku')
            ->withDefault([
                'qty_wmfaturado' => 0
            ]);
    }

    public function erp_expected()
    {

        return $this->hasOne(bms_procurement_purchase_order_product::class, 'sku', 'reference')
            ->selectRaw('sku, SUM(qty_expected) as qty_expected')
            ->groupBy('sku')
            ->where('qty_expected', '>', 0)
            ->groupBy('sku')
            ->withDefault([
                'qty_expected' => 0
            ]);
    }


    public static function getCombination($id_product_attribute){

        $combination = '';
        $attribute_name = null;
   
        if($id_product_attribute > 0){

            $data = DB::table(env('DB2_DB_prefix') . 'attribute_lang')
            ->select( env('DB2_DB_prefix') . 'attribute_lang.name')
            ->join(   env('DB2_DB_prefix') . 'product_attribute_combination', env('DB2_DB_prefix') . 'attribute_lang.id_attribute', '=', env('DB2_DB_prefix') . 'product_attribute_combination.id_attribute')
            ->join(   env('DB2_DB_prefix') . 'product_attribute', env('DB2_DB_prefix') . 'product_attribute_combination.id_product_attribute', '=', env('DB2_DB_prefix') . 'product_attribute.id_product_attribute')
            ->where(  env('DB2_DB_prefix') . 'product_attribute.id_product_attribute', $id_product_attribute )
            ->where(  env('DB2_DB_prefix') . 'attribute_lang.id_lang', 1 )
            ->get();

            foreach($data AS $item) $attribute_name .= $item->name. '; ';
            $combination =  substr($attribute_name, 0, -2);
        }

        return $combination;
    }

    public function stock(){
        return $this->hasOne(stock_available::class, "id_product_attribute", 'id_product_attribute');
    }

    public function pack(){
        return $this->hasMany(pack::class, "id_product_pack", 'id_product');
    }

    public function sold(){
        return $this->hasMany(orders_details::class, "product_attribute_id", 'id_product_attribute');
    }
    
    public static function dashboard_attribute_less_then_5_pics($type){
        
        $array_exceptions = asm_dashboard::getExceptions('marketing_no_images');
        
        $products = array();
        $no_images = product_attribute::select('ps_product.id_product', 'ps_product.id_category_default', DB::raw('count(*) AS nr_images'), 'ps_product_attribute.reference', 'ps_product_attribute.location AS housing', 'ps_manufacturer.name AS brand')
                    ->leftjoin('ps_product', 'ps_product_attribute.id_product', '=', 'ps_product.id_product')
                    ->leftjoin('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
                    ->leftjoin('ps_stock_available', 'ps_product_attribute.id_product_attribute', '=', 'ps_stock_available.id_product_attribute')
                    ->leftjoin('ps_product_attribute_image', 'ps_product_attribute.id_product_attribute', '=', 'ps_product_attribute_image.id_product_attribute')
                    ->whereNotIn('ps_product.id_product', $array_exceptions)
                    ->where('ps_stock_available.quantity', '>', 0)
                    ->where('visibility', '<>', 'none')
                    ->orderBy('ps_product.id_product')
                    ->groupBy('ps_product_attribute.reference')
                    ->get();  

        foreach($no_images AS $image){
            
            if((isset($image['id_product'])) && ($image['nr_images'] < 5 )){
            
                $products[$image['reference']]=[
                    'id_product' => $image['id_product'],
                    'id_category_default' => $image['id_category_default'],
                    'nr_images' => $image['nr_images'],
                    'reference' => $image['reference'],
                    'housing' => $image['housing'],
                    'brand' => $image['brand']
                ];
            }
            
        }
        
        return [
            'name'              => trans('dashboard.ATTRIBUTES - No 5 photos'),
            'col'               => 4,
            'item_id'           => $type . '_attributes_no_5_pics',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'brand', 'housing', 'nr_images'],
            'counter'           => count($products),
            'data'              => $products
        ];  
        
        return $products;
    }
    
}
