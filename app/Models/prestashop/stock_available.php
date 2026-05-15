<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class stock_available extends PrestashopModel
{
    use HasFactory;

    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('stock_available');
    }

    public function product()
    {
        return $this->hasOne(product::class, 'id_product', 'id_product');
    }

    public function product_attribute()
    {
        return $this->hasOne(product_attribute::class, 'id_product_attribute', 'id_product_attribute');
    }

    public static function getStock($id_product, $id_product_attribute = 0)
    {
        return self::where('id_product', $id_product)
            ->where('id_product_attribute', $id_product_attribute)
            ->value('quantity');
    }

    public static function counterNegativeStock()
    {
        $stockTable = self::tableName('stock_available');
        $productTable = self::tableName('product');

        $data = DB::connection('mysql2')
            ->table($stockTable)
            ->join($productTable, $stockTable . '.id_product', '=', $productTable . '.id_product')
            ->select(
                $productTable . '.reference',
                $stockTable . '.quantity'
            )
            ->where($stockTable . '.quantity', '<', 0)
            ->orderBy($productTable . '.reference')
            ->groupBy(
                $productTable . '.reference',
                $stockTable . '.quantity'
            )
            ->get()
            ->toArray();

        return [
            'col' => 2,
            'item_id' => 'counter_negativeStock',
            'name' => trans('dashboard.Negative stock'),
            'counter' => count($data),
            'columns' => [trans('tags.reference'), trans('tags.quantity')],
            'data' => $data
        ];
    }

    public static function dashboard_negative_stock($type)
    {
        $data = [];

        $stockTable = self::tableName('stock_available');
        $productTable = self::tableName('product');
        $productAttributeTable = self::tableName('product_attribute');

        $bd_data_product = self::select(
                $productTable . '.reference',
                $stockTable . '.quantity',
                $stockTable . '.id_product'
            )
            ->join($productTable, $stockTable . '.id_product', '=', $productTable . '.id_product')
            ->where($stockTable . '.id_product_attribute', 0)
            ->where($stockTable . '.quantity', '<', 0)
            ->where($productTable . '.reference', 'NOT LIKE', '%-Z')
            ->orderBy($stockTable . '.quantity', 'ASC')
            ->groupBy(
                $productTable . '.reference',
                $stockTable . '.quantity',
                $stockTable . '.id_product'
            )
            ->get();

        foreach ($bd_data_product as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'quantity' => $item->quantity
            ];
        }

        $bd_data_attr = self::select(
                $stockTable . '.quantity',
                $stockTable . '.id_product',
                $productAttributeTable . '.reference'
            )
            ->join($productAttributeTable, $stockTable . '.id_product_attribute', '=', $productAttributeTable . '.id_product_attribute')
            ->where($stockTable . '.quantity', '<', 0)
            ->where($stockTable . '.id_product_attribute', '<>', 0)
            ->orderBy($stockTable . '.quantity', 'ASC')
            ->groupBy(
                $productAttributeTable . '.reference',
                $stockTable . '.quantity',
                $stockTable . '.id_product'
            )
            ->get();

        foreach ($bd_data_attr as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'quantity' => $item->quantity
            ];
        }

        return [
            'name' => trans('dashboard.Negative stock'),
            'col' => 4,
            'item_id' => $type . '_negative_stock',
            'prestashop' => (isset(Config::get('token')->AdminProducts))
                ? [
                    'token' => Config::get('token')->AdminProducts,
                    'controller' => 'AdminProducts',
                    'element' => 'id_product',
                    'extraParameters' => '&updateproduct'
                ]
                : [],
            'columns' => ['id_product', 'reference', 'quantity'],
            'counter' => count($data),
            'data' => $data
        ];
    }

    public static function dashboard_out_of_stock($type)
    {
        $data = [];

        $stockTable = self::tableName('stock_available');
        $productTable = self::tableName('product');
        $customProductTable = self::tableName('custom_product');

        $bd_data = self::select(
                $stockTable . '.id_product',
                $stockTable . '.id_product_attribute',
                $stockTable . '.quantity'
            )
            ->with('product.manufacturer', 'product_attribute')
            ->join($productTable, $stockTable . '.id_product', '=', $productTable . '.id_product')
            ->leftJoin($customProductTable, $productTable . '.id_product', '=', $customProductTable . '.id_product')
            ->where($stockTable . '.quantity', '<', 1)
            ->where(function ($query) use ($customProductTable) {
                $query->whereNull($customProductTable . '.wmdeprecated')
                    ->orWhere($customProductTable . '.wmdeprecated', 0);
            })
            ->get();

        foreach ($bd_data as $item) {
            if (isset($item->product)) {
                $attr_reference = '';
                $prod_reference = $item->product->reference;

                if (isset($item->product_attribute)) {
                    $attr_reference = $item->product_attribute->reference;
                }

                if ($item->product->location != 'ZZ-ZZ-ZZ') {
                    $data[] = [
                        'id_product' => $item->id_product,
                        'reference' => strlen($attr_reference) ? $attr_reference : $prod_reference,
                        'quantity' => $item->quantity
                    ];
                }
            }
        }

        return [
            'name' => trans('dashboard.Out of stock'),
            'col' => 4,
            'item_id' => $type . '_out_of_stock',
            'prestashop' => (isset(Config::get('token')->AdminProducts))
                ? [
                    'token' => Config::get('token')->AdminProducts,
                    'controller' => 'AdminProducts',
                    'element' => 'id_product',
                    'extraParameters' => '&updateproduct'
                ]
                : [],
            'columns' => ['id_product', 'reference', 'quantity'],
            'counter' => count($data),
            'data' => $data
        ];
    }

    public static function dashboard_no_sales($type)
    {
        $data = [];

        $stockTable = self::tableName('stock_available');
        $productTable = self::tableName('product');
        $productAttributeTable = self::tableName('product_attribute');
        $manufacturerTable = self::tableName('manufacturer');

        $bd_data = self::select(
                $stockTable . '.id_product',
                $stockTable . '.id_product_attribute',
                $productTable . '.reference',
                $productTable . '.cache_is_pack',
                DB::raw($productAttributeTable . '.reference AS attr_reference'),
                DB::raw($manufacturerTable . '.name AS brand'),
                $stockTable . '.quantity'
            )
            ->leftJoin($productTable, $stockTable . '.id_product', '=', $productTable . '.id_product')
            ->leftJoin($productAttributeTable, $stockTable . '.id_product_attribute', '=', $productAttributeTable . '.id_product_attribute')
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->where($stockTable . '.out_of_stock', '=', 0)
            ->where($productTable . '.visibility', '!=', 'none')
            ->whereNotIn($productTable . '.id_manufacturer', [27, 47, 51, 76, 86, 89, 91, 115, 116, 120, 126, 127, 133, 135, 136, 140, 144, 151, 152, 156, 159, 162, 164, 170, 171, 172, 187])
            ->orderBy($stockTable . '.quantity', 'ASC')
            ->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => isset($item->attr_reference) && !is_null($item->attr_reference) ? $item->attr_reference : $item->reference,
                'quantity' => $item->quantity,
                'brand' => $item->brand
            ];
        }

        return [
            'name' => trans('dashboard.No Sales'),
            'col' => 4,
            'item_id' => $type . '_no_sales',
            'prestashop' => (isset(Config::get('token')->AdminProducts))
                ? [
                    'token' => Config::get('token')->AdminProducts,
                    'controller' => 'AdminProducts',
                    'element' => 'id_product',
                    'extraParameters' => '&updateproduct'
                ]
                : [],
            'columns' => ['id_product', 'quantity', 'reference', 'brand'],
            'counter' => count($data),
            'data' => $data
        ];
    }
}
