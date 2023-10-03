<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Models\ps_bms_procurement_purchase_order;

class stockEntryController extends Controller
{

    public function index()
    {
        $suppliersWithOpenOrders = ps_bms_procurement_purchase_order::with('supplier')->where('status_id', '5')->groupBy('supplier_id')->orderBy('supplier_id')->get();
        return View::make('areas/logistics/stockEntry/index')->with('suppliersWithOpenOrders', $suppliersWithOpenOrders);
    }

    public function show(string $id){ 
        $openOrders = ps_bms_procurement_purchase_order::with('supplier')->where('status_id', '5')->where('supplier_id', $id)->orderBy('supplier_id', 'DESC')->get();
        return View::make('areas/logistics/stockEntry/entryView')->with('openOrders', $openOrders);
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function create(){ }
    public function store(Request $request){ }
    public function destroy(string $id){ }
}
