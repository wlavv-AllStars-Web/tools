<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use App\Services\Prestashop\PrestashopAdminLinkService;

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
        $now = now()->format('Y-m-d H:i:s');

        $productProfiles = DB::connection('mysql2')
            ->table($productTable . ' as p')
            ->join($productShopTable . ' as ps', 'ps.id_product', '=', 'p.id_product')
            ->leftJoin($specificPriceTable . ' as sp', function ($join) use ($now) {
                $join->on('sp.id_product', '=', 'p.id_product')
                    ->whereRaw('sp.id_shop IN (0, ps.id_shop)')
                    ->where('sp.id_cart', 0)
                    ->where('sp.id_customer', 0)
                    ->where(function ($query) use ($now) {
                        $query->whereNull('sp.from')
                            ->orWhere('sp.from', '0000-00-00 00:00:00')
                            ->orWhere('sp.from', '<=', $now);
                    })
                    ->where(function ($query) use ($now) {
                        $query->whereNull('sp.to')
                            ->orWhere('sp.to', '0000-00-00 00:00:00')
                            ->orWhere('sp.to', '>=', $now);
                    });
            })
            ->whereIn('ps.id_shop', [2, 3])
            ->where('ps.active', 1)
            ->whereNotNull('p.reference')
            ->where('p.reference', '!=', '')
            ->groupBy('ps.id_shop', 'p.reference', 'p.id_product')
            ->select(
                'ps.id_shop',
                'p.reference',
                'p.id_product',
                DB::raw(
                    'COALESCE(GROUP_CONCAT(CONCAT_WS("|", ' .
                    'COALESCE(sp.id_shop, 0), ' .
                    'COALESCE(sp.id_currency, 0), ' .
                    'COALESCE(sp.id_country, 0), ' .
                    'COALESCE(sp.id_group, 0), ' .
                    'COALESCE(sp.id_customer, 0), ' .
                    'COALESCE(sp.id_product_attribute, 0), ' .
                    'COALESCE(sp.from_quantity, 0), ' .
                    'COALESCE(sp.reduction_type, "none"), ' .
                    'COALESCE(sp.reduction, 0), ' .
                    'COALESCE(sp.reduction_tax, 0)' .
                    ') ORDER BY sp.id_shop, sp.id_currency, sp.id_country, sp.id_group, sp.id_customer, sp.id_product_attribute, sp.from_quantity, sp.reduction_type, sp.reduction, sp.reduction_tax SEPARATOR ";"), "") AS discount_profile'
                )
            );

        $bd_data = DB::connection('mysql2')
            ->query()
            ->fromSub($productProfiles, 'profiles')
            ->select(
                'id_shop',
                'reference',
                DB::raw('MIN(id_product) AS id_product'),
                DB::raw('COUNT(DISTINCT id_product) AS products_count'),
                DB::raw('COUNT(DISTINCT discount_profile) AS discounts_count')
            )
            ->groupBy('id_shop', 'reference')
            ->havingRaw('COUNT(DISTINCT id_product) > 1')
            ->havingRaw('COUNT(DISTINCT discount_profile) > 1')
            ->orderBy('id_shop')
            ->orderBy('reference')
            ->get();
    
        foreach ($bd_data as $item) {
            $storeCode = ((int) $item->id_shop === 3) ? 'ASD' : 'ASM';
    
            $data[] = [
                'id_shop'              => ((int) $item->id_shop === 3) ? 'ASD' : 'ASM',
                'id_product'           => $item->id_product,
                'reference'            => $item->reference,
                'products_count'       => $item->products_count,
                'discounts_count'      => $item->discounts_count,
                'url'                  => PrestashopAdminLinkService::dashboardProductAdminUrl($item->id_product, $storeCode),
            ];
        }
    
        return self::dashboardPanel(
            trans('dashboard.Same SKU diff discount'),
            $type,
            'same_sku_diff_discount',
            ['id_shop', 'id_product', 'reference', 'discounts_count'],
            $data
        );
    }
}
