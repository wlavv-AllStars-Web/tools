<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;

use App\Models\prestashop\suppliers;

use App\Models\prestashop\product;
use App\Models\prestashop\product_lang;
use App\Models\prestashop\orders_details;
use App\Models\prestashop\stock_available;
use App\Models\prestashop\product_attribute;

use App\Models\modules\auto_orders\auto_orders;
use App\Models\modules\auto_orders\cross_auto_orders;
use App\Models\modules\auto_orders\auto_orders_purchase_list;

use App\Models\modules\bms_procurement\bms_procurement_purchase_order;
use App\Models\modules\bms_procurement\bms_procurement_purchase_order_product;

class erpController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('web'), 'url' => route('customer.index')];
    }

    public function index( $list = 1 ){
        
        if($list == 3) $data = suppliers::with('address')->orderBy('name', 'ASC')->get();
        
        if($list == 2){
            $table = (new bms_procurement_purchase_order)->getTable();
            
            $data = bms_procurement_purchase_order::query()
                ->whereNotIn('status_id', [5, 6])
                ->selectRaw('supplier_id, COUNT(*) AS total_orders')
                ->groupBy('supplier_id')
                ->join(env('DB2_prefix').'supplier as s', 's.id_supplier', '=', $table.'.supplier_id')
                ->orderBy('s.name')
                ->get();
        }
        
        if($list == 1){
            $table = (new bms_procurement_purchase_order)->getTable();
            
            $data = bms_procurement_purchase_order::query()
                ->whereIn('status_id', [5, 6])
                ->selectRaw('supplier_id, COUNT(*) AS total_orders')
                ->groupBy('supplier_id')
                ->join(env('DB2_prefix').'supplier as s', 's.id_supplier', '=', $table.'.supplier_id')
                ->orderBy('s.name')
                ->get();
        }
        
        return view('customTools.erp.index', compact('data', 'list'));
        
    }

    public function ordersOfSupplier( Request $request ){

        $table = (new bms_procurement_purchase_order)->getTable();
        
        $orders = bms_procurement_purchase_order::where('supplier_id', $request->id_supplier);
        
        $openOrders = $request->openOrders + 0;
        
        if($request->openOrders == 1){
            $orders->whereIn('status_id', [5, 6]);
        }else{
            $orders->whereNotIn('status_id', [5, 6]);
        }
        
        $orders = $orders->get();
        
        $supplier = suppliers::where('id_supplier', $request->id_supplier)->first();
        
        return view('customTools/erp/includes/lists/openOrdersOf', compact('orders', 'supplier', 'openOrders'))->render();
        
    }
    
    public function getOrderDetailsOf($po_id){
        
        $order = bms_procurement_purchase_order::with('rows.product.stock', 'rows.attribute.stock')->where('id_bms_procurement_purchase_order', $po_id)->first();
        
        $order_total = 0;
        
        foreach($order->rows AS $product){

            if( isset( $product->product ) ){
                $order_total += $product->qty_wmfaturado * $product->product->wholesale_price;
            }else{
                $wholesale_price = ( isset($product->product) ) ? $product->product->wholesale_price : 0;
                $wholesale_price_attr = ( isset($product->attribute) ) ? $product->attribute->wholesale_price : 0;
                $order_total += $product->qty_wmfaturado *  ( $wholesale_price + $wholesale_price_attr );
            }
            
        }
        
        $sums = bms_procurement_purchase_order_product::selectRaw('
                COUNT(*) as number_of_rows,
                SUM(qty_ordered) as total_qty_ordered,
                SUM(qty_wmfaturado) as total_qty_faturado,
                SUM(qty_received) as total_qty_received
            ')
            ->where('po_id', $po_id)
            ->first();
        
        return view('customTools.erp.order', compact('order', 'sums', 'order_total'));
    }
    
}