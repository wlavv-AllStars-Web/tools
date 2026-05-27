<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use App\Models\modules\oms\BilledOrderLine;
use App\Services\Prestashop\PrestashopAdminLinkService;

class product_attribute extends PrestashopModel
{
    use HasFactory;

    protected $primaryKey = 'id_product_attribute';
    protected $fillable = ['wholesale_price'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('product_attribute');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function product()
    {
        return $this->hasOne(product::class, 'id_product', 'id_product');
    }

    public function stock()
    {
        return $this->hasOne(stock_available::class, 'id_product_attribute', 'id_product_attribute');
    }

    public function pack()
    {
        return $this->hasMany(pack::class, 'id_product_pack', 'id_product');
    }

    public function sold()
    {
        return $this->hasMany(orders_details::class, 'product_attribute_id', 'id_product_attribute');
    }

    public function images()
    {
        return $this->hasMany(product_attribute_image::class, 'id_product_attribute', 'id_product_attribute');
    }

    public function erp_invoiced()
    {
        return $this->hasOne(BilledOrderLine::class, 'product_attribute_id', 'id_product_attribute')
            ->selectRaw('product_attribute_id, SUM(qty_billed) as qty_wmfaturado')
            ->whereRaw('COALESCE(qty_billed, 0) > COALESCE(qty_received, 0)')
            ->groupBy('product_attribute_id')
            ->withDefault([
                'qty_wmfaturado' => 0
            ]);
    }

    public function erp_expected()
    {
        return $this->hasOne(BilledOrderLine::class, 'product_attribute_id', 'id_product_attribute')
            ->selectRaw('product_attribute_id, SUM(GREATEST(COALESCE(qty_billed, 0) - COALESCE(qty_received, 0), 0)) as qty_expected')
            ->whereRaw('COALESCE(qty_billed, 0) > COALESCE(qty_received, 0)')
            ->groupBy('product_attribute_id')
            ->withDefault([
                'qty_expected' => 0
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public static function getCombination($id_product_attribute)
    {
        if ((int) $id_product_attribute <= 0) {
            return '';
        }

        $attributeLangTable = self::tableName('attribute_lang');
        $combinationTable = self::tableName('product_attribute_combination');
        $productAttributeTable = self::tableName('product_attribute');

        $data = DB::connection('mysql2')
            ->table($attributeLangTable)
            ->select($attributeLangTable . '.name')
            ->join($combinationTable, $attributeLangTable . '.id_attribute', '=', $combinationTable . '.id_attribute')
            ->join($productAttributeTable, $combinationTable . '.id_product_attribute', '=', $productAttributeTable . '.id_product_attribute')
            ->where($productAttributeTable . '.id_product_attribute', $id_product_attribute)
            ->where($attributeLangTable . '.id_lang', 1)
            ->get();

        $names = [];

        foreach ($data as $item) {
            $names[] = $item->name;
        }

        return implode('; ', $names);
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARDS
    |--------------------------------------------------------------------------
    */

    public static function dashboard_attribute_less_then_5_pics($type)
    {
        $products = [];

        $productAttributeTable = self::tableName('product_attribute');
        $productTable = self::tableName('product');
        $manufacturerTable = self::tableName('manufacturer');
        $stockTable = self::tableName('stock_available');
        $customProductAttributeTable = self::tableName('custom_product_attribute');
        $productAttributeImageTable = self::tableName('product_attribute_image');
        $productShopTable = self::tableName('product_shop');
        $asmShopId = PrestashopAdminLinkService::shopId('ASM') ?: 2;

        $excludedProductIds = asm_dashboard::getExceptions('marketing_no_images')
            ->pluck('id_product')
            ->toArray();

        $query = self::select(
                $productTable . '.id_product',
                $productAttributeTable . '.id_product_attribute',
                $productTable . '.id_category_default',
                DB::raw('COUNT(DISTINCT ' . $productAttributeImageTable . '.id_image) AS nr_images'),
                $productAttributeTable . '.reference',
                DB::raw($customProductAttributeTable . '.location AS housing'),
                DB::raw($manufacturerTable . '.name AS brand')
            )
            ->leftJoin($productTable, $productAttributeTable . '.id_product', '=', $productTable . '.id_product')
            ->join($productShopTable, function ($join) use ($productAttributeTable, $productShopTable, $asmShopId) {
                $join->on($productShopTable . '.id_product', '=', $productAttributeTable . '.id_product')
                    ->where($productShopTable . '.id_shop', $asmShopId)
                    ->where($productShopTable . '.active', 1);
            })
            ->leftJoin($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->leftJoin($stockTable, function ($join) use ($productAttributeTable, $stockTable, $asmShopId) {
                $join->on($productAttributeTable . '.id_product_attribute', '=', $stockTable . '.id_product_attribute')
                    ->on($productAttributeTable . '.id_product', '=', $stockTable . '.id_product')
                    ->whereIn($stockTable . '.id_shop', [$asmShopId, 0]);
            })
            ->leftJoin($customProductAttributeTable, $productAttributeTable . '.id_product_attribute', '=', $customProductAttributeTable . '.id_product_attribute')
            ->leftJoin($productAttributeImageTable, $productAttributeTable . '.id_product_attribute', '=', $productAttributeImageTable . '.id_product_attribute')
            ->where($productTable . '.visibility', '<>', 'none')
            ->orderBy($productTable . '.id_product')
            ->groupBy(
                $productTable . '.id_product',
                $productAttributeTable . '.id_product_attribute',
                $productTable . '.id_category_default',
                $productAttributeTable . '.reference',
                $customProductAttributeTable . '.location',
                $manufacturerTable . '.name',
                $productShopTable . '.id_shop'
            )
            ->havingRaw('MAX(' . $stockTable . '.quantity) > 0')
            ->havingRaw('COUNT(DISTINCT ' . $productAttributeImageTable . '.id_image) < 5');

        if (!empty($excludedProductIds)) {
            $query->whereNotIn($productTable . '.id_product', $excludedProductIds);
        }

        $no_images = $query->get();

        foreach ($no_images as $image) {
            if (isset($image->id_product)) {
                $products[(int) $image->id_product_attribute] = [
                    'clean' => $image->id_product_attribute,
                    'id_product' => $image->id_product,
                    'id_product_attribute' => $image->id_product_attribute,
                    'id_category_default' => $image->id_category_default,
                    'nr_images' => $image->nr_images,
                    'reference' => $image->reference,
                    'housing' => $image->housing,
                    'brand' => $image->brand,
                    'url' => PrestashopAdminLinkService::dashboardProductAdminUrl((int) $image->id_product, 'ASM')
                ];
            }
        }

        return [
            'name' => trans('dashboard.ATTRIBUTES - No 5 photos'),
            'col' => 4,
            'item_id' => $type . '_attributes_no_5_pics',
            'prestashop' => PrestashopAdminLinkService::dashboardProductLink('id_product', 'ASM'),
            'columns' => ['id_product', 'id_product_attribute', 'reference', 'brand', 'housing', 'nr_images'],
            'counter' => count($products),
            'data' => array_values($products)
        ];
    }
}
