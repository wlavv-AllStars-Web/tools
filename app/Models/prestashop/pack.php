<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use App\Services\Prestashop\PrestashopAdminLinkService;

class pack extends PrestashopModel
{
    use HasFactory;

    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('pack');
    }

    public function product_pack()
    {
        return $this->hasOne(product::class, 'id_product', 'id_product_item');
    }

    public static function isPack(int $idProduct): bool
    {
        return self::where('id_product_pack', $idProduct)->exists();
    }

    public static function is_pack($id_product)
    {
        return self::isPack((int) $id_product);
    }

    public static function items(int $idProduct)
    {
        return self::where('id_product_pack', $idProduct)->get();
    }

    public static function getPackItems($id_product)
    {
        return self::items((int) $id_product);
    }

    public static function dashboard_packs_without_stock($type)
    {
        $data = [];

        $packTable = self::tableName('pack');
        $productTable = self::tableName('product');
        $manufacturerTable = self::tableName('manufacturer');
        $stockTable = self::tableName('stock_available');

        $bd_data = self::select(
                $packTable . '.id_product_pack',
                $productTable . '.id_product',
                $productTable . '.reference',
                DB::raw($manufacturerTable . '.name AS brand'),
                DB::raw('MAX(' . $stockTable . '.quantity) AS quantity')
            )
            ->join($productTable, $packTable . '.id_product_pack', '=', $productTable . '.id_product')
            ->join($manufacturerTable, $productTable . '.id_manufacturer', '=', $manufacturerTable . '.id_manufacturer')
            ->join($stockTable, $productTable . '.id_product', '=', $stockTable . '.id_product')
            ->where($productTable . '.active', 1)
            ->groupBy(
                $packTable . '.id_product_pack',
                $productTable . '.id_product',
                $productTable . '.reference',
                $manufacturerTable . '.name'
            )
            ->havingRaw('MAX(' . $stockTable . '.quantity) < 1')
            ->orderBy('brand', 'ASC')
            ->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'reference' => $item->reference,
                'brand' => $item->brand,
                'quantity' => $item->quantity,
                'url' => PrestashopAdminLinkService::dashboardProductAdminUrl((int) $item->id_product, 'ASM'),
            ];
        }

        return self::dashboardPanel(
            trans("dashboard.Products's Pack without stock"),
            $type,
            'packs_without_stock',
            ['id_product', 'reference', 'brand', 'quantity'],
            $data,
            [],
            PrestashopAdminLinkService::dashboardProductLink('id_product', 'ASM')
        );
    }

    public static function availablePackQty(int $idProduct): array
    {
        $items = self::items($idProduct);

        if ($items->isEmpty()) {
            return [
                'is_pack' => false,
                'pack_qty' => null,
                'components' => [],
            ];
        }

        $stocks = stock_available::select(['id_product', 'id_product_attribute', 'quantity'])
            ->where(function ($q) use ($items) {
                foreach ($items as $it) {
                    $q->orWhere(function ($qq) use ($it) {
                        $qq->where('id_product', (int) $it->id_product_item)
                           ->where('id_product_attribute', (int) $it->id_product_attribute_item);
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
            $idItem = (int) $it->id_product_item;
            $idAttr = (int) $it->id_product_attribute_item;
            $qtyInPack = max(1, (int) $it->quantity);

            $key = $idItem . '-' . $idAttr;
            $stock = (int) ($stocks[$key]->quantity ?? 0);

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
