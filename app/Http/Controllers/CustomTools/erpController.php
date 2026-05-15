<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

use App\Http\Controllers\Controller;

use App\Models\prestashop\suppliers;

use App\Models\prestashop\product;
use App\Services\oms\OmsLegacyProcurementService;

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
            $data = OmsLegacyProcurementService::supplierCounts(false);
        }
        
        if($list == 1){
            $data = OmsLegacyProcurementService::supplierCounts(true);
        }
        
        return view('customTools.erp.index', compact('data', 'list'));
        
    }

    public function ordersOfSupplier( Request $request ){

        $openOrders = $request->openOrders + 0;
        $orders = OmsLegacyProcurementService::ordersOfSupplier((int) $request->id_supplier, $openOrders === 1);
        
        $supplier = suppliers::where('id_supplier', $request->id_supplier)->first();
        
        return view('customTools/erp/includes/lists/openOrdersOf', compact('orders', 'supplier', 'openOrders'))->render();
        
    }
    
    public function getOrderDetailsOf($po_id){
        
        $order = OmsLegacyProcurementService::orderDetails((int) $po_id);

        abort_if(!$order, 404);
        
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
        
        $sums = OmsLegacyProcurementService::lineSums((int) $po_id);
        
        return view('customTools.erp.order', compact('order', 'sums', 'order_total'));
    }
    
}
