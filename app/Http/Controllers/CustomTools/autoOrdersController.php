<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\prestashop\suppliers;
use App\Models\prestashop\product;
use App\Models\prestashop\product_lang;
use App\Models\prestashop\orders_details;
use App\Models\prestashop\product_attribute;
use App\Models\modules\auto_orders\AutoOrder;
use App\Models\modules\auto_orders\AutoOrdersCandidate;
use App\Models\modules\auto_orders\AutoOrdersPurchaseList;
use App\Models\modules\oms\OrderNote;
use App\Models\modules\oms\OrderNoteLine;

class autoOrdersController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = ['name' => 'purchase', 'url' => route('purchase.index')];
    }

    public function show(string $id){ 
        
        $this->breadcrumbs[] = ['name' => 'Auto orders', 'url' => route('autoOrders.index'), 'no_translation' => 1];
        $this->actions[]     = [ ];

        $data = [
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs,
            'suppliers' => AutoOrdersPurchaseList::getAll($id),
            'suppliers_list' => suppliers::select('id_supplier', 'name')->orderBy('name')->get()
        ];

        return View::make('customTools/autoOrders/show')->with($data);
    }

    public function index() { 

        $this->breadcrumbs[] = ['name' => 'Auto orders', 'url' => route('autoOrders.index'), 'no_translation' => 1];
        $this->actions[]     = [ 'name' => 'To order', 'icon' => '<i class="fa fa-box"></i>', 'url' => route('autoOrders.show', 0), 'class' => "btn-success"];

        $data = [
            'actions'     => $this->actions,
            'breadcrumbs' => $this->breadcrumbs,
            'auto_orders' => AutoOrder::checkAutoOrders()
        ];

        return View::make('customTools/autoOrders/index')->with($data);
    }

    public function edit(string $id){        echo "autoOrdersController EDIT";    exit; }
    public function create(){                echo "autoOrdersController CREATE";  exit; }
    public function store(Request $request){ echo "autoOrdersController STORE";   exit; }

    public function cleanBranditems(Request $request){ 
        AutoOrdersCandidate::where('manufacturer', $request->manufacturer)->where('ordered', 0)->delete();
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
            $array = AutoOrdersCandidate::select('reference', 'attr_reference', 'quantity', 'id_product_attribute', 'manufacturer')->where('ordered', 0)->orderBy('manufacturer', 'ASC')->get();
        }else{
            $columns = [ 'SKU', 'Qtity' ];
            $array = AutoOrdersCandidate::select('reference', 'attr_reference', 'quantity', 'id_product_attribute')->where('manufacturer', $request->manufacturer)->where('ordered', 0)->get();
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

        $row = AutoOrdersCandidate::find($request->id);

        $reference = $row['reference'];

        if(strlen($row['attr_reference']) > 0 ) $reference = $row['attr_reference'];

        if(isset($reference)){
            $exist = AutoOrdersPurchaseList::where('reference', $reference)->count();

            if($exist > 0){
                AutoOrdersPurchaseList::where('reference', '=', $reference)->update(['quantity' => $request->quantity ]);
            }else{
                $insert = new AutoOrdersPurchaseList();
                $insert->supplier = $row['supplier'];
                $insert->id_supplier = $row['id_supplier'];
                $insert->quantity = $request->quantity;
                $insert->reference = '' . $reference;
                $insert->name = $row['name'];
                $insert->sold = $row['sold'];
                $insert->save();
            }

            AutoOrdersCandidate::where('id', $request->id)->update([
                'ordered' => 1,
                'ordered_date' => now()->toDateString(),
            ]);

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
            $exist = AutoOrdersPurchaseList::where('reference', $request->reference)->count();
            
            if($exist > 0){
                $quantity = ($request->quantity > 0 ) ? $request->quantity : 1;
                AutoOrdersPurchaseList::where('reference', '=', $request->reference)->increment('quantity', $quantity);
                
                $updated_quantity = AutoOrdersPurchaseList::where('reference', '=', $request->reference)->pluck('quantity');
                return response()->json([ 'success' => true, 'type' => 'edit', 'quantity' => $updated_quantity ]);
            }else{
                $product = new AutoOrdersPurchaseList();
                $product->supplier = $request->supplier;
                $product->id_supplier = $request->id_supplier;
                $product->quantity = ($request->quantity > 0 ) ? $request->quantity : 1;
                $product->reference = '' . $request->reference;
                $product->name = $request->name;
                $product->save();

                $html = view('customTools/autoOrders/includes/orderRow', compact('product'))->render();

                return response()->json([ 'success' => true, 'type' => 'add', 'html' => $html, 'quantity_order' => AutoOrdersPurchaseList::where('id_supplier', '=', $request->id_supplier)->count()  ]);
            }
        }else{
            return response()->json([ 'success' => false ]);
        }

    }
    
    public function updateOrder(Request $request){

        if(isset($request->reference)){

            if($request->quantity > 0){

                AutoOrdersPurchaseList::where('reference', '=', $request->reference)->update(['quantity' => $request->quantity ]);
                return response()->json([ 'success' => true, 'type' => 'add', 'quantity_order' => AutoOrdersPurchaseList::where('id_supplier', '=', $request->id_supplier)->count() ]);
            }else{
                
                AutoOrdersPurchaseList::where('reference', '=', $request->reference)->delete();
                return response()->json([ 'success' => true, 'type' => 'remove', 'quantity_order' => AutoOrdersPurchaseList::where('id_supplier', '=', $request->id_supplier)->count() ]);
            }
            return response()->json([ 'success' => true ]);
        }else{
            return response()->json([ 'success' => false ]);
        }

    }
    
    public function getProductsInfo(Request $request){

        $products = AutoOrdersCandidate::where('manufacturer', $request->manufacturer)->where('ordered', 0)->get();
        $prefix = env('DB2_DB_prefix', 'ps_');
        
        $data = [];

        foreach($products AS $product){
            
            $ref = (strlen($product->attr_reference) > 0) ? $product->attr_reference : $product->reference;
            
            $sold_local = orders_details::getSoldOf($product->reference, $product->attr_reference);
            $sold = (isset($data[$ref])) ? $data[$ref] + $sold_local : $sold_local;
            $data[$ref] = $sold;

            $productData = DB::connection('mysql2')
                ->table($prefix . 'product as p')
                ->leftJoin($prefix . 'product_attribute as pa', function ($join) use ($product) {
                    $join->on('pa.id_product', '=', 'p.id_product')
                        ->where('pa.id_product_attribute', (int) $product->id_product_attribute);
                })
                ->leftJoin($prefix . 'supplier as s', 's.id_supplier', '=', 'p.id_supplier')
                ->leftJoin($prefix . 'custom_product as cp', 'cp.id_product', '=', 'p.id_product')
                ->leftJoin($prefix . 'custom_product_attribute as cpa', function ($join) use ($product) {
                    $join->on('cpa.id_product', '=', 'p.id_product')
                        ->where('cpa.id_product_attribute', (int) $product->id_product_attribute);
                })
                ->leftJoin($prefix . 'stock_available as sa', function ($join) use ($product) {
                    $join->on('sa.id_product', '=', 'p.id_product')
                        ->where('sa.id_product_attribute', (int) $product->id_product_attribute);
                })
                ->where('p.id_product', (int) $product->id_product)
                ->select(
                    DB::raw('COALESCE(p.id_supplier, 0) as id_supplier'),
                    DB::raw('COALESCE(s.name, "Unknown") as supplier'),
                    DB::raw('COALESCE(cpa.wmdeprecated, cp.wmdeprecated, 0) as deprecated'),
                    DB::raw('COALESCE(sa.quantity, 0) as quantity'),
                    DB::raw('COALESCE(cp.stock_arrive, 0) as stock_arrive'),
                    DB::raw('COALESCE(cpa.stock_arrive, 0) as stock_arrivepa'),
                    DB::raw('COALESCE(cp.not_to_order, 0) as not_to_order'),
                    DB::raw('COALESCE(cp.notes, "") as notes')
                )
                ->first();

            if(!is_null($productData)){
                AutoOrdersCandidate::where('id', $product->id)->update([
                    'id_supplier'    => $productData->id_supplier,
                    'supplier'       => $productData->supplier,
                    'sold'           => $sold,
                    'end_of_life'    => $productData->deprecated,
                    'qtd_in_stock'   => $productData->quantity,
                    'stock_arrive'   => ((int) $product->id_product_attribute > 0) ? 0 : $productData->stock_arrive,
                    'stock_arrivepa' => $productData->stock_arrivepa,
                    'not_to_order'   => $productData->not_to_order,
                    'notes'          => $productData->notes
                ]);
            }

        }

        $products = AutoOrdersCandidate::where('manufacturer', $request->manufacturer)->where('ordered', 0)->get();
        
        $viewRendered = view('customTools/autoOrders/includes/products_table', compact('products'))->render();

        return Response::json(['html'=>$viewRendered]);
    }
    
    public function saveOrder(Request $request){

        $products = AutoOrdersPurchaseList::where('id_supplier', $request->id_supplier)->get();

        if($products->isEmpty()){
            return response()->json([ 'success' => false, 'message' => 'No products to order.' ], 422);
        }

        $orderNote = DB::transaction(function () use ($request, $products) {
            $reference = trim((string) $request->order_reference);

            $orderNote = OrderNote::create([
                'supplier_id' => (int) $request->id_supplier,
                'reference' => $reference !== '' ? $reference : 'ON-' . now()->format('YmdHis'),
                'status' => 'order_note',
                'internal_note' => 'Created from Auto Orders.',
            ]);

            foreach($products as $product){
                $productInfo = $this->resolvePrestashopProductForOms((string) $product->reference);

                if(is_null($productInfo)){
                    continue;
                }

                $qtyOrdered = max(1, (int) $product->quantity);
                $productAttributeId = ((int) $productInfo->product_attribute_id > 0)
                    ? (int) $productInfo->product_attribute_id
                    : null;

                $existingLine = OrderNoteLine::query()
                    ->where('order_note_id', $orderNote->id)
                    ->where('product_id', (int) $productInfo->product_id)
                    ->where(function ($query) use ($productAttributeId) {
                        if (is_null($productAttributeId)) {
                            $query->whereNull('product_attribute_id');
                        } else {
                            $query->where('product_attribute_id', $productAttributeId);
                        }
                    })
                    ->first();

                if($existingLine){
                    $existingLine->qty_ordered = (int) $existingLine->qty_ordered + $qtyOrdered;
                    $existingLine->save();
                }else{
                    OrderNoteLine::create([
                        'order_note_id' => $orderNote->id,
                        'product_id' => (int) $productInfo->product_id,
                        'product_attribute_id' => $productAttributeId,
                        'qty_ordered' => $qtyOrdered,
                    ]);
                }

                $this->ensurePrestashopCustomProductRows((int) $productInfo->product_id, $productAttributeId);
                $this->adjustCustomStockArrive((int) $productInfo->product_id, $productAttributeId, $qtyOrdered);
            }

            AutoOrdersPurchaseList::where('id_supplier', $request->id_supplier)->delete();

            return $orderNote;
        });

        return response()->json([
            'success' => true,
            'order_note_id' => $orderNote->id,
            'path' => route('admin.tools.oms.order_notes.show', $orderNote),
        ]);
    }

    protected function resolvePrestashopProductForOms(string $reference): ?object
    {
        $prefix = env('DB2_prefix') ?: env('DB2_DB_prefix', 'ps_');

        return DB::connection('mysql2')
            ->table($prefix . 'product as p')
            ->leftJoin($prefix . 'product_attribute as pa', function ($join) use ($reference) {
                $join->on('pa.id_product', '=', 'p.id_product')
                    ->where('pa.reference', $reference);
            })
            ->where(function ($query) use ($reference) {
                $query->where('p.reference', $reference)
                    ->orWhere('pa.reference', $reference);
            })
            ->select(
                DB::raw('p.id_product as product_id'),
                DB::raw('COALESCE(pa.id_product_attribute, 0) as product_attribute_id')
            )
            ->first();
    }

    protected function ensurePrestashopCustomProductRows(int $productId, ?int $productAttributeId = null): void
    {
        $prefix = env('DB2_prefix') ?: env('DB2_DB_prefix', 'ps_');
        $productAttributeId = (int) ($productAttributeId ?? 0);

        DB::connection('mysql2')
            ->table($prefix . 'custom_product')
            ->updateOrInsert(
                ['id_product' => $productId],
                ['id_product' => $productId]
            );

        if($productAttributeId > 0){
            DB::connection('mysql2')
                ->table($prefix . 'custom_product_attribute')
                ->updateOrInsert(
                    ['id_product_attribute' => $productAttributeId],
                    [
                        'id_product_attribute' => $productAttributeId,
                        'id_product' => $productId,
                    ]
                );
        }
    }

    protected function adjustCustomStockArrive(int $productId, ?int $productAttributeId, int $delta): void
    {
        if($delta === 0){
            return;
        }

        $prefix = env('DB2_prefix') ?: env('DB2_DB_prefix', 'ps_');
        $productAttributeId = (int) ($productAttributeId ?? 0);

        DB::connection('mysql2')
            ->table($prefix . 'custom_product')
            ->where('id_product', $productId)
            ->update([
                'stock_arrive' => DB::raw('COALESCE(stock_arrive, 0) + ' . (int) $delta),
            ]);

        if($productAttributeId > 0){
            DB::connection('mysql2')
                ->table($prefix . 'custom_product_attribute')
                ->where('id_product_attribute', $productAttributeId)
                ->update([
                    'id_product' => $productId,
                    'stock_arrive' => DB::raw('COALESCE(stock_arrive, 0) + ' . (int) $delta),
                ]);
        }
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

        $insert = new AutoOrdersPurchaseList();
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

