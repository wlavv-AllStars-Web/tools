<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

use App\Models\prestashop\suppliers;

use App\Models\modules\backorders_list\backorders_list;

use App\Models\modules\bms_procurement\bms_procurement_purchase_order;
use App\Models\modules\bms_procurement\bms_procurement_purchase_order_product;

class suppliersBackordersController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('messages.suppliersBackorders'), 'url' => route('suppliersBackorders.index')];
    }

    public function index(){
        
        $current_year = date('Y');
        $current_month= date('M');
        
        $available_data = backorders_list::checkIfExistData($current_month, $current_year);
        
        if($available_data == 0){
            self::insertData();
        }
        
        $suppliers = backorders_list::getSuppliersBackordersOf($current_month, $current_year);
        
        $openOrders = backorders_list::getBackordersOfSupplier($suppliers[0]->id_supplier, $current_month, $current_year);
        $replied = backorders_list::getSupplierRepliedFromTokenOf($suppliers[0]->id_supplier, $suppliers[0]->token);

        $dataView = [ 
            'openOrders' => $openOrders, 
            'token' => $suppliers[0]->token,
            'reply' => $replied,
            'quantity_replied' => $suppliers[0]->quantity_replied,
            'number_of_rows' => $suppliers[0]->number_of_rows,
            'selected_supplier_name' => $suppliers[0]->supplier,
            'selected_supplier_id'   => $suppliers[0]->id_supplier
        ];
        
        $supplierBackorder = '';
        $supplierBackorder = view('customTools.suppliersBackorders.includes.render', compact('dataView'))->render();

        $data = [
            'suppliers'   => $suppliers,
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs,
            'supplierBackorder' => $supplierBackorder
        ];

        return View::make('customTools/suppliersBackorders/index')->with($data);
    }
    
    public function getSuppliersBackorders(Request $request){

        $current_year = date('Y');
        $current_month= date('M');
        
        $supplier = backorders_list::getSupplierBackordersOf($request->id_supplier, $current_month, $current_year);
        $openOrders = backorders_list::getBackordersOfSupplier($request->id_supplier, $current_month, $current_year);
        
        $dataView = [ 
            'openOrders' => $openOrders, 
            'reply' => $supplier->reply,
            'token' => $supplier->token,
            'quantity_replied' => $supplier->quantity_replied,
            'number_of_rows' => $supplier->number_of_rows,
            'selected_supplier_name' => $supplier->supplier,
            'selected_supplier_id'   => $supplier->id_supplier
        ];
        
        $viewRendered = view('customTools.suppliersBackorders.includes.render', compact('dataView'))->render();
        return response()->json([ 'html' => $viewRendered ]);
    }

    public function insertData(){ 
        
        $openOrders = bms_procurement_purchase_order::select('supplier_id')->where('date_add', '<', date("Y-m-d", strtotime("-1 months")))->whereIn('status_id', [5, 6])->groupBy('supplier_id')->get();

        foreach($openOrders AS $key => $order){
            
            $openOrdersOfSupplier = bms_procurement_purchase_order::getOpenOrdersWithRows($order->supplier_id);

            $num1 = rand(10000,99999);
            $num2 = rand(1000,9999);
            $num3 = rand(100,999);
            $token = $num1.$num2.$num3;

            $supplier_id = $order->supplier_id;
            $supplier_name = suppliers::where('id_supplier', $order->supplier_id)->value('name');

            foreach($openOrdersOfSupplier AS $order_supplier){
                
                $order_reference = $order_supplier->reference;
                $order_id = $order_supplier->id_bms_procurement_purchase_order;
                $order_date = $order_supplier->date_add;
                $order_month = date("M",strtotime($order_supplier->date_add));
                
                foreach($order_supplier->rows AS $row){

                    $data = [
                        'id_supplier' => $supplier_id,
                        'supplier' => $supplier_name,
                        'report_month' => date('M'),
                        'report_year' => date('Y'),
                        'order_id' => $order_id,
                        'order_reference' => $order_reference,
                        'order_date' => $order_date,
                        'order_month' => $order_month,
                        'product_reference' => $row->sku,
                        'qty_ordered' => $row->qty_ordered,
                        'qty_billed' => $row->qty_wmfaturado,
                        'qty_received' => $row->qty_received,
                        'token' => $token
                    ];
                    
                    backorders_list::saveBackordersReport($data);
                }
            }
        }
    }

    public function send_report($id_supplier, $token){

        $supplier = backorders_list::getFirstSupplierBackordersFromTokenOf($id_supplier, $token);
        $openOrders = backorders_list::getBackordersOfSupplierFromToken($id_supplier, $token);
        
        $dataView = [ 
            'token' => $supplier->token,
            'openOrders' => $openOrders, 
            'selected_supplier_name' => $supplier->supplier,
            'selected_supplier_id'   => $supplier->id_supplier
        ];
        
        $html = view('customTools/suppliersBackorders/email', compact('dataView'))->render();
        $email = suppliers::where('id_supplier', $supplier->id_supplier)->value('email');
        $subject = "ALL STARS BACK ORDERS OVERVIEW - " . $supplier->supplier . ' ( ' . date('m-Y') . ' )' . ' - ' . $email;        

        config(['mail.mailers.smtp.username' => 'suppliers@all-stars-distribution.com']);
        config(['mail.mailers.smtp.password' => 'D*223080261789ab']);
        config(['mail.from.address' => 'suppliers@all-stars-distribution.com']);
        config(['mail.from.name' => 'ALL STARS']);

        Mail::html($html, function ($message) use ($email, $subject) {
            $message->to($email)->subject($subject);
        });
    }

    public function create(){ }
    public function store(Request $request){ }
    public function show(string $id){ }
    public function edit(string $id){ }
    public function update(Request $request, string $id){ }
    public function destroy(string $id){ }
}
