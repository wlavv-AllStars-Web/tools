<?php

namespace App\Models\modules\auto_orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

use App\Models\modules\auto_orders\AutoOrdersCandidate;


class AutoOrder extends Model
{
    use HasFactory;
    public $timestamps = false;

    public STATIC function checkAutoOrders(){

        $local = new AutoOrdersCandidate();

        $paidOrderLines = self::paidOrderLinesForAutoOrders();

        if (count($paidOrderLines) > 0) {
            $local->insert($paidOrderLines, null);
            self::markOrderLinesAsImported($paidOrderLines);
        }

        return $local->getAllBrands();
    }

    protected static function paidOrderLinesForAutoOrders(): array
    {
        self::ensureImportedOrderDetailsTable();

        $prefix = env('DB2_DB_prefix', 'ps_');
        $ordersTable = $prefix . 'orders';
        $orderDetailTable = $prefix . 'order_detail';
        $productTable = $prefix . 'product';
        $productLangTable = $prefix . 'product_lang';
        $productAttributeTable = $prefix . 'product_attribute';
        $manufacturerTable = $prefix . 'manufacturer';
        $stockTable = $prefix . 'stock_available';
        $customProductTable = $prefix . 'custom_product';
        $customProductAttributeTable = $prefix . 'custom_product_attribute';
        $packTable = $prefix . 'pack';

        $rows = DB::connection('mysql2')
            ->table($orderDetailTable . ' as od')
            ->join($ordersTable . ' as o', 'o.id_order', '=', 'od.id_order')
            ->join($productTable . ' as p', 'p.id_product', '=', 'od.product_id')
            ->leftJoin($productAttributeTable . ' as pa', 'pa.id_product_attribute', '=', 'od.product_attribute_id')
            ->leftJoin($manufacturerTable . ' as m', 'm.id_manufacturer', '=', 'p.id_manufacturer')
            ->leftJoin($productLangTable . ' as pl', function ($join) {
                $join->on('pl.id_product', '=', 'p.id_product')
                    ->where('pl.id_lang', 1);
            })
            ->leftJoin($customProductTable . ' as cp', 'cp.id_product', '=', 'p.id_product')
            ->leftJoin($customProductAttributeTable . ' as cpa', function ($join) {
                $join->on('cpa.id_product_attribute', '=', 'od.product_attribute_id')
                    ->on('cpa.id_product', '=', 'od.product_id');
            })
            ->leftJoin($stockTable . ' as sa', function ($join) {
                $join->on('sa.id_product', '=', 'od.product_id')
                    ->whereRaw('sa.id_product_attribute = COALESCE(od.product_attribute_id, 0)');
            })
            ->whereIn('o.current_state', config('allstars.auto_orders.paid_order_states', [2, 3, 4, 5, 15, 16, 28]))
            ->where('o.valid', 1)
            ->when(config('allstars.auto_orders.import_from'), function ($query, $date) {
                $query->where('o.date_add', '>=', $date);
            })
            ->where('od.product_quantity', '>', 0)
            ->where('p.id_manufacturer', '>', 0)
            ->whereNotNull('od.product_reference')
            ->where('od.product_reference', '<>', '')
            ->select(
                'o.id_shop',
                'od.id_order',
                'od.id_order_detail',
                DB::raw('p.cache_is_pack as cache_is_pack'),
                DB::raw('p.id_manufacturer as id_manufacturer'),
                DB::raw('COALESCE(m.name, "") as name'),
                DB::raw('p.reference as reference'),
                DB::raw('COALESCE(pa.reference, "") as attr_reference'),
                DB::raw('COALESCE(cp.stock_arrive, 0) as stock_arrive'),
                DB::raw('od.product_id as id_product'),
                DB::raw('COALESCE(od.product_attribute_id, 0) as id_product_attribute'),
                DB::raw('COALESCE(pl.name, od.product_name) as product_name'),
                DB::raw('COALESCE(cpa.stock_arrive, 0) as stock_arrivepa'),
                DB::raw('COALESCE(sa.quantity, 0) as qtd_in_stock'),
                DB::raw('od.product_quantity as qtd_item'),
                DB::raw('COALESCE(cpa.wmdeprecated, cp.wmdeprecated, 0) as end_of_life')
            )
            ->get();

        $expandedRows = [];

        foreach ($rows as $row) {
            $row = (array) $row;
            $row['origin'] = self::shopCode((int) $row['id_shop']);

            $packItems = DB::connection('mysql2')
                ->table($packTable . ' as pack')
                ->where('pack.id_product_pack', (int) $row['id_product'])
                ->get();

            if ((int) $row['cache_is_pack'] === 1 || $packItems->isNotEmpty()) {
                foreach ($packItems as $packItem) {
                    $component = self::componentRowFromPackItem($row, $packItem);
                    if ($component) {
                        $expandedRows[] = $component;
                    }
                }

                continue;
            }

            $expandedRows[] = $row;
        }

        return self::filterAndGroupImportRows($expandedRows);
    }

    protected static function componentRowFromPackItem(array $sourceRow, object $packItem): ?array
    {
        $prefix = env('DB2_DB_prefix', 'ps_');
        $productTable = $prefix . 'product';
        $productLangTable = $prefix . 'product_lang';
        $productAttributeTable = $prefix . 'product_attribute';
        $manufacturerTable = $prefix . 'manufacturer';
        $stockTable = $prefix . 'stock_available';
        $customProductTable = $prefix . 'custom_product';
        $customProductAttributeTable = $prefix . 'custom_product_attribute';

        $idProduct = (int) $packItem->id_product_item;
        $idProductAttribute = (int) $packItem->id_product_attribute_item;

        $component = DB::connection('mysql2')
            ->table($productTable . ' as p')
            ->leftJoin($productAttributeTable . ' as pa', function ($join) use ($idProductAttribute) {
                $join->on('pa.id_product', '=', 'p.id_product')
                    ->where('pa.id_product_attribute', $idProductAttribute);
            })
            ->leftJoin($manufacturerTable . ' as m', 'm.id_manufacturer', '=', 'p.id_manufacturer')
            ->leftJoin($productLangTable . ' as pl', function ($join) {
                $join->on('pl.id_product', '=', 'p.id_product')
                    ->where('pl.id_lang', 1);
            })
            ->leftJoin($customProductTable . ' as cp', 'cp.id_product', '=', 'p.id_product')
            ->leftJoin($customProductAttributeTable . ' as cpa', function ($join) use ($idProductAttribute) {
                $join->on('cpa.id_product', '=', 'p.id_product')
                    ->where('cpa.id_product_attribute', $idProductAttribute);
            })
            ->leftJoin($stockTable . ' as sa', function ($join) use ($idProductAttribute) {
                $join->on('sa.id_product', '=', 'p.id_product')
                    ->where('sa.id_product_attribute', $idProductAttribute);
            })
            ->where('p.id_product', $idProduct)
            ->select(
                DB::raw('p.id_manufacturer as id_manufacturer'),
                DB::raw('COALESCE(m.name, "") as name'),
                DB::raw('p.reference as reference'),
                DB::raw('COALESCE(pa.reference, "") as attr_reference'),
                DB::raw('COALESCE(cp.stock_arrive, 0) as stock_arrive'),
                DB::raw('p.id_product as id_product'),
                DB::raw($idProductAttribute . ' as id_product_attribute'),
                DB::raw('COALESCE(pl.name, p.reference) as product_name'),
                DB::raw('COALESCE(cpa.stock_arrive, 0) as stock_arrivepa'),
                DB::raw('COALESCE(sa.quantity, 0) as qtd_in_stock'),
                DB::raw('COALESCE(cpa.wmdeprecated, cp.wmdeprecated, 0) as end_of_life')
            )
            ->first();

        if (!$component || (int) $component->id_manufacturer <= 0) {
            return null;
        }

        return array_merge((array) $component, [
            'id_shop' => $sourceRow['id_shop'],
            'origin' => $sourceRow['origin'],
            'id_order' => $sourceRow['id_order'],
            'id_order_detail' => $sourceRow['id_order_detail'],
            'qtd_item' => (int) $sourceRow['qtd_item'] * max(1, (int) $packItem->quantity),
        ]);
    }

    protected static function filterAndGroupImportRows(array $rows): array
    {
        $data = [];

        foreach ($rows as $row) {
            $idOrder = (int) $row['id_order'];
            $idOrderDetail = (int) $row['id_order_detail'];
            $idProduct = (int) $row['id_product'];
            $idProductAttribute = (int) $row['id_product_attribute'];

            if (self::alreadyImported($idOrderDetail, $idProduct, $idProductAttribute)) {
                continue;
            }

            $key = implode(':', [$idOrder, $idProduct, $idProductAttribute]);

            if (!isset($data[$key])) {
                $row['source_rows'] = [];
                $data[$key] = $row;
            } else {
                $data[$key]['qtd_item'] += (int) $row['qtd_item'];
            }

            $data[$key]['source_rows'][] = [
                'id_order' => $idOrder,
                'id_order_detail' => $idOrderDetail,
                'id_product' => $idProduct,
                'id_product_attribute' => $idProductAttribute,
                'origin' => $row['origin'],
            ];
        }

        return array_values($data);
    }

    protected static function shopCode(int $idShop): string
    {
        return config('allstars.auto_orders.shop_codes.' . $idShop, 'SHOP' . $idShop);
    }

    protected static function ensureImportedOrderDetailsTable(): void
    {
        $tableName = self::importedOrderDetailsTable();

        if (Schema::connection('mysql')->hasTable($tableName)) {
            return;
        }

        try {
            Schema::connection('mysql')->create($tableName, function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('id_order');
                $table->unsignedInteger('id_order_detail');
                $table->unsignedInteger('id_product');
                $table->unsignedInteger('id_product_attribute')->default(0);
                $table->string('origin', 10)->nullable();
                $table->timestamp('imported_at')->useCurrent();
                $table->timestamps();

                $table->unique(['id_order_detail', 'id_product', 'id_product_attribute'], 'auto_orders_import_source_unique');
                $table->index(['id_order', 'id_product', 'id_product_attribute'], 'auto_orders_import_order_product_idx');
            });
        } catch (\Throwable $exception) {
            Log::warning('Could not ensure auto orders imported order details table.', [
                'message' => $exception->getMessage(),
            ]);
        }
    }

    protected static function alreadyImported(int $idOrderDetail, int $idProduct, int $idProductAttribute): bool
    {
        $tracked = DB::connection('mysql')
            ->table(self::importedOrderDetailsTable())
            ->where('id_order_detail', $idOrderDetail)
            ->where('id_product', $idProduct)
            ->where('id_product_attribute', $idProductAttribute)
            ->exists();

        if ($tracked) {
            return true;
        }

        return DB::connection('mysql')
            ->table('auto_orders_candidates')
            ->where('id_order_detail', $idOrderDetail)
            ->where('id_product', $idProduct)
            ->where('id_product_attribute', $idProductAttribute)
            ->exists();
    }

    protected static function markOrderLinesAsImported(array $rows): void
    {
        foreach ($rows as $row) {
            foreach ($row['source_rows'] ?? [] as $sourceRow) {
                DB::connection('mysql')
                    ->table(self::importedOrderDetailsTable())
                    ->updateOrInsert(
                        [
                            'id_order_detail' => (int) $sourceRow['id_order_detail'],
                            'id_product' => (int) $sourceRow['id_product'],
                            'id_product_attribute' => (int) $sourceRow['id_product_attribute'],
                        ],
                        [
                            'id_order' => (int) $sourceRow['id_order'],
                            'origin' => $sourceRow['origin'],
                            'imported_at' => now(),
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
            }
        }
    }

    protected static function importedOrderDetailsTable(): string
    {
        return 'auto_orders_imported_order_details';
    }

}

