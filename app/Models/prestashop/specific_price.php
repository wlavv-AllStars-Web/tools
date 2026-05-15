<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use App\Services\Prestashop\PrestashopAdminLinkService;
use Illuminate\Support\Facades\Log;

class specific_price extends PrestashopModel
{
    use HasFactory;

    protected $primaryKey = 'id_specific_price';
    protected $fillable = [];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = self::tableName('specific_price');
    }

    public function product(){
        return $this->belongsTo(product::class, 'id_product', 'id_product');
    }
    
    public static function dashboard_same_sku_diff_discount($type){
        
        $data = [];
    
        $productTable = self::tableName('product');
        $productShopTable = self::tableName('product_shop');
        $specificPriceTable = self::tableName('specific_price');
    
        $bd_data = product::select(
                $productShopTable . '.id_shop',
                $productTable . '.reference',
                DB::raw('MIN(' . $productTable . '.id_product) AS id_product'),
                DB::raw('COUNT(DISTINCT ' . $productTable . '.id_product) AS products_count'),
                DB::raw('COUNT(DISTINCT ' . $specificPriceTable . '.id_product) AS specific_price_count'),
                DB::raw('COUNT(DISTINCT ' . $specificPriceTable . '.reduction) AS discounts_count')
            )
            ->join($productShopTable, $productShopTable . '.id_product', '=', $productTable . '.id_product')
            ->leftJoin($specificPriceTable, function ($join) use ($productTable, $productShopTable, $specificPriceTable) {
                $join->on($specificPriceTable . '.id_product', '=', $productTable . '.id_product')
                    ->whereRaw($specificPriceTable . '.id_shop IN (0, ' . $productShopTable . '.id_shop)')
                    ->where($specificPriceTable . '.id_cart', 0)
                    ->where($specificPriceTable . '.id_customer', 0);
            })
            ->whereIn($productShopTable . '.id_shop', [2, 3])
            ->where($productShopTable . '.active', 1)
            ->whereNotNull($productTable . '.reference')
            ->where($productTable . '.reference', '!=', '')
            ->groupBy(
                $productShopTable . '.id_shop',
                $productTable . '.reference'
            )
            ->havingRaw('COUNT(DISTINCT ' . $productTable . '.id_product) > 1')
            ->havingRaw('COUNT(DISTINCT ' . $specificPriceTable . '.reduction) > 1')
            ->orderBy($productShopTable . '.id_shop')
            ->orderBy($productTable . '.reference')
            ->get();
    
        Log::info('dashboard_same_sku_diff_discount RAW', [
            'count' => $bd_data->count(),
            'rows'  => $bd_data->toArray(),
        ]);
    
        foreach ($bd_data as $item) {
            Log::info('dashboard_same_sku_diff_discount ITEM', [
                'id_shop'              => $item->id_shop,
                'reference'            => $item->reference,
                'id_product'           => $item->id_product,
                'products_count'       => $item->products_count,
                'specific_price_count' => $item->specific_price_count,
                'discounts_count'      => $item->discounts_count,
            ]);
    
            $storeCode = ((int) $item->id_shop === 3) ? 'ASD' : 'ASM';
    
            $data[] = [
                'id_shop'              => ((int) $item->id_shop === 3) ? 'ASD' : 'ASM',
                'id_product'           => $item->id_product,
                'reference'            => $item->reference,
                'products_count'       => $item->products_count,
                'specific_price_count' => $item->specific_price_count,
                'discounts_count'      => $item->discounts_count,
                'url'                  => PrestashopAdminLinkService::dashboardProductAdminUrl($item->id_product, $storeCode),
            ];
        }
    
        Log::info('dashboard_same_sku_diff_discount FINAL DATA', [
            'count' => count($data),
            'data'  => $data,
        ]);
    
        return self::dashboardPanel(
            trans('dashboard.Same SKU diff discount'),
            $type,
            'same_sku_diff_discount',
            ['id_shop', 'id_product', 'reference', 'discounts_count'],
            $data
        );
    }
}
