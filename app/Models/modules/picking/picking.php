<?php

namespace App\Models\modules\picking;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use App\Models\prestashop\product_attribute;
use App\Models\prestashop\product;
use App\Models\prestashop\orders;
use App\Models\prestashop\pack;

class picking extends Model
{
    use HasFactory;
    protected $table = "picking";

    public static function getOrders(){
        return (object)[
            'preparation'   => self::mountOrdersArray('preparation')
        ];
    }
        
    public static function mountOrdersArray($status){
        
        $data_order  = self::where('status', $status)->where('row_done', 0)->groupBy('id_order')->get();

        $array_order = array();
        foreach($data_order AS $order){
            $array_order[] = (object)[
                'id_order' => $order->id_order,
                'carrier' => $order->carrier,
                'order' => self::where('id_order', $order->id_order)->where('row_done', 0)->get(),
            ];
        }
        
        return (object)[ 'counter' => count($array_order),  'data' => (object)$array_order ];
    }
        
    public static function add(){
        
        self::addData(3, 'preparation');
        
    }
        
    public static function addData($id_status, $status){
        
        $orders = orders::with('order_detail', 'carrier')->where('current_state', $id_status)->get();

        if(count($orders)){
            
            foreach($orders AS $order){

                foreach($order->order_detail AS $detail){
                    if($detail->product_quantity > $detail->qtd_sent){
                        
                        if( $detail->product_attribute_id == 0){
                            $product = product::where('id_product', $detail->product_id)->first();
                        }else{
                            $product = product_attribute::where('id_product', $detail->product_id)->where('id_product_attribute', $detail->product_attribute_id)->first();
                        }
                        
                        if(isset($product->id_product)){
                        
                            $detail->location = $product->location; 
                            
                            $is_pack = pack::is_pack($detail->product_id);
    
                            if( $is_pack ){
                                
                                $pack_products = pack::getPackItems($detail->product_id);
        
                                foreach($pack_products AS $pack_item){
                                    
                                    $picking = array();
                                    
                                    $product = product::with('lang')->where('id_product',  $pack_item->id_product_item)->first();
                                    
                                    $picking['id_shop'] = $detail->id_shop;
                                    $picking['product_name'] = $product->lang->name;
                                    
                                    if( $pack_item->id_product_attribute_item == 0){
                                        $picking['product_reference'] = $product->reference;                      
                                        $picking['product_ean13'] = $product->ean13; 
                                        $picking['location'] = $product->location; 
                                    }else{
                                        $attribute = product_attribute::where('id_product',  $pack_item->id_product_item)->where('id_product_attribute',  $pack_item->id_product_attribute_item)->first();
                                        $picking['product_reference'] = $attribute->reference;                      
                                        $picking['product_ean13'] = $attribute->ean13; 
                                        $picking['location'] = $attribute->location; 
                                    }
                                    
                                    $picking['product_id'] = $pack_item->id_product_item;                    
                                    $picking['product_attribute_id'] = $pack_item->id_product_attribute_item;  
                                    
                                    $quantity = $detail->product_quantity - $detail->qtd_sent;
                                    
                                    $picking['product_quantity'] = ( $quantity * $pack_item->quantity);
                                    $picking['quantity_picked'] = 0;                      
                                    $picking['row_done'] = 0;                      
                                    $picking['id_order'] = $detail->id_order; 
    
                                    self::insertData( (object)$picking, $picking['product_quantity'], $status, $order->carrier->name);
                                    
                                }
                            }else{
                                $quantity = $detail->product_quantity - $detail->qtd_sent;
                                self::insertData($detail, $quantity, $status, $order->carrier->name);
                            }
                        }
                    }
                }
            }    
        }
    }
        
    private static function insertData($row, $quantity, $status, $carrier_name){
        
        $exist = self::where('id_order', $row->id_order)->where('id_product', $row->product_id)->where('id_product_attribute', $row->product_attribute_id)->count();
        
        if( !$exist ){
            picking::insert(
                [
                    'status'  => $status,
                    'housing'  => (!is_null($row->location)) ? $row->location : 'N/D',
                    'id_shop' => $row->id_shop,
                    'name' => $row->product_name,
                    'id_product' => $row->product_id,                    
                    'id_product_attribute' => $row->product_attribute_id,                      
                    'reference' => $row->product_reference,        
                    'product_barcode' => $row->product_ean13,  
                    'quantity' => $quantity,                
                    'quantity_picked' => 0,     
                    'row_done' => 0,               
                    'id_order' => $row->id_order,
                    'carrier' => $carrier_name
                ]
            );
        }

    }

    public static function rowDone($data) {

        $user = Auth::id() . ' - ' . Auth::user()->name;
        
        picking::where('id_order', $data->id_order)
                ->where('id_product', $data->id_product)
                ->where('id_product_attribute', $data->id_product_attribute)
                ->where('quantity', $data->scannedQuantity)
                ->where('product_barcode', $data->barcode)
                ->orWhere('reference', $data->barcode)
                ->update(
                    [
                        'quantity_picked' => $data->scannedQuantity, 
                        'barcode' => $data->pickingContainer, 
                        'row_done' => 1
                    ]
                );
        
        picking::where('id_order', $data->id_order)->update( ['operator' => $user, 'barcode' => $data->pickingContainer ] );
        
        return self::orderDone($data->id_order);

    }

    private static function orderDone($id_order) {
        
        $rowsOfOrder = picking::where('id_order', $id_order)->count();
        $pickedRowsOfOrder = picking::where('id_order', $id_order)->where('row_done', 1)->count();
        
        if( $rowsOfOrder == $pickedRowsOfOrder ) return 1;

        return 2;
    }

    public static function getEAN($data) {
        
        $product = product::select('ean13')->where( 'ean13', $data->code )->orWhere( 'reference', $data->code )->first();
        
        if( isset( $product->ean13 ) ) return $product->ean13;
        
        $product = product_attribute::select('ean13')->where( 'ean13', $data->code )->orWhere( 'reference', $data->code )->first();
        
        if( isset( $product->ean13 ) ) return $product->ean13;

        return 999;

    }

}