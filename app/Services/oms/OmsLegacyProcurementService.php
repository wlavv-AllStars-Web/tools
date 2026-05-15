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
            ->leftJoin(self::psTable('product_lang') . ' as pl', function ($join) {
                $join->on('pl.id_product', '=', 'p.id_product')
                    ->where('pl.id_lang', '=', 1)
                    ->where('pl.id_shop', '=', 1);
            })
            ->whereIn('bol.billed_order_id', $billedOrderIds)
            ->selectRaw('bol.id AS oms_billed_order_line_id, bol.billed_order_id AS po_id, bol.product_id, COALESCE(bol.product_attribute_id, 0) AS product_attribute_id, COALESCE(pa.reference, p.reference, "") AS sku, COALESCE(pl.name, "") AS name, COALESCE(onl.qty_ordered, bol.qty_billed, 0) AS qty_ordered, COALESCE(bol.qty_billed, 0) AS qty_wmfaturado, COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0) AS qty_received')
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

        return self::billedOrdersBase()
            ->where(function ($query) use ($tag) {
                $query->where('bo.reference', 'like', '%' . $tag . '%')
                    ->orWhere('s.name', 'like', '%' . $tag . '%')
                    ->orWhere('p.reference', 'like', '%' . $tag . '%')
                    ->orWhere('pa.reference', 'like', '%' . $tag . '%');
            })
            ->selectRaw('bo.id AS oms_billed_order_id, bo.id AS po_id, bo.reference, onote.supplier_id, s.name AS supplier_name, MAX(bo.created_at) AS date_add, 5 AS status_id, GROUP_CONCAT(DISTINCT COALESCE(pa.reference, p.reference, "") SEPARATOR ", ") AS sku, SUM(COALESCE(onl.qty_ordered, bol.qty_billed, 0)) AS qty_ordered, SUM(COALESCE(bol.qty_billed, 0)) AS qty_wmfaturado, SUM(COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0)) AS qty_received')
            ->groupBy('bo.id', 'bo.reference', 'onote.supplier_id', 's.name')
            ->havingRaw('SUM(COALESCE(bol.qty_billed, 0)) > SUM(COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0))')
            ->orderByDesc('bo.id')
            ->get();
    }

    public static function searchReceivedProducts(string $tag): Collection
    {
        if (trim($tag) === '' || !Schema::hasTable('oms_reception_lines')) {
            return collect();
        }

        return DB::table('oms_reception_lines as rl')
            ->join('oms_billed_order_lines as bol', 'bol.id', '=', 'rl.billed_order_line_id')
            ->join('oms_receptions as r', 'r.id', '=', 'rl.reception_id')
            ->join('oms_billed_orders as bo', 'bo.id', '=', 'bol.billed_order_id')
            ->leftJoin(self::psTable('product') . ' as p', 'p.id_product', '=', 'bol.product_id')
            ->leftJoin(self::psTable('product_attribute') . ' as pa', 'pa.id_product_attribute', '=', 'bol.product_attribute_id')
            ->where(function ($query) use ($tag) {
                $query->where('p.reference', 'like', '%' . $tag . '%')
                    ->orWhere('pa.reference', 'like', '%' . $tag . '%');
            })
            ->selectRaw('bo.id AS po_id, bol.product_id, COALESCE(bol.product_attribute_id, 0) AS product_attribute_id, COALESCE(pa.reference, p.reference, "") AS sku, rl.qty_received AS qty, r.created_at AS date_add')
            ->orderByDesc('r.created_at')
            ->get();
    }

    public static function openBackorderLinesOlderThan(string $date): Collection
    {
        if (!self::available()) {
            return collect();
        }

        return self::billedOrdersBase()
            ->whereDate('bo.created_at', '<', $date)
            ->whereRaw('COALESCE(bol.qty_billed, 0) > COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0)')
            ->selectRaw('bo.id AS order_id, bo.reference AS order_reference, bo.created_at AS order_date, onote.supplier_id, s.name AS supplier_name, COALESCE(pa.reference, p.reference, "") AS product_reference, COALESCE(onl.qty_ordered, bol.qty_billed, 0) AS qty_ordered, COALESCE(bol.qty_billed, 0) AS qty_billed, COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0) AS qty_received')
            ->orderBy('onote.supplier_id')
            ->orderBy('bo.id')
            ->get();
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
