<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\prestashop\issues;
use App\Models\prestashop\orders;
use App\Models\prestashop\product;
use App\Models\prestashop\stock_available;
use App\Models\prestashop\product_attribute;
use App\Models\prestashop\orders_details;
use App\Models\modules\oms\OrderNote;
use App\Services\oms\OmsProcurementBridge;
use App\Services\oms\SupplierInvoiceWorkflowService;

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
        
        $product = [];
        $data_found = 0;
        $nr_open_orders= 0;
        $message = "Product not found!";
        
        if(strlen($request->code) < 1){
            return response()->json([
                'status' => 'fail', 
                'selectedOrder' => '',
                'open_orders' => '',
                'message' => $message,
            ], 200);
        }

        $openLines = OmsProcurementBridge::pendingLinesByCode((string) $request->code, (int) $request->po_id);
        $nr_open_orders = $openLines->pluck('po_id')->unique()->count();
            
        if($nr_open_orders == 1){

            $product = $openLines->first();
            
            if(isset($product->product_id)) $message = "Product found";
        }elseif($request->po_id != 0){

            $product = $openLines->first();
                
            if(isset($product->product_id)) $message = "Product found";
            $nr_open_orders = 1;
        }

        if($nr_open_orders == 1){
            if(isset($product->sku)) $data_found = 1;
            
            if($data_found == 1){

                $partial      = orders::getParcials($product->sku);
                $preparations = orders::getPreparations($product->sku);
                $backorders   = orders::getBackorders($product->sku);

                return response()->json([
                    'status' => 'success', 
                    'order_reference' => $product->order_reference, 
                    'product' => $product, 
                    'partials' => $partial, 
                    'preparations' => $preparations, 
                    'backorders' => $backorders, 
                    'message' => $message,
                    'selectedOrder' => $nr_open_orders,
                    'updateRoute' => route('stockEntry.update', $product->product_id)
                ], 200);

            }else{

                $productInfo = product::where('ean13', $request->code)->orWhere('reference', $request->code)->first();

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

        }else{
            $message = "Product found";
            $openOrders = $openLines->unique('po_id')->values();
            
            if(count( $openOrders ) < 1){
                
                $status = 'NOK';
                $message = 'No open orders for this product!';

                $exist_product = product::where('ean13', $request->code)->orWhere('reference', $request->code)->count() + 0;    
                $exist_attr = product_attribute::where('ean13', $request->code)->orWhere('reference', $request->code)->count() + 0;    
                
                if( ( $exist_product + $exist_attr ) > 0){
                    $message = 'No open orders for this product!';
                }else{
                    $message = 'No products with this code!';
                }

                return response()->json([
                    'status' => $status, 
                    'selectedOrder' => 0,
                    'open_orders' => [],
                    'message' => $message,
                ], 200);                
            }
            
            $openOrderHTML='<input type="hidden" name="multipleOrders" value="1">';
            $openOrderHTML.='<div style="text-align: center;">';

            foreach($openOrders AS $order){

                $openOrderHTML.="<div style='margin-top: 10px;'><button style='width: 200px; margin: 0 auto;' class='btn btn-info' onclick='ajaxCall(\"" . $request->action . "\", " . $order->po_id . ", 1)'>" . $order->order_reference . '</button></div>';

            }

            $openOrderHTML.='</div>';

            return response()->json([
                'status' => 'success', 
                'selectedOrder' => $nr_open_orders,
                'open_orders' => $openOrderHTML,
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
            
            $current = OmsProcurementBridge::pendingLineById((int) $request->oms_billed_order_line_id);

            if (!$current) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Open OMS line not found.'
                ], 200);
            }
            
            $requestedReceived = (int) $request->received;
            $alreadyReceived = (int) $current->qty_received;
            $qtyBilled = (int) $current->qty_wmfaturado;
            $quantityToReceive = $requestedReceived - $alreadyReceived;

            if($quantityToReceive > 0){
                if($requestedReceived > $qtyBilled){
                    return response()->json([
                        'status' => 'fail',
                        'message' => 'Failed! Trying to receive more than billed quantity.'
                    ], 200);
                }

                DB::beginTransaction();
                DB::connection('mysql2')->beginTransaction();

                try {
                    $recordedQuantity = OmsProcurementBridge::recordReception((int) $request->get('oms_billed_order_line_id'), $requestedReceived);

                    if($recordedQuantity <= 0){
                        DB::connection('mysql2')->rollBack();
                        DB::rollBack();

                        return response()->json([
                            'status' => 'fail',
                            'message' => 'Failed! Stock entry was not recorded.'
                        ], 200);
                    }

                    $receptionId = $this->latestReceptionIdForLine((int) $request->get('oms_billed_order_line_id'), $recordedQuantity);
                    $targets = $this->stockTargetsForReference(
                        (string) $request->get('reference'),
                        (int) $current->product_attribute_id
                    );

                    $stockLogs = [];
                    foreach($targets AS $target){
                        $stockBefore = $this->prestashopStock((int) $target->id_product, (int) $target->id_product_attribute);
                        stock_available::where('id_product', $target->id_product)
                            ->where('id_product_attribute', $target->id_product_attribute)
                            ->increment('quantity', $recordedQuantity);
                        $stockAfter = $this->prestashopStock((int) $target->id_product, (int) $target->id_product_attribute);

                        $stockLogs[] = [
                            'target' => $target,
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockAfter,
                            'arrive_before' => $this->prestashopStockArrive((int) $target->id_product, (int) $target->id_product_attribute),
                            'arrive_after' => $this->prestashopStockArrive((int) $target->id_product, (int) $target->id_product_attribute),
                            'arrive_delta' => 0,
                        ];
                    }

                    $mainArriveBefore = $this->prestashopStockArrive((int) $current->product_id, (int) $current->product_attribute_id);
                    $this->adjustPrestashopStockArrive((int) $current->product_id, (int) $current->product_attribute_id, -$recordedQuantity);
                    $mainArriveAfter = $this->prestashopStockArrive((int) $current->product_id, (int) $current->product_attribute_id);

                    foreach($stockLogs AS $stockLog){
                        if ((int) $stockLog['target']->id_product === (int) $current->product_id
                            && (int) $stockLog['target']->id_product_attribute === (int) $current->product_attribute_id) {
                            $stockLog['arrive_before'] = $mainArriveBefore;
                            $stockLog['arrive_after'] = $mainArriveAfter;
                            $stockLog['arrive_delta'] = -$recordedQuantity;
                        }

                        $this->insertStockHistory(
                            'reception_line',
                            (int) $current->oms_billed_order_line_id,
                            $receptionId,
                            (int) $current->po_id,
                            (int) $stockLog['target']->id_product,
                            (int) $stockLog['target']->id_product_attribute,
                            (int) $stockLog['stock_before'],
                            $recordedQuantity,
                            (int) $stockLog['stock_after'],
                            (int) $stockLog['arrive_before'],
                            (int) $stockLog['arrive_delta'],
                            (int) $stockLog['arrive_after']
                        );
                    }

                    DB::connection('mysql2')->commit();
                    DB::commit();
                } catch (\Throwable $exception) {
                    DB::connection('mysql2')->rollBack();
                    DB::rollBack();

                    throw $exception;
                }
    
                /** stock_available::where('id_product', $current->product_id )->where('id_product_attribute', $current->product_attribute_id )->increment( 'quantity', $quantityToReceive ); **/
    
                $progress = min(100, ($requestedReceived / max(1, $qtyBilled)) * 100);
    
                $closeMessage = '';
                if($progress == 100){
                    $closeMessage= ' OMS line fully received.';
                }else{
                    $closeMessage= ' OMS line reception progress is at ' . number_format($progress) . "%";
                }
    
                return response()->json([
                    'status' => 'success', 
                    'message' => $recordedQuantity . ' X ' . $request->get('reference') . ', for order ' . $current->po_id . $closeMessage
                ], 200);
            }else{
    
                return response()->json([
                    'status' => 'fail', 
                    'message' => 'Failed! Trying to save quantity = 0!'           
                ], 200);
                
            }

        }elseif($request->action == 'updateEAN'){
            product::where('reference', $request->get('reference'))->update( [ 'ean13' => $request->get('ean13') ] );
            product_attribute::where('reference', $request->get('reference'))->update( [ 'ean13' => $request->get('ean13') ] );
            orders_details::where('product_reference', $request->get('reference'))->update( [ 'product_ean13' => $request->get('ean13') ] );

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

        $prefix = env('DB2_DB_prefix');
        $lastEntries = DB::table('oms_receptions as r')
            ->join('oms_reception_lines as rl', 'rl.reception_id', '=', 'r.id')
            ->join('oms_billed_order_lines as bol', 'bol.id', '=', 'rl.billed_order_line_id')
            ->join('oms_billed_orders as bo', 'bo.id', '=', 'r.billed_order_id')
            ->leftJoin('users as u', 'u.id', '=', 'r.created_by')
            ->leftJoin(DB::raw($prefix . 'product as p'), 'p.id_product', '=', 'bol.product_id')
            ->leftJoin(DB::raw($prefix . 'product_attribute as pa'), 'pa.id_product_attribute', '=', 'bol.product_attribute_id')
            ->select(
                'r.id as oms_reception_id',
                'bo.id as po_id',
                'bo.reference',
                DB::raw('COALESCE(pa.reference, p.reference) as sku'),
                'rl.qty_received as qty',
                DB::raw('0 as deleted'),
                DB::raw('COALESCE(u.name, "") as firstname'),
                DB::raw('"" as lastname')
            )
            ->orderByDesc('r.id')
            ->limit(1000)
            ->get()
            ->map(fn ($row) => (array) $row);

        $data = [
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs,
            'entries'    => $lastEntries
        ];

        return View::make('customTools/stockEntry/showToDelete')->with($data);
    }

    public function destroy(string $id){  
        $reception = DB::table('oms_receptions as r')
            ->join('oms_reception_lines as rl', 'rl.reception_id', '=', 'r.id')
            ->join('oms_billed_order_lines as bol', 'bol.id', '=', 'rl.billed_order_line_id')
            ->where('r.id', (int) $id)
            ->select('r.id', 'r.billed_order_id', 'rl.id as reception_line_id', 'rl.qty_received', 'bol.id as billed_order_line_id', 'bol.product_id', 'bol.product_attribute_id')
            ->first();

        if (!$reception) {
            return redirect()->route('stockEntry.listToRemove');
        }

        $quantity = (int) $reception->qty_received;
        $id_product = (int) $reception->product_id;
        $id_product_attribute = (int) $reception->product_attribute_id;

        DB::beginTransaction();
        DB::connection('mysql2')->beginTransaction();

        try {
            $reference = $this->displayReference($id_product, $id_product_attribute);
            $targets = $this->stockTargetsForReference($reference, $id_product_attribute);

            $stockLogs = [];
            foreach($targets AS $target){
                $stockBefore = $this->prestashopStock((int) $target->id_product, (int) $target->id_product_attribute);
                stock_available::where('id_product', $target->id_product)
                    ->where('id_product_attribute', $target->id_product_attribute)
                    ->decrement('quantity', $quantity);
                $stockAfter = $this->prestashopStock((int) $target->id_product, (int) $target->id_product_attribute);

                $stockLogs[] = [
                    'target' => $target,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'arrive_before' => $this->prestashopStockArrive((int) $target->id_product, (int) $target->id_product_attribute),
                    'arrive_after' => $this->prestashopStockArrive((int) $target->id_product, (int) $target->id_product_attribute),
                    'arrive_delta' => 0,
                ];
            }

            $mainArriveBefore = $this->prestashopStockArrive($id_product, $id_product_attribute);
            $this->adjustPrestashopStockArrive($id_product, $id_product_attribute, $quantity);
            $mainArriveAfter = $this->prestashopStockArrive($id_product, $id_product_attribute);

            DB::table('oms_billed_order_lines')
                ->where('id', (int) $reception->billed_order_line_id)
                ->update([
                    'qty_received' => DB::raw('GREATEST(COALESCE(qty_received, 0) - ' . $quantity . ', 0)'),
                    'updated_at' => now(),
                ]);

            foreach($stockLogs AS $stockLog){
                if ((int) $stockLog['target']->id_product === $id_product
                    && (int) $stockLog['target']->id_product_attribute === $id_product_attribute) {
                    $stockLog['arrive_before'] = $mainArriveBefore;
                    $stockLog['arrive_after'] = $mainArriveAfter;
                    $stockLog['arrive_delta'] = $quantity;
                }

                $this->insertStockHistory(
                    'stock_entry_removal',
                    (int) $reception->billed_order_line_id,
                    (int) $reception->id,
                    (int) $reception->billed_order_id,
                    (int) $stockLog['target']->id_product,
                    (int) $stockLog['target']->id_product_attribute,
                    (int) $stockLog['stock_before'],
                    -$quantity,
                    (int) $stockLog['stock_after'],
                    (int) $stockLog['arrive_before'],
                    (int) $stockLog['arrive_delta'],
                    (int) $stockLog['arrive_after']
                );
            }

            DB::table('oms_reception_lines')->where('id', (int) $reception->reception_line_id)->delete();
            DB::table('oms_receptions')->where('id', (int) $reception->id)->delete();

            $orderNoteId = DB::table('oms_billed_orders')
                ->where('id', (int) $reception->billed_order_id)
                ->value('order_note_id');

            if ($orderNoteId) {
                $orderNote = OrderNote::query()->find((int) $orderNoteId);

                if ($orderNote) {
                    app(SupplierInvoiceWorkflowService::class)->refreshOrderNoteStatus(
                        $orderNote->fresh(['lines', 'billedOrders'])
                    );
                }
            }

            DB::connection('mysql2')->commit();
            DB::commit();
        } catch (\Throwable $exception) {
            DB::connection('mysql2')->rollBack();
            DB::rollBack();

            throw $exception;
        }

        return redirect()->route('stockEntry.listToRemove');
    }

    private function latestReceptionIdForLine(int $lineId, int $quantity): ?int
    {
        $receptionId = DB::table('oms_reception_lines')
            ->where('billed_order_line_id', $lineId)
            ->where('qty_received', $quantity)
            ->orderByDesc('id')
            ->value('reception_id');

        return $receptionId ? (int) $receptionId : null;
    }

    private function stockTargetsForReference(string $reference, int $productAttributeId): \Illuminate\Support\Collection
    {
        $reference = trim($reference);

        if ($reference === '') {
            return collect();
        }

        if($productAttributeId > 0){
            return product_attribute::where('reference', $reference)
                ->get(['id_product', 'id_product_attribute'])
                ->map(fn ($row) => (object) [
                    'id_product' => (int) $row->id_product,
                    'id_product_attribute' => (int) $row->id_product_attribute,
                ])
                ->unique(fn ($row) => $row->id_product . ':' . $row->id_product_attribute)
                ->values();
        }

        return product::where('reference', $reference)
            ->get(['id_product'])
            ->map(fn ($row) => (object) [
                'id_product' => (int) $row->id_product,
                'id_product_attribute' => 0,
            ])
            ->unique(fn ($row) => $row->id_product . ':0')
            ->values();
    }

    private function displayReference(int $productId, int $productAttributeId): string
    {
        if($productAttributeId > 0){
            return (string) product_attribute::where('id_product_attribute', $productAttributeId)->value('reference');
        }

        return (string) product::where('id_product', $productId)->value('reference');
    }

    private function prestashopStock(int $productId, int $productAttributeId): int
    {
        return (int) stock_available::where('id_product', $productId)
            ->where('id_product_attribute', $productAttributeId)
            ->value('quantity');
    }

    private function prestashopStockArrive(int $productId, int $productAttributeId): int
    {
        $prefix = env('DB2_DB_prefix');

        if($productAttributeId > 0){
            return (int) DB::connection('mysql2')
                ->table($prefix . 'custom_product_attribute')
                ->where('id_product_attribute', $productAttributeId)
                ->value('stock_arrive');
        }

        return (int) DB::connection('mysql2')
            ->table($prefix . 'custom_product')
            ->where('id_product', $productId)
            ->value('stock_arrive');
    }

    private function adjustPrestashopStockArrive(int $productId, int $productAttributeId, int $delta): void
    {
        if($delta === 0){
            return;
        }

        $prefix = env('DB2_DB_prefix');

        if($productAttributeId > 0){
            $exists = DB::connection('mysql2')
                ->table($prefix . 'custom_product_attribute')
                ->where('id_product_attribute', $productAttributeId)
                ->exists();

            if(!$exists){
                DB::connection('mysql2')
                    ->table($prefix . 'custom_product_attribute')
                    ->insert([
                        'id_product' => $productId,
                        'id_product_attribute' => $productAttributeId,
                        'stock_arrive' => 0,
                    ]);
            }

            DB::connection('mysql2')
                ->table($prefix . 'custom_product_attribute')
                ->where('id_product_attribute', $productAttributeId)
                ->increment('stock_arrive', $delta);

            return;
        }

        $exists = DB::connection('mysql2')
            ->table($prefix . 'custom_product')
            ->where('id_product', $productId)
            ->exists();

        if(!$exists){
            DB::connection('mysql2')
                ->table($prefix . 'custom_product')
                ->insert([
                    'id_product' => $productId,
                    'stock_arrive' => 0,
                ]);
        }

        DB::connection('mysql2')
            ->table($prefix . 'custom_product')
            ->where('id_product', $productId)
            ->increment('stock_arrive', $delta);
    }

    private function referenceSnapshots(int $productId, int $productAttributeId): array
    {
        $productReference = trim((string) product::where('id_product', $productId)->value('reference'));
        $attributeReference = '';

        if($productAttributeId > 0){
            $attributeReference = trim((string) product_attribute::where('id_product_attribute', $productAttributeId)->value('reference'));
        }

        return [
            'product_reference_snapshot' => $productReference !== '' ? $productReference : null,
            'attribute_reference_snapshot' => $attributeReference !== '' ? $attributeReference : null,
            'display_reference_snapshot' => $attributeReference !== '' ? $attributeReference : ($productReference !== '' ? $productReference : null),
        ];
    }

    private function insertStockHistory(
        string $sourceType,
        int $sourceId,
        ?int $receptionId,
        int $billedOrderId,
        int $productId,
        int $productAttributeId,
        int $stockBefore,
        int $stockDelta,
        int $stockAfter,
        int $arriveBefore,
        int $arriveDelta,
        int $arriveAfter
    ): void {
        $billedOrder = DB::table('oms_billed_orders')->where('id', $billedOrderId)->first();
        $snapshots = $this->referenceSnapshots($productId, $productAttributeId);
        $user = Auth::user();

        DB::table('oms_stock_history')->insert([
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'order_note_id' => $billedOrder?->order_note_id,
            'billed_order_id' => $billedOrderId,
            'supplier_invoice_id' => $billedOrder?->supplier_invoice_id,
            'reception_id' => $receptionId,
            'product_id' => $productId,
            'product_attribute_id' => $productAttributeId,
            'product_reference_snapshot' => $snapshots['product_reference_snapshot'],
            'attribute_reference_snapshot' => $snapshots['attribute_reference_snapshot'],
            'display_reference_snapshot' => $snapshots['display_reference_snapshot'],
            'ps_quantity_before' => $stockBefore,
            'ps_quantity_delta' => $stockDelta,
            'ps_quantity_after' => $stockAfter,
            'ps_quantity_arrive_before' => $arriveBefore,
            'ps_quantity_arrive_delta' => $arriveDelta,
            'ps_quantity_arrive_after' => $arriveAfter,
            'user_id' => $user?->id,
            'user_name_snapshot' => $user?->name,
            'user_email_snapshot' => $user?->email,
            'created_at' => now(),
        ]);
    }

    public function index() {                echo "stockEntryController INDEX";   exit; }
    public function edit(string $id){        echo "stockEntryController EDIT";    exit; }
    public function create(){                echo "stockEntryController CREATE";  exit; }
    public function store(Request $request){ echo "stockEntryController STORE";   exit; }
}
