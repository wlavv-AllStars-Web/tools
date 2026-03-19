<?php

namespace App\Models\modules\dashboard;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Config;

use App\Models\prestashop\asm_dashboard;
use App\Models\prestashop\orders;

class dashboard extends Model
{
    use HasFactory;
    protected $table = "dashboard";

    /** TO UPDATE COUNTERS **/
    public static function updateCountersOFTab( $tab ){ 
        
        $counters = array();
        
        $panels = dashboard::where('tab', $tab)->get();
    
        foreach($panels AS $panel){
            
            [$class, $method] = explode('::', $panel->function);
            $fullClass = "App\\Models\\prestashop\\" . $class;
            $alternativeClass  = "App\\Models\\modules\\" . $class . "\\" . $class;

            if (class_exists($fullClass) && method_exists($fullClass, $method)) {
                $callableClass = $fullClass;
            } elseif (class_exists($alternativeClass) && method_exists($alternativeClass, $method)) {
                $callableClass = $alternativeClass;
            } else {
                \Log::warning("Método {$method} não encontrado nem em {$fullClass} nem em {$alternativeClass}");
                continue;
            }
            
            $counters[$panel->panel] = (object)$result = call_user_func([$callableClass, $method], 'counter', self::getCountersOFTabPanel( $tab, $panel->panel ));
            
            dashboard::where('panel', $panel->panel)->update(['counter' => $result['counter']]);
        }
        
        return $counters;
    }

    public static function creatTab( $tab, $panel, $function ){
        
        $tab_result = self::existTab( $tab, $panel );
        
        if( !isset($tab_result->id) ){
          
            $new = new dashboard();
            $new->tab = $tab;
            $new->panel = $panel;
            $new->function = $function;
            $new->counter = 0;
            $new->created_at = date('Y-m-d h:s:i');
            $new->updated_at = date('Y-m-d h:s:i');
            $new->save();
        
            return $new->id;
        }
        
        return $tab_result->id;
    }

    public static function existTab( $tab, $panel ){
        return dashboard::where('tab', $tab)->where('panel', $panel)->first();
    }

    public static function updateCounters( ){
        
        $counters = self::updateCountersOFTab( 'admin' );
        $counters = self::updateCountersOFTab( 'support' );
        $counters = self::updateCountersOFTab( 'finance' );
        $counters = self::updateCountersOFTab( 'logistics' );
        $counters = self::updateCountersOFTab( 'marketing' );
        $counters = self::updateCountersOFTab( 'sales' );
        
        return 1;
    }

    public static function getCountersList( $tab ){
        $panels = dashboard::select('tab', 'panel', 'counter')->where('tab', $tab)->get();
        return View::make('customTools/dashboard/list_header', compact('panels'));
    }

    public static function getCountersOFTab( $tab ){ 
        $counters = dashboard::where('tab', $tab)->orderBy('store')->get();
        
        $asm = dashboard::where('tab', $tab)->where('store', 'ASM')->get();
        $asd = dashboard::where('tab', $tab)->where('store', 'ASD')->get();
        return View::make('areas/dashboard/includes/counters_header', compact('asm', 'asd'));
    }

    public static function getCountersOFTabPanel( $tab, $panel ){ 
        return dashboard::where('tab', $tab)->where('panel', $panel)->value('counter');
    }

    public static function getCountersContentOfTabPanel( $tab, $panel_name ){
        
        $data = array();
        $panel = dashboard::where('tab', $tab)->where('panel', $panel_name)->first();

        [$class, $method] = explode('::', $panel->function);
        $fullClass = "App\\Models\\prestashop\\" . $class;
        $alternativeClass  = "App\\Models\\modules\\" . $class . "\\" . $class;

        if (class_exists($fullClass) && method_exists($fullClass, $method)) {
            $content = (object)$result = call_user_func([$fullClass, $method], 'counter', self::getCountersOFTabPanel( $tab, $panel->panel ));
        } elseif (class_exists($alternativeClass) && method_exists($alternativeClass, $method)) {
            $content = (object)$result = call_user_func([$alternativeClass, $method], 'counter', self::getCountersOFTabPanel( $tab, $panel->panel ));
        }
        
        if(count($content->data) != $panel->counter) dashboard::where('panel', $panel_name)->update(['counter' => $result['counter']]);
        
        $data = [
            'details' => $content,
            'tab' => $tab,
            'panel' => $panel
        ];
        
        if( in_array($panel_name, ['reviews'])){
            return [
                'redirect' => true,
                'data' => $data
            ];
        }else{
            return [
                'counter' => count($content->data),
                'update_tag' => (count($content->data) > $panel->counter) ? 1 : 0,
                'html' => view('areas/dashboard/includes/counters_content')->with($data)->render()
            ];
        }
    }

    public static function getExternalData( $tab, $panel ){ 
        
        if($panel == 'dashboard_order_invoiced_exvat') self::externalOrderInvoiceExVat($tab, $panel, 'https://www.all-stars-distribution.com/custom/api/orders/exvat.php');
        if($panel == 'dashboard_products_without_discount') self::externalProductsWithoutDiscounts($tab, $panel, 'https://www.all-stars-distribution.com/custom/api/products/without_discount.php');
        
    }
    public static function externalOrderInvoiceExVat( $tab, $panel ){ 

        $data = [];
        $ids_exceptions = [];
        $bd_data = self::externalDataRequest( 'https://www.all-stars-distribution.com/custom/api/orders/exvat.php', $params = [] );
        $exceptions = asm_dashboard::getExceptions('asd_orders_exvat');

        foreach($exceptions AS $exception){
            $ids_exceptions[] = $exception->id_product;
        }    
        
        foreach($bd_data AS $item){
            if( !in_array($item['id_order'], $ids_exceptions) ) $data[] = ['clean' => 'ASD_' . $item['id_order'], 'id_order' => $item['id_order'], 'reference' => $item['reference'], 'total_products' => $item['total_products'], 'total_products_wt' => $item['total_products_wt']];
        }

        return [
            'name'              => trans('dashboard.ASD - ORDER INVOICE EXVAT'),
            'col'               => 4,
            'item_id'           => 'counter_asd_orders_exvat',
            'prestashop'        => ( isset ( Config::get('tokenASD')->AdminOrders ) ) ? [ 'token' => Config::get('tokenASD')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder', 'store' => 'ASD' ] : [],
            'columns'           => ['clean', 'id_order', 'total_products', 'total_products_wt'],
            'exception_fields'  => ['asd_orders_exvat', 'id_order', 'total_products', 'total_products_wt'],   
            'counter'           => count($data),
            'data'              => $data
        ];  
    }

    public static function externalProductsWithoutDiscounts( $tab, $panel ){ 
        
        $data = [];
        $ids_exceptions = [];
        $bd_data = self::externalDataRequest( 'https://www.all-stars-distribution.com/custom/api/products/without_discount.php', $params = [] );
        $exceptions = asm_dashboard::getExceptions('asd_products_without_discounts');
        
        foreach($exceptions AS $exception){
            $ids_exceptions[] = $exception->id_product;
        }
        
        foreach($bd_data AS $item){
            if( !in_array($item['id_product'], $ids_exceptions) ) $data[] = ['clean' => 'ASD_' . $item['id_product'], 'id_product' => $item['id_product'], 'reference' => $item['reference'], 'extra' => 0 ];
        }

        return [
            'name'              => trans('dashboard.ASD - PRODUCTS WITHOUT DISCOUNT'),
            'col'               => 4,
            'item_id'           => 'counter_asd_orders_exvat',
            'prestashop'        => ( isset ( Config::get('tokenASD')->AdminOrders ) ) ? [ 'token' => Config::get('tokenASD')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct', 'store' => 'ASD' ] : [],
            'columns'           => ['clean', 'id_product', 'reference'],
            'exception_fields'  => ['asd_products_without_discounts', 'id_product', 'reference', 'extra'],   
            'counter'           => count($data),
            'data'              => $data
        ]; 
    }

    public static function orderInvoiceExVat( ){ 
        
        $data = [];
        $ids_exceptions = [];
        $prefix = env('DB2_DB_prefix');

        $exceptions = asm_dashboard::getExceptions('asm_orders_exvat');

        foreach($exceptions AS $exception){
            $ids_exceptions[] = $exception->id_product;
        }

        $bd_data = orders::select(
                "{$prefix}orders.id_order",
                "{$prefix}orders.reference",
                "{$prefix}orders.current_state",
                "{$prefix}orders.total_products",
                "{$prefix}orders.total_products_wt"
            )
            ->leftJoin("{$prefix}customer", "{$prefix}customer.id_customer", "=", "{$prefix}orders.id_customer")
            ->where("{$prefix}orders.date_add", '>', now()->subDays(5))
            ->where("{$prefix}customer.id_default_group", 4)
            ->whereNotIn("{$prefix}orders.id_order", $exceptions)
            ->orderBy('id_order', 'DESC')
            ->get();
        
        foreach($bd_data AS $item){
            $data[] = ['clean' => $item['id_order'], 'id_order' => $item['id_order'], 'reference' => $item['reference'], 'total_products' => $item['total_products'], 'total_products_wt' => $item['total_products_wt'] ];
        }
        
        return [
            'name'              => trans('dashboard.PRODUCTS WITHOUT DISCOUNT'),
            'col'               => 4,
            'item_id'           => 'counter_asm_orders_exvat',
            'prestashop'        => ( isset ( Config::get('token')->AdminOrders ) ) ? [ 'token' => Config::get('token')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder' ] : [],
            'columns'           => ['clean', 'id_order', 'reference', 'total_products', 'total_products_wt'],
            'exception_fields'  => ['asm_orders_exvat', 'id_order', 'reference', 'total_products', 'total_products_wt'],   
            'counter'           => count($data),
            'data'              => $data
        ]; 
    }
    

    public static function externalDataRequest( $url, $params = [] ){ 
        
        $data = array();
        
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $url, [ 
            'headers' => [
                    'User-Agent' => 'Firefox/1.0',
                    'Accept' => 'application/json', 
                    'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => $params
        ]);

        if($response->getStatusCode() == 200) $data = json_decode($response->getBody()->getContents(), true);
        
        return $data;
    }
    
    public static function externalOrdersWithoutPaymentAccepted( $tab, $panel ){ 

        $data = [];
        $ids_exceptions = [];
        $bd_data = self::externalDataRequest( 'https://www.all-stars-distribution.com/custom/api/orders/ordersWithoutPaymentAccepted.php', $params = [] );
        $exceptions = asm_dashboard::getExceptions('asd_orders_without_payment');

        foreach($exceptions AS $exception){
            $ids_exceptions[] = $exception->id_product;
        }    
        
        foreach($bd_data AS $item){
            if( !in_array($item['id_order'], $ids_exceptions) ) $data[] = ['clean' => 'ASD_' . $item['id_order'], 'id_order' => $item['id_order'], 'reference' => $item['reference'], 'other' => ''];
        }

        return [
            'name'              => trans('dashboard.ASD - ORDERS WITHOUT PAYMENT ACCEPTED'),
            'col'               => 4,
            'item_id'           => 'counter_asd_orders_without_payment',
            'prestashop'        => ( isset ( Config::get('tokenASD')->AdminOrders ) ) ? [ 'token' => Config::get('tokenASD')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder', 'store' => 'ASD' ] : [],
            'columns'           => ['clean', 'id_order', 'reference', 'other'],
            'exception_fields'  => ['asd_orders_without_payment', 'id_order', 'reference', 'other'],   
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
    
    public static function externalOrdersWithoutShipping( $tab, $panel ){ 

        $data = [];
        $ids_exceptions = [];
        $bd_data = self::externalDataRequest( 'https://www.all-stars-distribution.com/custom/api/orders/ordersWithoutShipping.php', $params = [] );
        $exceptions = asm_dashboard::getExceptions('asd_orders_without_shipping');

        foreach($exceptions AS $exception){
            $ids_exceptions[] = $exception->id_product;
        }    
        
        foreach($bd_data AS $item){
            if( !in_array($item['id_order'], $ids_exceptions) ) $data[] = ['clean' => 'ASD_' . $item['id_order'], 'id_order' => $item['id_order'], 'reference' => $item['reference'], 'other' => ''];
        }

        return [
            'name'              => trans('dashboard.ASD - ORDERS WITHOUT PAYMENT ACCEPTED'),
            'col'               => 4,
            'item_id'           => 'counter_asd_orders_without_shipping',
            'prestashop'        => ( isset ( Config::get('tokenASD')->AdminOrders ) ) ? [ 'token' => Config::get('tokenASD')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder', 'store' => 'ASD' ] : [],
            'columns'           => ['clean', 'id_order', 'reference', 'other'],
            'exception_fields'  => ['asd_orders_without_shipping', 'id_order', 'reference', 'other'],   
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
    
    public static function externalOrdersReferenceWithSpaces( $tab, $panel ){ 

        $data = [];
        $bd_data = self::externalDataRequest( 'https://www.all-stars-distribution.com/custom/api/products/productReferenceWithSpaces.php', $params = [] );

        foreach($bd_data AS $item){
            $data[] = ['clean' => 'ASD_' . $item['id_product'], 'id_product' => $item['id_product'], 'reference' => $item['reference'], 'other' => ''];
        }

        return [
            'name'              => trans('dashboard.ASD - ORDERS WITHOUT PAYMENT ACCEPTED'),
            'col'               => 4,
            'item_id'           => 'counter_asd_product_reference_with_spaces',
            'prestashop'        => ( isset ( Config::get('tokenASD')->AdminProducts ) ) ? [ 'token' => Config::get('tokenASD')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct', 'store' => 'ASD' ] : [],
            'columns'           => ['id_product', 'reference', 'other'],
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
    
    public static function externalOrdersDuplicatedOrder( $tab, $panel ){ 

        $data = [];
        $ids_exceptions = [];
        $bd_data = self::externalDataRequest( 'https://www.all-stars-distribution.com/custom/api/orders/ordersDuplicatedOrders.php', $params = [] );
        $exceptions = asm_dashboard::getExceptions('asd_orders_duplicated_orders');

        foreach($exceptions AS $exception){
            $ids_exceptions[] = $exception->id_product;
        }    
        
        foreach($bd_data AS $item){
            if( !in_array($item['id_order'], $ids_exceptions) ) $data[] = ['clean' => 'ASD_' . $item['id_order'], 'id_order' => $item['id_order'], 'repeated' => $item['repeated'], 'other' => ''];
        }

        return [
            'name'              => trans('dashboard.ASD - ORDERS WITHOUT PAYMENT ACCEPTED'),
            'col'               => 4,
            'item_id'           => 'counter_asd_orders_without_shipping',
            'prestashop'        => ( isset ( Config::get('tokenASD')->AdminOrders ) ) ? [ 'token' => Config::get('tokenASD')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder', 'store' => 'ASD' ] : [],
            'columns'           => ['clean', 'id_order', 'repeated', 'other'],
            'exception_fields'  => ['asd_orders_duplicated_orders', 'id_order', 'repeated', 'other'],   
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
    
    public static function externalOrdersPartialShipping( $tab, $panel ){ 

        $data = [];
        $bd_data = self::externalDataRequest( 'https://www.all-stars-distribution.com/custom/api/orders/ordersPartialShipping.php', $params = [] );

        foreach($bd_data AS $item){
            $data[] = ['id_order' => $item['id_order'], 'reference' => $item['reference'], 'other' => ''];
        }

        return [
            'name'              => trans('dashboard.ASD - ORDERS - Partial Shipping'),
            'col'               => 4,
            'item_id'           => 'counter_asd_orders_partial_shipping',
            'prestashop'        => ( isset ( Config::get('tokenASD')->AdminOrders ) ) ? [ 'token' => Config::get('tokenASD')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder', 'store' => 'ASD' ] : [],
            'columns'           => ['id_order', 'reference', 'other'],
            'exception_fields'  => ['id_order', 'reference', 'other'],   
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
    
    public static function externalOrdersWaitingInfo( $tab, $panel ){ 

        $data = [];
        $bd_data = self::externalDataRequest( 'https://www.all-stars-distribution.com/custom/api/orders/ordersWaitingInfo.php', $params = [] );

        foreach($bd_data AS $item){
            $data[] = ['id_order' => $item['id_order'], 'reference' => $item['reference'], 'other' => ''];
        }

        return [
            'name'              => trans('dashboard.ASD - ORDERS - Waiting Info'),
            'col'               => 4,
            'item_id'           => 'counter_asd_orders_waiting_info',
            'prestashop'        => ( isset ( Config::get('tokenASD')->AdminOrders ) ) ? [ 'token' => Config::get('tokenASD')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder', 'store' => 'ASD' ] : [],
            'columns'           => ['id_order', 'reference', 'other'],
            'exception_fields'  => ['id_order', 'reference', 'other'],   
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
    
    public static function externalWarrantyOrders( $tab, $panel ){ 

        $data = [];
        $bd_data = self::externalDataRequest( 'https://www.all-stars-distribution.com/custom/api/orders/ordersWarranty.php', $params = [] );

        foreach($bd_data AS $item){
            $data[] = ['brand' => $item['brand'], 'products' => $item['products'], 'warranty_order' => $item['warranty_order']];
        }

        return [
            'name'              => trans('dashboard.ASD - WARRANTY ORDERS'),
            'col'               => 4,
            'item_id'           => 'counter_asd_warranty_orders',
            'columns'           => ['brand', 'products', 'warranty_order'],
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
    
    public static function externalNoHousingWithStock( $tab, $panel ){ 

        $data = [];
        $bd_data = self::externalDataRequest( 'https://www.all-stars-distribution.com/custom/api/products/noHousingWithStock.php', $params = [] );

        foreach($bd_data AS $item){
            $data[] = ['clean' => 'ASD_' . $item['id_product'], 'id_product' => $item['id_product'], 'reference' => $item['reference'], 'other' => ''];
        }

        return [
            'name'              => trans('dashboard.ASD - No housing with stock'),
            'col'               => 4,
            'item_id'           => 'counter_asd_no_housing_with_stock',
            'prestashop'        => ( isset ( Config::get('tokenASD')->AdminProducts ) ) ? [ 'token' => Config::get('tokenASD')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct', 'store' => 'ASD' ] : [],
            'columns'           => ['id_product', 'reference', 'other'],
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
    
    public static function externalProductsNoImage( $tab, $panel ){ 

        $data = [];
        $bd_data = self::externalDataRequest( 'https://www.all-stars-distribution.com/custom/api/products/productsWithNoImage.php', $params = [] );

        foreach($bd_data AS $item){
            $data[] = ['clean' => 'ASD_' . $item['id_product'], 'id_product' => $item['id_product'], 'reference' => $item['reference'], 'manufacturer' => $item['manufacturer']];
        }

        return [
            'name'              => trans('dashboard.ASD - No images'),
            'col'               => 4,
            'item_id'           => 'counter_asd_product_no_image',
            'prestashop'        => ( isset ( Config::get('tokenASD')->AdminProducts ) ) ? [ 'token' => Config::get('tokenASD')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct', 'store' => 'ASD' ] : [],
            'columns'           => ['id_product', 'reference', 'manufacturer'],
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
    
    public static function externalProductsPriceIssue( $tab, $panel ){ 

        $data = [];
        $bd_data = self::externalDataRequest( 'https://www.all-stars-distribution.com/custom/api/products/productsWithPriceIssue.php', $params = [] );

        foreach($bd_data AS $item){
            $data[] = ['clean' => 'ASD_' . $item['id_product'], 'id_product' => $item['id_product'], 'reference' => $item['reference'], 'other' => ''];
        }

        return [
            'name'              => trans('dashboard.ASD - Wholesale > price ( ex VAT)'),
            'col'               => 4,
            'item_id'           => 'counter_asd_product_price_issues',
            'prestashop'        => ( isset ( Config::get('tokenASD')->AdminProducts ) ) ? [ 'token' => Config::get('tokenASD')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct', 'store' => 'ASD' ] : [],
            'columns'           => ['id_product', 'reference', 'other'],
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
    
    public static function externalOrdersPricesDiff( $tab, $panel ){ 

        $data = [];
        $ids_exceptions = [];
        $bd_data = self::externalDataRequest( 'https://www.all-stars-distribution.com/custom/api/orders/pricesDiff.php', $params = [] );
        $exceptions = asm_dashboard::getExceptions('asd_orders_price_diff');
        
        foreach($exceptions AS $exception){
            $ids_exceptions[] = $exception->id_product;
        }    
        
        foreach($bd_data AS $item){
            if( !in_array($item['id_order'], $ids_exceptions) ) $data[] = ['clean' => 'ASD_' . $item['id_order'], 'id_order' => $item['id_order'], 'reference' => $item['reference'], 'total_products' => $item['total_products'], 'total_products_wt' => $item['total_products_wt'], 'soma_excl' => $item['soma_excl'], 'soma_incl' => $item['soma_incl'], 'other' => '' ];
        }

        return [
            'name'              => trans('dashboard.ASD - ORDERS PRICE DIFF'),
            'col'               => 6,
            'item_id'           => 'counter_asd_orders_price_diff',
            'prestashop'        => ( isset ( Config::get('tokenASD')->AdminOrders ) ) ? [ 'token' => Config::get('tokenASD')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder', 'store' => 'ASD' ] : [],
            'columns'           => ['clean', 'id_order', 'reference', 'total_products', 'total_products_wt', 'soma_excl', 'soma_incl'],
            'exception_fields'  => ['asd_orders_price_diff', 'id_order', 'reference', 'other'],   
            'counter'           => count($data),
            'data'              => $data
        ];  
    }
    
    
}
