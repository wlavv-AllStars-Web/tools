<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Config;

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

        $productLangTable = self::tableName('product_lang');
        $productTable = self::tableName('product');

        $bd_data = self::select(
                $productTable . '.id_product',
                $productTable . '.reference',
                $productLangTable . '.name'
            )
            ->leftJoin($productTable, $productLangTable . '.id_product', '=', $productTable . '.id_product')
            ->where(function ($query) use ($productLangTable) {
                $query->where($productLangTable . '.available_now', '=', '')
                    ->orWhere($productLangTable . '.available_later', '=', '')
                    ->orWhere($productLangTable . '.available_soon_text', '=', '');
            })
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
            'name' => trans('dashboard.No availability text'),
            'col' => 4,
            'item_id' => $type . '_no_availability_text',
            'prestashop' => (isset(Config::get('token')->AdminProducts))
                ? [
                    'token' => Config::get('token')->AdminProducts,
                    'controller' => 'AdminProducts',
                    'element' => 'id_product',
                    'extraParameters' => '&updateproduct'
                ]
                : [],
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
            'prestashop' => (isset(Config::get('token')->AdminProducts))
                ? [
                    'token' => Config::get('token')->AdminProducts,
                    'controller' => 'AdminProducts',
                    'element' => 'id_product',
                    'extraParameters' => '&updateproduct'
                ]
                : [],
            'columns' => ['id_product', 'reference', 'name'],
            'counter' => count($data),
            'data' => $data
        ];
    }
}