<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use App\Services\Prestashop\PrestashopAdminLinkService;

class orders_details extends PrestashopModel
{
    use HasFactory;

    protected $primaryKey = 'id_order_detail';
    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('order_detail');
    }

    public function product()
    {
        return $this->hasOne(product::class, 'id_product', 'product_id');
    }

    public function product_attribute()
    {
        return $this->hasMany(product_attribute::class, 'id_product_attribute', 'product_attribute_id');
    }

    protected static function soldSnapshotQuery()
    {
        return DB::connection('mysql2')
            ->table(self::tableName('custom_sold'))
            ->where('month', '>=', now()->startOfMonth()->subMonths(11)->toDateString());
    }

    protected static function soldDynamicQuery()
    {
        $orderDetailTable = self::tableName('order_detail');
        $ordersTable = self::tableName('orders');

        return DB::connection('mysql2')
            ->table($orderDetailTable)
            ->join($ordersTable, $ordersTable . '.id_order', '=', $orderDetailTable . '.id_order')
            ->where($ordersTable . '.date_add', '>=', now()->subMonths(12)->toDateTimeString())
            ->where($ordersTable . '.valid', 1)
            ->whereIn(
                $ordersTable . '.current_state',
                config('allstars.auto_orders.paid_order_states', [2, 3, 4, 5, 15, 16, 28])
            );
    }

    public static function getSoldOf($product_reference, $attr_reference = '')
    {
        $orderDetailTable = self::tableName('order_detail');
        $reference = strlen((string) $attr_reference) > 0 ? $attr_reference : $product_reference;
        $dynamicSold = self::soldDynamicQuery()
            ->where($orderDetailTable . '.product_reference', $reference)
            ->sum($orderDetailTable . '.product_quantity');

        if (strlen((string) $attr_reference) > 0) {
            $snapshotSold = self::soldSnapshotQuery()
                ->where('attribute_reference', $attr_reference)
                ->sum('quantity_sold');
        } else {
            $snapshotSold = self::soldSnapshotQuery()
                ->where('reference', $product_reference)
                ->where('id_product_attribute', 0)
                ->sum('quantity_sold');
        }

        return $snapshotSold + $dynamicSold;
    }

    public static function getSoldByIDOf($id_product, $id_product_attribute = 0)
    {
        $orderDetailTable = self::tableName('order_detail');
        $snapshotSold = self::soldSnapshotQuery()
            ->where('id_product', $id_product)
            ->where('id_product_attribute', $id_product_attribute)
            ->sum('quantity_sold');
        $dynamicSold = self::soldDynamicQuery()
            ->where($orderDetailTable . '.product_id', $id_product)
            ->where($orderDetailTable . '.product_attribute_id', $id_product_attribute)
            ->sum($orderDetailTable . '.product_quantity');

        return $snapshotSold + $dynamicSold;
    }

    public static function getSoldByRefOf($reference)
    {
        $orderDetailTable = self::tableName('order_detail');
        $snapshotSold = self::soldSnapshotQuery()
            ->where(function ($query) use ($reference) {
                $query->where('attribute_reference', $reference)
                    ->orWhere(function ($query) use ($reference) {
                        $query->where('reference', $reference)
                            ->where('id_product_attribute', 0);
                    });
            })
            ->sum('quantity_sold');
        $dynamicSold = self::soldDynamicQuery()
            ->where($orderDetailTable . '.product_reference', $reference)
            ->sum($orderDetailTable . '.product_quantity');

        return $snapshotSold + $dynamicSold;
    }

    /**
     * Return the 12-month sold quantity for several references at once.
     *
     * The individual getSoldByRefOf() query is useful for a single product,
     * but using it while rendering an Auto Orders list causes an expensive
     * pair of aggregate queries for every row. This preserves its matching
     * rules (attribute reference, plus product reference without an
     * attribute) while aggregating all requested references in bulk.
     */
    public static function getSoldByReferences(array $references): array
    {
        $references = array_values(array_unique(array_filter(array_map(
            static fn ($reference) => trim((string) $reference),
            $references
        ), static fn ($reference) => $reference !== '')));

        if ($references === []) {
            return [];
        }

        $sold = array_fill_keys($references, 0);
        $orderDetailTable = self::tableName('order_detail');

        // Keep chunks comfortably below practical MySQL IN() limits while
        // still reducing hundreds of per-reference queries to a few queries.
        foreach (array_chunk($references, 500) as $chunk) {
            $snapshotByAttribute = self::soldSnapshotQuery()
                ->whereIn('attribute_reference', $chunk)
                ->selectRaw('attribute_reference as reference, SUM(quantity_sold) as quantity_sold')
                ->groupBy('attribute_reference')
                ->get();

            foreach ($snapshotByAttribute as $row) {
                $reference = (string) $row->reference;
                if (array_key_exists($reference, $sold)) {
                    $sold[$reference] += (int) $row->quantity_sold;
                }
            }

            $snapshotByProduct = self::soldSnapshotQuery()
                ->whereIn('reference', $chunk)
                ->where('id_product_attribute', 0)
                ->selectRaw('reference, SUM(quantity_sold) as quantity_sold')
                ->groupBy('reference')
                ->get();

            foreach ($snapshotByProduct as $row) {
                $reference = (string) $row->reference;
                if (array_key_exists($reference, $sold)) {
                    $sold[$reference] += (int) $row->quantity_sold;
                }
            }

            $dynamicSold = self::soldDynamicQuery()
                ->whereIn($orderDetailTable . '.product_reference', $chunk)
                ->selectRaw($orderDetailTable . '.product_reference as reference, SUM(' . $orderDetailTable . '.product_quantity) as quantity_sold')
                ->groupBy($orderDetailTable . '.product_reference')
                ->get();

            foreach ($dynamicSold as $row) {
                $reference = (string) $row->reference;
                if (array_key_exists($reference, $sold)) {
                    $sold[$reference] += (int) $row->quantity_sold;
                }
            }
        }

        return $sold;
    }

    public static function getProductsOfOrder($id_order)
    {
        $products = self::select('product_reference')
            ->where('id_order', $id_order)
            ->pluck('product_reference')
            ->toArray();

        return implode(', ', $products);
    }

    protected static function dashboardVoucherBase($type, $board, $suffix)
    {
        $data = [];

        $ordersTable = self::tableName('orders');
        $orderDetailTable = self::tableName('order_detail');

        $excludedOrderIds = asm_dashboard::getExceptions($board)
            ->pluck('id_product')
            ->toArray();

        $query = self::select(
                $ordersTable . '.id_order',
                $ordersTable . '.reference',
                $orderDetailTable . '.product_reference'
            )
            ->join($ordersTable, $ordersTable . '.id_order', '=', $orderDetailTable . '.id_order')
            ->where($orderDetailTable . '.product_reference', 'LIKE', '%voucher%');

        if (!empty($excludedOrderIds)) {
            $query->whereNotIn($ordersTable . '.id_order', $excludedOrderIds);
        }

        $bd_data = $query->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'clean' => $item->id_order,
                'id_order' => $item->id_order,
                'reference' => $item->reference,
                'product_reference' => $item->product_reference,
                'url' => PrestashopAdminLinkService::dashboardOrderAdminUrl((int) $item->id_order, 'ASM'),
            ];
        }

        return [
            'name' => trans('dashboard.ORDERS - WAITING INFO'),
            'col' => 4,
            'item_id' => $type . '_' . $suffix,
            'prestashop' => PrestashopAdminLinkService::dashboardOrderLink('id_order', 'ASM'),
            'columns' => ['clean', 'id_order', 'reference', 'product_reference'],
            'counter' => count($data),
            'exception_fields' => [$board, 'id_order', 'reference', 'product_reference'],
            'data' => $data
        ];
    }

    public static function dashboard_order_with_voucher($type)
    {
        return self::dashboardVoucherBase($type, 'order_with_voucher', 'order_with_voucher');
    }

    public static function dashboard_order_with_voucher_sales($type)
    {
        return self::dashboardVoucherBase($type, 'order_with_voucher_sales', 'order_with_voucher');
    }
}
