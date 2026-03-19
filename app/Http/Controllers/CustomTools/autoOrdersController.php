<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\prestashop\suppliers;
use App\Models\prestashop\product;
use App\Models\prestashop\product_lang;
use App\Models\prestashop\orders_details;
use App\Models\prestashop\stock_available;
use App\Models\prestashop\product_attribute;
use App\Models\modules\auto_orders\auto_orders;
use App\Models\modules\auto_orders\cross_auto_orders;
use App\Models\modules\auto_orders\auto_orders_purchase_list;
use App\Models\modules\bms_procurement\bms_procurement_purchase_order;
use App\Models\modules\bms_procurement\bms_procurement_purchase_order_product;

class autoOrdersController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('Logistics'), 'url' => route('customer.index')];
    }

    public function show(string $id){ 
        
        $this->breadcrumbs[] = [ 'name' => 'Entry form',   'url' => route('autoOrders.index', $id)];
        $this->actions[]     = [ ];

        $data = [
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs,
            'suppliers' => auto_orders_purchase_list::getAll($id),
            'suppliers_list' => suppliers::select('id_supplier', 'name')->orderBy('name')->get()
        ];

        return View::make('customTools/autoOrders/show')->with($data);
    }

    public function index() { 

        $this->breadcrumbs[] = [ 'name' => 'Entry form',   'url' => route('autoOrders.index')];
        $this->actions[]     = [ 'name' => 'To order', 'icon' => '<i class="fa fa-box"></i>', 'url' => route('autoOrders.show', 0), 'class' => "btn-success"];

        $data = [
            'actions'     => $this->actions,
            'breadcrumbs' => $this->breadcrumbs,
            'auto_orders' => auto_orders::checkAutoOrders()
        ];

        return View::make('customTools/autoOrders/index')->with($data);
    }

    public function edit(string $id){        echo "autoOrdersController EDIT";    exit; }
    public function create(){                echo "autoOrdersController CREATE";  exit; }
    public function store(Request $request){ echo "autoOrdersController STORE";   exit; }

    public function cleanBranditems(Request $request){ 
        cross_auto_orders::where('manufacturer', $request->manufacturer)->delete();
        return response()->json([ 'success' => true ]);
    }

    public function exportCSV(Request $request){ 
        
        $fileName = str_replace(' ', '_', $request->manufacturer) . '.csv';
        
        $filePath = public_path() . '/admin/download/' . $fileName;

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        if($request->manufacturer == 'full'){
            $columns = [ 'MANUFACTURER', 'REFERENCE', 'ATTRIBUTE', 'Qtity' ];
            $array = cross_auto_orders::select('reference', 'attr_reference', 'quantity', 'id_product_attribute', 'manufacturer')->orderBy('manufacturer', 'ASC')->get();
        }else{
            $columns = [ 'SKU', 'Qtity' ];
            $array = cross_auto_orders::select('reference', 'attr_reference', 'quantity', 'id_product_attribute')->where('manufacturer', $request->manufacturer)->get();
        }


        $file = fopen(public_path() . '/admin/download/' . $fileName, 'w');
        fputcsv($file, $columns, ';');
        foreach ($array as $item){

            if( strlen($item->attr_reference) > 0 ){
                if( isset($item->manufacturer) ){
                    fputcsv($file, [$item->manufacturer, $item->attr_reference, $item->quantity], ';');
                }else{
                    fputcsv($file, [$item->attr_reference, $item->quantity], ';');
                }
            }else{
                if( isset($item->manufacturer) ){
                    fputcsv($file, [$item->manufacturer, $item->reference, $item->quantity], ';');
                }else{
                    fputcsv($file, [$item->reference, $item->quantity], ';');
                }
            }
        }

        fclose($file);

        return response()->json([ 'success' => true, 'path' => '/admin/download/' . $fileName ]);  

    }
        
    public function setAsOrdered(Request $request){

        $row = cross_auto_orders::find($request->id);

        $reference = $row['reference'];

        if(strlen($row['attr_reference']) > 0 ) $reference = $row['attr_reference'];

        if(isset($reference)){
            $exist = auto_orders_purchase_list::where('reference', $reference)->count();

            if($exist > 0){
                auto_orders_purchase_list::where('reference', '=', $reference)->update(['quantity' => $request->quantity ]);
            }else{
                $insert = new auto_orders_purchase_list();
                $insert->supplier = $row['supplier'];
                $insert->id_supplier = $row['id_supplier'];
                $insert->quantity = $request->quantity;
                $insert->reference = '' . $reference;
                $insert->name = $row['name'];
                $insert->sold = $row['sold'];
                $insert->save();
            }

            cross_auto_orders::where('id', $request->id)->delete();

            return response()->json([ 'success' => true ]);

        }else{
            return response()->json([ 'success' => false ]);
        }
    }
    
    public function getProductInfo(Request $request){

        $attr    = product_attribute::select('reference', 'id_product', 'id_product_attribute')->where('reference', 'LIKE', '%' . $request->reference . '%')->groupBy('reference')->get();
        $product = product::select('reference', 'id_product', 'id_supplier')->where('id_supplier', $request->id_supplier)->where('reference', 'LIKE', '%' . $request->reference . '%')->groupBy('reference')->get();
        
        $item = array();

        foreach($attr AS $attr_item){
            
            $exist = product::select('id_supplier')->where('id_supplier', $request->id_supplier)->where('id_product', $attr_item->id_product)->count();

            if($exist > 0){
                $supplier = suppliers::select('id_supplier', 'name')->where('id_supplier', $request->id_supplier)->first();

                $product_lang = product_lang::select('name')->where('id_product', $attr_item['id_product'])->where('id_lang', 1)->first();
                $item[] = '<li id_supplier="' . $supplier->id_supplier . '" supplier="' . $supplier->name . '" title="' . $product_lang['name'] . '" style="padding: 3px 0; border-bottom: 1px solid #999" onclick="addToOrder($(this))" reference="' . $attr_item['reference'] . '">' . $attr_item['reference'] . '</li>';
            }

        }

        foreach($product AS $product_item){

            $supplier = suppliers::select('id_supplier', 'name')->where('id_supplier', $request->id_supplier)->first();

            $product_lang = product_lang::select('name')->where('id_product', $product_item['id_product'])->where('id_lang', 1)->first();
            if(!str_ends_with($product_item['reference'], '-Z')) $item[] = '<li id_supplier="' . $supplier->id_supplier . '" title="' . $product_lang['name'] . '" style="padding: 3px 0; border-bottom: 1px solid #999" onclick="addToOrder($(this))" reference="' . $product_item['reference'] . '" supplier="'.$supplier->name.'" >' . $product_item['reference'] . '</li>';
        }

        if(count($item) > 0){
            return response()->json( [ 'items' => '<div class="ajax_search_response" id="ajax_search_response_' . $request->id_supplier . '"><ul style="padding-left: 0; list-style: none;width: 100%;">' . implode(' ', $item) . '</ul></div>' ] );
        }else{
            return response()->json( [ 'items' => '<div style="background-color: red;color: #FFF;" class="ajax_search_response" id="ajax_search_response_' . $request->id_supplier . '"><ul style="padding-left: 0; list-style: none;"><li title="No products found!">No products found!</li></ul></div>' ] );
        }        
    }
    
    public function addToOrder(Request $request){

        if(isset($request->reference)){
            $exist = auto_orders_purchase_list::where('reference', $request->reference)->count();
            
            if($exist > 0){
                $quantity = ($request->quantity > 0 ) ? $request->quantity : 1;
                auto_orders_purchase_list::where('reference', '=', $request->reference)->increment('quantity', $quantity);
                
                $updated_quantity = auto_orders_purchase_list::where('reference', '=', $request->reference)->pluck('quantity');
                return response()->json([ 'success' => true, 'type' => 'edit', 'quantity' => $updated_quantity ]);
            }else{
                $product = new auto_orders_purchase_list();
                $product->supplier = $request->supplier;
                $product->id_supplier = $request->id_supplier;
                $product->quantity = ($request->quantity > 0 ) ? $request->quantity : 1;
                $product->reference = '' . $request->reference;
                $product->name = $request->name;
                $product->save();

                $html = view('customTools/autoOrders/includes/orderRow', compact('product'))->render();

                return response()->json([ 'success' => true, 'type' => 'add', 'html' => $html, 'quantity_order' => auto_orders_purchase_list::where('id_supplier', '=', $request->id_supplier)->count()  ]);
            }
        }else{
            return response()->json([ 'success' => false ]);
        }

    }
    
    public function updateOrder(Request $request){

        if(isset($request->reference)){

            if($request->quantity > 0){

                auto_orders_purchase_list::where('reference', '=', $request->reference)->update(['quantity' => $request->quantity ]);
                return response()->json([ 'success' => true, 'type' => 'add', 'quantity_order' => auto_orders_purchase_list::where('id_supplier', '=', $request->id_supplier)->count() ]);
            }else{
                
                auto_orders_purchase_list::where('reference', '=', $request->reference)->delete();
                return response()->json([ 'success' => true, 'type' => 'remove', 'quantity_order' => auto_orders_purchase_list::where('id_supplier', '=', $request->id_supplier)->count() ]);
            }
            return response()->json([ 'success' => true ]);
        }else{
            return response()->json([ 'success' => false ]);
        }

    }
    
    public function getProductsInfo(Request $request){

        $products = cross_auto_orders::where('manufacturer', $request->manufacturer)->get();
        
        $references_to_check = array();
        foreach($products AS $product){
            $references_to_check[] = (strlen($product->attr_reference) > 0) ? $product->attr_reference : $product->reference;
        }

        $stream = auto_orders::getExternalDataByPOST('/custom/api/autoOrders/getSold.php', ['references' => $references_to_check] );
    
        $data = $stream['data'];

        foreach($products AS $product){

            $id_supplier = 0;
            $supplier = "Unknown";
            $deprecated = 0;
            $id_product = 0;  
            $id_product_attribute = 0;  
            $product_attr_data = null;
            $stock_arrive = 0;
            $stock_arrivepa = 0;
            $not_to_order = 0;
            $notes = '';
            
            $ref = (strlen($product->attr_reference) > 0) ? $product->attr_reference : $product->reference;
            
            $sold_local = orders_details::getSoldOf($product->reference, $product->attr_reference);
            $sold = (isset($data[$ref])) ? $data[$ref] + $sold_local : $sold_local;

            $product_data = product::select('id_product', 'reference', 'id_supplier', 'wmdeprecated', 'stock_arrive', 'not_to_order', 'notes')->where('reference', $ref)->first();
            
            if( isset( $product_data->id_product ) ) $id_product = $product_data->id_product;

            $notFather = ( !is_null($product_data) ) ? 0 : 1;

            if( ( $product->attr_reference !='' ) || ( $notFather )){
            
                $product_attr_data = product_attribute::where('reference', $ref)->first();

                if(is_null($product_attr_data)){
                    
                }else{
                    $id_product_attribute = $product_attr_data->id_product_attribute;
                    $id_product = $product_attr_data->id_product;
                    $stock_arrivepa = $product_attr_data->stock_arrivepa;

                    $product_data = product::select('id_product', 'reference', 'id_supplier', 'wmdeprecated', 'stock_arrive', 'not_to_order', 'notes')->where('id_product', $id_product)->first();
                    
                    $not_to_order = $product_data->not_to_order;
                }

            }

            if(!is_null($product_data)){
                
                if($product_data->id_supplier != 0){
                    
                    $supplier_info = suppliers::where('id_supplier', $product_data->id_supplier)->first();
                    
                    if(isset($supplier_info->id_supplier)){
    
                        $id_supplier  = $supplier_info->id_supplier;
                        $supplier     = $supplier_info->name;
                        $deprecated   = $product_data->wmdeprecated;
                    
                    }else{
                        
                        $id_supplier  = 0;
                        $supplier     = 'Unknown';
                        $deprecated   = 1;
                        
                    }
                    
                    $not_to_order = $product_data->not_to_order;
                    $notes = $product_data->notes;
                    
                    $stock_arrive = (isset($product_attr_data->id_product_attribute)) ? 0 : $product_data->stock_arrive;
                    
                    $stock_available = stock_available::select('quantity')->where('id_product', $id_product)->where('id_product_attribute', $id_product_attribute)->first();
                    $quantity = (isset($stock_available->quantity)) ? $stock_available->quantity : 0;
                    
                    cross_auto_orders::where('reference', $product->reference)->update([
                        'id_supplier'    => $id_supplier,
                        'supplier'       => $supplier,
                        'sold'           => $sold,
                        'end_of_life'    => $deprecated,
                        'qtd_in_stock'   => $quantity,
                        'stock_arrive'   => $stock_arrive,
                        'stock_arrivepa' => $stock_arrivepa,
                        'not_to_order'   => $not_to_order,
                        'notes'          => $notes
                    ]);
                    
                }
            }

        }

        $products = cross_auto_orders::where('manufacturer', $request->manufacturer)->get();
        
        $viewRendered = view('customTools/autoOrders/includes/products_table', compact('products'))->render();

        return Response::json(['html'=>$viewRendered]);
    }
    
    public function saveOrder(Request $request){

        $products = auto_orders_purchase_list::where('id_supplier', $request->id_supplier)->get();

        $order_id = bms_procurement_purchase_order::createOrder($request->order_reference, $request->id_supplier);

        bms_procurement_purchase_order_product::createOrderRows($order_id, $request->all(), $products);

        auto_orders_purchase_list::where('id_supplier', $request->id_supplier)->delete();

        $counters = bms_procurement_purchase_order_product::getCountersOfOrder($order_id);

        bms_procurement_purchase_order::where('id_bms_procurement_purchase_order', $order_id)->update([
            'total' => $counters['total_price'],
            'total_base' => $counters['total_base_price']
        ]);

        return response()->json([ 'success' => true ]);
    }
    
    public function loadProducts(Request $request){
        
        $products = product::select('id_product', 'reference')->where('id_supplier', $request->id_supplier)->groupBy('reference')->get();
        
        $html= '<select id="selectProductListOfSuppliers" name="selectProductListOfSuppliers" onchange="loadAttributesOfBrand()" style="width: calc(100% - 20px );">';
            foreach($products AS $product) $html.= '<option value="' . $product->id_product . '">' . $product->reference . '</option>';
        $html.= '</select>';
        return $html;
    }
    
    public function loadAttributes(Request $request){
        
        $attrs = product_attribute::select('id_product', 'id_product_attribute', 'reference')->where('id_product', $request->id_product)->get();
        
        $html= '<select id="selectAttributeListOfSuppliers" name="selectAttributeListOfSuppliers" style="width: calc(100% - 20px );">';
            foreach($attrs AS $attr) $html.= '<option value="' . $attr->id_product_attribute . '">' . $attr->reference . '</option>';
        $html.= '</select>';
        return ( count($attrs) > 0 ) ? $html : '<select id="selectAttributeListOfSuppliers" name="selectAttributeListOfSuppliers" style="width: calc(100% - 20px );display: none;"> </select>';
    }
    
    public function saveNewOrderFromScratch(Request $request){
        
        $supplier = suppliers::where('id_supplier', $request->id_supplier)->value('name');
        
        if( strlen($request->id_product_attribute) > 0){
            $reference = product_attribute::where('id_product', $request->id_product)->where('id_product_attribute', $request->id_product_attribute)->value('reference');
        }else{
            $reference = product::where('id_product', $request->id_product)->value('reference');
        }

        $name = product_lang::where('id_lang', 1)->where('id_product', $request->id_product)->value('name');

        $insert = new auto_orders_purchase_list();
        $insert->supplier = $supplier;
        $insert->id_supplier = $request->id_supplier;
        $insert->quantity = 1;
        $insert->reference = '' . $reference;
        $insert->name = $name;
        $insert->sold = 0;
        $insert->save();
        return 1;
    }

}