<?php

namespace App\Models\modules\backorders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


use App\Models\prestashop\orders;
use App\Models\prestashop\product_lang;
use App\Models\prestashop\stock_available;

class customers_backorders extends Model
{   
    use HasFactory;
    protected $table = "customers_backorders";
    public $primaryKey = 'id';
    
    public static function checkBackorder($id_order, $id_product, $id_product_attribute, $store){
        $store = self::normalizeStore($store);
        return self::where('id_order', $id_order)->where('id_product', $id_product)->where('id_product_attribute', $id_product_attribute)->where('store', $store)->first();
    }
    
    public static function insertBackorder($data){

        $exists = self::checkBackorder($data['id_order'], $data['id_product'], $data['id_product_attribute'], $data['store']);

        if( !isset($exists->id) ){
            
            $backorder = new customers_backorders();
            
            $backorder->id_order = $data['id_order'];
            $backorder->id_product = $data['id_product'];
            $backorder->id_product_attribute = $data['id_product_attribute'];
            $backorder->store = self::normalizeStore($data['store']);
            $backorder->type = $data['type'];
            $backorder->brand = $data['brand'];
            $backorder->supplier = $data['supplier'];
            $backorder->module = $data['payment'];
            $backorder->sold = $data['sold'];
            $backorder->order_date = $data['date_add'];
            $backorder->reference = $data['reference'];
            $backorder->created_at = date('Y-m-d');
            
            $backorder->save();
            
        }
        return 1;
    }
    
    public static function getAll(){
        
        $rows = array();
        
        $data = self::where('customers_backorders.done', 0)->get();
        
        foreach($data AS $item){

            $id_product = (int) $item->id_product;
            $id_product_attribute = (int) $item->id_product_attribute;

            if (self::isGoodiesBackorderLine($id_product, (string) $item->reference)) {
                continue;
            }
            $stock_available = stock_available::where('id_product', $id_product)
                ->where('id_product_attribute', $id_product_attribute)
                ->where('id_shop', 0)
                ->where('id_shop_group', 1)
                ->first();

            if (is_null($stock_available)) {
                $stock_available = stock_available::where('id_product', $id_product)
                    ->where('id_product_attribute', $id_product_attribute)
                    ->where('id_shop_group', 0)
                    ->when(self::normalizeStore($item->store) === 'ASM', fn ($query) => $query->where('id_shop', 2))
                    ->when(self::normalizeStore($item->store) === 'ASD', fn ($query) => $query->where('id_shop', 3))
                    ->first();
            }

            $expected_days = product_lang::where('id_product', $id_product)->where('id_lang', 1)->value('available_later');

            $date1 = strtotime($item->order_date);
            $date2 = strtotime(date('Y-m-d'));
            $diff = $date2 - $date1;
            $days = floor($diff / (60 * 60 * 24)) + 1;
            
            preg_match_all('/\d+/', (string) $expected_days, $matches);
            
            if (!empty($matches[0])) {
                if (count($matches[0]) == 2) {
                    $expected = max($matches[0]);
                } else {
                    $expected = $matches[0][0];
                }
            } else {
                $expected = 0;
            }

            $erp_ordered = null;
            if (Schema::hasTable('oms_billed_order_lines')) {
                $erp_ordered = DB::table('oms_billed_order_lines as bol')
                    ->where('bol.product_id', $id_product)
                    ->where(function ($query) use ($id_product_attribute) {
                        if ($id_product_attribute > 0) {
                            $query->where('bol.product_attribute_id', $id_product_attribute);
                        } else {
                            $query->whereNull('bol.product_attribute_id')
                                ->orWhere('bol.product_attribute_id', 0);
                        }
                    })
                    ->selectRaw('SUM(COALESCE(bol.qty_billed, 0)) AS invoiced, SUM(COALESCE(bol.qty_received, 0)) AS qty_received, SUM(GREATEST(COALESCE(bol.qty_billed, 0) - COALESCE(bol.qty_received, 0), 0)) AS expected')
                    ->first();
            }
            
            $erp_ordered = ( is_null($erp_ordered) ) ? null : $erp_ordered;
            $erpExpected = (int) ($erp_ordered->expected ?? 0);

            $omsExpected = DB::table('oms_order_note_lines as onl')
                ->where('onl.product_id', $id_product)
                ->where(function ($query) use ($id_product_attribute) {
                    if ($id_product_attribute > 0) {
                        $query->where('onl.product_attribute_id', $id_product_attribute);
                    } else {
                        $query->whereNull('onl.product_attribute_id')
                            ->orWhere('onl.product_attribute_id', 0);
                    }
                })
                ->leftJoinSub(
                    DB::table('oms_billed_order_lines as bol')
                        ->join('oms_billed_orders as bo', 'bo.id', '=', 'bol.billed_order_id')
                        ->join('oms_supplier_invoices as si', 'si.id', '=', 'bo.supplier_invoice_id')
                        ->where('si.status', '<>', 'cancelled')
                        ->select(
                            'bol.order_note_line_id',
                            DB::raw('SUM(bol.qty_received) AS qty_received')
                        )
                        ->groupBy('bol.order_note_line_id'),
                    'received',
                    'received.order_note_line_id',
                    '=',
                    'onl.id'
                )
                ->sum(DB::raw(
                    'GREATEST(COALESCE(onl.qty_ordered, 0) - COALESCE(received.qty_received, 0), 0)'
                ));

            // A billed line can exist without an OMS order-note line (for example, an imported supplier bill).
            // Keep its outstanding quantity visible and avoid double-counting when both sources represent the same demand.
            $erpExpected = max($erpExpected, (int) $omsExpected);

            $stockQuantity = isset($stock_available->quantity) ? (int) $stock_available->quantity : 0;
            $soldQuantity = (int) $item->sold;

            if ($stockQuantity === 0 || max($stockQuantity, 0) >= $soldQuantity) {
                continue;
            }
            
            $country_order = orders::with('delivery', 'delivery.country.lang_en')->where('id_order', $item->id_order)->first();
            $isShipPick = ((int) $item->id_product === 0 && (int) $item->id_product_attribute === 0);
            
            $rows[] = (object)[
                    'id'                    => $item->id,
                    'id_order'              => $item->id_order,
                    'id_product'            => $id_product,
                    'id_product_attribute'  => $id_product_attribute,
                    'original_id_product'            => $item->id_product,
                    'original_id_product_attribute'  => $item->id_product_attribute,
                    'reference'             => $item->reference,
                    'is_ship_pick'          => $isShipPick,
                    'supplier'              => $item->supplier,
                    'brand'                 => $item->brand,
                    'sold'                  => $item->sold,
                    'store'                 => self::normalizeStore($item->store),
                    'stock'                 => ( isset($stock_available->quantity) ) ? $stock_available->quantity : 'N/D',
                    'module'                => $item->module,
                    'type'                  => $item->type,
                    'invoiced'              => $item->invoiced,
                    'eta'                   => $item->eta,
                    'customer_contact'      => $item->customer_contact,
                    'customer_answer'       => $item->customer_answer,
                    'order_date'            => $item->order_date,
                    'days'                  => $days,
                    'expected_days'         => $expected_days,
                    'expected'              => $expected,
                    'color'                 => $item->rowColor,
                    'erp_qty_received'      => (isset($erp_ordered->qty_received)) ? $erp_ordered->qty_received : 0,    
                    'erp_qty_invoiced'      => (isset($erp_ordered->invoiced)) ? $erp_ordered->invoiced : 0,    
                    'erp_qty_expected'      => $erpExpected,
                    'id_country'            => (self::normalizeStore($item->store) == 'ASM') ? ($country_order?->delivery?->id_country ?? 0) : 0,
                    'country'               => (self::normalizeStore($item->store) == 'ASM') ? ($country_order?->delivery?->country?->lang_en?->name ?? 'N/D') : 'ASD'
                ];
        }
        return $rows;
    }
    
    private static function isGoodiesBackorderLine(int $idProduct, string $reference): bool
    {
        $reference = strtoupper(trim($reference));

        if (in_array($idProduct, [11276, 17452, 20034, 20035, 20036, 20037, 20038, 20039, 20041, 20741], true)) {
            return true;
        }

        if (str_starts_with($reference, 'ASMGOODS-')) {
            return true;
        }

        return in_array($reference, ['ASAF', 'ASKC', 'FLYERASM', 'STICKERASM', 'ASMSTICKRING'], true);
    }

    public static function getAllASM(){
        return self::where('customers_backorders.done', 0)->where('store', 'ASM')->get();
    }
    
    public static function getAllASD(){
        return self::where('customers_backorders.done', 0)->where('store', 'ASD')->get();
    }
    
    public static function verifyOrdersStatus(){
        
        $getAllASM = self::getAllASM();
        $getAllASD = self::getAllASD();
        
        foreach($getAllASM as $order){

            $order_info = orders::where('id_order', $order->id_order)->first();
            
            if( !is_null($order_info) && in_array($order_info->current_state , [16, 7, 6, 5, 4] ) ){
                self::where('id', $order->id)
                    ->where(function ($query) {
                        $query->where('id_product', '!=', 0)
                            ->orWhere('id_product_attribute', '!=', 0);
                    })
                    ->update(['done' => 1]);
            }
            /**
            else{
                self::where('id', $order->id)->update(['done' => 0]);
            }
            **/
        }

        $asd_ids = array();
        
        foreach($getAllASD as $order){
            $asd_ids[] = $order->id_order;
        }
        
        if(isset( $asd_ids ) && ( count($asd_ids) > 0 ) ){
            
            try {
                $asd_ids = self::getASDStatusOrders($asd_ids);
            } catch (\Throwable $exception) {
                $asd_ids = [];
            }
        
            foreach(($asd_ids ?? []) as $id_order => $status){
                
                self::where('id_order', $id_order)->where('store', 'ASD')->update(['done' => $status]);
                
            }
        }
    }
    
    public static function getCounters($rows = null){
        if (!is_null($rows)) {
            $rows = collect($rows);

            return [
                'asm_backorder' => $rows->where('store', 'ASM')->where('type', 'backorder')->count(),
                'asd_backorder' => $rows->where('store', 'ASD')->where('type', 'backorder')->count(),
            ];
        }
        
        $asm_backorders = self::where('done', 0)->where('store', 'ASM')->where('type', 'backorder')->count();
        $asd_backorders = self::where('done', 0)->where('store', 'ASD')->where('type', 'backorder')->count();
        
        return [
            'asm_backorder' => $asm_backorders,
            'asd_backorder' => $asd_backorders,
        ];
    }
    
    public static function updateInfo($data){
        
        $column = substr($data->column, 10, -1);

        self::where('id_order', $data->id_order)
            ->where('id_product', $data->id_product)
            ->where('id_product_attribute', $data->id_product_attribute)
            ->where(function ($query) {
                $query->where('id_product', '!=', 0)
                    ->orWhere('id_product_attribute', '!=', 0);
            })
            ->update([$column => $data->value]);
        
        return 1;

    }

    public static function getASDStatusOrders($data){
        $ids = collect((array) $data)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $prefix = env('DB2_DB_prefix', env('DB2_prefix', 'ps_'));
        $doneStates = [16, 7, 6, 5, 4];

        $orders = DB::connection('mysql2')
            ->table($prefix . 'orders')
            ->whereIn('id_order', $ids->all())
            ->where('id_shop', 3)
            ->pluck('current_state', 'id_order');

        $statuses = [];
        foreach ($ids as $id) {
            $statuses[$id] = in_array((int) ($orders[$id] ?? 0), $doneStates, true) ? 1 : 0;
        }

        return $statuses;
    }

    public static function getBackorderDetail($id_order){
        return self::where('id_order', $id_order)->first();
    }

    private static function normalizeStore($store): string
    {
        return match ((string) $store) {
            '2' => 'ASM',
            '3' => 'ASD',
            default => (string) $store,
        };
    }
    
}

