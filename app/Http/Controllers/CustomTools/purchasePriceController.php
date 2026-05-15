<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Services\PurchasePriceSyncService;

use App\Models\prestashop\manufacturers;

class purchasePriceController extends Controller
{
    public $actions;
    public $breadcrumbs;

    public function index(){
        
        $this->breadcrumbs[] = [ 'name' =>  trans('administration'), 'url' => route('sales.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('PURCHASE PRICE'), 'url' => route('purchasePrice.index')];
        
        $brands = manufacturers::orderBy('name', 'ASC')->get();
        
        $data = [
            'actions'    => [],
            'breadcrumbs'=> $this->breadcrumbs,
            'manufacturers'     => $brands
        ];
        
        return View::make('customTools/purchasePrice/index')->with($data);
    }

    public function update(Request $request, PurchasePriceSyncService $service): RedirectResponse{
        
        $request->validate([ 'id_manufacturer' => ['required', 'integer', 'min:1'] ]);

        $result = array();
        $id = (int) $request->integer('id_manufacturer');
        $brand = manufacturers::where('id_manufacturer', $id)->first();
        
        /** Logica para update em ASD **/
        $result = $service->syncByManufacturerId($id);

        $msg = "Synchronization of {$brand->name} completed! {$result['updated']} updated, {$result['not_found']} not found.";
        return back()->with('status', $msg)->with('result', $result);
    }

    public function updateAll(Request $request, PurchasePriceSyncService $service): RedirectResponse{

        $result = array();
        $id = $request->integer('id_manufacturer');
        
        if($id == 'ALL'){

            /** Logica para update em ASD **/
            /** $result = $service->syncByManufacturerId($id); **/
            
            $msg = "Full synchronization completed: {$result['updated']} updated, {$result['not_found']} not found.";
            return back()->with('status', $msg)->with('result', $result);
        }else{
            return back();
        }
    }
    
}
