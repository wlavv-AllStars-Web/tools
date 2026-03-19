<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

use App\Models\prestashop\stock_available;

class pack extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."pack";
    }
    
    public static function is_pack($id_product){
        return self::where('id_product_pack', $id_product)->count();
    }

    public function product_pack(){
        return $this->hasOne(product::class, "id_product", 'id_product_item');
    }
    
    public static function getPackItems($id_product){
        return self::where('id_product_pack', $id_product)->get();
    }

    public static function dashboard_packs_without_stock($type){

        $data = array();

        $bd_data = self::select('ps_pack.id_product_pack', 'ps_product.id_product', 'ps_product.reference', DB::RAW('ps_manufacturer.name AS brand'), 'ps_stock_available.quantity')
            ->join('ps_product',            'ps_pack.id_product_pack',      '=', 'ps_product.id_product')
            ->join('ps_manufacturer',       'ps_product.id_manufacturer',   '=', 'ps_manufacturer.id_manufacturer')
            ->join('ps_stock_available',    'ps_product.id_product',        '=', 'ps_stock_available.id_product')
            ->where('ps_stock_available.quantity', '<', 1)
            ->where('ps_product.active', 1 )
            ->orderBy('brand', 'ASC')
            ->get();

        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'brand' => $item->brand, 'quantity' => $item->quantity];
        
        return [
            'name'              => trans("dashboard.Products's Pack without stock"),
            'col'               => 4,
            'item_id'           => $type . '_packs_without_stock',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'brand', 'quantity'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function isPack(int $idProduct){
        return self::where('id_product_pack', $idProduct)->exists();
    }

    public static function items(int $idProduct){
        return self::where('id_product_pack', $idProduct)->get();
    }

    public static function availablePackQty(int $idProduct, string $stockTable = 'ps_stock_available'): array
    {
        $items = self::items($idProduct);

        if ($items->isEmpty()) {
            return [
                'is_pack' => false,
                'pack_qty' => null,
                'components' => [],
            ];
        }

        $stocks = stock_available::select(array('id_product', 'id_product_attribute', 'quantity'))
            ->where(function ($q) use ($items) {
                foreach ($items as $it) {
                    $q->orWhere(function ($qq) use ($it) {
                        $qq->where('id_product', (int)$it->id_product_item)
                           ->where('id_product_attribute', (int)$it->id_product_attribute_item);
                    });
                }
            })
            ->get()
            ->keyBy(function ($r) {
                return $r->id_product . '-' . $r->id_product_attribute;
            });


        $packQty = null;
        $components = [];

        foreach ($items as $it) {
            $idItem = (int)$it->id_product_item;
            $idAttr = (int)$it->id_product_attribute_item;
            $qtyInPack = max(1, (int)$it->quantity);

            $key = $idItem.'-'.$idAttr;
            $stock = (int)($stocks[$key]->quantity ?? 0);

            $possible = (int) floor($stock / $qtyInPack);
            $packQty = is_null($packQty) ? $possible : min($packQty, $possible);

            $components[] = [
                'id_product' => $idItem,
                'id_product_attribute' => $idAttr,
                'qty_in_pack' => $qtyInPack,
                'stock' => $stock,
                'possible_packs' => $possible,
            ];
        }

        return [
            'is_pack' => true,
            'pack_qty' => $packQty ?? 0,
            'components' => $components,
        ];
    }
    
}
