<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Arr;

use App\Models\prestashop\image;
use App\Models\prestashop\accessory;
use App\Models\prestashop\suppliers;
use App\Models\prestashop\manufacturers;
use App\Models\prestashop\product_attachment;
use App\Models\prestashop\stock_available;
use App\Models\prestashop\product_attribute;
use App\Models\prestashop\orders_details;
use App\Models\prestashop\specific_price;
use App\Models\modules\ukoocompat\ukoocompat_compat;

use App\Models\modules\bms_procurement\bms_procurement_purchase_order;
use App\Models\modules\bms_procurement\bms_procurement_purchase_order_product;

use Carbon\Carbon;

class product extends Model{
    
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['wholesale_price'];

    public function __construct(){
        $this->table = env('DB2_prefix')."product";
    }

    public function product_lang(){
        return $this->hasOne(product_lang::class, "id_product", 'id_product');
    }

    public function pack(){
        return $this->hasMany(pack::class, "id_product_pack", 'id_product');
    }

    public function lang(){
        return $this->hasOne(product_lang::class, "id_product", 'id_product')->where('id_lang', 1);
    }

    public function manufacturer(){
        return $this->hasOne(manufacturers::class, "id_manufacturer", 'id_manufacturer');
    }

    public function supplier(){
        return $this->hasOne(suppliers::class, "id_supplier", 'id_supplier');
    }

    public function attribute()
    {
        return $this->hasMany(product_attribute::class, "id_product", 'id_product');
    }

    public function stock()
    {
        return $this->hasOne(stock_available::class, "id_product", 'id_product')->where('id_product_attribute', 0);
    }

    public function discount()
    {
        return $this->hasOne(specific_price::class, "id_product", 'id_product')->where('id_product_attribute', 0);
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

    public function stock_product_pack()
    {
        return $this->hasOne(stock_available::class, 'id_product', 'id_product')
            ->when($this->id_product_attribute, function ($query) {
                $query->where('id_product_attribute', $this->id_product_attribute);
            });
    }

    public function sold()
    {
        return $this->hasMany(orders_details::class, "product_id", 'id_product');
    }

    public static function dashboard_ec_approved($type){

        $data = array();
        $array = asm_dashboard::getExceptions('ec_approved');
        
        $bd_data = self::select('id_product', 'id_manufacturer', 'reference')->with('manufacturer')->where('upc', 0)->where('active', 1)->where('visibility', '<>', 'none')->whereNotIn('id_product', $array)->groupBy('id_product')->get();

        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_product, 'id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->manufacturer->name];
        
        return [
            'name'              => trans('dashboard.EC approved'),
            'col'               => 4,
            'item_id'           => $type . '_ec_approved',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['clean', 'id_product', 'reference', 'brand'],
            'exception_fields'  => ['ec_approved', 'id_product', 'reference', 'brand'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_universal_products($type){

        $data = array();
        $array = asm_dashboard::getExceptions('universal_products');
        
        $bd_data = self::select('id_product', 'id_manufacturer', 'reference')->with('manufacturer')->where('universal', 1)->whereNotIn('id_product', $array)->groupBy('id_product')->get();

        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_product, 'id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->manufacturer->name];
        
        return [
            'name'              => trans('dashboard.Universal'),
            'col'               => 4,
            'item_id'           => $type . '_universal_products',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['clean', 'id_product', 'reference', 'brand'],
            'exception_fields'  => ['universal_products', 'id_product', 'reference', 'brand'], 
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_shipping_restrictions($type){

        $data = array();
        $array = asm_dashboard::getExceptions('shipping_restrictions');
        
        $bd_data = self::select('id_product', 'id_manufacturer', 'reference')->with('manufacturer')->where('shipping_restrictions', 1)->whereNotIn('id_product', $array)->groupBy('id_product')->get();

        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_product, 'id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->manufacturer->name];
        
        return [
            'name'              => trans('dashboard.Shipping restrictions'),
            'col'               => 4,
            'item_id'           => $type . '_shipping_restrictions',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['clean', 'id_product', 'reference', 'brand'],
            'exception_fields'  => ['shipping_restrictions', 'id_product', 'reference', 'brand'], 
            'exception'         => 'shipping_restrictions',
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_no_compatibilities($type){

        $data = array();
        $array = asm_dashboard::getExceptions('no_compatibilities');
        
        $ukoo_ids = ukoocompat_compat::select('id_product')->groupBy('id_product')->get();

        $bd_data = self::select('id_product', 'id_manufacturer', 'reference')->with('manufacturer')->where('active', 1)->where('universal', 0)->where('visibility', '<>', 'none')->whereNotIn('id_product', $ukoo_ids)->whereNotIn('id_product', $array)->groupBy('id_product')->get();

        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_product, 'id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->manufacturer->name];
        
        return [
            'name'              => trans('dashboard.No compatibilities'),
            'col'               => 4,
            'item_id'           => $type . '_no_compatibilities',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['clean', 'id_product', 'reference', 'brand'],
            'exception_fields'  => ['no_compatibilities', 'id_product', 'reference', 'brand'],   
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_compatibilities_exception($type){

        $data = array();
        $array = asm_dashboard::getExceptions('compatibilities_exception');
        
        $bd_data = self::select('id_product', 'id_manufacturer', 'reference')->with('manufacturer')->where('show_compat_exception', 1)->whereNotIn('id_product', $array)->groupBy('id_product')->get();

        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_product, 'id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->manufacturer->name];
        
        return [
            'name'              => trans('dashboard.Compatibilities exceptions'),
            'col'               => 4,
            'item_id'           => $type . '_compatibilities_exception',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['clean', 'id_product', 'reference', 'brand'],
            'exception_fields'  => ['compatibilities_exception', 'id_product', 'reference', 'brand'],            
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_without_brand($type){

        $data = array();

        $bd_data = self::select('id_product', 'id_manufacturer', 'reference')->where('id_manufacturer', 0)->get();

        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference];
        
        return [
            'name'              => trans('dashboard.Product without brand'),
            'col'               => 4,
            'item_id'           => $type . '_without_brand',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_without_category($type){

        $data = array();
        $bd_data = self::select('id_product', 'id_manufacturer', 'reference')->with('manufacturer')->where('id_category_default', 0)->groupBy('id_product')->get();

        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->manufacturer->name];
        
        return [
            'name'              => trans('dashboard.Product without category'),
            'col'               => 4,
            'item_id'           => $type . '_without_category',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'brand'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_no_instructions($type){

        $data = array();
        $array = asm_dashboard::getExceptions('no_instructions');
        
        $array_attachments = product_attachment::select('id_product')->groupBy('id_product')->get();

        $bd_data = self::select('id_product', 'ps_product.id_manufacturer', 'reference')
                            ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
                            ->where('visibility', '<>', 'none')->whereNotIn('id_product', $array_attachments)->whereNotIn('id_product', $array)->whereNotIn('ps_product.id_manufacturer', [104,109,127,139,153])->groupBy('id_product')->orderBy('ps_product.id_manufacturer')->get();

        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_product, 'id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->manufacturer->name];
        
        return [
            'name'              => trans('dashboard.No instructions'),
            'col'               => 4,
            'item_id'           => $type . '_no_instructions',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['clean', 'id_product', 'reference', 'brand'],
            'exception_fields'  => ['no_instructions', 'id_product', 'reference', 'brand'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_no_real_photos($type){

        $data = array();
        $bd_data = self::select('ps_product.id_product', 'id_manufacturer', 'reference')->join('ps_stock_available', 'ps_product.id_product', '=', 'ps_stock_available.id_product')->with('manufacturer')->where('ps_stock_available.quantity', '>', 0)->where('real_photos', 0)->where('visibility', '<>', 'none')->orderBy('id_product')->groupBy('reference')->get();
        
        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->manufacturer->name];
        
        return [
            'name'              => trans('dashboard.No real photos'),
            'col'               => 4,
            'item_id'           => $type . '_no_real_photos',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'brand'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

public static function dashboard_product_less_then_5_pics($type)
{
    $products = product::select(
                    'ps_product.id_product',
                    'ps_product.id_category_default',
                    DB::raw('COUNT(ps_image.id_image) AS nr_images'),
                    'ps_product.reference',
                    'ps_product.housing',
                    'ps_manufacturer.name AS brand'
                )
                ->leftJoin('ps_image', 'ps_product.id_product', '=', 'ps_image.id_product')
                ->leftJoin('ps_stock_available', function ($join) {
                    $join->on('ps_product.id_product', '=', 'ps_stock_available.id_product')
                         ->where('ps_stock_available.id_product_attribute', 0);
                })
                ->leftJoin('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
                ->where('ps_stock_available.quantity', '>', 0)
                ->where('ps_product.visibility', '<>', 'none')
                ->where('ps_product.id_category_default', '<>', 526)
                ->groupBy(
                    'ps_product.id_product',
                    'ps_product.id_category_default',
                    'ps_product.reference',
                    'ps_product.housing',
                    'ps_manufacturer.name'
                )
                ->havingRaw('COUNT(ps_image.id_image) < 5')
                // ordenação garantida no SQL (numérica)
                ->orderByRaw('CAST(ps_product.id_product AS UNSIGNED) ASC')
                ->get();

    // ordenação garantida novamente em PHP (caso exista algum reorder noutro ponto)
    $products = $products
                    ->sortBy('id_product', SORT_NUMERIC)
                    ->values()
                    ->toArray();

    return [
        'name'       => trans('dashboard.PRODUCTS - No 5 photos'),
        'col'        => 4,
        'item_id'    => $type . '_products_no_5_pics',
        'prestashop' => (isset(Config::get('token')->AdminProducts)) ? [
                            'token' => Config::get('token')->AdminProducts,
                            'controller' => 'AdminProducts',
                            'element' => 'id_product',
                            'extraParameters' => '&updateproduct'
                        ] : [],
        'columns'    => ['id_product', 'reference', 'brand', 'housing', 'nr_images'],
        'counter'    => count($products),
        'data'       => $products
    ];
}


    public static function dashboard_no_ean($type){

        $data = array();

        $bd_data_product = self::select('ps_product.id_product', 'ps_manufacturer.name AS name', 'ps_product.reference')
            ->join('ps_stock_available', 'ps_product.id_product', '=', 'ps_stock_available.id_product')
            ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->where('ps_product.ean13', '')
            ->where('ps_stock_available.quantity', '>', '0')
            ->groupBy('ps_product.id_product')
            ->get();

        $bd_data_attribute = product_attribute::select('ps_product_attribute.id_product', 'ps_manufacturer.name AS name', 'ps_product_attribute.reference')
            ->join('ps_product', 'ps_product_attribute.id_product', '=', 'ps_product.id_product')
            ->join('ps_stock_available', 'ps_product_attribute.id_product_attribute', '=', 'ps_stock_available.id_product_attribute')
            ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->where('ps_product_attribute.ean13', '')
            ->where('ps_stock_available.quantity', '>', '0')
            ->groupBy('ps_product_attribute.id_product')
            ->get();

        $bd_data = array();
                    
        foreach($bd_data_product AS $data_product) $bd_data[] = $data_product;
        foreach($bd_data_attribute AS $data_attribute) $bd_data[] = $data_attribute;

        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->reference];
        
        return [
            'name'              => trans('dashboard.NO EAN'),
            'col'               => 4,
            'item_id'           => $type . '_no_ean',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_no_housing($type){

        $data = array();

        $bd_data_product = self::select('ps_product.id_product', 'ps_manufacturer.name AS name', 'ps_product.reference')
            ->join('ps_stock_available', 'ps_product.id_product', '=', 'ps_stock_available.id_product')
            ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->where('ps_product.housing', '')
            ->where('ps_product.active', 1)
            ->where('ps_stock_available.quantity', '>', '0')
            ->groupBy('ps_product.id_product')
            ->get();

        $bd_data_attribute = product_attribute::select('ps_product_attribute.id_product', 'ps_manufacturer.name AS name', 'ps_product_attribute.reference')
            ->join('ps_product', 'ps_product_attribute.id_product', '=', 'ps_product.id_product')
            ->join('ps_stock_available', 'ps_product_attribute.id_product_attribute', '=', 'ps_stock_available.id_product_attribute')
            ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->where('ps_product_attribute.location', '')
            ->where('ps_product.active', 1)
            ->where('ps_stock_available.quantity', '>', '0')
            ->groupBy('ps_product_attribute.id_product')
            ->get();

        $bd_data = array();
                    
        foreach($bd_data_product AS $data_product) $bd_data[] = $data_product;
        foreach($bd_data_attribute AS $data_attribute) $bd_data[] = $data_attribute;

        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->reference];
        
        return [
            'name'              => trans('dashboard.NO HOUSING'),
            'col'               => 4,
            'item_id'           => $type . '_no_housing',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_no_size($type){

        $data = array();

        $bd_data = self::select('ps_product.id_product', 'ps_product.id_manufacturer', 'reference')
            ->join('ps_stock_available', 'ps_product.id_product', '=', 'ps_stock_available.id_product')
            ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->where('dim_verify', 0)
            ->where('ps_stock_available.quantity', '>', 0)
            ->where('ps_product.active', 1)
            ->groupBy('ps_product.id_product')
            ->orderBy('ps_product.id_manufacturer')
            ->get();

        foreach($bd_data AS $item) $data[] = [ 'id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->name];
        
        return [
            'name'              => trans('dashboard.Measures unverified'),
            'col'               => 4,
            'item_id'           => $type . '_unverified_measures',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'brand'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_no_weight($type){

        $data = array();

        $bd_data = self::select('id_product', 'ps_product.id_manufacturer', 'reference')
            ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->where('weight', 0)
            ->groupBy('id_product')
            ->orderBy('ps_product.id_manufacturer')
            ->get();

        foreach($bd_data AS $item) $data[] = [ 'id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->name];
        
        return [
            'name'              => trans('dashboard.Weight 0'),
            'col'               => 4,
            'item_id'           => $type . '_no_weight',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'brand'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_qtd_arrive($type){

        $data = array();

        $bd_data = self::select('id_product', 'reference', 'stock_arrive', 'ps_manufacturer.name')
            ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->where('stock_arrive', '<', 0)
            ->where('reference', 'not like', "%-Z")
            ->groupBy('reference')
            ->orderBy('ps_product.id_manufacturer')
            ->get();
    		
        $bd_data_attr = product_attribute::select('ps_product_attribute.id_product', 'ps_product_attribute.reference AS attr_reference', 'stock_arrivepa', 'ps_manufacturer.name')
            ->join('ps_product', 'ps_product_attribute.id_product', '=', 'ps_product.id_product')
            ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->where('stock_arrivepa', '<', 0)
            ->where('ps_product_attribute.reference', 'not like', "%-Z")
            ->groupBy('ps_product_attribute.reference')
            ->orderBy('ps_product.id_manufacturer')
            ->get();

        foreach($bd_data AS $item) $data[] = [ 'id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->name, 'arrive' => $item->stock_arrive];
        foreach($bd_data_attr AS $item) $data[] = [ 'id_product' => $item->id_product, 'reference' => $item->attr_reference, 'brand' => $item->name, 'arrive' => $item->arrivepa];
        
        return [
            'name'              => trans('dashboard.Quantity arrive < 0'),
            'col'               => 4,
            'item_id'           => $type . '_qty_arrive',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'brand', 'arrive'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_end_of_life($type){

        $data = array();

        $bd_data = self::select('ps_product.id_product', 'ps_manufacturer.name', 'ps_product.housing', 'ps_product.reference', 'ps_product_attribute.reference AS refattr', 'ps_product_attribute.location AS housingattr', 'ps_stock_available.quantity AS quantity')
            ->leftJoin('ps_product_attribute', 'ps_product.id_product', '=', 'ps_product_attribute.id_product')
            ->leftJoin('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->join('ps_stock_available', function($join)
                {
                    $join->on('ps_product.id_product', '=', 'ps_product.id_product');
                    $join->on('ps_product_attribute.id_product_attribute', '=', 'ps_stock_available.id_product_attribute');
                })
            ->where('ps_product.wmdeprecated', 1)
            ->where('ps_product.active', 1)
            /*->where('ps_stock_available.quantity', '>', 0)*/
            ->orderBy('ps_stock_available.quantity')
            ->get();

        foreach($bd_data AS $item){
            
            $reference = ( isset( $item->refattr ) ) ? $item->refattr : $item->reference;
            $housing = ( isset( $item->housingattr ) ) ? $item->housingattr : $item->housing;

            $data[] = ['id_product' => $item->id_product, 'reference' => $reference, 'name' => $item->name, 'housing' => $housing, 'quantity' => $item->quantity];
        }
        
        return [
            'name'              => trans('dashboard.END OF LIFE PRODUCTS'),
            'col'               => 4,
            'item_id'           => $type . '_endoflife',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'housing', 'reference', 'quantity'],
            'counter'           => count($data),
            'data'              => $data
        ]; 
        
        
    }

    public static function dashboard_end_of_life_logistics($type){

        $data = array();
        $array = asm_dashboard::getExceptions('endoflife_logistics');
        
        $bd_data = self::select('ps_product.id_product', 'ps_manufacturer.name', 'ps_product.housing', 'ps_product.reference', 'ps_product_attribute.reference AS refattr', 'ps_product_attribute.location AS housingattr', 'ps_stock_available.quantity')
            ->join('ps_product_attribute', 'ps_product.id_product', '=', 'ps_product_attribute.id_product')
            ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->join('ps_stock_available', 'ps_product.id_product', '=', 'ps_stock_available.id_product')
            ->where('ps_product.wmdeprecated', 1)
            ->where('ps_product.active', 1)
            ->whereNotIn('ps_product.id_product', $array)
            //->groupBy('ps_stock_available.id_product')
            ->orderBy('ps_stock_available.quantity')
            ->get();

        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_product, 'id_product' => $item->id_product, 'reference' => ( isset( $item->refattr ) ) ? $item->refattr : $item->reference, 'name' => $item->name, 'housing' => ( isset( $item->housingattr ) ) ? $item->housingattr : $item->housing, 'quantity' => $item->quantity];
        
        return [
            'name'              => trans('dashboard.END OF LIFE PRODUCTS label'),
            'col'               => 4,
            'item_id'           => $type . '_endoflife_logistics',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['clean', 'id_product', 'housing', 'reference', 'quantity'],
            'exception_fields'  => ['endoflife_logistics', 'id_product', 'reference', 'name'],
            'counter'           => count($data),
            'data'              => $data
        ]; 

    }

    public static function dashboard_hs_code($type){

        $data = array();
        
        $bd_data = self::select('ps_product.id_product', 'ps_product.reference', 'ps_manufacturer.name')
            ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->where('ps_product.nc', '')
            ->orWhere('ps_product.nc', '0')
            ->orderBy('ps_product.id_manufacturer')
            ->get();

        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->name];
        
        return [
            'name'              => trans('dashboard.WITHOUT HS CODE'),
            'col'               => 4,
            'item_id'           => $type . '_WITHOUT_HS_CODE',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'brand'],
            'counter'           => count($data),
            'data'              => $data
        ]; 
        
    }

    public static function dashboard_visibility($type){

        $data = array();
        $array = asm_dashboard::getExceptions('product_visibility');
        
        $bd_data = self::select('ps_product.id_product', 'ps_product.id_manufacturer', 'ps_product.reference', DB::RAW('ps_manufacturer.name as brand'))
            ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->where('visibility', '<>', 'both')
            ->whereNotIn('id_product', $array)
            ->get();

        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_product, 'id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->manufacturer->name];
        
        return [
            'name'              => trans('dashboard.Visibility'),
            'col'               => 4,
            'item_id'           => $type . '_product_visibility',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['clean', 'id_product', 'reference', 'brand'],
            'exception_fields'  => ['product_visibility', 'id_product', 'reference', 'brand'],            
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_end_of_life_without_stock($type){

        $data = array();

        $bd_data = self::select('ps_product.id_product', 'ps_product.id_manufacturer', 'ps_product.reference', DB::RAW('ps_product_attribute.reference AS attr_reference'))
            ->leftJoin('ps_product_attribute', 'ps_product.id_product', '=', 'ps_product_attribute.id_product')
            ->leftJoin('ps_stock_available', 'ps_product.id_product', '=', 'ps_stock_available.id_product')
            ->where('wmdeprecated', 1)
            ->groupBy('ps_stock_available.id_product')
            ->orderBy('ps_product.date_upd')
            ->havingRaw('SUM(ps_stock_available.quantity) < 1')
            ->get();

        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => ( is_null($item->attr_reference)) ? $item->reference : $item->attr_reference];
        
        return [
            'name'              => trans('dashboard.End of life - Disabled'),
            'col'               => 4,
            'item_id'           => $type . '_end_of_life_without_stock',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_ean_with_spaces($type){

        $data = array();

        $bd_prod = self::select('id_product', 'reference', 'ean13')->where('active', 1)->get();
        $bd_attr = product_attribute::select('id_product', 'reference', 'ean13')->get();

        foreach($bd_prod AS $item) if ( preg_match('/\\s/',$item->ean13) ){
            $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'ean13' => $item->ean13];
        }
        
        foreach($bd_attr AS $item) if ( preg_match('/\\s/',$item->ean13) ){
            $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'ean13' => $item->ean13];
        }
        
        return [
            'name'              => trans('dashboard.EAN13 with spaces'),
            'col'               => 4,
            'item_id'           => $type . '_ean13_with_spaces',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'ean13'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_products_for_newsletter($type){

        $data = array();
        $array = asm_dashboard::getExceptions('products_for_newsletter');
        
        $bd_data = self::select('ps_product.id_product', 'ps_product.id_manufacturer', 'ps_product.reference', DB::RAW('ps_manufacturer.name as brand'))
            ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->where('ps_product.active', 1)
            ->where('ps_product.date_add', '>', date('Y-m-d', strtotime('-30 DAYS')))
            ->whereNotIn('id_product', $array)
            ->get();
            

        foreach($bd_data AS $item){

        $extra_actions = "
            <div style='text-align: center;'>
                <button type='button' class='btn btn-success' onclick='setProductForNewsletter(" . $item->id_product . ", " . json_encode($item->reference) . " , 1)' >YES</button>
                <button type='button' class='btn btn-danger'  onclick='setProductForNewsletter(" . $item->id_product . ", " . json_encode($item->reference) . " , 0)' style='margin-left: 10px;'>NO</button>
            </div>
        ";
        
            $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->brand, 'extra_actions' => $extra_actions];
        }
        
        return [
            'name'              => trans('dashboard.Products for newsletter'),
            'col'               => 4,
            'item_id'           => $type . '_products_for_newsletter',
            'columns'           => ['id_product', 'reference', 'extra_actions'],
            'exception_fields'  => ['products_for_newsletter', 'id_product', 'reference', 'brand'],            
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_references_with_spaces($type){

        $data = array();

        $array = asm_dashboard::getExceptions('references_with_spaces');

        $bd_prod = self::select('ps_product.id_product', 'ps_product.reference', DB::RAW('ps_manufacturer.name as brand'))
        ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
        ->whereNotIn('ps_product.id_product', $array)
        ->get();
        
        $bd_attr = product_attribute::select('ps_product.id_product', 'ps_product_attribute.reference', DB::RAW('ps_manufacturer.name as brand'))
        ->join('ps_product', 'ps_product_attribute.id_product', '=', 'ps_product.id_product')
        ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
        ->whereNotIn('ps_product.id_product', $array)
        ->get();

        foreach($bd_prod AS $item){
            if ( preg_match('/[^A-Za-z0-9\/.-]/',$item->reference) ){
                $data[] = ['clean' => $item->id_product, 'id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->brand];
            }
        }
        
        foreach($bd_attr AS $item){
            if ( preg_match('/[^A-Za-z0-9\/.-]/',$item->reference) ){
                $data[] = ['clean' => $item->id_product, 'id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->brand];
            }
        }
        
        return [
            'name'              => trans('dashboard.References with spaces'),
            'col'               => 4,
            'item_id'           => $type . '_references_with_spaces',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['clean', 'id_product', 'reference'],
            'exception_fields'  => ['references_with_spaces', 'id_product', 'reference', 'brand'],         
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_no_purchase_discount($type){

        $data = array();

        $bd_data = self::select('id_product', 'reference', DB::RAW('ps_manufacturer.name as brand'))
            ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->whereNull('discount_percentage')
            ->orWhere('ps_product.discount_percentage', '<', 1)
            ->orderBy('ps_product.id_manufacturer', 'DESC')
            ->get();
            
        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->brand];
        
        return [
            'name'              => trans('dashboard.No Purchase discount'),
            'col'               => 4,
            'item_id'           => $type . '_no_purchase_discount',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'brand'],
            'exception_fields'  => ['no_purchase_discount', 'id_product', 'reference', 'brand'],            
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_on_clearence($type){

        $data = array();

        $bd_data = self::select('ps_product.id_product', 'ps_product.reference', DB::RAW('ps_manufacturer.name as brand'), 'ps_product.date_add', DB::RAW('ps_stock_available.quantity AS stock') )
            ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->join('ps_stock_available', 'ps_product.id_product', '=', 'ps_stock_available.id_product')
            ->join('ps_category_product', 'ps_product.id_product', '=', 'ps_category_product.id_product')
            ->where('ps_category_product.id_category', 523)
            ->where('ps_stock_available.quantity', '>', 0)
            ->groupBy('ps_product.id_product')
            ->orderBy('ps_product.date_upd', 'DESC')
            ->get();
            
        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->brand, 'stock' => $item->stock];
        
        return [
            'name'              => trans('dashboard.Products in clearence'),
            'col'               => 4,
            'item_id'           => $type . '_on_clearence',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'brand', 'stock'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_no_video($type){

        $data = array();

        $bd_data = self::select('id_product', 'reference', DB::RAW('ps_manufacturer.name as brand'))
            ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->where('ps_product.active', 1)
            ->where('visibility', '<>', 'none')
            ->where('wmdeprecated', '=', 0)
            ->where('youtube_1', '=', '')
            ->where('youtube_2', '=', '')
            ->where('youtube_3', '=', '')
            ->where('youtube', '=', '')
            ->orderBy('ps_product.id_product', 'DESC')
            ->get();
            
        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->brand];
        
        return [
            'name'              => trans('dashboard.No video'),
            'col'               => 4,
            'item_id'           => $type . '_no_video',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'brand'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_no_purchase_price($type){

        $data = array();

        $bd_data = self::select('id_product', 'reference', DB::RAW('ps_manufacturer.name as brand'))
            ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->where('wholesale_price', 0)
            ->get();
            
        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->brand];

        $bd_data = product_attribute::select('ps_product.id_product', 'ps_product_attribute.reference', DB::RAW('ps_manufacturer.name as brand'))
            ->join('ps_product', 'ps_product_attribute.id_product', '=', 'ps_product.id_product')
            ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->where('ps_product_attribute.wholesale_price', 0)
            ->get();
            
        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->brand];
        
        return [
            'name'              => trans('dashboard.No wholesale price Set'),
            'col'               => 4,
            'item_id'           => $type . '_no_purchase_price',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'brand'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_products_same_reference($type){

        $data = array();
        $array = asm_dashboard::getExceptions('same_reference');

        $bd_data = self::select( DB::RAW('count(*) AS repeated'), 'ps_product.id_product', 'ps_product.reference', DB::RAW('ps_manufacturer.name as brand') )
            ->leftJoin('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->where('ps_product.active', 1)
            ->whereNotIn('id_product', $array)
            ->groupBy('ps_product.reference')
            ->orderBy('brand', 'DESC')
            ->get();

        foreach($bd_data AS $item) if($item->repeated > 1) $data[] = ['clean' => $item->id_product, 'id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->brand];
        
        return [
            'name'              => trans('dashboard.Products same reference'),
            'col'               => 4,
            'item_id'           => $type . '_same_reference',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['clean', 'id_product', 'reference'],
            'exception_fields'  => ['same_reference', 'id_product', 'reference', 'brand'],            
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_wholesale_price_exVAT($type){

        $data = array();

        $bd_data = self::select('id_product', 'reference', 'wholesale_price', 'price')
            ->whereColumn( 'wholesale_price', '>', 'price' )
            ->groupBy('ps_product.id_product')
            ->get();

        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'wholesale_price' => $item->wholesale_price, 'price' => $item->price];
        
        return [
            'name'              => trans('dashboard.Wholesale > Price ( EX VAT )'),
            'col'               => 4,
            'item_id'           => $type . '_wholesale_price_exVAT',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'wholesale_price', 'price'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_recommended_products($type){

        $data = array();

        $bd_data = self::select('ps_product.id_product', 'ps_product.reference', DB::RAW('ps_manufacturer.name AS brand'), 'ps_product.date_add')
            ->leftJoin('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->where( 'ps_product.active', 1 )
            ->where( 'ps_product.wmdeprecated', 0 )
            ->where('ps_product.visibility', 'NOT LIKE', "none")
            ->orderBy('ps_product.date_add', 'ASC')
            ->get();

        foreach($bd_data AS $item){
            
            $data_2 = accessory::select('ps_product.id_product', 'ps_product.active', 'ps_product.wmdeprecated', 'ps_stock_available.quantity')
            ->leftJoin('ps_product', 'ps_accessory.id_product_2', 'ps_product.id_product')
            ->leftJoin('ps_stock_available', 'ps_product.id_product', 'ps_stock_available.id_product')
            ->where('id_product_1', $item->id_product)
            ->get();
            
            $count = 0;
            
            foreach($data_2 AS $item_2){
                
                if( ( ($item_2->active) && ($item_2->wmdeprecated == 1) && ($item_2->quantity > 0) ) || ( ($item_2->active) && ($item_2->wmdeprecated == 0) ) ){
                    $count++;
                }
            }

            if ( $count < 4 ) $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->brand];
        
        }
        
        return [
            'name'              => trans('dashboard.RECOMMENDED PRODUCTS MISSING'),
            'col'               => 4,
            'item_id'           => $type . '_recommended_products',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'brand'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_packs($type){

        $data = array();

        $bd_data = self::select('id_product', 'reference', DB::RAW('ps_manufacturer.name AS brand'))
            ->leftJoin('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->where( 'cache_is_pack', 1 )
            ->groupBy('ps_product.reference')
            ->orderBy('brand', 'ASC')
            ->get();

        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->brand];
        
        return [
            'name'              => trans("dashboard.Products's Pack"),
            'col'               => 4,
            'item_id'           => $type . '_packs',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ?  [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'brand'],
            'counter'           => count($data),
            'info'              => true,
            'data'              => $data
        ];        
    }

    public static function dashboard_same_ean_diff_ref($type){

        $data = array();
        $products = array();
        $exceptions = array();

        $array = asm_dashboard::getExceptions('same_reference_diff_ean');
        
        foreach($array AS $product){
            $exceptions[]=$product->id_product;
        }
        
        $product_ean13     = self::select( DB::RAW('count(*) AS repeated_ean'), 'ean13' )->whereNotIn('id_product', $exceptions)->groupBy('ean13')->pluck('repeated_ean', 'ean13');
        $product_reference = self::select( DB::RAW('count(*) AS repeated_ref'), 'ean13' )->whereNotIn('id_product', $exceptions)->groupBy('reference')->pluck('repeated_ref', 'ean13');

        foreach( $product_ean13 AS $key => $counter ){
            
            if( isset($product_reference[$key]) ){

                if( $product_reference[$key] <> $product_ean13[$key] ){
                    
                    if($key != ''){
                        $refs = self::select( 'reference' )->distinct()->where('ean13', '=', $key)->get();
    
                        $str_refs = '';
                        foreach($refs AS $ref) $str_refs .= $ref->reference . ' | ';
        
                        $products[$key] = substr($str_refs, 0, -3);
                    }
                }
            }
        }

        $attr_ean13     = product_attribute::select( DB::RAW('count(*) AS repeated_ean'), 'ean13' )->whereNotIn('id_product', $exceptions)->groupBy('ean13')->pluck('repeated_ean', 'ean13');
        $attr_reference = product_attribute::select( DB::RAW('count(*) AS repeated_ref'), 'ean13' )->whereNotIn('id_product', $exceptions)->groupBy('reference')->pluck('repeated_ref', 'ean13');

        foreach( $attr_ean13 AS $key => $counter ){
            
            if( isset($attr_reference[$key]) ){

                if( $attr_reference[$key] <> $attr_ean13[$key] ){
                    
                    if($key != ''){
                        $refs = self::select( 'reference' )->distinct()->where('ean13', '=', $key)->get();
    
                        $str_refs = '';
                        foreach($refs AS $ref) $str_refs .= $ref->reference . ' | ';
        
                        $products[$key] = substr($str_refs, 0, -3);
                    }
                }
            }
        }
        
        foreach($products AS $key => $item){
            $reference =  explode(' | ', $item)[0];
            $id_product = product_attribute::select( 'id_product' )->where('reference', $reference)->whereNotIn('id_product', $exceptions)->groupBy('reference')->value('id_product') + 0;

            if( $id_product == 0 ) $id_product = product::select( 'id_product' )->where('reference', $reference)->whereNotIn('id_product', $exceptions)->groupBy('reference')->value('id_product');
            
            if($id_product > 0) $data[] = ['clean' => $id_product, 'id_product' => $id_product, 'reference' => $item, 'ean' => $key, 'brand' => 'none'];
        }
        
        return [
            'name'              => trans('dashboard.Products same reference differente EAN13'),
            'col'               => 4,
            'item_id'           => $type . '_same_reference_diff_ean',
            'columns'           => ['clean', 'reference', 'ean'],
            'exception_fields'  => ['same_reference_diff_ean', 'id_product', 'reference', 'brand'],
            'counter'           => count($data),
            'data'              => $data
        ];
        
    }

    public static function dashboard_same_sku_diff_measures($type){

        $data = array();

        $array = asm_dashboard::getExceptions('same_sku_diff_measures');

        $bd = self::select('ps_product.id_product', 'ps_product.reference', DB::RAW('ps_manufacturer.name as brand'), DB::RAW('COUNT(reference) AS counter_reference'), DB::RAW('COUNT( DISTINCT width ) AS distinct_width'), DB::RAW('COUNT( DISTINCT height ) AS distinct_height'), DB::RAW('COUNT( DISTINCT depth ) AS distinct_depth'), DB::RAW('COUNT( DISTINCT weight ) AS distinct_weight'))
        ->join('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
        ->whereNotIn('ps_product.id_product', $array)
        ->groupBy('reference')
        ->get();
        

        foreach($bd AS $item){
            
            if( ($item->distinct_width > 1) || ($item->distinct_height > 1) || ($item->distinct_depth > 1) || ($item->distinct_weight > 1) ){
                $data[] = ['clean' => $item->id_product, 'id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->brand];
                //echo '<br>( ' . $item->counter_reference . ' ) ' . $item->reference . ' ----> ' . $item->distinct_width . ' | ' . $item->distinct_height . ' | ' . $item->distinct_depth . ' | ' . $item->distinct_weight;
            }
        }
        
        //exit;
        
        return [
            'name'              => trans('dashboard.SAME SKU DIFFERENT MEASUREMENTS'),
            'col'               => 4,
            'item_id'           => $type . '_references_with_spaces',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'brand'],
            'exception_fields'  => ['same_sku_diff_measures', 'id_product', 'reference', 'brand'],         
            'counter'           => count($data),
            'data'              => $data
        ];      
    }

    public static function dashboard_in_stock_not_sold($type){
        
        $all  = array();
        $sold = array();
        $data_bd = array();

        $date = Carbon::now('UTC')->subYear()->toDateTimeString();
        
        $all_products = [];
        
        $products = self::select(
                'ps_product.id_product',
                'ps_product.reference',
                DB::raw('ps_stock_available.quantity AS stock'),
                'ps_manufacturer.name AS manufacturer'
            )
            ->leftJoin('ps_stock_available', function($join) {
                $join->on('ps_product.id_product', '=', 'ps_stock_available.id_product')
                     ->where('ps_stock_available.id_product_attribute', '=', 0);
            })
            ->leftJoin('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->leftJoin('ps_category_product as cp', 'ps_product.id_product', '=', 'cp.id_product')
            ->where('ps_product.active', 1)
            ->where('ps_product.date_add', '<', $date)
            ->where('ps_stock_available.quantity', '>', 0)
            ->where('ps_product.reference', 'NOT LIKE', '%-Z')
            ->whereNotIn('cp.id_category', [524, 525, 526])
            ->distinct()
            ->get();
        
        foreach ($products as $item) {
            $all_products[] = [
                'id_product'   => $item->id_product,
                'reference'    => $item->reference,
                'stock'        => $item->stock,
                'manufacturer' => $item->manufacturer,
            ];
        }
        
        $attributes = product_attribute::select(
                'ps_product_attribute.id_product',
                'ps_product_attribute.reference',
                DB::raw('ps_stock_available.quantity AS stock'),
                'ps_manufacturer.name AS manufacturer'
            )
            ->leftJoin('ps_stock_available', 'ps_product_attribute.id_product_attribute', '=', 'ps_stock_available.id_product_attribute')
            ->leftJoin('ps_product', 'ps_product_attribute.id_product', '=', 'ps_product.id_product')
            ->leftJoin('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->leftJoin('ps_category_product as cp', 'ps_product.id_product', '=', 'cp.id_product')
            ->where('ps_product.active', 1)
            ->where('ps_product.date_add', '<', $date)
            ->where('ps_stock_available.quantity', '>', 0)
            ->whereNotIn('cp.id_category', [524, 525, 526])
            ->distinct()
            ->get();
                
        foreach ($attributes as $item) {
            $all_products[] = [
                'id_product'   => $item->id_product,
                'reference'    => $item->reference,
                'stock'        => $item->stock,
                'manufacturer' => $item->manufacturer,
            ];
        }
        
        $sold_refs = orders_details::join('ps_orders as o', 'o.id_order', '=', 'ps_order_detail.id_order')
            ->where('o.date_add', '>', $date)
            ->pluck('ps_order_detail.product_reference')
            ->unique()
            ->toArray();
        
        $data_bd = collect($all_products)
            ->reject(function ($item) use ($sold_refs) {
                return in_array($item['reference'], $sold_refs);
            })
            ->sortBy('manufacturer')
            ->values();

        return [
            'name'              => trans('dashboard.PRODUCT IN STOCK BUT NOT SOLD FOR 1 YEAR'),
            'col'               => 4,
            'item_id'           => $type . '_in_stock_not_sold',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'manufacturer', 'reference', 'stock'],
            'counter'           => count($data_bd),
            'data'              => $data_bd
        ];        
    }

    public static function dashboard_avs_on_erp($type){

        $data = array();

        $products = self::select('reference')->where('wmdeprecated', 1)->groupBy('reference')->get();
        
        $erp_orders = bms_procurement_purchase_order::getAllOpenOrdersWithRows();

        foreach($products AS $product) $avs[] = $product->reference;

        $references_ordered = array();
        foreach($erp_orders AS $order){
            foreach($order->rows AS $row){
                $references_ordered[] = $row->wmsku;
            }
        }
        
        $references_ordered = array_unique($references_ordered);

        $result = array_intersect($references_ordered, $avs);

        $products = self::select('reference')->where('wmdeprecated', 1)->groupBy('reference')->get();

        $bd_data = self::select('ps_product.id_product', 'ps_product.reference', DB::RAW('ps_manufacturer.name as brand') )
            ->leftJoin('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->leftJoin('ps_product_attribute', 'ps_product.id_product', '=', 'ps_product_attribute.id_product')
            ->whereIn('ps_product.reference', $result)
            ->groupBy('ps_product.reference')
            ->get();
            
        foreach($bd_data AS $item){
            $data[] = ['clean' => $item->id_product, 'id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->brand];
        }

        return [
            'name'              => trans('dashboard.PRODUCT AVS BUT ORDERED IN ERP'),
            'col'               => 4,
            'item_id'           => $type . '_avs_on_erp',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'brand'],
            'exception_fields'  => ['same_sku_diff_measures', 'id_product', 'reference', 'brand'],         
            'counter'           => count($data),
            'data'              => $data
        ];      
    }

    public static function dashboard_non_standard($type){

        $data = array();
        
        $products = product::select('ps_product.id_product', 'reference')->leftJoin('ps_stock_available', 'ps_stock_available.id_product', 'ps_product.id_product')->where('ps_stock_available.quantity', '>', 0)->groupBy('ps_stock_available.id_product')->get();

        foreach($products AS $product){
            $data_arr = self::getProductMeasures($product->id_product, $product->reference);
            if($data_arr['volumetric'] > 299) $data[] = $data_arr;
            else if( $data_arr['weight'] > 31.49 ) $data[] = $data_arr;
        }
        
        
                
        $collection = collect( $data );
        $sorted = $collection->sortByDesc([ ['volumetric', 'DESC'], ['weight', 'DESC'] ]);

        return [
            'name'              => trans('dashboard.PRODUCT non standard'),
            'col'               => 4,
            'item_id'           => $type . '_non_standard',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['reference', 'weight', 'width', 'height', 'depth', 'volumetric'],
            'counter'           => count($sorted),
            'data'              => $sorted,
        ];        
    }

    public static function dashboard_locked_products_with_stock($type){

        $data = array();
        
        $diffs = array();
        
        $ids_to_search= array();

        $products = self::select('ps_product.id_product', DB::RAW(' SUM( ps_stock_available.quantity) AS product_quantity') )
            ->join('ps_stock_available', 'ps_product.id_product', '=', 'ps_stock_available.id_product')
            ->where('ps_stock_available.id_product_attribute', 0)
            ->where('ps_stock_available.out_of_stock', 0)
            ->where('visibility', '<>', 'none')
            ->where('wmdeprecated', '=', 0)
            ->where('active', '=', 1)
            ->groupBy('ps_product.id_product')
            ->get();

        foreach( $products AS $product){
            
            $sons = self::select(DB::raw('SUM(ps_stock_available.quantity) AS quantity'), DB::raw('COUNT(ps_stock_available.quantity) AS linhas'))
            ->join('ps_stock_available', 'ps_product.id_product', '=', 'ps_stock_available.id_product')
            ->where('ps_stock_available.id_product', $product->id_product)
            ->where('ps_stock_available.id_product_attribute', '<>', 0)
            ->first();
            
            if(($sons->linhas > 1) && ( $sons->quantity != $product->product_quantity ) && ( $product->product_quantity < 1 ) ){
            //if(($sons->linhas > 1) && ( $sons->quantity != $product->product_quantity ) ){
                $diffs[] = [
                    'id_product' => $product->id_product,
                    'qty_father' => $product->product_quantity,
                    'qty_sons'   => $sons->quantity,
                ];
                
                $ids_to_search[]=$product->id_product;
            }
        }
        
        $bd_data = self::select('ps_product.id_product', 'id_manufacturer', DB::raw('ps_product.reference AS prod_ref'), DB::raw('ps_product_attribute.reference AS attr_ref'))
            ->join('ps_product_attribute', 'ps_product.id_product', '=', 'ps_product_attribute.id_product')
            ->with('manufacturer')
            ->whereIn('ps_product.id_product', $ids_to_search )
            ->orderBy('id_manufacturer')
            ->groupBy('ps_product.reference')
            ->get();
        
        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => ( isset($item->attr_ref) ) ? $item->attr_ref : $item->prod_ref, 'brand' => $item->manufacturer->name];
        
        return [
            'name'              => trans('dashboard.Locked with stock'),
            'col'               => 4,
            'item_id'           => $type . '_locked_products_with_stock',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'brand'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function getProductMeasures($id_product, $reference){
        
        $weight = self::getProductMeasure($id_product, 'weight');
        $width  = self::getProductMeasure($id_product, 'width' );
        $height = self::getProductMeasure($id_product, 'height');
        $depth  = self::getProductMeasure($id_product, 'depth' );
        
        $array_measures = [ $width, $height, $depth ];
        rsort($array_measures);
        
        $volumetric = $array_measures[0] + ( 2 * $array_measures[1] ) + ( 2 * $array_measures[2] );
        
        $tr_color = '';
        if(  ( $weight < 31.5 ) && ( $volumetric < 300 ) )      $tr_color = '';
        else if( ( $weight < 31.5 ) && ( $volumetric < 305 ) )  $tr_color = 'row_yellow';
        else if( ( $weight < 35 ) && ( $volumetric < 300 ) )    $tr_color = 'row_yellow';
        else if( ( $weight < 35 ) && ( $volumetric < 305 ) )    $tr_color = 'row_yellow';
        else if( $volumetric > 304 ) $tr_color = ' row_red ';
        else if( $weight > 34.99 ) $tr_color = ' row_red ';

        return [
            'tr_color' => $tr_color,
            'id_product' => $id_product,
            'reference' => $reference,
            'weight' => number_format($weight, 2, '.'),
            'width'  => number_format($array_measures[0], 0, '.'),
            'height'  => number_format($array_measures[1], 0, '.'),
            'depth'  => number_format($array_measures[2], 0, '.'),
            'volumetric'  => $volumetric
        ];

    }

    public static function getProductMeasure($id_product, $field){
        return product::where('id_product', $id_product)->value($field);
    }

    public static function dashboard_same_sku_diff_stock($type){

        $data = array();

        $array = asm_dashboard::getExceptions('same_sku_diff_stock');
        
        $refsProd = self::join('ps_stock_available', 'ps_product.id_product', '=', 'ps_stock_available.id_product')
            ->where('ps_product.active', 1)
            ->where('ps_stock_available.id_product_attribute', 0)
            ->groupBy('ps_product.reference')
            ->havingRaw('COUNT(*) > 1')
            ->havingRaw('MIN(ps_stock_available.quantity) <> MAX(ps_stock_available.quantity)')
            ->pluck('ps_product.reference');
        
                    
                    
        $bd_prod = self::join('ps_stock_available', 'ps_product.id_product', '=', 'ps_stock_available.id_product')
            ->where('ps_stock_available.id_product_attribute', 0)
            ->whereNotIn('ps_stock_available.id_stock_available', $array)
            ->whereIn('ps_product.reference', $refsProd)
            ->select(
                'ps_stock_available.id_stock_available',
                'ps_product.id_product',
                'ps_product.reference',
                'ps_stock_available.quantity'
            )
            ->orderBy('ps_product.reference')
            ->groupBy('ps_product.reference')
            ->get();

        $refsAttr = product_attribute::join('ps_stock_available', 'ps_product_attribute.id_product_attribute', '=', 'ps_stock_available.id_product_attribute')
            ->select('ps_product_attribute.reference')
            ->groupBy('ps_product_attribute.reference')
            ->havingRaw('MIN(ps_stock_available.quantity) <> MAX(ps_stock_available.quantity)')
            ->pluck('reference');
        
                
        $bd_attr = product_attribute::join('ps_product', 'ps_product_attribute.id_product', '=', 'ps_product.id_product')
            ->join('ps_stock_available', 'ps_product_attribute.id_product_attribute', '=', 'ps_stock_available.id_product_attribute')
            ->whereIn('ps_product_attribute.reference', $refsAttr->toArray())
            ->whereNotIn('ps_stock_available.id_stock_available', $array)
            ->where('ps_stock_available.id_product_attribute', '>', 0)
            ->select(
                'ps_stock_available.id_stock_available',
                'ps_product.id_product',
                'ps_product_attribute.reference',
                'ps_stock_available.quantity'
            )
            ->orderBy('ps_product_attribute.reference')
            ->get();

        foreach($bd_prod AS $item){
            $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference];
        }
        
        foreach($bd_attr AS $item){
            $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference];
        }
        
        return [
            'name'              => trans('dashboard.SAME SKU DIFFERENT STOCK'),
            'col'               => 4,
            'item_id'           => $type . '_same_sku_diff_stock',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference'],
            'counter'           => count($data),
            'data'              => $data
        ];      
    }
    
    
    
}