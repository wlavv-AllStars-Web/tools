<?php

namespace App\Models\modules\backorders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Config;

use App\Models\prestashop\orders;
use App\Models\prestashop\product;
use App\Models\prestashop\product_lang;
use App\Models\prestashop\product_attribute;
use App\Models\prestashop\stock_available;
use App\Models\modules\bms_procurement\bms_procurement_purchase_order_product;

class customers_backorders extends Model
{   
    use HasFactory;
    protected $table = "customers_backorders";
    public $primaryKey = 'id';
    
    public static function checkBackorder($id_order, $id_product, $id_product_attribute, $store){
        return self::where('id_order', $id_order)->where('id_product', $id_product)->where('id_product_attribute', $id_product_attribute)->where('store', $store)->first();
    }
    
    public static function insertBackorder($data){
        
        $exists = self::checkBackorder($data['id_order'], $data['id_product'], $data['id_product_attribute'], $data['store']);
        
        if( !isset($exists->id) ){
            
            $backorder = new customers_backorders();
            
            $backorder->id_order = $data['id_order'];
            $backorder->id_product = $data['id_product'];
            $backorder->id_product_attribute = $data['id_product_attribute'];
            $backorder->store = $data['store'];
            $backorder->type = $data['type'];
            $backorder->brand = $data['brand'];
            $backorder->supplier = $data['supplier'];
            $backorder->module = $data['payment'];
            $backorder->sold = $data['sold'];
            $backorder->order_date = $data['date_add'];
            $backorder->reference = $data['reference'];
            $backorder->created_at = date('Y-m-d');
            
            $backorder->save();
            
        }
        return 1;
    }
    
    public static function getAll(){
        
        $rows = array();
        
        $data = self::where('customers_backorders.done', 0)->get();
        
        foreach($data AS $item){

            $product = product_attribute::where('reference', $item->reference)->first();

            if( !isset( $product->id_product)) $product = product::where('reference', $item->reference)->first();
            
            if(isset($product->id_product)){
                $id_product = $product->id_product;
                $id_product_attribute = ( isset($product->id_product_attribute)) ? $product->id_product_attribute : 0;
                $stock_available = stock_available::where('id_product', $id_product)->where('id_product_attribute', $id_product_attribute)->first();
            }else{
                $id_product = 0;
                $id_product_attribute = 0;
                $stock_available = 'N/D';
            }
            
            $expected_days = product_lang::where('id_product', $id_product)->where('id_lang', 1)->value('available_later');

            $date1 = strtotime($item->order_date);
            $date2 = strtotime(date('Y-m-d'));
            $diff = $date2 - $date1;
            $days = floor($diff / (60 * 60 * 24)) + 1;
            
            preg_match_all('/\d+/', $expected_days, $matches);
            
            if (!empty($matches[0])) {
                if (count($matches[0]) == 2) {
                    $expected = max($matches[0]);
                } else {
                    $expected = $matches[0][0];
                }
            } else {
                $expected = 0;
            }

            $erp_ordered = bms_procurement_purchase_order_product::select( DB::raw('sum(qty_wmfaturado) AS invoiced'), DB::raw('sum(qty_received) AS qty_received'), DB::raw('sum(qty_expected) AS expected') )
            ->where('sku', $item->reference)
            ->where('qty_expected', '>', 0)
            ->groupBy('sku')
            ->first();
            
            $country_order = orders::with('delivery', 'delivery.country.lang_en')->where('id_order', $item->id_order)->first();
            
            $rows[] = (object)[
                    'id'                    => $item->id,
                    'id_order'              => $item->id_order,
                    'id_product'            => $id_product,
                    'id_product_attribute'  => $id_product_attribute,
                    'original_id_product'            => $item->id_product,
                    'original_id_product_attribute'  => $item->id_product_attribute,
                    'reference'             => $item->reference,
                    'supplier'              => $item->supplier,
                    'brand'                 => $item->brand,
                    'sold'                  => $item->sold,
                    'store'                 => $item->store,
                    'stock'                 => ( isset($stock_available->quantity) ) ? $stock_available->quantity : 'N/D',
                    'module'                => $item->module,
                    'type'                  => $item->type,
                    'invoiced'              => $item->invoiced,
                    'eta'                   => $item->eta,
                    'customer_contact'      => $item->customer_contact,
                    'customer_answer'       => $item->customer_answer,
                    'order_date'            => $item->order_date,
                    'days'                  => $days,
                    'expected_days'         => $expected_days,
                    'expected'              => $expected,
                    'color'                 => $item->rowColor,
                    'erp_qty_received'      => (isset($erp_ordered->received)) ? $erp_ordered->received : 0,    
                    'erp_qty_invoiced'      => (isset($erp_ordered->invoiced)) ? $erp_ordered->invoiced : 0,    
                    'erp_qty_expected'      => (isset($erp_ordered->expected)) ? $erp_ordered->expected : 0,
                    'id_country'            => ($item->store == 'ASM') ? $country_order->delivery->id_country : 0,
                    'country'               => ($item->store == 'ASM') ? $country_order->delivery->country->lang_en->name : 'ASD'
                ];
        }
        
        return $rows;
        
    }
    
    public static function getAllASM(){
        return self::where('customers_backorders.done', 0)->where('store', 'ASM')->get();
    }
    
    public static function getAllASD(){
        return self::where('customers_backorders.done', 0)->where('store', 'ASD')->get();
    }
    
    public static function verifyOrdersStatus(){
        
        $getAllASM = self::getAllASM();
        $getAllASD = self::getAllASD();
        
        foreach($getAllASM as $order){

            $order_info = orders::where('id_order', $order->id_order)->first();
            
            if( in_array($order_info->current_state , [16, 7, 6, 5, 4] ) ){
                self::where('id', $order->id)->whereNot('reference', 'SHIP-PICK')->update(['done' => 1]);
            }
            /**
            else{
                self::where('id', $order->id)->update(['done' => 0]);
            }
            **/
        }

        $asd_ids = array();
        
        foreach($getAllASD as $order){
            $asd_ids[] = $order->id_order;
        }
        
        if(isset( $asd_ids ) && ( count($asd_ids) > 0 ) ){
            
            $asd_ids = self::getASDStatusOrders($asd_ids);
        
            foreach($asd_ids as $id_order => $status){
                
                self::where('id_order', $id_order)->where('store', 'ASD')->update(['done' => $status]);
                
            }
        }
    }
    
    public static function getCounters(){
        
        $asm_backorders = self::where('store', 'ASM')->where('type', 'backorder')->count();
        $asd_backorders = self::where('store', 'ASD')->where('type', 'backorder')->count();
        
        $asm_partials = self::where('store', 'ASM')->where('type', 'partial')->count();
        $asd_partials = self::where('store', 'ASD')->where('type', 'partial')->count();
        
        return [
            'asm_backorder' => $asm_backorders,
            'asm_partial'   => $asm_partials,
            
            'asd_backorder' => $asd_backorders,
            'asd_partial'   => $asd_partials,
        ];
    }
    
    public static function updateInfo($data){
        
        $column = substr($data->column, 10, -1);

        self::where('id_order', $data->id_order)->where('reference', $data->reference)->whereNot('reference', 'SHIP-PICK')->update([$column => $data->value]);
        
        return 1;

    }

    public static function getASDStatusOrders($data){

        $client = new \GuzzleHttp\Client();
        
        $response = $client->request('POST', 'https://www.all-stars-distribution.com/custom/api/orders/checkStatus.php', [
            'json' => $data,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);
        
        return json_decode($response->getBody(), true);
    }

    public static function getBackorderDetail($id_order){
        return self::where('id_order', $id_order)->first();
    }
    
}