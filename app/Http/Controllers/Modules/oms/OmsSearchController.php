<?php

namespace App\Http\Controllers\Modules\oms;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OmsSearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $empty = collect();

        if ($search === '') {
            return view('modules.oms.search.index', [
                'search' => $search,
                'orderNoteRows' => $empty,
                'billedRows' => $empty,
                'receivedRows' => $empty,
                'stockHistoryRows' => $empty,
                'priceHistoryRows' => $empty,
            ]);
        }

        [$productIds, $attributeIds] = $this->resolveProductIds($search);

        $orderNoteRows = $this->matchingQuery('oms_order_note_lines as l', $productIds, $attributeIds, 'l.product_id', 'l.product_attribute_id')
            ->join('oms_order_notes as onn', 'onn.id', '=', 'l.order_note_id')
            ->select('l.*', 'onn.reference as document_reference', 'onn.status as document_status', 'onn.supplier_id', 'onn.created_at as document_date')
            ->orderByDesc('onn.id')
            ->get();

        $billedRows = $this->matchingQuery('oms_billed_order_lines as l', $productIds, $attributeIds, 'l.product_id', 'l.product_attribute_id')
            ->join('oms_billed_orders as bo', 'bo.id', '=', 'l.billed_order_id')
            ->leftJoin('oms_supplier_invoices as si', 'si.id', '=', 'bo.supplier_invoice_id')
            ->select('l.*', 'bo.reference as document_reference', 'bo.status as document_status', 'bo.order_note_id', 'si.invoice_reference', 'si.status as invoice_status', 'bo.created_at as document_date')
            ->orderByDesc('bo.id')
            ->get();

        $receivedRows = $this->matchingQuery('oms_billed_order_lines as bol', $productIds, $attributeIds, 'bol.product_id', 'bol.product_attribute_id')
            ->join('oms_reception_lines as rl', 'rl.billed_order_line_id', '=', 'bol.id')
            ->join('oms_receptions as r', 'r.id', '=', 'rl.reception_id')
            ->join('oms_billed_orders as bo', 'bo.id', '=', 'bol.billed_order_id')
            ->leftJoin('oms_supplier_invoices as si', 'si.id', '=', 'bo.supplier_invoice_id')
            ->select('rl.*', 'bol.product_id', 'bol.product_attribute_id', 'bol.billed_order_id', 'bo.reference as document_reference', 'si.invoice_reference', 'r.created_at as document_date')
            ->orderByDesc('r.id')
            ->get();

        $stockHistoryRows = $this->matchingQuery('oms_stock_history as h', $productIds, $attributeIds, 'h.product_id', 'h.product_attribute_id')
            ->orderByDesc('h.created_at')
            ->orderByDesc('h.id')
            ->get();

        $priceHistoryRows = $this->matchingQuery('oms_document_line_history as h', $productIds, $attributeIds, 'h.product_id', 'h.product_attribute_id')
            ->orderByDesc('h.created_at')
            ->orderByDesc('h.id')
            ->get();

        $allRows = collect([$orderNoteRows, $billedRows, $receivedRows, $stockHistoryRows, $priceHistoryRows])->flatten(1);
        $productMeta = $this->productMeta(
            $allRows->pluck('product_id')->map(fn ($id) => (int) $id)->filter()->unique(),
            $allRows->pluck('product_attribute_id')->map(fn ($id) => (int) $id)->filter()->unique()
        );

        foreach ([$orderNoteRows, $billedRows, $receivedRows, $stockHistoryRows, $priceHistoryRows] as $rows) {
            $rows->each(function ($row) use ($productMeta) {
                $key = (int) $row->product_id . '|' . (int) ($row->product_attribute_id ?? 0);
                $fallbackKey = (int) $row->product_id . '|0';
                $meta = $productMeta->get($key) ?? $productMeta->get($fallbackKey);
                $row->search_reference = $meta->display_reference ?? $row->display_reference_snapshot ?? $row->attribute_reference_snapshot ?? $row->product_reference_snapshot ?? '-';
                $row->search_product_name = $meta->name ?? ('Product #' . (int) $row->product_id);
            });
        }

        return view('modules.oms.search.index', compact(
            'search',
            'orderNoteRows',
            'billedRows',
            'receivedRows',
            'stockHistoryRows',
            'priceHistoryRows'
        ));
    }

    protected function resolveProductIds(string $search): array
    {
        $prefix = (string) (env('DB2_prefix') ?: env('DB2_DB_prefix') ?: 'ps_');
        $like = '%' . $search . '%';

        $productIds = DB::connection('mysql2')
            ->table($prefix . 'product as p')
            ->leftJoin($prefix . 'product_lang as pl', 'pl.id_product', '=', 'p.id_product')
            ->where(function ($query) use ($like) {
                $query->where('p.reference', 'like', $like)
                    ->orWhere('p.ean13', 'like', $like)
                    ->orWhere('pl.name', 'like', $like);
            })
            ->distinct()
            ->pluck('p.id_product')
            ->map(fn ($id) => (int) $id)
            ->values();

        $attributeIds = DB::connection('mysql2')
            ->table($prefix . 'product_attribute as pa')
            ->where(function ($query) use ($like) {
                $query->where('pa.reference', 'like', $like)
                    ->orWhere('pa.ean13', 'like', $like);
            })
            ->distinct()
            ->pluck('pa.id_product_attribute')
            ->map(fn ($id) => (int) $id)
            ->values();

        return [$productIds, $attributeIds];
    }

    protected function matchingQuery(string $table, Collection $productIds, Collection $attributeIds, string $productColumn, string $attributeColumn): Builder
    {
        $query = DB::table($table);

        if ($productIds->isEmpty() && $attributeIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($match) use ($productIds, $attributeIds, $productColumn, $attributeColumn) {
            if ($productIds->isNotEmpty()) {
                $match->whereIn($productColumn, $productIds->all());
            }
            if ($attributeIds->isNotEmpty()) {
                $method = $productIds->isNotEmpty() ? 'orWhereIn' : 'whereIn';
                $match->{$method}($attributeColumn, $attributeIds->all());
            }
        });
    }

    protected function productMeta(Collection $productIds, Collection $attributeIds): Collection
    {
        if ($productIds->isEmpty()) {
            return collect();
        }

        $prefix = (string) (env('DB2_prefix') ?: env('DB2_DB_prefix') ?: 'ps_');

        $products = DB::connection('mysql2')
            ->table($prefix . 'product as p')
            ->leftJoin($prefix . 'product_lang as pl', 'pl.id_product', '=', 'p.id_product')
            ->whereIn('p.id_product', $productIds->all())
            ->groupBy('p.id_product', 'p.reference')
            ->selectRaw('p.id_product, p.reference, MIN(pl.name) as name')
            ->get()
            ->mapWithKeys(function ($row) {
                $row->id_product_attribute = 0;
                $row->display_reference = trim((string) $row->reference) ?: (string) $row->id_product;
                return [(int) $row->id_product . '|0' => $row];
            });

        if ($attributeIds->isEmpty()) {
            return $products;
        }

        $attributes = DB::connection('mysql2')
            ->table($prefix . 'product_attribute as pa')
            ->whereIn('pa.id_product_attribute', $attributeIds->all())
            ->select('pa.id_product', 'pa.id_product_attribute', 'pa.reference')
            ->get()
            ->mapWithKeys(function ($row) use ($products) {
                $parent = $products->get((int) $row->id_product . '|0');
                $row->display_reference = trim((string) $row->reference) ?: ($parent->display_reference ?? (string) $row->id_product);
                $row->name = $parent->name ?? ('Product #' . (int) $row->id_product);
                return [(int) $row->id_product . '|' . (int) $row->id_product_attribute => $row];
            });

        return $products->merge($attributes);
    }
}
