<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

use App\Models\ASD_missing_images;

class image extends PrestashopModel
{
    use HasFactory;

    protected $primaryKey = 'id_image';
    protected $fillable = [];

    protected const ASD_SHOP_ID = 3;
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('image');
    }

    /**
     * Verifica imagens ASD em bulk por referência do produto.
     * Guarda em mysql.ASD_missing_images as referências sem imagem.
     *
     * $size:
     * - 'thumb' => /thumb/REF.jpg
     * - '600'   => /600/REF.jpg
     */
    public static function syncASDMissingImages($size = 'thumb', $limit = null)
    {
        $productTable = self::tableName('product');
        $productShopTable = self::tableName('product_shop');
        $manufacturerTable = self::tableName('manufacturer');

        /*
        |--------------------------------------------------------------------------
        | Buscar produtos ativos da shop ASD
        |--------------------------------------------------------------------------
        */
        $imageTable = self::tableName('image');
        $imageShopTable = self::tableName('image_shop');

        $query = DB::connection('mysql2')
            ->table($productTable . ' as p')
            ->join($productShopTable . ' as ps', function ($join) use ($productTable, $productShopTable) {
                $join->on('ps.id_product', '=', 'p.id_product');
            })
            ->leftJoin($manufacturerTable . ' as m', 'm.id_manufacturer', '=', 'p.id_manufacturer')
            ->leftJoin($imageTable . ' as i', 'i.id_product', '=', 'p.id_product')
            ->leftJoin($imageShopTable . ' as ish', function ($join) {
                $join->on('ish.id_image', '=', 'i.id_image')
                    ->where('ish.id_shop', self::ASD_SHOP_ID);
            })
            ->where('ps.id_shop', self::ASD_SHOP_ID)
            ->whereNull('ish.id_image')
            ->select([
                'p.id_product',
                'p.reference',
                'm.name as manufacturer',
            ])
            ->groupBy('p.id_product', 'p.reference', 'm.name')
            ->orderBy('p.id_product', 'ASC');

        if (!is_null($limit) && (int)$limit > 0) {
            $query->limit((int)$limit);
        }

        $products = $query->get();

        /*
        |--------------------------------------------------------------------------
        | Limpar tabela auxiliar antes de regenerar
        |--------------------------------------------------------------------------
        */
        ASD_missing_images::truncate();

        $missing = [];
        $checked = 0;

        foreach ($products as $product) {
            $checked++;

            if (empty($product->reference) || empty($product->manufacturer)) {
                $missing[] = [
                    'manufacturer' => $product->manufacturer ?? '',
                    'reference' => $product->reference ?? '',
                ];
                continue;
            }

            $missing[] = [
                'manufacturer' => $product->manufacturer,
                'reference' => $product->reference,
            ];
        }

        if (!empty($missing)) {
            ASD_missing_images::insert($missing);
        }

        return (object)[
            'checked' => $checked,
            'missing' => count($missing),
            'size' => $size,
        ];
    }

}
