<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\prestashop\orders_details;
use App\Models\prestashop\orders_history;
use App\Models\prestashop\customer;
use App\Models\prestashop\addresses;
use App\Models\prestashop\carriers;
use App\Models\prestashop\product_attribute;

use Illuminate\Support\Facades\Config;

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

    public function order_detail(){
        return $this->hasMany(orders_details::class, "id_order", 'id_order');
    }

    public function customer(){
        return $this->hasOne(customer::class, "id_customer", 'id_customer');
    }

    public function delivery(){
        return $this->hasOne(address::class, "id_address", 'id_address_delivery');
    }

    public function invoice(){
        return $this->hasOne(address::class, "id_address", 'id_address_invoice');
    }

    public function carrier(){
        return $this->hasOne(carriers::class, "id_carrier", 'id_carrier');
    }
    
    public static function dashboard_order_payed_with_voucher($type){

        $data = array();

        $prefix = env('DB2_DB_prefix');

        $array = asm_dashboard::getExceptions('order_payed_with_voucher');
        
        $bd_data = self::select( "id_order", "reference", "total_discounts" )
            ->where("{$prefix}orders.total_discounts", '>', 0)
            ->where("{$prefix}orders.id_order", '>', 90000)
            ->whereNotIn('id_order', $array)
            ->get();

        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_order, 'id_order' => $item->id_order, 'reference' => $item->reference, 'total_discounts' => $item->total_discounts];
        
        return [
            'name'              => trans('dashboard.ORDERS PAYED WITH VOUCHER'),
            'col'               => 4,
            'item_id'           => $type . '_order_payed_with_voucher',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder' ] : [],
            'columns'           => ['clean', 'id_order', 'reference', 'total_discounts'],
            'counter'           => count($data),
            'exception_fields'  => ['order_payed_with_voucher', 'id_order', 'reference', 'total_discounts'],
            'data'              => $data
        ]; 
    }

    public static function dashboard_partial_orders($type){

        $data = array();
        
        $bd_data = Orders::select('id_order', 'reference')->where('current_state', 28)->get();

        foreach($bd_data AS $item) $data[] = ['id_order' => $item->id_order, 'reference' => $item->reference];
        
        return [
            'name'              => trans('dashboard.PARTIAL ORDERS'),
            'col'               => 4,
            'item_id'           => $type . '_partial_orders',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder' ] : [],
            'columns'           => ['id_order', 'reference'],
            'counter'           => count($data),
            'data'              => $data
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

    public static function getAllOrdersOf($id_current_state){

        $data = self::with('order_detail.product.stock', 'order_detail.product.pack.product_pack.stock_product_pack', 'order_detail.product.manufacturer', 'order_detail.product_attribute.stock', 'customer', 'delivery.country', 'invoice.country.lang_en')->where('current_state', $id_current_state)->get();
        
        if( ( $id_current_state == 15 ) OR ( $id_current_state = 28 ) ){
            
            foreach($data AS $item){
                
                //if($item->id_order == 96716) dd($item);

                $extra_data_product = array();

                foreach($item->order_detail AS $detail){

                    $history = order_history::select('date_add')->where('id_order', $detail->id_order)->where('id_order_state', 2)->first();

                    $is_pack = 0;                
                
                    if( $detail->product_quantity > 0 ){

                        $row_extra_data_product = [
                            'id_product' => $detail->product_id,  
                            'id_product_attribute' => 0,  
                            'reference' => $detail->product_reference, 
                            'supplier' => (isset($detail->product->supplier)) ? $detail->product->supplier->name : '', 
                            'brand' => ( isset($detail->product->manufacturer) ) ? $detail->product->manufacturer->name : 'N/D', 
                            'product_quantity' => $detail->product_quantity,
                            'qtd_sent' => $detail->qtd_sent,
                            'control' => $detail->control,
                            'type' => $detail->type,
                            'sold' => $detail->product_quantity - $detail->qtd_sent,
                            'date_payed' => (isset($history->date_add)) ? $history->date_add : null,
                            'store' => 'ASM',
                            'stock' => 0
                        ];
                                    
                        if($detail->product_attribute_id != 0){
                            
                            $row_extra_data_product['id_product_attribute'] = $detail->product_attribute_id;

                            if( count( $detail->product_attribute) > 0){

                                foreach( $detail->product_attribute AS $product_attribute){

                                        if( $product_attribute->stock->quantity < 0 ){

                                            $row_extra_data_product['stock'] = $product_attribute->stock->quantity;
                                            
                                        }
                                }
                            
                            }else{
                    
                                if( isset( $detail->product_attribute[0]->id_order ) ){
                    
                                    foreach( $detail->product_attribute AS $product_attribute){
                                                            
                                        if( $detail->product_attribute->stock->quantity < 0 ){
                    
                                            $row_extra_data_product['stock'] = $detail->product_attribute->stock->quantity;
                                            
                                        }
                                    }
                                }
                            
                            }
                            
                            
                        }else{

                            if( isset($detail->product->pack[0]) ){
                    
                                foreach($detail->product->pack AS $product_pack){
                                                        
                                    if($product_pack->id_product_attribute_item > 0){
                                        $attr_pack = product_attribute::where('id_product', $product_pack->id_product_item)->where('id_product_attribute', $product_pack->id_product_attribute_item)->first();
                                        $row_extra_data_product['reference'] = $attr_pack->reference;
                                    }else{
                                        $row_extra_data_product['reference'] = $product_pack->product_pack->reference;
                                    }
                                    
                                    $row_extra_data_product['sold'] = $product_pack->quantity * $detail->product_quantity - $detail->qtd_sent;
                                    $row_extra_data_product['stock'] = $product_pack->product_pack->stock_product_pack->quantity;
                                    
                                    if( ( $product_pack->product_pack->stock_product_pack->quantity < 0 ) && ( $row_extra_data_product['sold'] != 0 )){

                                        $extra_data_product[] = $row_extra_data_product;
                                    }
                                    
                                    $is_pack = 1;
                                }
                                
                            }elseif( isset($detail->product->stock) ){
                                if( $detail->product->stock->quantity < 0 ){
                                    $row_extra_data_product['stock'] = $detail->product->stock->quantity;
                                }
                                
                            }
                            
                        }
                    }else{
                        $row_extra_data_product = null;
                    }
                    
                    
                    if($is_pack == 0){
                        if(isset($row_extra_data_product['stock'])){
                            if( ( $id_current_state == 15 ) &&  ( $row_extra_data_product['stock'] < 0) ){
                                $extra_data_product[] = $row_extra_data_product;   
                            }elseif( ( $id_current_state == 28 ) && ( isset($row_extra_data_product['product_quantity']) ) && ( $row_extra_data_product['product_quantity'] != $row_extra_data_product['qtd_sent'] ) && ( $row_extra_data_product['control'] == 0 ) ){
                                $extra_data_product[] = $row_extra_data_product;
                            }
                        }
                    }
                }

                $item->extraDataField = $extra_data_product;
                
            }
            
        }
        
        return $data;
    }

    public static function getASDbackorders(){

        $data = [];
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://www.all-stars-distribution.com/custom/api/orders/getBackorders.php');
        
        if($response->getStatusCode() == 200) $data = json_decode($response->getBody(), true);

        return $data;
    }

    public static function getASDpartials(){

        $data = [];
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://www.all-stars-distribution.com/custom/api/orders/getPartials.php');
        
        if($response->getStatusCode() == 200) $data = json_decode($response->getBody(), true);

        return $data;
    }
    
    public static function getSoldItems($reference){

        $time = strtotime("-1 year", time());
        $date = date("Y-m-d", $time);

        $soldASM = DB::table(env('DB2_DB_prefix') . 'orders')
            ->select(     env('DB2_DB_prefix') . 'order_detail.product_quantity AS product_quantity')
            ->join(       env('DB2_DB_prefix') . 'order_detail',     env('DB2_DB_prefix') . 'orders.id_order', '=', env('DB2_DB_prefix') . 'order_detail.id_order')
            ->where(      env('DB2_DB_prefix') . 'order_detail.product_reference', '"' . $reference . '"' )
            ->where(      env('DB2_DB_prefix') . 'orders.date_add','>', '"' . $date . '"' )
            ->groupBy(    env('DB2_DB_prefix') . 'order_detail.product_reference')
            ->sum('product_quantity');

        $soldASD = 0;

        return $soldASM + $soldASD;
    }

    public static function dashboard_without_tracking($type){

        $data = array();
        $array = asm_dashboard::getExceptions('orders_without_tracking');
        
        $bd_data = self::select('ps_orders.id_order', 'ps_orders.reference', 'ps_order_state_lang.name AS state')
                        ->join('ps_order_state_lang', 'ps_orders.current_state', '=', 'ps_order_state_lang.id_order_state')
                        ->join('ps_order_carrier', 'ps_orders.id_order', '=', 'ps_order_carrier.id_order')
                        ->where('ps_order_carrier.tracking_number', '')
                        ->whereNotIn('ps_orders.id_order', $array)
                        ->whereIN('ps_orders.current_state', [4, 28])
                        ->groupBy('ps_orders.id_order')
                        ->get();

        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_order, 'id_order' => $item->id_order, 'reference' => $item->reference, 'state' => $item->state];
        
        return [
            'name'              => trans('dashboard.ORDER WITHOUT TRACKING'),
            'col'               => 4,
            'item_id'           => $type . '_orders_without_tracking',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder' ] : [],
            'columns'           => ['clean', 'id_order', 'reference', 'state'],
            'exception_fields'  => ['orders_without_tracking', 'id_order', 'reference', 'state'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_waiting_info($type){

        $data = array();


        $bd_data = self::select('id_order', 'reference')->where('current_state', 30)->get();

        foreach($bd_data AS $item) $data[] = ['id_order' => $item->id_order, 'reference' => $item->reference];
        
        return [
            'name'              => trans('dashboard.ORDERS - WAITING INFO'),
            'col'               => 4,
            'item_id'           => $type . '_waiting_info',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder' ] : [],
            'columns'           => ['id_order', 'reference'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    } 

    public static function dashboard_duplicated_orders($type = 'panel', $counter = null){

        $data = array();
        $array = asm_dashboard::getExceptions('duplicated_order');
        
        
        
        /**
        if($type == 'counter'){
            return [
                'name'              => trans('dashboard.DUPLICATED ORDER'),
                'col'               => 4,
                'item_id'           => $type . '_duplicated_order',
                'counter'           => $counter + 0,
            ]; 
            
        }else{**/
            $bd_data = self::select('id_order', 'reference', 'total_paid', 'id_customer', DB::raw('count(*) AS repeated'))
                ->whereNotIn('id_order', $array)
                ->groupBy('id_customer', 'payment', 'total_paid', DB::raw('DATE(date_add)'))
                ->havingRaw(' count(*) > 1')  
                ->orderBy('id_order')
                ->get();
    
            foreach($bd_data AS $item) $data[] = ['clean' => $item->id_order, 'id_order' => $item->id_order, 'reference' => $item->reference, 'total_paid' => $item->total_paid, 'repeated' => $item->repeated];
            
            return [
                'name'              => trans('dashboard.DUPLICATED ORDER'),
                'col'               => 4,
                'item_id'           => $type . '_duplicated_order',
                'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder' ] : [],
                'columns'           => ['clean', 'id_order', 'reference', 'total_paid', 'repeated'],
                'exception_fields'  => ['duplicated_order', 'id_order', 'reference', 'repeated'],
                'counter'           => count($data),
                'data'              => $data
            ];     
        /**}**/
    } 

    public static function dashboard_duplicated_status($type){

        $data = array();
        $array = asm_dashboard::getExceptions('payment_accept');

        $bd_data = order_history::getPanelInfo($array);
        
        foreach($bd_data AS $item){
            if( $item->total > 1) $data[] = ['clean' => $item->id_order, 'id_order' => $item->id_order, 'reference' => $item->reference, 'client' => $item->firstname . ' ' . $item->lastname, 'total' => $item->total];
        }
        
        return [
            'name'              => trans('dashboard.DUPLICATED STATUS'),
            'col'               => 4,
            'item_id'           => $type . '_duplicated_status',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder' ] : [],
            'columns'           => ['clean', 'id_order', 'reference', 'client', 'total'],
            'exception_fields'  => ['payment_accept', 'id_order', 'reference', 'client'],
            'counter'           => count($data),
            'data'              => $data
        ];  ;        
    } 

    public static function dashboard_returns_warranties($type){

        $data = array();
        $orders = self::select('id_order', 'reference')->where('current_state', 29)->get();

        foreach($orders AS $order) $data[] = ['id_order' => $order->id_order, 'reference' => $order->reference, 'products' => orders_details::getProductsOfOrder($order->id_order)];

        return [
            'name'              => trans('dashboard.RETURNS & WARRANTIES OK'),
            'col'               => 4,
            'item_id'           => $type . '_returns_warranties',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder' ] : [],
            'columns'           => ['id_order', 'reference', 'products'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function shippingPaidByCustomer($year){
        
        $total_order = self::select( DB::raw('sum(ps_orders.total_shipping_tax_incl) AS total_shipping') )
            ->where('ps_orders.date_add', '>', date('Y') . '-01-01 00:00:00')
            ->whereIn('ps_orders.current_state', [2, 3, 4, 5, 15, 16, 28])
            ->value('total_shipping');
        
        $total_by_carrier = self::select( 'ps_carrier.name AS name', DB::raw('sum(ps_orders.total_shipping_tax_incl) AS total_shipping') )
            ->leftjoin( 'ps_carrier', 'ps_carrier.id_carrier', '=', 'ps_orders.id_carrier')
            ->where('ps_orders.date_add', '>', date('Y') . '-01-01 00:00:00')
            ->whereIn('ps_orders.current_state', [2, 3, 4, 5, 15, 16, 28])
            ->groupBy('ps_carrier.id_reference')
            ->get();

        $carrier_data['DPD'] = 0;
        $carrier_data['UPS'] = 0;
        $carrier_data['TNT'] = 0;
        $carrier_data['NACEX'] = 0;
        $carrier_data['GLS'] = 0;
        $carrier_data['FEDEX'] = 0;
        
        foreach($total_by_carrier AS $carrier){
            
            if (strpos($carrier['name'],"DPD") !== false)   $carrier_data['DPD']   += $carrier['total_shipping'];
            if (strpos($carrier['name'],"UPS") !== false)   $carrier_data['UPS']   += $carrier['total_shipping'];
            if (strpos($carrier['name'],"TNT") !== false)   $carrier_data['TNT']   += $carrier['total_shipping'];
            if (strpos($carrier['name'],"NACEX") !== false) $carrier_data['NACEX'] += $carrier['total_shipping'];
            if (strpos($carrier['name'],"GLS") !== false)   $carrier_data['GLS']   += $carrier['total_shipping'];
            if (strpos($carrier['name'],"FEDEX") !== false) $carrier_data['FEDEX'] += $carrier['total_shipping'];

        }

        $carrier_data['total'] = $total_order;

        return $carrier_data;
    }

    public static function getCounters($id_shop, $expectedEvolution){
        
        if($id_shop == 1){

            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', 'https://www.all-stars-distribution.com/custom/api/dashboards/main_dashboard.php');
            
            $stream_data = array();            
            if($response->getStatusCode() == 200) $stream_data = json_decode($response->getBody(), true);

            $data['awaiting']   = (int)$stream_data['data']['awaiting'];
            $data['packing']    = (int)$stream_data['data']['packing'];
            $data['shipped']    = (int)$stream_data['data']['shipped'];
            $data['warranty']   = (int)$stream_data['data']['warranty'];
            $data['backorders'] = (int)$stream_data['data']['backorders'];
            $data['partial']    = (int)$stream_data['data']['partial'];
            $data['pending']    = (int)$stream_data['data']['pending'];
            $data['today_forcast'] = (float)$stream_data['data']['today_forcast'] * $expectedEvolution;
            $data['today_realized'] = (float)$stream_data['data']['today_realized'];
            $data['yesterday_forcast'] = (float)$stream_data['data']['yesterday_forcast'] * $expectedEvolution;
            $data['yesterday_realized'] = (float)$stream_data['data']['yesterday_realized'];

        }else if($id_shop == 2){
            
            $yesterday = order_payment::getASMTotals(0);
            $today = order_payment::getASMTotals(1);
            
            $data['awaiting'] = 0;
            $data['packing'] = self::where('current_state', 3)->count();
            $data['shipped'] = self::join('ps_order_history', 'ps_order_history.id_order', 'ps_orders.id_order')->where('ps_orders.current_state', 4)->where('ps_order_history.id_order_state', 4)->where('ps_order_history.date_add', '>', date('Y') . '-' . date('m') . '-' . date('d')  . ' 00:00:00')->count();
            $data['warranty'] = self::where('current_state', 29)->count();
            $data['backorders'] = self::where('current_state', 15)->count();
            $data['partial'] = self::where('current_state', 28)->count();
            $data['pending'] = self::where('current_state', 30)->count();
            $data['today_forcast'] = (float)$today->homologue_day * $expectedEvolution;
            $data['today_realized'] = (float)$today->day;
            $data['yesterday_forcast'] = (float)$yesterday->homologue_day * $expectedEvolution;
            $data['yesterday_realized'] = (float)$yesterday->day;

        }else if($id_shop == 3){
            
            $data['awaiting'] = 0;
            $data['packing'] = 0;
            $data['shipped'] = 0;
            $data['warranty'] = 0;
            $data['backorders'] = 0;
            $data['partial'] = 0;
            $data['pending'] = 0;
            $data['today_forcast'] = 0;
            $data['today_realized'] = 0;
            $data['yesterday_forcast'] = 0;
            $data['yesterday_realized'] = 0;
            
        }else if($id_shop == 4){
            
            $data['awaiting'] = 0;
            $data['packing'] = 0;
            $data['shipped'] = 0;
            $data['warranty'] = 0;
            $data['backorders'] = 0;
            $data['partial'] = 0;
            $data['pending'] = 0;
            $data['today_forcast'] = 0;
            $data['today_realized'] = 0;
            $data['yesterday_forcast'] = 0;
            $data['yesterday_realized'] = 0;
            
        }
    
        return (object)$data;
        
    }
    
    public static function dashboard_order_reviewed($type){
        
        $data = [];
    
        $prefix = env('DB2_DB_prefix');
        $array = asm_dashboard::getExceptions('order_reviewed');
    
        $sevenDaysAgo = now()->subDays(7);
        $twentyDaysAgo = now()->subDays(15);
        
        $bd_data = self::select(
                "{$prefix}orders.id_order",
                "{$prefix}orders.reference",
                "{$prefix}customer.email",
                DB::raw("(SELECT oh.date_add 
                    FROM {$prefix}order_history oh 
                    WHERE oh.id_order = {$prefix}orders.id_order 
                    AND oh.id_order_state = 4 
                    ORDER BY oh.date_add ASC 
                    LIMIT 1) AS date_shipped")
            )
            ->leftJoin("{$prefix}customer", "{$prefix}customer.id_customer", "=", "{$prefix}orders.id_customer")
            ->where("{$prefix}orders.id_order", '>', 100000)
            ->where("{$prefix}orders.current_state", '=', 4) 
            ->where("{$prefix}orders.date_add", '>', $twentyDaysAgo)
            ->where("{$prefix}orders.not_for_review", '<>', 1)
            ->whereNotIn("{$prefix}orders.id_order", $array)
            ->whereNotExists(function($sub) use ($prefix) {
                $sub->select(DB::raw(1))
                    ->from("{$prefix}order_history")
                    ->whereRaw("{$prefix}order_history.id_order = {$prefix}orders.id_order")
                    ->where("{$prefix}order_history.id_order_state", '=', 15);
            })
            ->whereExists(function($sub) use ($prefix, $sevenDaysAgo) {
                $sub->select(DB::raw(1))
                    ->from("{$prefix}order_history")
                    ->whereRaw("{$prefix}order_history.id_order = {$prefix}orders.id_order")
                    ->where("{$prefix}order_history.id_order_state", '=', 4)
                    ->where("{$prefix}order_history.date_add", '<', $sevenDaysAgo);
            })
            ->whereNotIn('ps_orders.id_order', $array)
            ->get();
    
        foreach ($bd_data as $item) {
            $data[] = [
                'clean' => $item->id_order,
                'id_order' => $item->id_order,
                'email' => $item->email,
                'send_email_reviewed' => $item->email,
                'date_shipped' =>  date('Y-m-d', strtotime($item->date_shipped))
            ];
        }
    
        return [
            'name'             => trans('dashboard.ORDERS PAYED WITH VOUCHER'),
            'col'              => 4,
            'item_id'          => $type . '_order_reviewed',
            'prestashop'       => (isset(Config::get('token')->AdminProducts)) ? [
                'token'         => Config::get('token')->AdminOrders,
                'controller'    => 'AdminOrders',
                'element'       => 'id_order',
                'extraParameters' => '&vieworder'
            ] : [],
            'columns'          => ['clean', 'id_order', 'date_shipped', 'email', 'send_email_reviewed'],
            'counter'          => count($data),
            'exception_fields' => ['order_reviewed', 'id_order', 'date_shipped', 'email', 'send_email_reviewed'],
            'data'             => $data,
        ];
    }


    
    public static function dashboard_order_reviewed_2($type){
        
        $data = [];
    
        $prefix = env('DB2_DB_prefix');
        $array = asm_dashboard::getExceptions('order_reviewed_2');
    
        $sevenDaysAgo = now()->subDays(15);
        $twentyDaysAgo = now()->subDays(30);
        
        $bd_data = self::select(
                "{$prefix}orders.id_order",
                "{$prefix}orders.reference",
                "{$prefix}customer.email",
                DB::raw("(SELECT oh.date_add 
                    FROM {$prefix}order_history oh 
                    WHERE oh.id_order = {$prefix}orders.id_order 
                    AND oh.id_order_state = 4 
                    ORDER BY oh.date_add ASC 
                    LIMIT 1) AS date_shipped")
            )
            ->leftJoin("{$prefix}customer", "{$prefix}customer.id_customer", "=", "{$prefix}orders.id_customer")
            ->where("{$prefix}orders.id_order", '>', 100000)
            ->where("{$prefix}orders.current_state", '=', 4) 
            ->where("{$prefix}orders.date_add", '>', $twentyDaysAgo)
            ->where("{$prefix}orders.not_for_review", '<>', 1)
            ->whereNotIn("{$prefix}orders.id_order", $array)
            ->whereNotExists(function($sub) use ($prefix) {
                $sub->select(DB::raw(1))
                    ->from("{$prefix}order_history")
                    ->whereRaw("{$prefix}order_history.id_order = {$prefix}orders.id_order")
                    ->where("{$prefix}order_history.id_order_state", '=', 15);
            })
            ->whereExists(function($sub) use ($prefix, $sevenDaysAgo) {
                $sub->select(DB::raw(1))
                    ->from("{$prefix}order_history")
                    ->whereRaw("{$prefix}order_history.id_order = {$prefix}orders.id_order")
                    ->where("{$prefix}order_history.id_order_state", '=', 4)
                    ->where("{$prefix}order_history.date_add", '<', $sevenDaysAgo);
            })
            ->whereNotIn('ps_orders.id_order', $array)
            ->get();
    
        foreach ($bd_data as $item) {
            $data[] = [
                'clean' => $item->id_order,
                'id_order' => $item->id_order,
                'email' => $item->email,
                'send_email_reviewed' => $item->email,
                'date_shipped' =>  date('Y-m-d', strtotime($item->date_shipped))
            ];
        }
    
        return [
            'name'             => trans('dashboard.ORDERS PAYED WITH VOUCHER'),
            'col'              => 4,
            'item_id'          => $type . '_order_reviewed',
            'prestashop'       => (isset(Config::get('token')->AdminProducts)) ? [
                'token'         => Config::get('token')->AdminOrders,
                'controller'    => 'AdminOrders',
                'element'       => 'id_order',
                'extraParameters' => '&vieworder'
            ] : [],
            'columns'          => ['clean', 'id_order', 'date_shipped', 'email', 'send_email_reviewed'],
            'counter'          => count($data),
            'exception_fields' => ['order_reviewed_2', 'id_order', 'date_shipped', 'email', 'send_email_reviewed'],
            'data'             => $data,
        ];
    }

}
