<?php

namespace App\Http\Controllers\Modules\bmsProcurement;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use App\Models\modules\bms_procurement\bms_procurement_purchase_order;
use App\Models\prestashop\suppliers;

class bmsProcurementController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('bmsProcurement'), 'url' => route('bmsProcurement.index')];
    }

    public function index() {

        $suppliersWithOpenOrders = bms_procurement_purchase_order::with('supplier')->where('status_id', '5')->groupBy('supplier_id')->orderBy('supplier_id')->get();

        $data = [
            'suppliersWithOpenOrders' => $suppliersWithOpenOrders,
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs
        ];

        return View::make('modules/bmsProcurement/index')->with($data);
    }

    public function create() {}
    public function store(Request $request) { }
    public function show(string $id) { }
    public function edit(string $id) { }
    public function update(Request $request, string $id) { }
    public function destroy(string $id) { }
}