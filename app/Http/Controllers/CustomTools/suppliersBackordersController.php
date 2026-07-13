<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use App\Services\Mail\StoreMailer;

use App\Models\prestashop\suppliers;

use App\Models\modules\backorders_list\backorders_list;
use App\Models\modules\supplier_map\supplier_map;
use App\Services\oms\OmsLegacyProcurementService;

class suppliersBackordersController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $indexRoute = request()->routeIs('purchase.tools.suppliersBackorders.*')
            ? 'purchase.tools.suppliersBackorders.index'
            : (request()->routeIs('backoffice.tools.suppliersBackorders.*') ? 'backoffice.tools.suppliersBackorders.index' : 'suppliersBackorders.index');

        $this->breadcrumbs[] = ['name' => 'purchase', 'url' => route('purchase.index')];
        $this->breadcrumbs[] = ['name' => trans('messages.suppliersBackorders'), 'url' => route($indexRoute), 'no_translation' => 1];
    }

    public function index(){
        
        $current_year = date('Y');
        $current_month= date('M');
        
        $available_data = backorders_list::checkIfExistData($current_month, $current_year);
        
        if($available_data == 0){
            self::insertData();
        }
        
        $suppliers = backorders_list::getSuppliersBackordersOf($current_month, $current_year);
        
        $supplierBackorder = '<div class="alert alert-info" style="margin: 15px;">No supplier backorders found for the current month.</div>';

        if ($suppliers->isNotEmpty()) {
            $selectedSupplier = $suppliers->first();
            $openOrders = backorders_list::getBackordersOfSupplier($selectedSupplier->id_supplier, $current_month, $current_year);
            $replied = backorders_list::getSupplierRepliedFromTokenOf($selectedSupplier->id_supplier, $selectedSupplier->token);

            $dataView = [ 
                'openOrders' => $openOrders, 
                'token' => $selectedSupplier->token,
                'reply' => $replied,
                'quantity_replied' => $selectedSupplier->quantity_replied,
                'number_of_rows' => $selectedSupplier->number_of_rows,
                'selected_supplier_name' => $selectedSupplier->supplier,
                'selected_supplier_id'   => $selectedSupplier->id_supplier
            ];
            
            $supplierBackorder = view('customTools.suppliersBackorders.includes.render', compact('dataView'))->render();
        }

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

        if (!$supplier) {
            return response()->json([
                'html' => '<div id="backorders_suppliers_holder"><div class="alert alert-info" style="margin: 15px;">No supplier backorders found.</div></div>'
            ]);
        }

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
        
        $openOrderLines = OmsLegacyProcurementService::openBackorderLinesOlderThan(date("Y-m-d", strtotime("-1 months")));

        foreach($openOrderLines->groupBy('supplier_id') AS $supplierId => $openOrdersOfSupplier){

            $num1 = rand(10000,99999);
            $num2 = rand(1000,9999);
            $num3 = rand(100,999);
            $token = $num1.$num2.$num3;

            $supplier_id = (int) $supplierId;
            $supplier_name = suppliers::where('id_supplier', $supplier_id)->value('name');

            foreach($openOrdersOfSupplier AS $order_supplier){
                
                $order_date = $order_supplier->order_date;

                $data = [
                    'id_supplier' => $supplier_id,
                    'supplier' => $supplier_name,
                    'report_month' => date('M'),
                    'report_year' => date('Y'),
                    'order_id' => $order_supplier->order_id,
                    'order_reference' => $order_supplier->order_reference,
                    'order_date' => $order_date,
                    'order_month' => date("M", strtotime($order_date)),
                    'product_reference' => $order_supplier->product_reference,
                    'qty_ordered' => $order_supplier->qty_ordered,
                    'qty_billed' => $order_supplier->qty_billed,
                    'qty_received' => $order_supplier->qty_received,
                    'token' => $token
                ];
                
                backorders_list::saveBackordersReport($data);
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
        $email = supplier_map::where('id_supplier', $supplier->id_supplier)->value('email') ?: '';
        $subject = "ALL STARS BACK ORDERS OVERVIEW - " . $supplier->supplier . ' ( ' . date('m-Y') . ' )' . ' - ' . $email;        

        StoreMailer::sendHtml('asd_sales', $email, $subject, $html);
    }

    public function create(){ }
    public function store(Request $request){ }
    public function show(string $id){ }
    public function edit(string $id){ }
    public function update(Request $request, string $id){ }
    public function destroy(string $id){ }
}
