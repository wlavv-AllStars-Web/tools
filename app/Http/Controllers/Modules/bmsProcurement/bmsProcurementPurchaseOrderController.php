<?php

namespace App\Http\Controllers\Modules\bmsProcurement;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use App\Models\modules\bms_procurement\bms_procurement_purchase_order;
use App\Models\prestashop\suppliers;

class bmsProcurementPurchaseOrderController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('Logistics'), 'url' => route('logistics.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('Stock entry'), 'url' => route('bmsProcurement.index')];
    }

    public function index()
    {
        $suppliersWithOpenOrders = bms_procurement_purchase_order::with('supplier')->where('status_id', '5')->groupBy('supplier_id')->orderBy('supplier_id')->get();

        $data = [
            'suppliersWithOpenOrders' => $suppliersWithOpenOrders,
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs
        ];

        return View::make('areas/logistics/bmsProcurement/index')->with($data);
    }

    public function show(string $id){ 

        $supplier = suppliers::where('id_supplier', $id)->first();
        
        $this->actions[]     = [ 'name' => 'Do entry', 'url' => route('stockEntry.show', $id), 'class' => "btn btn-success"];
        $this->actions[]     = [ 'name' => 'Supplier info', 'url' => route('suppliers.show', $id), 'class' => " btn-warning"];
        $this->actions[]     = [ 'name' => 'Remove entry', 'url' => "/remove", 'class' => " btn-info btn-action"];
        $this->breadcrumbs[] = [ 'name' => 'Orders list', 'url' => route('bmsProcurement.show', $id)];

        $openOrders = bms_procurement_purchase_order::with('rows.product','rows.attribute')->where('status_id', '5')->where('supplier_id', $id)->orderBy('supplier_id', 'DESC')->get();

        $data = [
            'openOrders' => $openOrders,
            'supplier'   => $supplier,
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs
        ];

        return View::make('areas/logistics/bmsProcurement/entryView')->with($data);
    }

    public function edit(string $id) { }
    public function update(Request $request, string $id) { }
    public function create(){ }
    public function store(Request $request){ }
    public function destroy(string $id){ }
}
