<?php

namespace App\Models\modules\bms_procurement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\prestashop\product;
use App\Models\prestashop\product_attribute;

use App\Models\modules\supplier_delivery_issues\supplier_delivery_issues;

class bms_procurement_purchase_order_product extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."bms_procurement_purchase_order_product";
    }

    public function product()
    {
        return $this->hasOne(product::class, "id_product", 'product_id');
    }

    public function attribute()
    {
        return $this->hasOne(product_attribute::class, "id_product_attribute", 'product_attribute_id');
    }

    public static function getCountProductsOfOrder($id)
    {
        return bms_procurement_purchase_order_product::where('po_id', $id)->count();
    }

    public static function getProductsOfOrder($id)
    {
        return bms_procurement_purchase_order_product::where('po_id', $id)->get();
    }
    
    public static function getDetailProductsOfOrder($id)
    {
        return bms_procurement_purchase_order_product::with('product', 'attribute')->where('po_id', $id)->get();
    }

    public static function getTotalPriceOfOrder($id)
    {
        return bms_procurement_purchase_order_product::where('po_id', $id)->sum('price');
    }

    public static function getTotalBasePriceOfOrder($id)
    {
        return bms_procurement_purchase_order_product::where('po_id', $id)->sum('price_base');
    }

    public static function getTotalProductsOrdered($id)
    {
        return bms_procurement_purchase_order_product::where('po_id', $id)->sum('qty_ordered');
    }

    public static function getTotalProductsInvoiced($id)
    {
        return bms_procurement_purchase_order_product::where('po_id', $id)->sum('qty_wmfaturado');
    }

    public static function getTotalProductsReceived($id)
    {
        return bms_procurement_purchase_order_product::where('po_id', $id)->sum('qty_received');
    }

    public static function getTotalProductsExpected($id)
    {
        return bms_procurement_purchase_order_product::where('po_id', $id)->sum('qty_expected');
    }

    public static function checkIfWaitingQuantity($reference)
    {
        return bms_procurement_purchase_order_product::where('sku', $reference)->where('qty_expected', '>', 0)->get();
    }

    public static function getCountersOfOrder($id)
    {
        return [
            'ordered' => self::getTotalProductsOrdered($id),
            'invoiced' => self::getTotalProductsInvoiced($id),
            'received' => self::getTotalProductsReceived($id),
            'expected' => self::getTotalProductsExpected($id),
            'total_price' => self::getTotalPriceOfOrder($id),
            'total_base_price' => self::getTotalBasePriceOfOrder($id)
        ];
    }
    
    public static function createOrderRows($id_order, $data, $products)
    {
        foreach($products AS $product){

            $db_product = product::where('reference', $product->reference)->first();
            $db_attribute = product_attribute::where('reference', $product->reference)->first();
            
            if(isset($db_attribute->id_product)){
                $db_product = product::where('id_product', $db_attribute->id_product)->first();
            }

            if(isset($db_product->id_product)){

                $row = new bms_procurement_purchase_order_product();
                $row->po_id = $id_order;
                $row->name= $product->name;
                $row->sku = (isset( $db_attribute->id_product_attribute)) ? $db_attribute->reference : $product->reference;
                $row->supplier_sku = '';
                $row->qty_ordered = $product->quantity;
                $row->qty_received = 0;
                $row->qty_expected = $product->quantity;
                $row->qty_wmfaturado = 0;
                $row->tax_rate = 0;
                $row->extended_cost = 0;
                $row->extended_cost_base = 0;
                $row->is_new = 0;
                $row->printed = 0;
                $row->date_wmfaturado = date('Y-m-d');
                $row->product_id = $db_product->id_product;
                $row->product_attribute_id =  (isset( $db_attribute->id_product_attribute)) ? $db_attribute->id_product_attribute : 0;
                $row->price = $db_product->price;
                $row->price_base = $db_product->wholesale_price;
                $row->wmsku = $db_product->reference;
                $row->wmean13 = (isset( $db_attribute->reference)) ? $db_attribute->ean13 : $db_product->ean13;
                $row->save();

                if(isset( $db_attribute->reference )){
                    product_attribute::where('reference', $db_attribute->reference )->increment('stock_arrivepa', $product->quantity);
                }else{
                    product::where('reference', $product->reference )->increment('stock_arrive', $product->quantity);
                }
            }else{
                /*
				Mail::send([], [], function ($message) {
				   $message->to('bruno.fernandes.asm@gmail.com')->subject('TOOLS::auto_orders - Order creation')->setBody($product->reference . ' - ' . $product->quantity);
				});*/
				
				/*echo $product->reference;
				exit;*/
			}

        }

        return 1;
        
    }
    
    public static function getOrdersWithDeliveryIssues(){
        
        $bySupplier = bms_procurement_purchase_order_product::select('ps_bms_procurement_purchase_order.supplier_id', 'ps_supplier.name', 'qty_wmfaturado', 'qty_received')->join(
            'ps_bms_procurement_purchase_order', 
            'ps_bms_procurement_purchase_order.id_bms_procurement_purchase_order', 
            'ps_bms_procurement_purchase_order_product.po_id'
        )->join(
                'ps_supplier', 
                'ps_supplier.id_supplier', 
                'ps_bms_procurement_purchase_order.supplier_id'
            )
        ->whereRaw('TRIM(ps_bms_procurement_purchase_order_product.qty_wmfaturado) <> TRIM(ps_bms_procurement_purchase_order_product.qty_received)')
        ->where('ps_bms_procurement_purchase_order.reception_progress', '>', 0)
        ->whereIn('ps_bms_procurement_purchase_order.status_id', [5, 7])
        ->groupBy('ps_bms_procurement_purchase_order.supplier_id')
        ->orderBy('ps_supplier.name')
        ->get();
        
        $issues = array();
        
        foreach($bySupplier AS $supplier){

            $results = bms_procurement_purchase_order_product::join(
                'ps_bms_procurement_purchase_order', 
                'ps_bms_procurement_purchase_order.id_bms_procurement_purchase_order', 
                'ps_bms_procurement_purchase_order_product.po_id'
            )->join(
                'ps_supplier', 
                'ps_supplier.id_supplier', 
                'ps_bms_procurement_purchase_order.supplier_id'
            )
            ->where('ps_bms_procurement_purchase_order.reception_progress', '>', 0)
            ->whereRaw('TRIM(ps_bms_procurement_purchase_order_product.qty_wmfaturado) <> TRIM(ps_bms_procurement_purchase_order_product.qty_received)')
            ->whereIn('ps_bms_procurement_purchase_order.status_id', [5, 7])
            ->where('ps_bms_procurement_purchase_order.supplier_id', $supplier->supplier_id)
            ->get();

            $results = $results->map(function ($item) {
                
                
                    $item->comment = '';
                
                
                return $item;
            });

            $issues[] = $results;
        }

        return $issues;
    }
}
