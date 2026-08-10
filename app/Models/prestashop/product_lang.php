<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\Prestashop\PrestashopAdminLinkService;

class product_lang extends PrestashopModel
{
    use HasFactory;

    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('product_lang');
    }

    public function product()
    {
        return $this->belongsTo(product::class, 'id_product', 'id_product');
    }

    public function language()
    {
        return $this->belongsTo(language::class, 'id_lang', 'id_lang');
    }

    public static function getProductName($id_product, $id_lang)
    {
        return self::where('id_product', $id_product)
            ->where('id_lang', $id_lang)
            ->value('name');
    }
    
    public static function dashboard_no_availability_text($type)
    {
        $data = [];
        $shopId = PrestashopAdminLinkService::shopId('ASM');

        $productLangTable = self::tableName('product_lang');
        $productTable = self::tableName('product');
        $productShopTable = self::tableName('product_shop');

        $bd_data = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                $productLangTable . '.name'
            )
            ->leftJoin($productTable, $productLangTable . '.id_product', '=', $productTable . '.id_product')
            ->join($productShopTable, function ($join) use ($productLangTable, $productShopTable) {
                $join->on($productShopTable . '.id_product', '=', $productLangTable . '.id_product')
                    ->on($productShopTable . '.id_shop', '=', $productLangTable . '.id_shop');
            })
            ->where($productLangTable . '.id_shop', $shopId)
            ->where($productShopTable . '.active', 1)
            ->where($productShopTable . '.visibility', '<>', 'none')
            ->where(function ($query) use ($productLangTable) {
                $query->whereNull($productLangTable . '.available_now')
                    ->orWhere($productLangTable . '.available_now', '')
                    ->orWhereNull($productLangTable . '.available_later')
                    ->orWhere($productLangTable . '.available_later', '');
            })
            ->where($productTable . '.reference', 'not like', 'VAT-%')
            ->where($productTable . '.reference', 'not like', '%parts')
            ->where($productTable . '.reference', 'not like', 'shipping%')
            ->where($productTable . '.reference', '<>', 'PICK-UP')
            ->where($productTable . '.reference', '<>', 'SHIP-PICK')
            ->get();

        foreach ($bd_data->unique('id_product') as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'name' => $item->name
            ];
        }

        return [
            'name' => trans('dashboard.No availability text'),
            'col' => 4,
            'item_id' => $type . '_no_availability_text',
            'prestashop' => self::adminProductLink('ASM'),
            'columns' => ['id_product', 'reference', 'name'],
            'counter' => count($data),
            'data' => $data
        ];
    }
    public static function dashboard_double_spaces($type)
    {
        $data = [];

        $productLangTable = self::tableName('product_lang');
        $productTable = self::tableName('product');

        $bd_data = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                $productLangTable . '.name'
            )
            ->leftJoin($productTable, $productLangTable . '.id_product', '=', $productTable . '.id_product')
            ->where($productLangTable . '.name', 'LIKE', '%  %')
            ->where($productTable . '.reference', 'not like', 'VAT-%')
            ->where($productTable . '.reference', 'not like', '%parts')
            ->where($productTable . '.reference', 'not like', 'shipping%')
            ->where($productTable . '.reference', '<>', 'PICK-UP')
            ->where($productTable . '.reference', '<>', 'SHIP-PICK')
            ->groupBy(
                $productLangTable . '.id_product',
                $productTable . '.id_product',
                $productTable . '.reference',
                $productLangTable . '.name'
            )
            ->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'name' => $item->name
            ];
        }
        
        return [
            'name' => trans('dashboard.PRODUCTS TITLE DOUBLE 2 SPACES CHARACTER'),
            'col' => 4,
            'item_id' => $type . '_titles_double_spaces',
            'prestashop' => self::adminProductLink('ASM'),
            'columns' => ['id_product', 'reference', 'name'],
            'counter' => count($data),
            'data' => $data
        ];
    }
}
