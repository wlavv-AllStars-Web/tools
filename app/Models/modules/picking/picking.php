<?php

namespace App\Models\modules\picking;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\prestashop\product_attribute;
use App\Models\prestashop\product;
use App\Models\prestashop\orders;
use App\Models\prestashop\pack;

class picking extends Model
{
    use HasFactory;
    protected $table = "picking";

    public static function getOrders(){
        return (object)[
            'asm' => self::mountOrdersArray('preparation', [2]),
            'asd' => self::mountOrdersArray('preparation', [3]),
            'other' => self::mountOrdersArray('preparation', [1, 6]),
        ];
    }
        
    public static function mountOrdersArray($status, array $shops = []){
        
        $data_order = self::where('status', $status)
            ->where('row_done', 0)
            ->when(!empty($shops), fn ($query) => $query->whereIn('id_shop', $shops))
            ->groupBy('id_order')
            ->get();

        $array_order = array();
        foreach($data_order AS $order){
            $array_order[] = (object)[
                'id_order' => $order->id_order,
                'carrier' => $order->carrier,
                'order_main' => orders::where('id_order', $order->id_order)->first(),
                'order' => self::where('id_order', $order->id_order)
                    ->where('row_done', 0)
                    ->get()
                    ->map(function ($row) {
                        $row->is_new = self::needsMarketingPhotos(
                            (int) $row->id_product,
                            (int) $row->id_product_attribute
                        );

                        return $row;
                    }),
            ];
        }
        
        return (object)[ 'counter' => count($array_order),  'data' => (object)$array_order ];
    }
        
    public static function add(){
        self::classifyPaymentAcceptedOrders();
        self::removeNonPreparationPickingRows();
        self::addData(3, 'preparation');
    }
        
    public static function addData($id_status, $status){
        
        $states = is_array($id_status) ? $id_status : [$id_status];
        $orders = orders::with('order_detail', 'carrier')->whereIn('current_state', $states)->get();

        if(count($orders)){
            
            foreach($orders AS $order){

                $customDetails = self::customOrderDetails($order);

                foreach($order->order_detail AS $detail){

                    $qtdSent = self::qtdSent($detail, $customDetails);

                    if($detail->product_quantity > $qtdSent){
                        
                        if( $detail->product_attribute_id == 0){
                            $product = product::where('id_product', $detail->product_id)->first();
                        }else{
                            $product = product_attribute::where('id_product', $detail->product_id)->where('id_product_attribute', $detail->product_attribute_id)->first();
                        }
                        
                        if(isset($product->id_product)){
                        
                            $detail->location = $product->location; 
                            
                            $is_pack = pack::is_pack($detail->product_id);
    
                            if( $is_pack ){
                                
                                $pack_products = pack::getPackItems($detail->product_id);
        
                                foreach($pack_products AS $pack_item){
                                    
                                    $picking = array();
                                    
                                    $product = product::with('lang')->where('id_product',  $pack_item->id_product_item)->first();
                                    
                                    $picking['id_shop'] = $detail->id_shop;
                                    $picking['product_name'] = $product->lang->name;
                                    if( $pack_item->id_product_attribute_item == 0){
                                        $picking['product_reference'] = $product->reference;                      
                                        $picking['product_ean13'] = $product->ean13; 
                                        $picking['location'] = $product->location; 
                                    }else{
                                        $attribute = product_attribute::where('id_product',  $pack_item->id_product_item)->where('id_product_attribute',  $pack_item->id_product_attribute_item)->first();
                                        $picking['product_reference'] = $attribute->reference;                      
                                        $picking['product_ean13'] = $attribute->ean13; 
                                        $picking['location'] = $attribute->location; 
                                    }
                                    
                                    $picking['product_id'] = $pack_item->id_product_item;                    
                                    $picking['product_attribute_id'] = $pack_item->id_product_attribute_item;  
                                    $picking['is_new'] = self::needsMarketingPhotos(
                                        (int) $pack_item->id_product_item,
                                        (int) $pack_item->id_product_attribute_item
                                    );
                                    
                                    $quantity = $detail->product_quantity - $qtdSent;
                                    
                                    $picking['product_quantity'] = ( $quantity * $pack_item->quantity);
                                    $picking['quantity_picked'] = 0;                      
                                    $picking['row_done'] = 0;                      
                                    $picking['id_order'] = $detail->id_order; 
    
                                    self::insertData( (object)$picking, $picking['product_quantity'], $status, $order->carrier->name);
                                    
                                }
                            }else{
                                $quantity = $detail->product_quantity - $qtdSent;
                                self::insertData($detail, $quantity, $status, $order->carrier->name);
                            }
                        }
                    }
                }
            }    
        }
    }

    public static function classifyPaymentAcceptedOrders(): object
    {
        $orders = orders::with('order_detail')->where('current_state', 2)->get();
        $summary = [
            'checked' => 0,
            'preparation' => 0,
            'backorder' => 0,
        ];

        foreach ($orders as $order) {
            $summary['checked']++;
            $idOrder = (int) $order->id_order;
            $targetState = self::orderHasEnoughStock($order) ? 3 : 15;

            self::moveOrderState($idOrder, $targetState);
            $summary[$targetState === 3 ? 'preparation' : 'backorder']++;

            if ($targetState === 15) {
                self::where('id_order', $idOrder)->delete();
            }
        }

        return (object) $summary;
    }

    private static function removeNonPreparationPickingRows(): void
    {
        $orderIds = self::where('status', 'preparation')
            ->pluck('id_order')
            ->unique()
            ->values()
            ->all();

        if (empty($orderIds)) {
            return;
        }

        $preparationOrderIds = orders::whereIn('id_order', $orderIds)
            ->where('current_state', 3)
            ->pluck('id_order')
            ->map(fn ($idOrder) => (int) $idOrder)
            ->all();

        self::where('status', 'preparation')
            ->whereNotIn('id_order', $preparationOrderIds)
            ->delete();
    }

    private static function orderHasEnoughStock($order): bool
    {
        foreach (self::stockRequirementsForOrder($order) as $requirement) {
            $stock = self::stockQuantityFor(
                (int) $requirement['id_product'],
                (int) $requirement['id_product_attribute'],
                (int) $order->id_shop
            );

            if ($stock < (int) $requirement['quantity']) {
                return false;
            }
        }

        return true;
    }

    private static function stockRequirementsForOrder($order): array
    {
        $requirements = [];
        $customDetails = self::customOrderDetails($order);

        foreach ($order->order_detail as $detail) {
            $quantity = max(0, (int) $detail->product_quantity - self::qtdSent($detail, $customDetails));

            if ($quantity <= 0) {
                continue;
            }

            if (pack::is_pack((int) $detail->product_id)) {
                foreach (pack::getPackItems((int) $detail->product_id) as $packItem) {
                    self::addStockRequirement(
                        $requirements,
                        (int) $packItem->id_product_item,
                        (int) $packItem->id_product_attribute_item,
                        $quantity * max(1, (int) $packItem->quantity)
                    );
                }

                continue;
            }

            self::addStockRequirement(
                $requirements,
                (int) $detail->product_id,
                (int) $detail->product_attribute_id,
                $quantity
            );
        }

        return $requirements;
    }

    private static function addStockRequirement(array &$requirements, int $idProduct, int $idProductAttribute, int $quantity): void
    {
        if ($idProduct <= 0 || $quantity <= 0) {
            return;
        }

        $key = $idProduct . ':' . $idProductAttribute;

        if (!isset($requirements[$key])) {
            $requirements[$key] = [
                'id_product' => $idProduct,
                'id_product_attribute' => $idProductAttribute,
                'quantity' => 0,
            ];
        }

        $requirements[$key]['quantity'] += $quantity;
    }

    private static function stockQuantityFor(int $idProduct, int $idProductAttribute, int $idShop): int
    {
        if ($idProduct <= 0) {
            return 0;
        }

        return (int) (DB::connection('mysql2')
            ->table(self::psTable('stock_available'))
            ->where('id_product', $idProduct)
            ->where('id_product_attribute', $idProductAttribute)
            ->when($idShop > 0, fn ($query) => $query->where('id_shop', $idShop))
            ->value('quantity') ?? 0);
    }

    private static function customOrderDetails($order)
    {
        $detailIds = $order->order_detail
            ->pluck('id_order_detail')
            ->filter()
            ->values()
            ->all();

        if (empty($detailIds)) {
            return collect();
        }

        return DB::connection('mysql2')
            ->table(self::psTable('custom_order_detail'))
            ->whereIn('id_order_detail', $detailIds)
            ->get()
            ->keyBy('id_order_detail');
    }

    private static function qtdSent($detail, $customDetails): int
    {
        $customDetail = $customDetails->get($detail->id_order_detail);

        return (int) ($customDetail->qtd_sent ?? $detail->qtd_sent ?? 0);
    }

    private static function moveOrderState(int $idOrder, int $idOrderState): void
    {
        if ($idOrder <= 0 || !in_array($idOrderState, [3, 15], true)) {
            return;
        }

        DB::connection('mysql2')->transaction(function () use ($idOrder, $idOrderState) {
            $updated = DB::connection('mysql2')
                ->table(self::psTable('orders'))
                ->where('id_order', $idOrder)
                ->where('current_state', 2)
                ->update([
                    'current_state' => $idOrderState,
                    'date_upd' => now()->toDateTimeString(),
                ]);

            if (!$updated) {
                return;
            }

            DB::connection('mysql2')
                ->table(self::psTable('order_history'))
                ->insert([
                    'id_employee' => Auth::id() ?? 0,
                    'id_order' => $idOrder,
                    'id_order_state' => $idOrderState,
                    'date_add' => now()->toDateTimeString(),
                ]);
        });
    }
        
    private static function insertData($row, $quantity, $status, $carrier_name){
        
        $needsMarketingPhotos = self::needsMarketingPhotos((int) $row->product_id, (int) $row->product_attribute_id);
        $existingRow = self::where('id_order', $row->id_order)
            ->where('id_product', $row->product_id)
            ->where('id_product_attribute', $row->product_attribute_id)
            ->first();
        
        if( $existingRow ){
            if (self::hasPickingIsNewColumn()) {
                $existingRow->is_new = $needsMarketingPhotos;
            }

            $existingRow->save();
        }else{
            $insert = [
                'status'  => $status,
                'housing'  => (!is_null($row->location)) ? $row->location : 'N/D',
                'id_shop' => $row->id_shop,
                'name' => $row->product_name,
                'id_product' => $row->product_id,
                'id_product_attribute' => $row->product_attribute_id,
                'reference' => $row->product_reference,
                'product_barcode' => $row->product_ean13,
                'quantity' => $quantity,
                'quantity_picked' => 0,
                'row_done' => 0,
                'id_order' => $row->id_order,
                'carrier' => $carrier_name
            ];

            if (self::hasPickingIsNewColumn()) {
                $insert['is_new'] = $needsMarketingPhotos;
            }

            picking::insert($insert);
        }

    }

    private static function needsMarketingPhotos(int $idProduct, int $idProductAttribute = 0): bool
    {
        if ($idProduct <= 0) {
            return false;
        }

        if ($idProductAttribute > 0) {
            $photoCount = DB::connection('mysql2')
                ->table(self::psTable('product_attribute_image'))
                ->where('id_product_attribute', $idProductAttribute)
                ->distinct()
                ->count('id_image');
        } else {
            $photoCount = DB::connection('mysql2')
                ->table(self::psTable('image'))
                ->where('id_product', $idProduct)
                ->count();
        }

        return $photoCount < 5;
    }

    private static function hasPickingIsNewColumn(): bool
    {
        static $hasColumn = null;

        if ($hasColumn === null) {
            $hasColumn = Schema::connection('mysql')->hasColumn('picking', 'is_new');
        }

        return $hasColumn;
    }

    private static function psTable(string $table): string
    {
        return (string) env('DB2_DB_prefix', 'ps_') . $table;
    }

    public static function rowDone($data) {

        $user = Auth::id() . ' - ' . Auth::user()->name;

        $scannedQuantity = max(0, (int) ($data->scannedQuantity ?? 0));
        $quantityRequested = max(0, (int) ($data->quantityRequested ?? 0));

        $row = picking::where('id_order', (int) $data->id_order)
                ->where('id_product', (int) $data->id_product)
                ->where('id_product_attribute', (int) $data->id_product_attribute)
                ->where(function ($query) use ($data) {
                    $query->where('product_barcode', $data->barcode)
                          ->orWhere('reference', $data->barcode);
                })
                ->first();

        if (!$row) {
            return 0;
        }

        $targetQuantity = $quantityRequested > 0 ? $quantityRequested : (int) $row->quantity;

        if ($scannedQuantity > $targetQuantity) {
            return 3;
        }

        $row->quantity_picked = $scannedQuantity;
        $row->operator = $user;
        $row->row_done = ($scannedQuantity >= $targetQuantity) ? 1 : 0;

        if (!empty($data->pickingContainer)) {
            $row->barcode = $data->pickingContainer;
        }

        $row->save();

        $updateData = ['operator' => $user];

        if (!empty($data->pickingContainer)) {
            $updateData['barcode'] = $data->pickingContainer;
        }

        picking::where('id_order', (int) $data->id_order)->update($updateData);
        
        return self::orderDone($data->id_order);

    }

    public static function saveContainer($data) {

        $idOrder = (int) ($data->id_order ?? 0);
        $barcode = trim((string) ($data->pickingContainer ?? ''));

        if ($idOrder <= 0 || $barcode === '') {
            return 0;
        }

        if (self::orderDone($idOrder) != 1) {
            return 2;
        }

        picking::where('id_order', $idOrder)->update([
            'barcode' => $barcode,
            'operator' => Auth::id() . ' - ' . Auth::user()->name,
        ]);

        return 1;
    }

    private static function orderDone($id_order) {
        
        $rowsOfOrder = picking::where('id_order', $id_order)->count();
        $pickedRowsOfOrder = picking::where('id_order', $id_order)->where('row_done', 1)->count();
        
        if( $rowsOfOrder == $pickedRowsOfOrder ) return 1;

        return 2;
    }

    public static function getEAN($data) {
        
        $product = product::select('ean13')->where( 'ean13', $data->code )->orWhere( 'reference', $data->code )->first();
        
        if( isset( $product->ean13 ) ) return $product->ean13;
        
        $product = product_attribute::select('ean13')->where( 'ean13', $data->code )->orWhere( 'reference', $data->code )->first();
        
        if( isset( $product->ean13 ) ) return $product->ean13;

        return 999;

    }

}
