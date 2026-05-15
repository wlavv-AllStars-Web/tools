<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\prestashop\suppliers;
use App\Models\prestashop\manufacturers;
use App\Models\modules\supplier_map\supplier_map;

class suppliersMapController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function index(){
        
        $this->breadcrumbs[] = ['name' => 'purchase', 'url' => route('purchase.index')];
        $this->breadcrumbs[] = ['name' => "Supplier's map", 'url' => route('suppliersMap.index'), 'no_translation' => 1];
        
        $supplierMap = supplier_map::getAll();
        
        $data = [
            'actions'    => [],
            'breadcrumbs'=> $this->breadcrumbs,
            'new_manufacturers' => manufacturers::orderBy('name', 'ASC')->get(),
            'new_suppliers' => suppliers::orderBy('name', 'ASC')->get(),
            'supplierMap' => $supplierMap
        ];
        
        return View::make('customTools/suppliers/map/index')->with($data);
    }
    
    public function store(Request $request){
        supplier_map::saveData($request);
        return redirect()->route('suppliersMap.index');
    }
    
    public function modal(Request $request){
        
        $supplier_map = supplier_map::getData($request);
        $new_manufacturers = manufacturers::orderBy('name', 'ASC')->get();
        $new_suppliers = suppliers::orderBy('name', 'ASC')->get();
            
        return response()->json([
            'html' => view('customTools.suppliers.map.includes.modals.edit', compact('supplier_map', 'new_manufacturers', 'new_suppliers'))->render(),
        ]);
    }
    
}
