<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use App\Models\modules\bms_procurement\bms_procurement_purchase_order;
use App\Models\modules\bms_procurement\bms_procurement_purchase_order_product;

class stockEntryController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('Logistics'), 'url' => route('logistics')];
        $this->breadcrumbs[] = [ 'name' =>  trans('Stock entry'), 'url' => route('bmsProcurement.index')];

    }

    public function index()
    {
        echo "stockEntryController INDEX";
        exit;
    }

    public function show(string $id){ 

        $this->breadcrumbs[] = [ 'name' => 'Entry form',   'url' => route('stockEntry.show', $id)];

        $data = [
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs
        ];

        return View::make('customTools/stockEntry/show')->with($data);
    }

    public function edit(string $id){ }
    public function update(Request $request, string $id){ }
    public function create(){ }
    public function store(Request $request){ }
    public function destroy(string $id){ }

    public function post(Request $request){ 

        $product = [];
        $message = "Product not found!";
        if($request->action == 'getProductByEAN'){
            $product = bms_procurement_purchase_order_product::where('wmean13', $request->code)->with('product', 'attribute')->first();
            if(isset($product->product_id)) $message = "Product found by EAN-13";
        }elseif($request->action == 'getProductByRef'){
            $product = bms_procurement_purchase_order_product::where('sku', $request->code)->with('product', 'attribute')->first();
            if(isset($product->product_id)) $message = "Product found by reference";
        }

        return response()->json([
            'status' => 'success', 
            'product' => $product, 
            'message' => $message
        ], 200);

    }


}
