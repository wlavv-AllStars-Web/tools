<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Models\modules\bms_procurement\bms_procurement_purchase_order;
use App\Models\modules\bms_procurement\bms_procurement_purchase_order_product;
use App\Models\modules\bms_procurement\bms_procurement_purchase_order_reception_product;
use App\Models\modules\bms_procurement\bms_procurement_purchase_order_reception;
use App\Models\prestashop\issues;
use App\Models\prestashop\orders;
use App\Models\prestashop\product;
use App\Models\prestashop\stock_available;
use App\Models\prestashop\product_attribute;
use App\Models\prestashop\orders_details;

class stockEntryController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('Logistics'), 'url' => route('logistics.index')];
    }

    public function show(string $id){ 

        $this->breadcrumbs[] = [ 'name' => 'Entry form',   'url' => route('stockEntry.show', $id)];
        $this->actions[]     = [ 'name' => 'Remove entry', 'icon' => '<i class="f-left fa fa-trash"></i>', 'url' => route('stockEntry.listToRemove'), 'class' => "btn btn-danger"];

        $data = [
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs
        ];

        return View::make('customTools/stockEntry/show')->with($data);
    }

    public function post(Request $request){ 

        $data_found = 0;

        $product = [];
        $message = "Product not found!";
        if($request->action == 'getProductByEAN'){
            $product = bms_procurement_purchase_order_product::where('wmean13', $request->code)->where('qty_expected', '>', 0)->with('product', 'attribute')->first();
            if(isset($product->product_id)) $message = "Product found by EAN-13";
        }elseif($request->action == 'getProductByRef'){
            $product = bms_procurement_purchase_order_product::where('sku', $request->code)->where('qty_expected', '>', 0)->with('product', 'attribute')->first();
            if(isset($product->product_id)) $message = "Product found by reference";
        }
        
        if(isset($product->sku)) $data_found = 1;
        
        if($data_found == 1){

            $partial      = orders::getParcials($product->sku);
            $preparations = orders::getPreparations($product->sku);
            $backorders   = orders::getBackorders($product->sku);

            return response()->json([
                'status' => 'success', 
                'product' => $product, 
                'partials' => $partial, 
                'preparations' => $preparations, 
                'backorders' => $backorders, 
                'message' => $message,
                'updateRoute' => route('stockEntry.update', $product->product_id)
            ], 200);

        }else{

            $productInfo = product::where('ean13', $request->code)->first();

            if(isset($productInfo->reference)){
                $message =  "No orders open for: " .$productInfo->reference;
            }else{
                $message =  $request->code . " is not related with any product on our database. Please verify!";
            }
            
            return response()->json([
                'status' => 'nok', 
                'message' => $message,
            ], 200);
            
        }

    }

    public function update(Request $request, string $id){ 

        $data = array();

        if($request->action == 'measurementsForm'){

            product::where('reference', $request->get('reference'))->update(
                [
                    'parcels'    => $request->get('multibox'),
                    'fc'         => $request->get('fc'),
                    'width'      => $request->get('width'),
                    'height'     => $request->get('height'),
                    'depth'      => $request->get('depth'),
                    'weight'     => $request->get('weight'),
                    'dim_verify' => 1
                ]);

                

            $data = response()->json([
                'status' => 'success', 
                'message' => "Data updated successfully!",
            ], 200);


        }elseif($request->action == 'saveStockEntry'){

            $current = bms_procurement_purchase_order_product::where('id_bms_procurement_purchase_order_product', $request->id_bms_procurement_purchase_order_product)->first();

            $quantityToReceive = $request->received - $current->qty_received;

            //product -> StockArrive ( Descontar QTD arrive )
            product::where('reference', $request->get('reference'))->update( [ 'stock_arrive' => $quantityToReceive ] );

            //attribute -> StockArrive ( Descontar QTD arrive )
            product_attribute::where('reference', $request->get('reference'))->update( [ 'stock_arrivepa' => $quantityToReceive ] );

            //ps_bms_procurement_purchase_order_product ( atualizar qty_received | qty_expected  )
            bms_procurement_purchase_order_product::where('id_bms_procurement_purchase_order_product', $request->get('id_bms_procurement_purchase_order_product'))->update( 
                [ 
                    'qty_received' => $request->received,
                    'qty_expected' => $current->qty_ordered - $request->received,
                ] 
            );

            //ps_bms_procurement_purchase_order_reception ( criar linha  de recepção )
            $bms_procurement_purchase_order_reception_data = bms_procurement_purchase_order_reception::create(
                [
                    'po_id' => $current->po_id,
                    'date_add' => date("Y-m-d h:i:s"),
                    'employee_id' => 1,
                    'products_count' => 0
                ]
            );

            //ps_bms_procurement_purchase_order_reception_product ( criar linha  de recepção )
            bms_procurement_purchase_order_reception_product::create(
                [
                    'reception_id' => $bms_procurement_purchase_order_reception_data->id,
                    'product_id' => $request->get('id_product'),
                    'product_attribute_id' => $request->get('id_product_attribute'),
                    'sku' => $request->get('reference'),
                    'name' => '',
                    'qty' => $quantityToReceive,
                ]
            );

            $new_current = bms_procurement_purchase_order_product::select( DB::raw('SUM(qty_received) AS received, SUM(qty_ordered) AS ordered, SUM(qty_expected) AS expected'))->where('po_id', $current->po_id)->first();

            $progress = ( $new_current->received / $new_current->expected ) * 100;

            if($new_current->ordered == $new_current->expected){
                //ps_bms_procurement_purchase_order ( atualizar date_upd | verificar atualização do status_id )
                $current_order = bms_procurement_purchase_order::where('id_bms_procurement_purchase_order', $current->po_id)->update( 
                    [ 
                        'status_id' => 7,
                        'date_upd' => date("Y-m-d- h:i:s"),
                        'reception_progress' => $progress
                    ] 
                );
            }else{
                //ps_bms_procurement_purchase_order ( atualizar date_upd | verificar atualização do status_id )
                $current_order = bms_procurement_purchase_order::where('id_bms_procurement_purchase_order', $current->po_id)->update( 
                    [ 
                        'status_id' => 6,
                        'date_upd' => date("Y-m-d- h:i:s"),
                        'reception_progress' => $progress
                    ] 
                );
            }

            $closeMessage = '';
            if($progress > 99){
                $closeMessage= ' Order will close automatically. ';
            }else{
                $closeMessage= ' Order reception progress is at ' . number_format($progress) . "%";
            }

            return response()->json([
                'status' => 'success', 
                'message' => $quantityToReceive . ' X ' . $request->get('reference') . ', for order ' . $current->po_id . $closeMessage            
            ], 200);

        }elseif($request->action == 'updateEAN'){

            product::where('reference', $request->get('reference'))->update( [ 'ean13' => $request->get('ean13') ] );
            product_attribute::where('reference', $request->get('reference'))->update( [ 'ean13' => $request->get('ean13') ] );
            orders_details::where('product_reference', $request->get('reference'))->update( [ 'product_ean13' => $request->get('ean13') ] );
            bms_procurement_purchase_order_product::where('sku', $request->get('reference'))->update( [ 'wmean13' => $request->get('ean13') ] );

            return response()->json([
                'status' => 'success', 
                'message' => 'Product ' . $request->get('reference') . ': EAN-13 change to: ' .  $request->get('ean13')
            ], 200);

        }elseif($request->action == 'sendReport'){

            issues::saveReport(23, $request->get('title'), $request->get('message'), $request->get('id_product'), $request->get('id_product_attribute'), $request->get('reference'));

            return response()->json([
                'status'    => 'success', 
                'response'   => 'REPORT SAVED AT ISSUES MODULE',
                'reference' => $request->get('reference'),
                'title'     => $request->get('title'),
                'message'   => $request->get('message')
            ], 200);

        }
    }

    public function listToRemove(){

        $this->breadcrumbs[] = [ 'name' =>  trans('Remove stock entry'), 'url' => route('stockEntry.listToRemove')];

        $lastEntries = bms_procurement_purchase_order_reception::getLastEntries(100);

        $data = [
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs,
            'entries'    => $lastEntries
        ];

        return View::make('customTools/stockEntry/showToDelete')->with($data);
    }

    public function destroy(string $id){  
     
        $reception_product =bms_procurement_purchase_order_reception_product::where('reception_id', $id)->first();
        $reception =bms_procurement_purchase_order_reception::where('id_bms_procurement_purchase_order_reception', $id)->first();

        $reference = $reception_product->sku;
        $id_product = $reception_product->product_id;
        $id_product_attribute = $reception_product->product_attribute_id;
        $reception_id = $reception_product->reception_id;
        $quantity = $reception_product->qty;
        $po_id = $reception->po_id;

        if($id_product_attribute > 0){
            product_attribute::where('reference', $reference )->increment('stock_arrivepa', $quantity);

            $attrs = product_attribute::where('reference', $reference)->get();

            foreach($attrs AS $attr){
                stock_available::where('id_product', $attr['id_product'] )->where('id_product_attribute', $attr['id_product_attribute'] )->decrement( 'quantity', $quantity );
            }

        }else{
            product::where('reference', $reference)->increment( 'stock_arrive', $quantity );

            $prods = product::where('reference', $reference)->get();

            foreach($prods AS $prod){
                stock_available::where('id_product', $prod['id_product'] )->where('id_product_attribute', 0 )->decrement( 'quantity', $quantity );
            }
        }

        bms_procurement_purchase_order_product::where('po_id', $po_id )
                                                ->where('product_id', $id_product )
                                                ->where('product_attribute_id', $id_product_attribute )
                                                ->increment( 'qty_expected', $quantity );

        bms_procurement_purchase_order_product::where('po_id', $po_id )
                                                ->where('product_id', $id_product )
                                                ->where('product_attribute_id', $id_product_attribute )
                                                ->decrement( 'qty_received', $quantity );

        $new_current = bms_procurement_purchase_order_product::select( DB::raw('SUM(qty_received) AS received, SUM(qty_ordered) AS ordered, SUM(qty_expected) AS expected'))->where('po_id', $po_id)->first();

        $progress = ( $new_current->received / $new_current->ordered ) * 100;

        if($new_current->ordered == $new_current->expected){
            $current_order = bms_procurement_purchase_order::where('id_bms_procurement_purchase_order', $po_id)->update( 
                [ 
                    'status_id' => 7,
                    'date_upd' => date("Y-m-d- h:i:s"),
                    'reception_progress' => $progress
                ] 
            );
        }else{
            $current_order = bms_procurement_purchase_order::where('id_bms_procurement_purchase_order', $po_id)->update( 
                [ 
                    'status_id' => 6,
                    'date_upd' => date("Y-m-d- h:i:s"),
                    'reception_progress' => $progress
                ] 
            );
        }

        bms_procurement_purchase_order_reception_product::where('reception_id', $id)->delete();
        bms_procurement_purchase_order_reception::where('id_bms_procurement_purchase_order_reception', $id)->delete();

        return redirect()->route('stockEntry.listToRemove');
    }

    public function index() {                echo "stockEntryController INDEX";   exit; }
    public function edit(string $id){        echo "stockEntryController EDIT";    exit; }
    public function create(){                echo "stockEntryController CREATE";  exit; }
    public function store(Request $request){ echo "stockEntryController STORE";   exit; }
}