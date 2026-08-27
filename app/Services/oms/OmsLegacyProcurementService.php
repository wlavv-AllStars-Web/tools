<?php

namespace App\Services\oms;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OmsLegacyProcurementService
{
    public static function available(): bool
    {
        return Schema::hasTable('oms_order_notes')
            && Schema::hasTable('oms_order_note_lines')
            && Schema::hasTable('oms_billed_orders')
            && Schema::hasTable('oms_billed_order_lines');
    }

    public static function supplierCounts(bool $open): Collection
    {
        if (!self::available()) {
            return collect();
        }

        return self::billedOrdersBase()
            ->selectRaw('onote.supplier_id, COUNT(DISTINCT bo.id) AS total_orders')
            ->groupBy('onote.supplier_id', 's.name')
            ->havingRaw($open
                ? 'SUM(COALESCE(bol.qty_billed, 0)) > SUM(COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0))'
                : 'SUM(COALESCE(bol.qty_billed, 0)) <= SUM(COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0))'
            )
            ->orderBy('s.name')
            ->get();
    }

    public static function ordersOfSupplier(int $supplierId, bool $open): Collection
    {
        if (!self::available()) {
            return collect();
        }

        return self::billedOrdersBase()
            ->where('onote.supplier_id', $supplierId)
            ->selectRaw('bo.id AS oms_billed_order_id, bo.reference, bo.created_at AS date_add, onote.supplier_id, SUM(COALESCE(bol.qty_billed, 0)) AS qty_billed, SUM(COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0)) AS qty_received')
            ->groupBy('bo.id', 'bo.reference', 'bo.created_at', 'onote.supplier_id')
            ->havingRaw($open
                ? 'SUM(COALESCE(bol.qty_billed, 0)) > SUM(COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0))'
                : 'SUM(COALESCE(bol.qty_billed, 0)) <= SUM(COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0))'
            )
            ->orderByDesc('bo.id')
            ->get();
    }

    public static function orderDetails(int $billedOrderId): ?object
    {
        if (!self::available()) {
            return null;
        }

        $order = DB::table('oms_billed_orders as bo')
            ->join('oms_order_notes as onote', 'onote.id', '=', 'bo.order_note_id')
            ->leftJoin(self::psTable('supplier') . ' as s', 's.id_supplier', '=', 'onote.supplier_id')
            ->where('bo.id', $billedOrderId)
            ->selectRaw('bo.id AS oms_billed_order_id, bo.reference, bo.created_at AS date_add, onote.supplier_id, s.name AS supplier_name')
            ->first();

        if (!$order) {
            return null;
        }

        $order->rows = self::linesForOrders([$billedOrderId]);

        return $order;
    }

    public static function lineSums(int $billedOrderId): object
    {
        if (!self::available()) {
            return (object) [
                'number_of_rows' => 0,
                'total_qty_ordered' => 0,
                'total_qty_faturado' => 0,
                'total_qty_received' => 0,
            ];
        }

        return DB::table('oms_billed_order_lines as bol')
            ->leftJoin('oms_order_note_lines as onl', 'onl.id', '=', 'bol.order_note_line_id')
            ->where('bol.billed_order_id', $billedOrderId)
            ->selectRaw('COUNT(*) as number_of_rows, SUM(COALESCE(onl.qty_ordered, bol.qty_billed, 0)) as total_qty_ordered, SUM(COALESCE(bol.qty_billed, 0)) as total_qty_faturado, SUM(COALESCE(bol.qty_received, 0)) as total_qty_received')
            ->first() ?: (object) [];
    }

    public static function linesForOrders(array $billedOrderIds): Collection
    {
        $billedOrderIds = array_values(array_unique(array_filter(array_map('intval', $billedOrderIds))));

        if (empty($billedOrderIds) || !self::available()) {
            return collect();
        }

        $receivedSubquery = DB::table('oms_reception_lines')
            ->select('billed_order_line_id', DB::raw('SUM(qty_received) as qty_received_sum'))
            ->groupBy('billed_order_line_id');

        return DB::table('oms_billed_order_lines as bol')
            ->leftJoin('oms_order_note_lines as onl', 'onl.id', '=', 'bol.order_note_line_id')
            ->leftJoinSub($receivedSubquery, 'rl_sum', 'rl_sum.billed_order_line_id', '=', 'bol.id')
            ->leftJoin(self::psTable('product') . ' as p', 'p.id_product', '=', 'bol.product_id')
            ->leftJoin(self::psTable('product_attribute') . ' as pa', 'pa.id_product_attribute', '=', 'bol.product_attribute_id')
            ->leftJoin(self::psTable('product_lang') . ' as pl_asm', function ($join) {
                $join->on('pl_asm.id_product', '=', 'p.id_product')
                    ->where('pl_asm.id_lang', '=', 1)
                    ->where('pl_asm.id_shop', '=', 2);
            })
            ->leftJoin(self::psTable('product_lang') . ' as pl_asd', function ($join) {
                $join->on('pl_asd.id_product', '=', 'p.id_product')
                    ->where('pl_asd.id_lang', '=', 1)
                    ->where('pl_asd.id_shop', '=', 3);
            })
            ->whereIn('bol.billed_order_id', $billedOrderIds)
            ->selectRaw('bol.id AS oms_billed_order_line_id, bol.billed_order_id AS po_id, bol.product_id, COALESCE(bol.product_attribute_id, 0) AS product_attribute_id, COALESCE(pa.reference, p.reference, "") AS sku, COALESCE(NULLIF(pl_asm.name, ""), NULLIF(pl_asd.name, ""), "Unknown") AS name, COALESCE(onl.qty_ordered, bol.qty_billed, 0) AS qty_ordered, COALESCE(bol.qty_billed, 0) AS qty_wmfaturado, COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0) AS qty_received')
            ->orderBy('sku')
            ->get()
            ->map(function ($row) {
                $row->qty_expected = max(0, (int) $row->qty_wmfaturado - (int) $row->qty_received);
                return $row;
            });
    }

    public static function searchOpenOrders(string $tag): Collection
    {
        if (trim($tag) === '' || !self::available()) {
            return collect();
        }

        // The OMS credentials must not query the PrestaShop database directly.
        $matches = self::catalogueMatches($tag);
        $receivedSubquery = DB::table('oms_reception_lines')
            ->select('billed_order_line_id', DB::raw('SUM(qty_received) as qty_received_sum'))
            ->groupBy('billed_order_line_id');

        $orders = DB::table('oms_billed_orders as bo')
            ->join('oms_order_notes as onote', 'onote.id', '=', 'bo.order_note_id')
            ->join('oms_billed_order_lines as bol', 'bol.billed_order_id', '=', 'bo.id')
            ->leftJoin('oms_order_note_lines as onl', 'onl.id', '=', 'bol.order_note_line_id')
            ->leftJoinSub($receivedSubquery, 'rl_sum', 'rl_sum.billed_order_line_id', '=', 'bol.id')
            ->where(function ($query) use ($tag, $matches) {
                $query->where('bo.reference', 'like', '%' . $tag . '%');
                if ($matches['supplier_ids']->isNotEmpty()) $query->orWhereIn('onote.supplier_id', $matches['supplier_ids']);
                if ($matches['product_ids']->isNotEmpty()) $query->orWhereIn('bol.product_id', $matches['product_ids']);
                if ($matches['attribute_ids']->isNotEmpty()) $query->orWhereIn('bol.product_attribute_id', $matches['attribute_ids']);
            })
            ->where(fn ($query) => $query->whereNull('bo.status')->orWhereNotIn('bo.status', ['cancelled']))
            ->selectRaw('bo.id AS oms_billed_order_id, bo.id AS po_id, bo.reference, onote.supplier_id, MAX(bo.created_at) AS date_add, 5 AS status_id, SUM(COALESCE(onl.qty_ordered, bol.qty_billed, 0)) AS qty_ordered, SUM(COALESCE(bol.qty_billed, 0)) AS qty_wmfaturado, SUM(COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0)) AS qty_received')
            ->groupBy('bo.id', 'bo.reference', 'onote.supplier_id')
            ->havingRaw('SUM(COALESCE(bol.qty_billed, 0)) > SUM(COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0))')
            ->orderByDesc('bo.id')
            ->get();

        return self::attachCatalogueDetails($orders);
    }

    public static function searchReceivedProducts(string $tag): Collection
    {
        if (trim($tag) === '' || !Schema::hasTable('oms_reception_lines')) {
            return collect();
        }

        $matches = self::catalogueMatches($tag);
        $rows = DB::table('oms_reception_lines as rl')
            ->join('oms_billed_order_lines as bol', 'bol.id', '=', 'rl.billed_order_line_id')
            ->join('oms_receptions as r', 'r.id', '=', 'rl.reception_id')
            ->join('oms_billed_orders as bo', 'bo.id', '=', 'bol.billed_order_id')
            ->where(function ($query) use ($tag, $matches) {
                $query->where('bo.reference', 'like', '%' . $tag . '%');
                if ($matches['product_ids']->isNotEmpty()) $query->orWhereIn('bol.product_id', $matches['product_ids']);
                if ($matches['attribute_ids']->isNotEmpty()) $query->orWhereIn('bol.product_attribute_id', $matches['attribute_ids']);
            })
            ->selectRaw('bo.id AS po_id, bol.product_id, COALESCE(bol.product_attribute_id, 0) AS product_attribute_id, rl.qty_received AS qty, r.created_at AS date_add')
            ->orderByDesc('r.created_at')
            ->get();

        return self::attachCatalogueDetails($rows, false);
    }

    public static function openBackorderLinesOlderThan(string $date): Collection
    {
        if (!self::available()) {
            return collect();
        }

        $receivedSubquery = DB::table('oms_reception_lines')
            ->select('billed_order_line_id', DB::raw('SUM(qty_received) as qty_received_sum'))
            ->groupBy('billed_order_line_id');

        $rows = DB::table('oms_billed_orders as bo')
            ->join('oms_order_notes as onote', 'onote.id', '=', 'bo.order_note_id')
            ->join('oms_billed_order_lines as bol', 'bol.billed_order_id', '=', 'bo.id')
            ->leftJoin('oms_order_note_lines as onl', 'onl.id', '=', 'bol.order_note_line_id')
            ->leftJoinSub($receivedSubquery, 'rl_sum', 'rl_sum.billed_order_line_id', '=', 'bol.id')
            ->where(function ($query) {
                $query->whereNull('bo.status')
                    ->orWhereNotIn('bo.status', ['cancelled']);
            })
            ->whereDate('bo.created_at', '<', $date)
            ->whereRaw('COALESCE(bol.qty_billed, 0) > COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0)')
            ->selectRaw('bo.id AS order_id, bo.reference AS order_reference, bo.created_at AS order_date, onote.supplier_id, bol.product_id, COALESCE(bol.product_attribute_id, 0) AS product_attribute_id, COALESCE(onl.qty_ordered, bol.qty_billed, 0) AS qty_ordered, COALESCE(bol.qty_billed, 0) AS qty_billed, COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0) AS qty_received')
            ->orderBy('onote.supplier_id')
            ->orderBy('bo.id')
            ->get();

        return self::enrichProductReferences($rows);
    }

    private static function enrichProductReferences(Collection $rows): Collection
    {
        $productIds = $rows->pluck('product_id')->filter()->map(fn ($id) => (int) $id)->unique()->values();
        $attributeIds = $rows->pluck('product_attribute_id')->filter()->map(fn ($id) => (int) $id)->unique()->values();

        $prefix = self::psTable('');

        $products = $productIds->isEmpty()
            ? collect()
            : DB::connection('mysql2')
                ->table($prefix . 'product')
                ->whereIn('id_product', $productIds->all())
                ->pluck('reference', 'id_product');

        $attributes = $attributeIds->isEmpty()
            ? collect()
            : DB::connection('mysql2')
                ->table($prefix . 'product_attribute')
                ->whereIn('id_product_attribute', $attributeIds->all())
                ->pluck('reference', 'id_product_attribute');

        return $rows->map(function ($row) use ($products, $attributes) {
            $attributeReference = $attributes->get((int) $row->product_attribute_id);
            $productReference = $products->get((int) $row->product_id);
            $row->product_reference = $attributeReference ?: ($productReference ?: '');

            return $row;
        });
    }

    private static function catalogueMatches(string $tag): array
    {
        $prefix = env('DB2_DB_prefix', env('DB2_prefix', 'ps_'));
        $catalogue = DB::connection('mysql2');
        $like = '%' . $tag . '%';

        return [
            'supplier_ids' => $catalogue->table($prefix . 'supplier')->where('name', 'like', $like)->pluck('id_supplier'),
            'product_ids' => $catalogue->table($prefix . 'product')->where('reference', 'like', $like)->pluck('id_product'),
            'attribute_ids' => $catalogue->table($prefix . 'product_attribute')->where('reference', 'like', $like)->pluck('id_product_attribute'),
        ];
    }

    private static function attachCatalogueDetails(Collection $rows, bool $orders = true): Collection
    {
        if ($rows->isEmpty()) {
            return $rows;
        }

        $prefix = env('DB2_DB_prefix', env('DB2_prefix', 'ps_'));
        $catalogue = DB::connection('mysql2');
        $supplierNames = $catalogue->table($prefix . 'supplier')
            ->whereIn('id_supplier', $rows->pluck('supplier_id')->filter()->unique())
            ->pluck('name', 'id_supplier');

        $lines = $orders
            ? DB::table('oms_billed_order_lines')->whereIn('billed_order_id', $rows->pluck('po_id'))->get(['billed_order_id', 'product_id', 'product_attribute_id'])
            : $rows;
        $productReferences = $catalogue->table($prefix . 'product')->whereIn('id_product', $lines->pluck('product_id')->filter()->unique())->pluck('reference', 'id_product');
        $attributeReferences = $catalogue->table($prefix . 'product_attribute')->whereIn('id_product_attribute', $lines->pluck('product_attribute_id')->filter()->unique())->pluck('reference', 'id_product_attribute');

        $referenceFor = static fn ($line): string => (string) ($attributeReferences->get((int) ($line->product_attribute_id ?? 0))
            ?: $productReferences->get((int) $line->product_id, ''));

        return $rows->map(function ($row) use ($orders, $lines, $supplierNames, $referenceFor) {
            $row->supplier_name = $supplierNames->get((int) ($row->supplier_id ?? 0), '');
            $row->sku = $orders
                ? $lines->where('billed_order_id', $row->po_id)->map($referenceFor)->filter()->unique()->implode(', ')
                : $referenceFor($row);

            return $row;
        });
    }

    private static function billedOrdersBase()
    {
        $receivedSubquery = DB::table('oms_reception_lines')
            ->select('billed_order_line_id', DB::raw('SUM(qty_received) as qty_received_sum'))
            ->groupBy('billed_order_line_id');

        return DB::table('oms_billed_orders as bo')
            ->join('oms_order_notes as onote', 'onote.id', '=', 'bo.order_note_id')
            ->join('oms_billed_order_lines as bol', 'bol.billed_order_id', '=', 'bo.id')
            ->leftJoin('oms_order_note_lines as onl', 'onl.id', '=', 'bol.order_note_line_id')
            ->leftJoinSub($receivedSubquery, 'rl_sum', 'rl_sum.billed_order_line_id', '=', 'bol.id')
            ->leftJoin(self::psTable('supplier') . ' as s', 's.id_supplier', '=', 'onote.supplier_id')
            ->leftJoin(self::psTable('product') . ' as p', 'p.id_product', '=', 'bol.product_id')
            ->leftJoin(self::psTable('product_attribute') . ' as pa', 'pa.id_product_attribute', '=', 'bol.product_attribute_id')
            ->where(function ($query) {
                $query->whereNull('bo.status')
                    ->orWhereNotIn('bo.status', ['cancelled']);
            });
    }

    private static function psTable(string $table): string
    {
        return env('DB2_DB_prefix', env('DB2_prefix', 'ps_')) . $table;
    }
}
