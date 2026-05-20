<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

use App\Models\modules\compats\compats_product;
use App\Models\modules\oms\BilledOrderLine;

use App\Services\Prestashop\PrestashopAdminLinkService;

class product extends PrestashopModel
{
    use HasFactory;

    protected $primaryKey = 'id_product';
    protected $fillable = ['wholesale_price'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('product');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function product_lang()
    {
        return $this->hasOne(product_lang::class, 'id_product', 'id_product');
    }

    public function lang()
    {
        return $this->hasOne(product_lang::class, 'id_product', 'id_product')
            ->where('id_lang', 1);
    }

    public function pack()
    {
        return $this->hasMany(pack::class, 'id_product_pack', 'id_product');
    }

    public function manufacturer()
    {
        return $this->hasOne(manufacturers::class, 'id_manufacturer', 'id_manufacturer');
    }

    public function supplier()
    {
        return $this->hasOne(suppliers::class, 'id_supplier', 'id_supplier');
    }

    public function langs()
    {
        return $this->hasMany(product_lang::class, 'id_product', 'id_product');
    }

    public function attributes()
    {
        return $this->hasMany(product_attribute::class, 'id_product', 'id_product');
    }

    public function attribute()
    {
        return $this->hasMany(product_attribute::class, 'id_product', 'id_product');
    }

    public function stock()
    {
        return $this->hasOne(stock_available::class, 'id_product', 'id_product')
            ->where('id_product_attribute', 0);
    }

    public function discount()
    {
        return $this->hasOne(specific_price::class, 'id_product', 'id_product')
            ->where('id_product_attribute', 0);
    }

    public function sold()
    {
        return $this->hasMany(orders_details::class, 'product_id', 'id_product');
    }

    public function attachments()
    {
        return $this->hasMany(product_attachment::class, 'id_product', 'id_product');
    }

    public function images()
    {
        return $this->hasMany(image::class, 'id_product', 'id_product');
    }

    public function stock_product_pack()
    {
        return $this->hasOne(stock_available::class, 'id_product', 'id_product')
            ->when(isset($this->id_product_attribute), function ($query) {
                $query->where('id_product_attribute', $this->id_product_attribute);
            });
    }

    public function erp_invoiced()
    {
        return $this->hasOne(BilledOrderLine::class, 'product_id', 'id_product')
            ->selectRaw('product_id, SUM(qty_billed) as qty_wmfaturado')
            ->where(function ($query) {
                $query->whereNull('product_attribute_id')
                    ->orWhere('product_attribute_id', 0);
            })
            ->whereRaw('COALESCE(qty_billed, 0) > COALESCE(qty_received, 0)')
            ->groupBy('product_id')
            ->withDefault([
                'qty_wmfaturado' => 0
            ]);
    }

    public function erp_expected()
    {
        return $this->hasOne(BilledOrderLine::class, 'product_id', 'id_product')
            ->selectRaw('product_id, SUM(GREATEST(COALESCE(qty_billed, 0) - COALESCE(qty_received, 0), 0)) as qty_expected')
            ->where(function ($query) {
                $query->whereNull('product_attribute_id')
                    ->orWhere('product_attribute_id', 0);
            })
            ->whereRaw('COALESCE(qty_billed, 0) > COALESCE(qty_received, 0)')
            ->groupBy('product_id')
            ->withDefault([
                'qty_expected' => 0
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */
    
    protected static function adminProductLink(string $store = 'ASM'): array
    {
        return parent::adminProductLink($store);
    }

    protected static function excludedProductIds($board): array
    {
        return self::dashboardExceptions($board, 'id_product');
    }

    protected static function productDashboardResponse($name, $type, $suffix, $columns, $data, array $extra = [])
    {
        $store = $extra['store'] ?? 'ASM';
        unset($extra['store']);

        $data = collect($data)
            ->map(function ($row) use ($store) {
                $row = (array) $row;

                if (!empty($row['id_product']) && empty($row['url'])) {
                    $row['url'] = PrestashopAdminLinkService::dashboardProductAdminUrl((int) $row['id_product'], $store);
                }

                return $row;
            })
            ->values()
            ->all();

        return self::dashboardPanel(
            $name,
            $type,
            $suffix,
            $columns,
            $data,
            $extra,
            PrestashopAdminLinkService::dashboardProductLink('id_product', $store)
        );
    }

    protected static function baseProductWithBrandQuery()
    {
        $productTable = self::tableName('product');
        $manufacturerTable = self::tableName('manufacturer');

        return self::select(
                $productTable . '.id_product',
                $productTable . '.id_manufacturer',
                $productTable . '.reference',
                DB::raw($manufacturerTable . '.name as brand')
            )
            ->leftJoin($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer');
    }

    public static function getProductMeasure($id_product, $field)
    {
        return self::where('id_product', $id_product)->value($field);
    }

    public static function getProductMeasures($id_product, $reference)
    {
        $weight = self::getProductMeasure($id_product, 'weight');
        $width  = self::getProductMeasure($id_product, 'width');
        $height = self::getProductMeasure($id_product, 'height');
        $depth  = self::getProductMeasure($id_product, 'depth');

        $array_measures = [$width, $height, $depth];
        rsort($array_measures);

        $volumetric = $array_measures[0] + (2 * $array_measures[1]) + (2 * $array_measures[2]);

        $tr_color = '';
        if (($weight < 31.5) && ($volumetric < 300)) $tr_color = '';
        else if (($weight < 31.5) && ($volumetric < 305)) $tr_color = 'row_yellow';
        else if (($weight < 35) && ($volumetric < 300)) $tr_color = 'row_yellow';
        else if (($weight < 35) && ($volumetric < 305)) $tr_color = 'row_yellow';
        else if ($volumetric > 304) $tr_color = ' row_red ';
        else if ($weight > 34.99) $tr_color = ' row_red ';

        return [
            'tr_color' => $tr_color,
            'id_product' => $id_product,
            'reference' => $reference,
            'weight' => number_format($weight, 2, '.'),
            'width' => number_format($array_measures[0], 0, '.'),
            'height' => number_format($array_measures[1], 0, '.'),
            'depth' => number_format($array_measures[2], 0, '.'),
            'volumetric' => $volumetric
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARDS - SIMPLE FLAGS
    |--------------------------------------------------------------------------
    */

    public static function dashboard_ec_approved($type)
    {
        $data = [];
        $excluded = self::excludedProductIds('ec_approved');
        $productTable = self::tableName('product');

        $query = self::with('manufacturer')
            ->where('upc', 0)
            ->where('active', 1)
            ->where($productTable . '.visibility', '<>', 'none');

        if (!empty($excluded)) {
            $query->whereNotIn($productTable . '.id_product', $excluded);
        }

        $bd_data = $query->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'clean' => $item->id_product,
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->manufacturer->name ?? ''
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.EC approved'),
            $type,
            'ec_approved',
            ['clean', 'id_product', 'reference', 'brand'],
            $data,
            [
                'exception_fields' => ['ec_approved', 'id_product', 'reference', 'brand']
            ]
        );
    }

    public static function dashboard_universal_products($type)
    {
        $data = [];
        $excluded = self::excludedProductIds('universal_products');

        $productTable = self::tableName('product');
        $customProductTable = self::tableName('custom_product');

        $query = self::with('manufacturer')
            ->select($productTable . '.*')
            ->leftJoin($customProductTable, $productTable . '.id_product', '=', $customProductTable . '.id_product')
            ->where($customProductTable . '.universal', 1); 

        if (!empty($excluded)) {
            $query->whereNotIn($productTable . '.id_product', $excluded);
        }

        foreach ($query->get() as $item) {
            $data[] = [
                'clean' => $item->id_product,
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->manufacturer->name ?? ''
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.Universal'),
            $type,
            'universal_products',
            ['clean', 'id_product', 'reference', 'brand'],
            $data,
            [
                'exception_fields' => ['universal_products', 'id_product', 'reference', 'brand']
            ]
        );
    }

    public static function dashboard_shipping_restrictions($type)
    {
        $data = [];
        $excluded = self::excludedProductIds('shipping_restrictions');

        $productTable = self::tableName('product');
        $customProductTable = self::tableName('custom_product');

        $query = self::with('manufacturer')
            ->select($productTable . '.*')
            ->leftJoin($customProductTable, $productTable . '.id_product', '=', $customProductTable . '.id_product')
            ->where($customProductTable . '.shipping_restrictions', 1); 

        if (!empty($excluded)) {
            $query->whereNotIn($productTable . '.id_product', $excluded);
        }

        foreach ($query->get() as $item) {
            $data[] = [
                'clean' => $item->id_product,
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->manufacturer->name ?? ''
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.Shipping restrictions'),
            $type,
            'shipping_restrictions',
            ['clean', 'id_product', 'reference', 'brand'],
            $data,
            [
                'exception_fields' => ['shipping_restrictions', 'id_product', 'reference', 'brand'],
                'exception' => 'shipping_restrictions'
            ]
        );
    }

    public static function dashboard_compatibilities_exception($type)
    {
        $data = [];
        $excluded = self::excludedProductIds('compatibilities_exception');

        $productTable = self::tableName('product');
        $customProductTable = self::tableName('custom_product');

        $query = self::with('manufacturer')
            ->select($productTable . '.*')
            ->leftJoin($customProductTable, $productTable . '.id_product', '=', $customProductTable . '.id_product')
            ->where($customProductTable . '.show_compat_exception', 1); 

        if (!empty($excluded)) {
            $query->whereNotIn($productTable . '.id_product', $excluded);
        }

        foreach ($query->get() as $item) {
            $data[] = [
                'clean' => $item->id_product,
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->manufacturer->name ?? ''
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.Compatibilities exceptions'),
            $type,
            'compatibilities_exception',
            ['clean', 'id_product', 'reference', 'brand'],
            $data,
            [
                'exception_fields' => ['compatibilities_exception', 'id_product', 'reference', 'brand']
            ]
        );
    }

    public static function dashboard_without_brand($type)
    {
        $data = [];

        foreach (self::select('id_product', 'reference')->where('id_manufacturer', 0)->get() as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.Product without brand'),
            $type,
            'without_brand',
            ['id_product', 'reference'],
            $data
        );
    }

    public static function dashboard_without_category($type)
    {
        $data = [];

        foreach (self::with('manufacturer')->where('id_category_default', 0)->get() as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->manufacturer->name ?? ''
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.Product without category'),
            $type,
            'without_category',
            ['id_product', 'reference', 'brand'],
            $data
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARDS - COMPATIBILITIES / ATTACHMENTS
    |--------------------------------------------------------------------------
    */

    public static function dashboard_no_compatibilities($type, string $store = 'ASM', $panel = null)
    {
        $data = [];
        $excluded = self::excludedProductIds('no_compatibilities');
        $compatStoreId = (int) config('allstars.stores.' . $store . '.compat_store_id', 2);

        if (!Schema::hasTable('compats_product')) {
            return self::productDashboardResponse(
                trans('dashboard.No compatibilities'),
                $type,
                'no_compatibilities',
                ['clean', 'id_product', 'reference', 'brand'],
                $data,
                [
                    'exception_fields' => ['no_compatibilities', 'id_product', 'reference', 'brand']
                ]
            );
        }

        $compatProductIds = compats_product::where('store', $compatStoreId)
            ->pluck('id_product')
            ->toArray();

        $productTable = self::tableName('product');
        $customProductTable = self::tableName('custom_product');

        $query = self::with('manufacturer')
            ->select($productTable . '.*')
            ->leftJoin($customProductTable, $productTable . '.id_product', '=', $customProductTable . '.id_product')
            ->where($productTable . '.active', 1)
            ->where(function ($q) use ($customProductTable) {
                $q->whereNull($customProductTable . '.universal')
                  ->orWhere($customProductTable . '.universal', 0);
            })
            ->where($productTable . '.visibility', '<>', 'none')
            ->when(!empty($compatProductIds), function ($query) use ($productTable, $compatProductIds) {
                $query->whereNotIn($productTable . '.id_product', $compatProductIds);
            });

        if (!empty($excluded)) {
            $query->whereNotIn($productTable . '.id_product', $excluded);
        }

        foreach ($query->get() as $item) {
            $data[] = [
                'clean' => $item->id_product,
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->manufacturer->name ?? ''
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.No compatibilities'),
            $type,
            'no_compatibilities',
            ['clean', 'id_product', 'reference', 'brand'],
            $data,
            [
                'exception_fields' => ['no_compatibilities', 'id_product', 'reference', 'brand']
            ]
        );
    }

    public static function dashboard_no_instructions($type)
    {
        $data = [];
        $excluded = self::excludedProductIds('no_instructions');
        $attachmentIds = product_attachment::select('id_product')->groupBy('id_product')->pluck('id_product')->toArray();

        $productTable = self::tableName('product');
        $manufacturerTable = self::tableName('manufacturer');

        $query = self::select(
                $productTable . '.id_product',
                $productTable . '.reference'
            )
            ->with('manufacturer')
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->where($productTable . '.visibility', '<>', 'none')
            ->whereNotIn($productTable . '.id_product', $attachmentIds)
            ->whereNotIn($productTable . '.id_manufacturer', [104, 109, 127, 139, 153]);

        if (!empty($excluded)) {
            $query->whereNotIn($productTable . '.id_product', $excluded);
        }

        foreach ($query->get() as $item) {
            $data[] = [
                'clean' => $item->id_product,
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->manufacturer->name ?? ''
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.No instructions'),
            $type,
            'no_instructions',
            ['clean', 'id_product', 'reference', 'brand'],
            $data,
            [
                'exception_fields' => ['no_instructions', 'id_product', 'reference', 'brand']
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARDS - IMAGES / MEDIA
    |--------------------------------------------------------------------------
    */

    public static function dashboard_no_real_photos($type)
    {
        $data = [];
        $productTable = self::tableName('product');
        $stockTable = self::tableName('stock_available');
        $customProductTable = self::tableName('custom_product');

        $bd_data = self::select($productTable . '.id_product', $productTable . '.reference')
            ->with('manufacturer')
            ->join($stockTable, $productTable . '.id_product', '=', $stockTable . '.id_product')
            ->leftJoin($customProductTable, $productTable . '.id_product', '=', $customProductTable . '.id_product')
            ->where($stockTable . '.quantity', '>', 0)
            ->where(function ($q) use ($customProductTable) {
                $q->whereNull($customProductTable . '.real_photos')
                  ->orWhere($customProductTable . '.real_photos', 0);
            })
            ->where($productTable . '.visibility', '<>', 'none')
            ->orderBy($productTable . '.id_product')
            ->groupBy($productTable . '.id_product', $productTable . '.reference')
            ->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->manufacturer->name ?? ''
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.No real photos'),
            $type,
            'no_real_photos',
            ['id_product', 'reference', 'brand'],
            $data
        );
    }

    public static function dashboard_product_less_then_5_pics($type)
    {
        $productTable = self::tableName('product');
        $imageTable = self::tableName('image');
        $stockTable = self::tableName('stock_available');
        $manufacturerTable = self::tableName('manufacturer');
        $productShopTable = self::tableName('product_shop');

        $products = self::select(
                $productTable . '.id_product',
                $productTable . '.id_category_default',
                DB::raw('COUNT(' . $imageTable . '.id_image) AS nr_images'),
                $productTable . '.reference',
                DB::raw($productTable . '.location AS housing'),
                DB::raw($manufacturerTable . '.name AS brand')
            )
            ->leftJoin($imageTable, $productTable . '.id_product', '=', $imageTable . '.id_product')
            ->leftJoin($stockTable, $productTable . '.id_product', '=', $stockTable . '.id_product')
            ->leftJoin($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->join($productShopTable, function ($join) use ($productTable, $productShopTable) {
                $join->on($productShopTable . '.id_product', '=', $productTable . '.id_product')
                    ->where($productShopTable . '.id_shop', PrestashopAdminLinkService::shopId('ASM'))
                    ->where($productShopTable . '.active', 1);
            })
            ->where($productTable . '.visibility', '<>', 'none')
            ->where($productTable . '.id_category_default', '<>', 526)
            ->groupBy(
                $productTable . '.id_product',
                $productTable . '.id_category_default',
                $productTable . '.reference',
                $productTable . '.location',
                $manufacturerTable . '.name',
                $productShopTable . '.id_shop'
            )
            ->havingRaw('MAX(' . $stockTable . '.quantity) > 0')
            ->havingRaw('COUNT(' . $imageTable . '.id_image) < 5')
            ->orderByRaw('CAST(' . $productTable . '.id_product AS UNSIGNED) ASC')
            ->get()
            ->sortBy('id_product', SORT_NUMERIC)
            ->values()
            ->toArray();

        return self::productDashboardResponse(
            trans('dashboard.PRODUCTS - No 5 photos'),
            $type,
            'products_no_5_pics',
            ['id_product', 'reference', 'brand', 'housing', 'nr_images'],
            $products
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARDS - DATA QUALITY / LOGISTICS
    |--------------------------------------------------------------------------
    */

    public static function dashboard_no_ean($type)
    {
        $data = [];

        $productTable = self::tableName('product');
        $stockTable = self::tableName('stock_available');
        $manufacturerTable = self::tableName('manufacturer');
        $productAttributeTable = self::tableName('product_attribute');
        $customProductAttributeTable = self::tableName('custom_product_attribute');

        $bd_data_product = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($manufacturerTable . '.name AS brand')
            )
            ->join($stockTable, $productTable . '.id_product', '=', $stockTable . '.id_product')
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->where($productTable . '.ean13', '')
            ->where($stockTable . '.quantity', '>', 0)
            ->groupBy(
                $productTable . '.id_product',
                $productTable . '.reference',
                $manufacturerTable . '.name'
            )
            ->get();

        $bd_data_attribute = product_attribute::select(
                $productAttributeTable . '.id_product',
                $productAttributeTable . '.reference',
                DB::raw($manufacturerTable . '.name AS brand')
            )
            ->join($productTable, $productAttributeTable . '.id_product', '=', $productTable . '.id_product')
            ->join($stockTable, function ($join) use ($productAttributeTable, $stockTable) {
                $join->on($productAttributeTable . '.id_product_attribute', '=', $stockTable . '.id_product_attribute')
                    ->on($productAttributeTable . '.id_product', '=', $stockTable . '.id_product');
            })
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->where($productAttributeTable . '.ean13', '')
            ->where($stockTable . '.quantity', '>', 0)
            ->groupBy(
                $productAttributeTable . '.id_product',
                $productAttributeTable . '.reference',
                $manufacturerTable . '.name'
            )
            ->get();

        foreach ($bd_data_product as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->brand
            ];
        }

        foreach ($bd_data_attribute as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->brand
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.NO EAN'),
            $type,
            'no_ean',
            ['id_product', 'reference', 'brand'],
            $data
        );
    }

    public static function dashboard_no_housing($type)
    {
        $data = [];

        $productTable = self::tableName('product');
        $stockTable = self::tableName('stock_available');
        $manufacturerTable = self::tableName('manufacturer');
        $productAttributeTable = self::tableName('product_attribute');
        $customProductAttributeTable = self::tableName('custom_product_attribute');

        $bd_data_product = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($manufacturerTable . '.name AS brand')
            )
            ->join($stockTable, $productTable . '.id_product', '=', $stockTable . '.id_product')
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->where($productTable . '.location', '')
            ->where($productTable . '.active', 1)
            ->where($stockTable . '.quantity', '>', 0)
            ->whereNotExists(function ($query) use ($productTable, $productAttributeTable) {
                $query->select(DB::raw(1))
                    ->from($productAttributeTable)
                    ->whereColumn($productAttributeTable . '.id_product', $productTable . '.id_product');
            })
            ->groupBy(
                $productTable . '.id_product',
                $productTable . '.reference',
                $manufacturerTable . '.name'
            )
            ->get();

        $bd_data_attribute = product_attribute::select(
                $productAttributeTable . '.id_product',
                $productAttributeTable . '.reference',
                DB::raw($manufacturerTable . '.name AS brand')
            )
            ->join($productTable, $productAttributeTable . '.id_product', '=', $productTable . '.id_product')
            ->join($stockTable, $productAttributeTable . '.id_product_attribute', '=', $stockTable . '.id_product_attribute')
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->leftJoin($customProductAttributeTable, $productAttributeTable . '.id_product_attribute', '=', $customProductAttributeTable . '.id_product_attribute')
            ->where(function ($query) use ($customProductAttributeTable) {
                $query->whereNull($customProductAttributeTable . '.location')
                    ->orWhere($customProductAttributeTable . '.location', '');
            })
            ->where($productTable . '.active', 1)
            ->where($stockTable . '.quantity', '>', 0)
            ->groupBy(
                $productAttributeTable . '.id_product',
                $productAttributeTable . '.reference',
                $manufacturerTable . '.name'
            )
            ->get();

        foreach ($bd_data_product as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->brand
            ];
        }

        foreach ($bd_data_attribute as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->brand
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.NO HOUSING'),
            $type,
            'no_housing',
            ['id_product', 'reference', 'brand'],
            $data
        );
    }

    public static function dashboard_no_size($type)
    {
        $data = [];

        $productTable = self::tableName('product');
        $stockTable = self::tableName('stock_available');
        $manufacturerTable = self::tableName('manufacturer');
        $customProductTable = self::tableName('custom_product');

        $bd_data = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($manufacturerTable . '.name AS brand')
            )
            ->join($stockTable, $productTable . '.id_product', '=', $stockTable . '.id_product')
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->leftJoin($customProductTable, $productTable . '.id_product', '=', $customProductTable . '.id_product')
            ->where(function ($q) use ($customProductTable) {
                $q->whereNull($customProductTable . '.dim_verify')
                  ->orWhere($customProductTable . '.dim_verify', 0);
            })
            ->where($stockTable . '.quantity', '>', 0)
            ->where($productTable . '.active', 1)
            ->groupBy(
                $productTable . '.id_product',
                $productTable . '.reference',
                $manufacturerTable . '.name'
            )
            ->orderBy($productTable . '.id_manufacturer')
            ->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->brand
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.Measures unverified'),
            $type,
            'unverified_measures',
            ['id_product', 'reference', 'brand'],
            $data
        );
    }

    public static function dashboard_no_weight($type)
    {
        $data = [];

        $productTable = self::tableName('product');
        $manufacturerTable = self::tableName('manufacturer');

        $bd_data = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($manufacturerTable . '.name AS brand')
            )
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->where(function ($query) use ($productTable) {
                $query->whereNull($productTable . '.weight')
                    ->orWhere($productTable . '.weight', 0);
            })
            ->groupBy(
                $productTable . '.id_product',
                $productTable . '.reference',
                $manufacturerTable . '.name'
            )
            ->orderBy($productTable . '.id_manufacturer')
            ->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->brand
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.Weight 0'),
            $type,
            'no_weight',
            ['id_product', 'reference', 'brand'],
            $data
        );
    }

    public static function dashboard_qtd_arrive($type)
    {
        $data = [];

        $productTable = self::tableName('product');
        $manufacturerTable = self::tableName('manufacturer');
        $productAttributeTable = self::tableName('product_attribute');
        $customProductTable = self::tableName('custom_product');
        $customProductAttributeTable = self::tableName('custom_product_attribute');

        $bd_data = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($customProductTable . '.stock_arrive AS stock_arrive'),
                DB::raw($manufacturerTable . '.name AS brand')
            )
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->leftJoin($customProductTable, $productTable . '.id_product', '=', $customProductTable . '.id_product')
            ->where($customProductTable . '.stock_arrive', '<', 0)
            ->where($productTable . '.reference', 'not like', '%-Z')
            ->groupBy(
                $productTable . '.id_product',
                $productTable . '.reference',
                $customProductTable . '.stock_arrive',
                $manufacturerTable . '.name'
            )
            ->orderBy($productTable . '.id_manufacturer')
            ->get();

        $bd_data_attr = product_attribute::select(
                $productAttributeTable . '.id_product',
                $productAttributeTable . '.reference AS attr_reference',
                DB::raw($customProductAttributeTable . '.stock_arrive AS stock_arrivepa'),
                DB::raw($manufacturerTable . '.name AS brand')
            )
            ->join($productTable, $productAttributeTable . '.id_product', '=', $productTable . '.id_product')
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->leftJoin($customProductAttributeTable, $productAttributeTable . '.id_product_attribute', '=', $customProductAttributeTable . '.id_product_attribute')
            ->where($customProductAttributeTable . '.stock_arrive', '<', 0)
            ->where($productAttributeTable . '.reference', 'not like', '%-Z')
            ->groupBy(
                $productAttributeTable . '.id_product',
                $productAttributeTable . '.reference',
                $customProductAttributeTable . '.stock_arrive',
                $manufacturerTable . '.name'
            )
            ->orderBy($productTable . '.id_manufacturer')
            ->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->brand,
                'arrive' => $item->stock_arrive
            ];
        }

        foreach ($bd_data_attr as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->attr_reference,
                'brand' => $item->brand,
                'arrive' => $item->stock_arrivepa
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.Quantity arrive < 0'),
            $type,
            'qty_arrive',
            ['id_product', 'reference', 'brand', 'arrive'],
            $data
        );
    }

public static function dashboard_end_of_life($type)
{
    $data = [];

    $productTable = self::tableName('product');
    $productAttributeTable = self::tableName('product_attribute');
    $manufacturerTable = self::tableName('manufacturer');
    $stockTable = self::tableName('stock_available');
    $customProductTable = self::tableName('custom_product');
    $customProductAttributeTable = self::tableName('custom_product_attribute');

    $bd_data = self::select(
            $productTable . '.id_product',
            DB::raw($manufacturerTable . '.name AS name'),
            DB::raw($productTable . '.location AS housing'),
            $productTable . '.reference',
            DB::raw('MAX(' . $productAttributeTable . '.reference) AS refattr'),
            DB::raw('MAX(' . $customProductAttributeTable . '.location) AS housingattr'),
            DB::raw('SUM(COALESCE(' . $stockTable . '.quantity, 0)) AS quantity')
        )
        ->leftJoin($customProductTable, $productTable . '.id_product', '=', $customProductTable . '.id_product')
        ->leftJoin($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
        ->leftJoin($productAttributeTable, $productTable . '.id_product', '=', $productAttributeTable . '.id_product')
        ->leftJoin($customProductAttributeTable, function ($join) use ($productAttributeTable, $customProductAttributeTable) {
            $join->on(
                $productAttributeTable . '.id_product_attribute',
                '=',
                $customProductAttributeTable . '.id_product_attribute'
            );
        })
        ->leftJoin($stockTable, $productTable . '.id_product', '=', $stockTable . '.id_product')
        ->whereRaw('COALESCE(' . $customProductAttributeTable . '.wmdeprecated, ' . $customProductTable . '.wmdeprecated, 0) = 1')
        ->groupBy(
            $productTable . '.id_product',
            $manufacturerTable . '.name',
            $productTable . '.location',
            $productTable . '.reference'
        )
        ->orderBy('quantity')
        ->get();

    foreach ($bd_data as $item) {
        $reference = !empty($item->refattr) ? $item->refattr : $item->reference;
        $housing = !empty($item->housingattr) ? $item->housingattr : $item->housing;

        $data[] = [
            'id_product' => $item->id_product,
            'reference'  => $reference,
            'name'       => $item->name,
            'housing'    => $housing,
            'quantity'   => $item->quantity,
            'url'        => PrestashopAdminLinkService::dashboardProductAdminUrl($item->id_product, 'ASM'),
        ];
    }

    return self::productDashboardResponse(
        trans('dashboard.END OF LIFE PRODUCTS'),
        $type,
        'endoflife',
        ['id_product', 'housing', 'reference', 'quantity'],
        $data
    );
}   

    public static function dashboard_end_of_life_logistics($type)
    {
        $data = [];
        $excluded = self::excludedProductIds('endoflife_logistics');

        $productTable = self::tableName('product');
        $productAttributeTable = self::tableName('product_attribute');
        $manufacturerTable = self::tableName('manufacturer');
        $stockTable = self::tableName('stock_available');
        $customProductTable = self::tableName('custom_product');
        $customProductAttributeTable = self::tableName('custom_product_attribute');

        $query = self::select(
                $productTable . '.id_product',
                DB::raw($manufacturerTable . '.name AS name'),
                DB::raw($productTable . '.location AS housing'),
                $productTable . '.reference',
                DB::raw($productAttributeTable . '.reference AS refattr'),
                DB::raw($customProductAttributeTable . '.location AS housingattr'),
                $stockTable . '.quantity'
            )
            ->join($productAttributeTable, $productTable . '.id_product', '=', $productAttributeTable . '.id_product')
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->join($stockTable, $productTable . '.id_product', '=', $stockTable . '.id_product')
            ->leftJoin($customProductTable, $productTable . '.id_product', '=', $customProductTable . '.id_product')
            ->leftJoin($customProductAttributeTable, $productAttributeTable . '.id_product_attribute', '=', $customProductAttributeTable . '.id_product_attribute')
            ->whereRaw('COALESCE(' . $customProductAttributeTable . '.wmdeprecated, ' . $customProductTable . '.wmdeprecated, 0) = 1')
            ->where($productTable . '.active', 1)
            ->orderBy($stockTable . '.quantity');

        if (!empty($excluded)) {
            $query->whereNotIn($productTable . '.id_product', $excluded);
        }

        $bd_data = $query->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'clean' => $item->id_product,
                'id_product' => $item->id_product,
                'reference' => isset($item->refattr) ? $item->refattr : $item->reference,
                'name' => $item->name,
                'housing' => isset($item->housingattr) ? $item->housingattr : $item->housing,
                'quantity' => $item->quantity
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.END OF LIFE PRODUCTS label'),
            $type,
            'endoflife_logistics',
            ['clean', 'id_product', 'housing', 'reference', 'quantity'],
            $data,
            [
                'exception_fields' => ['endoflife_logistics', 'id_product', 'reference', 'name']
            ]
        );
    }

    public static function dashboard_hs_code($type)
    {
        $data = [];

        $productTable = self::tableName('product');
        $manufacturerTable = self::tableName('manufacturer');
        $customProductTable = self::tableName('custom_product');

        $bd_data = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($manufacturerTable . '.name AS brand')
            )
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->leftJoin($customProductTable, $productTable . '.id_product', '=', $customProductTable . '.id_product')
            ->where(function ($query) use ($customProductTable) {
                $query->whereNull($customProductTable . '.nc')
                    ->orWhere($customProductTable . '.nc', '')
                    ->orWhere($customProductTable . '.nc', '0');
            })
            ->orderBy($productTable . '.id_manufacturer')
            ->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->brand
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.WITHOUT HS CODE'),
            $type,
            'WITHOUT_HS_CODE',
            ['id_product', 'reference', 'brand'],
            $data
        );
    }

    public static function dashboard_visibility($type)
    {
        $data = [];
        $excluded = self::excludedProductIds('product_visibility');

        $productTable = self::tableName('product');
        $manufacturerTable = self::tableName('manufacturer');

        $query = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($manufacturerTable . '.name AS brand')
            )
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->where($productTable . '.visibility', '<>', 'both');

        if (!empty($excluded)) {
            $query->whereNotIn($productTable . '.id_product', $excluded);
        }

        $bd_data = $query->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'clean' => $item->id_product,
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->brand
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.Visibility'),
            $type,
            'product_visibility',
            ['clean', 'id_product', 'reference', 'brand'],
            $data,
            [
                'exception_fields' => ['product_visibility', 'id_product', 'reference', 'brand']
            ]
        );
    }

    public static function dashboard_end_of_life_without_stock($type)
    {
        $data = [];

        $productTable = self::tableName('product');
        $productAttributeTable = self::tableName('product_attribute');
        $stockTable = self::tableName('stock_available');
        $customProductTable = self::tableName('custom_product');
        $customProductAttributeTable = self::tableName('custom_product_attribute');
    
        $bd_data = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($productAttributeTable . '.reference AS attr_reference')
            )
            ->leftJoin($customProductTable, $productTable . '.id_product', '=', $customProductTable . '.id_product')
            ->leftJoin($productAttributeTable, $productTable . '.id_product', '=', $productAttributeTable . '.id_product')
            ->leftJoin($customProductAttributeTable, $productAttributeTable . '.id_product_attribute', '=', $customProductAttributeTable . '.id_product_attribute')
            ->leftJoin($stockTable, $productTable . '.id_product', '=', $stockTable . '.id_product')
            ->whereRaw('COALESCE(' . $customProductAttributeTable . '.wmdeprecated, ' . $customProductTable . '.wmdeprecated, 0) = 1')
            ->groupBy(
                $productTable . '.id_product',
                $productTable . '.reference',
                $productAttributeTable . '.reference',
                $stockTable . '.id_product',
                $customProductAttributeTable . '.wmdeprecated',
                $customProductTable . '.wmdeprecated'
            )
            ->orderBy($productTable . '.date_upd')
            ->havingRaw('SUM(' . $stockTable . '.quantity) < 1')
            ->get();

                        
        foreach ($bd_data as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference'  => is_null($item->attr_reference) ? $item->reference : $item->attr_reference,
                'url'        => PrestashopAdminLinkService::dashboardProductAdminUrl($item->id_product, 'ASM'),
            ];
        }
        
        return self::productDashboardResponse(
            trans('dashboard.End of life - Disabled'),
            $type,
            'end_of_life_without_stock',
            ['id_product', 'reference'],
            $data
        );
    }
    
    /*
    |--------------------------------------------------------------------------
    | DASHBOARDS - ADVANCED CHECKS
    |--------------------------------------------------------------------------
    */

    public static function dashboard_ean_with_spaces($type)
    {
        $data = [];

        $bd_prod = self::select('id_product', 'reference', 'ean13')
            ->where('active', 1)
            ->get();

        $bd_attr = product_attribute::select('id_product', 'reference', 'ean13')->get();

        foreach ($bd_prod as $item) {
            if (preg_match('/\s/', (string) $item->ean13)) {
                $data[] = [
                    'id_product' => $item->id_product,
                    'reference' => $item->reference,
                    'ean13' => $item->ean13
                ];
            }
        }

        foreach ($bd_attr as $item) {
            if (preg_match('/\s/', (string) $item->ean13)) {
                $data[] = [
                    'id_product' => $item->id_product,
                    'reference' => $item->reference,
                    'ean13' => $item->ean13
                ];
            }
        }

        return self::productDashboardResponse(
            trans('dashboard.EAN13 with spaces'),
            $type,
            'ean13_with_spaces',
            ['id_product', 'reference', 'ean13'],
            $data
        );
    }

    public static function dashboard_products_for_newsletter($type)
    {
        $data = [];
        $excluded = self::excludedProductIds('products_for_newsletter');

        $productTable = self::tableName('product');
        $manufacturerTable = self::tableName('manufacturer');

        $query = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($manufacturerTable . '.name as brand')
            )
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->where($productTable . '.active', 1)
            ->where($productTable . '.date_add', '>', date('Y-m-d', strtotime('-30 DAYS')));

        if (!empty($excluded)) {
            $query->whereNotIn($productTable . '.id_product', $excluded);
        }

        $bd_data = $query->get();

        foreach ($bd_data as $item) {
            $extra_actions = "
                <div style='text-align: center;'>
                    <button type='button' class='btn btn-success' onclick='setProductForNewsletter(" . $item->id_product . ", " . json_encode($item->reference) . " , 1)' >YES</button>
                    <button type='button' class='btn btn-danger' onclick='setProductForNewsletter(" . $item->id_product . ", " . json_encode($item->reference) . " , 0)' style='margin-left: 10px;'>NO</button>
                </div>
            ";

            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->brand,
                'extra_actions' => $extra_actions
            ];
        }

        return [
            'name' => trans('dashboard.Products for newsletter'),
            'col' => 4,
            'item_id' => $type . '_products_for_newsletter',
            'columns' => ['id_product', 'reference', 'extra_actions'],
            'exception_fields' => ['products_for_newsletter', 'id_product', 'reference', 'brand'],
            'counter' => count($data),
            'data' => $data
        ];
    }

    public static function dashboard_references_with_spaces($type)
    {
        $data = [];
        $excluded = self::excludedProductIds('references_with_spaces');

        $productTable = self::tableName('product');
        $manufacturerTable = self::tableName('manufacturer');
        $productAttributeTable = self::tableName('product_attribute');

        $bd_prod = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($manufacturerTable . '.name as brand')
            )
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->when(!empty($excluded), function ($query) use ($productTable, $excluded) {
                $query->whereNotIn($productTable . '.id_product', $excluded);
            })
            ->get();

        $bd_attr = product_attribute::select(
                $productTable . '.id_product',
                $productAttributeTable . '.reference',
                DB::raw($manufacturerTable . '.name as brand')
            )
            ->join($productTable, $productAttributeTable . '.id_product', '=', $productTable . '.id_product')
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->when(!empty($excluded), function ($query) use ($productTable, $excluded) {
                $query->whereNotIn($productTable . '.id_product', $excluded);
            })
            ->get();

        foreach ($bd_prod as $item) {
            if (preg_match('/[^A-Za-z0-9\/.-]/', (string) $item->reference)) {
                $data[] = [
                    'clean' => $item->id_product,
                    'id_product' => $item->id_product,
                    'reference' => $item->reference,
                    'brand' => $item->brand
                ];
            }
        }

        foreach ($bd_attr as $item) {
            if (preg_match('/[^A-Za-z0-9\/.-]/', (string) $item->reference)) {
                $data[] = [
                    'clean' => $item->id_product,
                    'id_product' => $item->id_product,
                    'reference' => $item->reference,
                    'brand' => $item->brand
                ];
            }
        }

        return self::productDashboardResponse(
            trans('dashboard.References with spaces'),
            $type,
            'references_with_spaces',
            ['clean', 'id_product', 'reference'],
            $data,
            [
                'exception_fields' => ['references_with_spaces', 'id_product', 'reference', 'brand']
            ]
        );
    }

    public static function dashboard_no_purchase_discount($type)
    {
        $data = [];

        $productTable = self::tableName('product');
        $manufacturerTable = self::tableName('manufacturer');
        $customProductTable = self::tableName('custom_product');

        $bd_data = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($manufacturerTable . '.name as brand')
            )
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->leftJoin($customProductTable, $productTable . '.id_product', '=', $customProductTable . '.id_product')
            ->where(function ($query) use ($customProductTable) {
                $query->whereNull($customProductTable . '.discount_percentage')
                    ->orWhere($customProductTable . '.discount_percentage', '<', 1);
            })
            ->orderBy($productTable . '.id_manufacturer', 'DESC')
            ->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->brand
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.No Purchase discount'),
            $type,
            'no_purchase_discount',
            ['id_product', 'reference', 'brand'],
            $data,
            [
                'exception_fields' => ['no_purchase_discount', 'id_product', 'reference', 'brand']
            ]
        );
    }

    public static function dashboard_on_clearence($type)
    {
        $data = [];

        $productTable = self::tableName('product');
        $manufacturerTable = self::tableName('manufacturer');
        $stockTable = self::tableName('stock_available');
        $categoryProductTable = self::tableName('category_product');

        $bd_data = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($manufacturerTable . '.name as brand'),
                $productTable . '.date_add',
                DB::raw($stockTable . '.quantity AS stock')
            )
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->join($stockTable, $productTable . '.id_product', '=', $stockTable . '.id_product')
            ->join($categoryProductTable, $productTable . '.id_product', '=', $categoryProductTable . '.id_product')
            ->where($categoryProductTable . '.id_category', 523)
            ->where($stockTable . '.quantity', '>', 0)
            ->groupBy(
                $productTable . '.id_product',
                $productTable . '.reference',
                $manufacturerTable . '.name',
                $productTable . '.date_add',
                $stockTable . '.quantity'
            )
            ->orderBy($productTable . '.date_upd', 'DESC')
            ->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->brand,
                'stock' => $item->stock
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.Products in clearence'),
            $type,
            'on_clearence',
            ['id_product', 'reference', 'brand', 'stock'],
            $data
        );
    }

    public static function dashboard_no_video($type)
    {
        $data = [];

        $productTable = self::tableName('product');
        $manufacturerTable = self::tableName('manufacturer');
        $customProductTable = self::tableName('custom_product');

        $bd_data = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($manufacturerTable . '.name as brand')
            )
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->leftJoin($customProductTable, $productTable . '.id_product', '=', $customProductTable . '.id_product')
            ->where($productTable . '.active', 1)
            ->where($productTable . '.visibility', '<>', 'none')
            ->where(function ($q) use ($customProductTable) {
                $q->whereNull($customProductTable . '.wmdeprecated')
                  ->orWhere($customProductTable . '.wmdeprecated', 0);
            })
            ->where(function ($q) use ($customProductTable) {
                $q->whereNull($customProductTable . '.youtube_1')
                  ->orWhere($customProductTable . '.youtube_1', '');
            })
            ->where(function ($q) use ($customProductTable) {
                $q->whereNull($customProductTable . '.youtube_2')
                  ->orWhere($customProductTable . '.youtube_2', '');
            })
            ->orderBy($productTable . '.id_product', 'DESC')
            ->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->brand
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.No video'),
            $type,
            'no_video',
            ['id_product', 'reference', 'brand'],
            $data
        );
    }

    public static function dashboard_no_purchase_price($type)
    {
        $rows = [];

        $productTable = self::tableName('product');
        $manufacturerTable = self::tableName('manufacturer');
        $productAttributeTable = self::tableName('product_attribute');

        $bd_data = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($manufacturerTable . '.name as brand')
            )
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->where($productTable . '.wholesale_price', 0)
            ->whereNotExists(function ($query) use ($productTable, $productAttributeTable) {
                $query->select(DB::raw(1))
                    ->from($productAttributeTable)
                    ->whereColumn($productAttributeTable . '.id_product', $productTable . '.id_product');
            })
            ->get();

        foreach ($bd_data as $item) {
            $idProduct = (int) $item->id_product;

            $rows[$idProduct] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->brand,
            ];
        }

        $bd_data_attr = product_attribute::select(
                $productTable . '.id_product',
                DB::raw($productTable . '.reference AS product_reference'),
                DB::raw($productAttributeTable . '.reference AS attribute_reference'),
                DB::raw($manufacturerTable . '.name as brand')
            )
            ->join($productTable, $productAttributeTable . '.id_product', '=', $productTable . '.id_product')
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->where($productAttributeTable . '.wholesale_price', 0)
            ->get();

        foreach ($bd_data_attr as $item) {
            $idProduct = (int) $item->id_product;

            if (!isset($rows[$idProduct])) {
                $rows[$idProduct] = [
                    'id_product' => $item->id_product,
                    'reference' => $item->product_reference ?: $item->attribute_reference,
                    'brand' => $item->brand,
                ];
            }
        }

        return self::productDashboardResponse(
            trans('dashboard.No wholesale price Set'),
            $type,
            'no_purchase_price',
            ['id_product', 'reference', 'brand'],
            array_values($rows)
        );
    }

    public static function dashboard_products_same_reference($type)
    {
        $data = [];
        $excluded = self::excludedProductIds('same_reference');

        $productTable = self::tableName('product');
        $manufacturerTable = self::tableName('manufacturer');

        $query = self::select(
                DB::raw('COUNT(*) AS repeated'),
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($manufacturerTable . '.name as brand')
            )
            ->leftJoin($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->where($productTable . '.active', 1)
            ->groupBy(
                $productTable . '.reference',
                $productTable . '.id_product',
                $manufacturerTable . '.name'
            )
            ->orderBy('brand', 'DESC');

        if (!empty($excluded)) {
            $query->whereNotIn($productTable . '.id_product', $excluded);
        }

        $bd_data = $query->get();

        foreach ($bd_data as $item) {
            if ($item->repeated > 1) {
                $data[] = [
                    'clean' => $item->id_product,
                    'id_product' => $item->id_product,
                    'reference' => $item->reference,
                    'brand' => $item->brand
                ];
            }
        }

        return self::productDashboardResponse(
            trans('dashboard.Products same reference'),
            $type,
            'same_reference',
            ['clean', 'id_product', 'reference'],
            $data,
            [
                'exception_fields' => ['same_reference', 'id_product', 'reference', 'brand']
            ]
        );
    }

    public static function dashboard_wholesale_price_exVAT($type)
    {
        $data = [];
        $productTable = self::tableName('product');
        $productShopTable = self::tableName('product_shop');

        $bd_data = self::query()
            ->join($productShopTable . ' as ps', function ($join) use ($productTable) {
                $join->on('ps.id_product', '=', $productTable . '.id_product')
                    ->where('ps.id_shop', PrestashopAdminLinkService::shopId('ASM'));
            })
            ->select($productTable . '.id_product', $productTable . '.reference', 'ps.wholesale_price', 'ps.price')
            ->whereColumn('ps.wholesale_price', '>', 'ps.price')
            ->groupBy($productTable . '.id_product', $productTable . '.reference', 'ps.wholesale_price', 'ps.price')
            ->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'wholesale_price' => $item->wholesale_price,
                'price' => $item->price
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.Wholesale > Price ( EX VAT )'),
            $type,
            'wholesale_price_exVAT',
            ['id_product', 'reference', 'wholesale_price', 'price'],
            $data
        );
    }

    public static function dashboard_recommended_products($type)
    {
        $data = [];

        $productTable = self::tableName('product');
        $manufacturerTable = self::tableName('manufacturer');
        $accessoryTable = self::tableName('accessory');
        $stockTable = self::tableName('stock_available');
        $customProductTable = self::tableName('custom_product');

        $bd_data = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($manufacturerTable . '.name AS brand'),
                $productTable . '.date_add'
            )
            ->leftJoin($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->leftJoin($customProductTable, $productTable . '.id_product', '=', $customProductTable . '.id_product')
            ->where($productTable . '.active', 1)
            ->where(function ($q) use ($customProductTable) {
                $q->whereNull($customProductTable . '.wmdeprecated')
                  ->orWhere($customProductTable . '.wmdeprecated', 0);
            })
            ->where($productTable . '.visibility', 'NOT LIKE', 'none')
            ->orderBy($productTable . '.date_add', 'ASC')
            ->get();

        foreach ($bd_data as $item) {
            $data_2 = accessory::select(
                    $productTable . '.id_product',
                    $productTable . '.active',
                    DB::raw($customProductTable . '.wmdeprecated AS wmdeprecated'),
                    $stockTable . '.quantity'
                )
                ->leftJoin($productTable, $accessoryTable . '.id_product_2', '=', $productTable . '.id_product')
                ->leftJoin($customProductTable, $productTable . '.id_product', '=', $customProductTable . '.id_product')
                ->leftJoin($stockTable, $productTable . '.id_product', '=', $stockTable . '.id_product')
                ->where($accessoryTable . '.id_product_1', $item->id_product)
                ->get();

            $count = 0;

            foreach ($data_2 as $item_2) {
                if ((($item_2->active) && ($item_2->wmdeprecated == 1) && ($item_2->quantity > 0)) || (($item_2->active) && ($item_2->wmdeprecated == 0))) {
                    $count++;
                }
            }

            if ($count < 4) {
                $data[] = [
                    'id_product' => $item->id_product,
                    'reference' => $item->reference,
                    'brand' => $item->brand
                ];
            }
        }

        return self::productDashboardResponse(
            trans('dashboard.RECOMMENDED PRODUCTS MISSING'),
            $type,
            'recommended_products',
            ['id_product', 'reference', 'brand'],
            $data
        );
    }

    public static function dashboard_packs($type)
    {
        $data = [];

        $productTable = self::tableName('product');
        $manufacturerTable = self::tableName('manufacturer');

        $bd_data = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($manufacturerTable . '.name AS brand')
            )
            ->leftJoin($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->where($productTable . '.cache_is_pack', 1)
            ->groupBy(
                $productTable . '.reference',
                $productTable . '.id_product',
                $manufacturerTable . '.name'
            )
            ->orderBy('brand', 'ASC')
            ->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->brand
            ];
        }

        return self::productDashboardResponse(
            trans("dashboard.Products's Pack"),
            $type,
            'packs',
            ['id_product', 'reference', 'brand'],
            $data,
            [
                'info' => true
            ]
        );
    }

    public static function dashboard_same_ean_diff_ref($type)
    {
        $data = [];
        $products = [];
        $exceptions = [];
        $productTable = self::tableName('product');

        $array = asm_dashboard::getExceptions('same_reference_diff_ean');

        foreach ($array as $product) {
            $exceptions[] = $product->id_product;
        }

        $product_ean13 = self::select(DB::raw('count(*) AS repeated_ean'), 'ean13')
            ->whereNotIn('id_product', $exceptions)
            ->groupBy('ean13')
            ->pluck('repeated_ean', 'ean13');

        $product_reference = self::select(DB::raw('count(*) AS repeated_ref'), 'ean13')
            ->whereNotIn('id_product', $exceptions)
            ->groupBy($productTable . '.reference')
            ->pluck('repeated_ref', 'ean13');

        foreach ($product_ean13 as $key => $counter) {
            if (isset($product_reference[$key]) && ($product_reference[$key] != $product_ean13[$key]) && ($key != '')) {
                $refs = self::select('reference')->distinct()->where('ean13', '=', $key)->get();
                $str_refs = '';

                foreach ($refs as $ref) {
                    $str_refs .= $ref->reference . ' | ';
                }

                $products[$key] = substr($str_refs, 0, -3);
            }
        }

        $attr_ean13 = product_attribute::select(DB::raw('count(*) AS repeated_ean'), 'ean13')
            ->whereNotIn('id_product', $exceptions)
            ->groupBy('ean13')
            ->pluck('repeated_ean', 'ean13');

        $attr_reference = product_attribute::select(DB::raw('count(*) AS repeated_ref'), 'ean13')
            ->whereNotIn('id_product', $exceptions)
            ->groupBy('reference')
            ->pluck('repeated_ref', 'ean13');

        foreach ($attr_ean13 as $key => $counter) {
            if (isset($attr_reference[$key]) && ($attr_reference[$key] != $attr_ean13[$key]) && ($key != '')) {
                $refs = product_attribute::select('reference')->distinct()->where('ean13', '=', $key)->get();
                $str_refs = '';

                foreach ($refs as $ref) {
                    $str_refs .= $ref->reference . ' | ';
                }

                $products[$key] = substr($str_refs, 0, -3);
            }
        }

        foreach ($products as $key => $item) {
            $reference = explode(' | ', $item)[0];

            $id_product = product_attribute::select('id_product')
                ->where('reference', $reference)
                ->whereNotIn('id_product', $exceptions)
                ->groupBy('reference', 'id_product')
                ->value('id_product') + 0;

            if ($id_product == 0) {
                $id_product = self::select('id_product')
                    ->where('reference', $reference)
                    ->whereNotIn('id_product', $exceptions)
                    ->groupBy('reference', 'id_product')
                    ->value('id_product');
            }

            if ($id_product > 0) {
                $data[] = [
                    'clean' => $id_product,
                    'id_product' => $id_product,
                    'reference' => $item,
                    'ean' => $key,
                    'brand' => 'none'
                ];
            }
        }

        return [
            'name' => trans('dashboard.Products same reference differente EAN13'),
            'col' => 4,
            'item_id' => $type . '_same_reference_diff_ean',
            'columns' => ['clean', 'reference', 'ean'],
            'exception_fields' => ['same_reference_diff_ean', 'id_product', 'reference', 'brand'],
            'counter' => count($data),
            'data' => $data
        ];
    }

    public static function dashboard_same_sku_diff_measures($type)
    {
        $data = [];
        $excluded = self::excludedProductIds('same_sku_diff_measures');

        $productTable = self::tableName('product');
        $manufacturerTable = self::tableName('manufacturer');

        $query = self::select(
                DB::raw('MIN(' . $productTable . '.id_product) as id_product'),
                $productTable . '.reference',
                DB::raw($manufacturerTable . '.name as brand'),
                DB::raw('COUNT(reference) AS counter_reference'),
                DB::raw('COUNT(DISTINCT COALESCE(width, -1)) AS distinct_width'),
                DB::raw('COUNT(DISTINCT COALESCE(height, -1)) AS distinct_height'),
                DB::raw('COUNT(DISTINCT COALESCE(depth, -1)) AS distinct_depth'),
                DB::raw('COUNT(DISTINCT COALESCE(weight, -1)) AS distinct_weight')
            )
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->groupBy(
                $productTable . '.reference',
                $manufacturerTable . '.name'
            );

        if (!empty($excluded)) {
            $query->whereNotIn($productTable . '.id_product', $excluded);
        }

        $bd = $query->get();

        foreach ($bd as $item) {
            if (($item->distinct_width > 1) || ($item->distinct_height > 1) || ($item->distinct_depth > 1) || ($item->distinct_weight > 1)) {
                $data[] = [
                    'clean' => $item->id_product,
                    'id_product' => $item->id_product,
                    'reference' => $item->reference,
                    'brand' => $item->brand
                ];
            }
        }

        return self::productDashboardResponse(
            trans('dashboard.SAME SKU DIFFERENT MEASUREMENTS'),
            $type,
            'references_with_spaces',
            ['id_product', 'reference', 'brand'],
            $data,
            [
                'exception_fields' => ['same_sku_diff_measures', 'id_product', 'reference', 'brand']
            ]
        );
    }

    public static function dashboard_in_stock_not_sold($type)
    {
        $date = Carbon::now('UTC')->subYear()->toDateTimeString();
        $allProducts = [];
    
        $productTable = self::tableName('product');
        $stockTable = self::tableName('stock_available');
        $manufacturerTable = self::tableName('manufacturer');
        $categoryProductTable = self::tableName('category_product');
        $ordersTable = self::tableName('orders');
        $orderDetailTable = self::tableName('order_detail');
        $productAttributeTable = self::tableName('product_attribute');
    
        $products = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($stockTable . '.quantity AS stock'),
                DB::raw($manufacturerTable . '.name AS manufacturer')
            )
            ->leftJoin($stockTable, function ($join) use ($productTable, $stockTable) {
                $join->on($productTable . '.id_product', '=', $stockTable . '.id_product')
                    ->where($stockTable . '.id_product_attribute', '=', 0);
            })
            ->leftJoin($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->leftJoin($categoryProductTable . ' as cp', $productTable . '.id_product', '=', 'cp.id_product')
            ->where($productTable . '.active', 1)
            ->where($stockTable . '.quantity', '>', 0)
            ->where($productTable . '.reference', 'NOT LIKE', '%-Z')
            ->whereNotIn('cp.id_category', [524, 525, 526])
            ->distinct()
            ->get();
    
        foreach ($products as $item) {
            $allProducts[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'stock' => $item->stock,
                'manufacturer' => $item->manufacturer,
            ];
        }
    
        $attributes = product_attribute::select(
                $productTable . '.id_product',
                $productAttributeTable . '.reference',
                DB::raw($stockTable . '.quantity AS stock'),
                DB::raw($manufacturerTable . '.name AS manufacturer')
            )
            ->leftJoin($stockTable, $productAttributeTable . '.id_product_attribute', '=', $stockTable . '.id_product_attribute')
            ->leftJoin($productTable, $productAttributeTable . '.id_product', '=', $productTable . '.id_product')
            ->leftJoin($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->leftJoin($categoryProductTable . ' as cp', $productTable . '.id_product', '=', 'cp.id_product')
            ->where($productTable . '.active', 1)
            ->where($productTable . '.date_add', '<', $date)
            ->where($stockTable . '.quantity', '>', 0)
            ->whereNotIn('cp.id_category', [524, 525, 526])
            ->distinct()
            ->get();
    
        foreach ($attributes as $item) {
            $allProducts[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'stock' => $item->stock,
                'manufacturer' => $item->manufacturer,
            ];
        }
    
        $soldRefs = orders_details::join($ordersTable . ' as o', 'o.id_order', '=', $orderDetailTable . '.id_order')
            ->pluck($orderDetailTable . '.product_reference')
            ->unique()
            ->toArray();
    
        $data = collect($allProducts)
            ->reject(fn ($item) => in_array($item['reference'], $soldRefs, true))
            ->sortBy('manufacturer')
            ->values()
            ->map(fn ($item) => [
                'id_product' => $item['id_product'],
                'manufacturer' => $item['manufacturer'],
                'reference' => $item['reference'],
                'stock' => $item['stock'],
                'url' => \App\Services\Prestashop\PrestashopAdminLinkService::dashboardProductAdminUrl((int) $item['id_product'], 'ASM'),
            ]);
    
        return self::dashboardPanel(
            trans('dashboard.PRODUCT IN STOCK BUT NOT SOLD FOR 1 YEAR'),
            $type,
            'in_stock_not_sold',
            ['id_product', 'manufacturer', 'reference', 'stock'],
            $data,
            [],
            \App\Services\Prestashop\PrestashopAdminLinkService::dashboardProductLink('id_product', 'ASM')
        );
    }

    public static function dashboard_avs_on_erp($type)
    {
        $data = [];
        $avs = [];

        $productTable = self::tableName('product');
        $customProductTable = self::tableName('custom_product');

        if (!Schema::hasTable('oms_billed_orders') || !Schema::hasTable('oms_billed_order_lines')) {
            return self::productDashboardResponse(
                trans('dashboard.PRODUCT AVS BUT ORDERED IN ERP'),
                $type,
                'avs_on_erp',
                ['id_product', 'reference', 'brand'],
                $data,
                [
                    'exception_fields' => ['same_sku_diff_measures', 'id_product', 'reference', 'brand']
                ]
            );
        }

        $products = self::select($productTable . '.reference')
            ->leftJoin($customProductTable, $productTable . '.id_product', '=', $customProductTable . '.id_product')
            ->where($customProductTable . '.wmdeprecated', 1)
            ->groupBy('reference')
            ->get();

        foreach ($products as $product) {
            $avs[] = $product->reference;
        }

        $references_ordered = DB::table('oms_billed_order_lines as bol')
            ->join('oms_billed_orders as bo', 'bo.id', '=', 'bol.billed_order_id')
            ->leftJoin(DB::raw(self::tableName('product') . ' as p'), function ($join) {
                $join->on('p.id_product', '=', 'bol.product_id')
                    ->where(function ($query) {
                        $query->whereNull('bol.product_attribute_id')
                            ->orWhere('bol.product_attribute_id', 0);
                    });
            })
            ->leftJoin(DB::raw(self::tableName('product_attribute') . ' as pa'), 'pa.id_product_attribute', '=', 'bol.product_attribute_id')
            ->where(function ($query) {
                $query->whereNull('bo.status')
                    ->orWhereNotIn('bo.status', ['cancelled', 'closed']);
            })
            ->selectRaw('COALESCE(pa.reference, p.reference) as reference')
            ->pluck('reference')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $result = array_intersect($references_ordered, $avs);

        $manufacturerTable = self::tableName('manufacturer');
        $productAttributeTable = self::tableName('product_attribute');

        $bd_data = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($manufacturerTable . '.name as brand')
            )
            ->leftJoin($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->leftJoin($productAttributeTable, $productTable . '.id_product', '=', $productAttributeTable . '.id_product')
            ->whereIn($productTable . '.reference', $result)
            ->groupBy(
                $productTable . '.id_product',
                $productTable . '.reference',
                $manufacturerTable . '.name'
            )
            ->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'clean' => $item->id_product,
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->brand
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.PRODUCT AVS BUT ORDERED IN ERP'),
            $type,
            'avs_on_erp',
            ['id_product', 'reference', 'brand'],
            $data,
            [
                'exception_fields' => ['same_sku_diff_measures', 'id_product', 'reference', 'brand']
            ]
        );
    }

    public static function dashboard_non_standard($type)
    {
        $data = [];
        $productTable = self::tableName('product');
        $stockTable = self::tableName('stock_available');

        $products = self::select($productTable . '.id_product', $productTable . '.reference')
            ->leftJoin($stockTable, $stockTable . '.id_product', '=', $productTable . '.id_product')
            ->where($stockTable . '.quantity', '>', 0)
            ->groupBy($stockTable . '.id_product', $productTable . '.id_product', $productTable . '.reference')
            ->get();

        foreach ($products as $product) {
            $data_arr = self::getProductMeasures($product->id_product, $product->reference);

            if ($data_arr['volumetric'] > 299) {
                $data[] = $data_arr;
            } elseif ($data_arr['weight'] > 31.49) {
                $data[] = $data_arr;
            }
        }

        $sorted = collect($data)->sortByDesc([
            ['volumetric', 'DESC'],
            ['weight', 'DESC']
        ]);

        return [
            'name' => trans('dashboard.PRODUCT non standard'),
            'col' => 4,
            'item_id' => $type . '_non_standard',
            'prestashop' => self::adminProductLink(),
            'columns' => ['reference', 'weight', 'width', 'height', 'depth', 'volumetric'],
            'counter' => count($sorted),
            'data' => $sorted,
        ];
    }
    
    /** TESTED AND WORKING WITH PS9 AND CUSTOM **/
    public static function dashboard_locked_products_with_stock($type){
        $data = [];
    
        $productTable = self::tableName('product');
        $stockTable = self::tableName('stock_available');
        $productAttributeTable = self::tableName('product_attribute');
        $customProductTable = self::tableName('custom_product');
        $manufacturerTable = self::tableName('manufacturer');
    
        $bd_data = self::select(
                $productTable . '.id_product',
                $productTable . '.id_manufacturer',
                DB::raw($productTable . '.reference AS prod_ref'),
                DB::raw('MAX(' . $productAttributeTable . '.reference) AS attr_ref'),
                DB::raw('MAX(' . $stockTable . '.quantity) AS total_stock'),
                DB::raw('COUNT(DISTINCT ' . $productAttributeTable . '.id_product_attribute) AS combinations_count'),
                DB::raw($manufacturerTable . '.name AS brand_name')
            )
            ->join($stockTable, $productTable . '.id_product', '=', $stockTable . '.id_product')
     
            ->leftJoin($productAttributeTable, function ($join) use ($productAttributeTable, $stockTable) {
                $join->on($productAttributeTable . '.id_product_attribute', '=', $stockTable . '.id_product_attribute');
            })
    
            ->leftJoin($customProductTable, $productTable . '.id_product', '=', $customProductTable . '.id_product')
    
            ->leftJoin($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
    
            ->where($productTable . '.visibility', '<>', 'none')
            ->where($productTable . '.active', 1)
    
            ->where($stockTable . '.out_of_stock', 0)
            ->where($stockTable . '.quantity', '>', 0)
    
            ->where(function ($q) use ($customProductTable) {
                $q->whereNull($customProductTable . '.wmdeprecated')
                  ->orWhere($customProductTable . '.wmdeprecated', 0);
            })
    
            ->groupBy(
                $productTable . '.id_product',
                $productTable . '.id_manufacturer',
                $productTable . '.reference',
                $manufacturerTable . '.name'
            )
    
            ->orderBy($productTable . '.id_manufacturer')
            ->get();
    
        foreach ($bd_data as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->prod_ref ?: $item->attr_ref,
                'brand' => $item->brand_name ?? '',
                'stock' => (int) $item->total_stock,
            ];
        }
    
        return self::productDashboardResponse(
            trans('dashboard.Locked with stock'),
            $type,
            'locked_products_with_stock',
            ['id_product', 'reference', 'brand', 'stock'],
            $data
        );
    }

    public static function dashboard_same_sku_diff_stock($type)
    {
        $data = [];
        $excluded = asm_dashboard::getExceptions('same_sku_diff_stock')
            ->pluck('id_product')
            ->toArray();

        $productTable = self::tableName('product');
        $stockTable = self::tableName('stock_available');
        $productAttributeTable = self::tableName('product_attribute');

        $productStockRows = self::join($stockTable, $productTable . '.id_product', '=', $stockTable . '.id_product')
            ->where($productTable . '.active', 1)
            ->where($stockTable . '.id_product_attribute', 0)
            ->select(
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw('MAX(' . $stockTable . '.quantity) AS quantity')
            )
            ->orderBy($productTable . '.reference')
            ->groupBy(
                $productTable . '.id_product',
                $productTable . '.reference'
            )
            ->get();

        $refsProd = $productStockRows
            ->groupBy('reference')
            ->filter(function ($items) {
                return $items->pluck('id_product')->unique()->count() > 1
                    && $items->min('quantity') != $items->max('quantity');
            })
            ->keys();

        $bd_prod = $productStockRows
            ->whereIn('reference', $refsProd)
            ->when(!empty($excluded), function ($items) use ($excluded) {
                return $items->whereNotIn('id_product', $excluded);
            });

        $attributeStockRows = product_attribute::join($productTable, $productAttributeTable . '.id_product', '=', $productTable . '.id_product')
            ->join($stockTable, $productAttributeTable . '.id_product_attribute', '=', $stockTable . '.id_product_attribute')
            ->where($stockTable . '.id_product_attribute', '>', 0)
            ->select(
                $productTable . '.id_product',
                $productAttributeTable . '.id_product_attribute',
                $productAttributeTable . '.reference',
                DB::raw('MAX(' . $stockTable . '.quantity) AS quantity')
            )
            ->orderBy($productAttributeTable . '.reference')
            ->groupBy(
                $productTable . '.id_product',
                $productAttributeTable . '.id_product_attribute',
                $productAttributeTable . '.reference'
            )
            ->get();

        $refsAttr = $attributeStockRows
            ->groupBy('reference')
            ->filter(function ($items) {
                return $items->pluck('id_product_attribute')->unique()->count() > 1
                    && $items->min('quantity') != $items->max('quantity');
            })
            ->keys();

        $bd_attr = $attributeStockRows
            ->whereIn('reference', $refsAttr)
            ->when(!empty($excluded), function ($items) use ($excluded) {
                return $items->whereNotIn('id_product', $excluded);
            });

        foreach ($bd_prod as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference
            ];
        }

        foreach ($bd_attr as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference
            ];
        }

        return self::productDashboardResponse(
            trans('dashboard.SAME SKU DIFFERENT STOCK'),
            $type,
            'same_sku_diff_stock',
            ['id_product', 'reference'],
            $data
        );
    }
    
}
