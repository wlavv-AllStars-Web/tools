<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;


use App\Models\modules\backorders_list\backorders_list;

class suppliersBackordersController extends Controller
{
    public function __construct(){ }

    public function index($id_supplier, $token){

        $replied = backorders_list::getSupplierRepliedFromTokenOf($id_supplier, $token);
        $supplier = backorders_list::getFirstSupplierBackordersFromTokenOf($id_supplier, $token);
        $openOrders = backorders_list::getBackordersOfSupplierFromToken($id_supplier, $token);

        if(is_null($supplier)) return View::make('customTools/suppliersBackorders/not_available');

        $dataView = [ 
            'token' => $supplier->token,
            'openOrders' => $openOrders, 
            'selected_supplier_name' => $supplier->supplier,
            'selected_supplier_id'   => $supplier->id_supplier
        ];
    
        if( $replied ){
            return View::make('customTools/suppliersBackorders/already_replied', compact('dataView'));
        }else{
            return View::make('customTools/suppliersBackorders/front', compact('dataView'));
        }
    }

    public function updateBackorders(Request $request){
        
        backorders_list::where('id', $request->get('id'))->where('id_supplier', $request->get('id_supplier'))->where('token', $request->get('token'))->update([
            'reply'         => 1,
            'reply_date'    => date('Y-m-d'),
            'in_backorder'  => $request->get('in_backorder'),
        ]);
    }

    public function updateComment(Request $request){
        
        backorders_list::where('id', $request->get('id'))->where('id_supplier', $request->get('id_supplier'))->where('token', $request->get('token'))->update([
            'reply'             => 1,
            'reply_date'        => date('Y-m-d'),
            'supplier_comment'  => $request->get('comment'),
        ]);
    }

    public function thanks($id_supplier, $token){
        
        $supplier = backorders_list::getFirstSupplierBackordersFromTokenOf($id_supplier, $token);
        
        $dataView = [ 
            'selected_supplier_name' => $supplier->supplier,
            'selected_supplier_id'   => $supplier->id_supplier
        ];
        
        return View::make('customTools/suppliersBackorders/thanks', compact('dataView'));
    }
}
