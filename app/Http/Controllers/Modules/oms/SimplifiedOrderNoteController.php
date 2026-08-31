<?php

namespace App\Http\Controllers\Modules\oms;

use App\Http\Controllers\Controller;
use App\Models\modules\oms\BilledOrderLine;
use App\Models\modules\oms\OrderNote;
use App\Models\prestashop\suppliers;
use App\Services\oms\SupplierInvoiceWorkflowService;
use App\Services\Prestashop\PrestashopAdminLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SimplifiedOrderNoteController extends Controller
{
    public function __construct(private readonly SupplierInvoiceWorkflowService $invoiceWorkflow)
    {
    }

    public function index(Request $request)
    {
        $supplierId = (int) $request->integer('supplier_id');
        $suppliers = suppliers::select(['id_supplier', 'name'])->orderBy('name')->get();
        $orderNotes = $supplierId
            ? OrderNote::where('supplier_id', $supplierId)->latest()->get(['id', 'supplier_id', 'reference', 'status', 'created_at'])
            : collect();
        $orderNote = $orderNotes->firstWhere('id', (int) $request->integer('order_note_id')) ?? $orderNotes->first();

        if ($orderNote) {
            $orderNote->load(['supplier', 'lines']);
        }

        $currencyMeta = $orderNote
            ? $this->invoiceWorkflow->resolveCurrencyForOrderNote($orderNote, $orderNote->lines)
            : null;


        $rows = $orderNote ? $this->rows($orderNote, $currencyMeta) : collect();
        return view('modules.oms.order_notes.simplified', [
            'suppliers' => $suppliers,
            'selectedSupplierId' => $supplierId,
            'orderNotes' => $orderNotes,
            'orderNote' => $orderNote,
            'currencyMeta' => $currencyMeta,
            'draftInvoices' => $orderNote
                ? $this->invoiceWorkflow->getDraftInvoicesForSupplier((int) $orderNote->supplier_id)
                : collect(),
            'simplifiedOmsRows' => $rows,
            'summary' => [
                'lines' => $rows->count(),
                'products' => (int) $rows->sum('ordered'),
                'invoiced' => (int) $rows->sum('invoiced'),
                'received' => (int) $rows->sum('received'),
                'purchase_supplier' => (float) $rows->sum(fn ($row) => $row['ordered'] * $row['purchase_supplier']),
                'purchase_eur' => (float) $rows->sum(fn ($row) => $row['ordered'] * $row['purchase_eur']),
            ],
        ]);
    }

    private function rows(OrderNote $orderNote, ?array $currencyMeta)
    {
        $lines = $orderNote->lines;
        $lineIds = $lines->pluck('id')->filter();

        if ($lineIds->isEmpty()) {
            return collect();
        }

        $prefix = (string) (env('DB2_prefix') ?: env('DB2_DB_prefix') ?: 'ps_');
        $invoiced = BilledOrderLine::whereIn('order_note_line_id', $lineIds)
            ->selectRaw('order_note_line_id, SUM(qty_billed) as qty')
            ->groupBy('order_note_line_id')
            ->pluck('qty', 'order_note_line_id');
        $received = DB::table('oms_reception_lines as r')
            ->join('oms_billed_order_lines as b', 'b.id', '=', 'r.billed_order_line_id')
            ->whereIn('b.order_note_line_id', $lineIds)
            ->selectRaw('b.order_note_line_id, SUM(r.qty_received) as qty')
            ->groupBy('b.order_note_line_id')
            ->pluck('qty', 'b.order_note_line_id');

        $productIds = $lines->pluck('product_id')->filter()->unique();
        $attributeIds = $lines->pluck('product_attribute_id')->filter()->unique();
        $products = DB::connection('mysql2')->table($prefix.'product as p')
            ->leftJoin($prefix.'product_lang as l', 'l.id_product', '=', 'p.id_product')
            ->leftJoin($prefix.'custom_product as cp', 'cp.id_product', '=', 'p.id_product')
            ->whereIn('p.id_product', $productIds)
            ->groupBy('p.id_product', 'p.reference', 'p.location', 'p.wholesale_price', 'p.price', 'cp.dim_verify', 'cp.wholesale_price_base_currency', 'cp.price_base_currency')
            ->selectRaw('p.id_product, p.reference, p.location as housing, p.wholesale_price as purchase_eur, p.price as sales_eur, COALESCE(cp.dim_verify, 0) as dim_verify, COALESCE(cp.wholesale_price_base_currency, 0) as purchase_supplier, COALESCE(cp.price_base_currency, 0) as sales_supplier, MIN(l.name) as name')
            ->get()->keyBy('id_product');
        $attributes = $attributeIds->isEmpty() ? collect() : DB::connection('mysql2')->table($prefix.'product_attribute as a')
            ->leftJoin($prefix.'custom_product_attribute as ca', function ($join) {
                $join->on('ca.id_product_attribute', '=', 'a.id_product_attribute')->on('ca.id_product', '=', 'a.id_product');
            })
            ->whereIn('a.id_product_attribute', $attributeIds)
            ->selectRaw('a.id_product_attribute, a.reference, ca.location as housing, a.wholesale_price as purchase_eur, a.price as sales_eur, COALESCE(ca.wholesale_price_base_currency, 0) as purchase_supplier, COALESCE(ca.price_base_currency, 0) as sales_supplier')
            ->get()->keyBy('id_product_attribute');
        $backorders = $this->backorders($productIds, $prefix);

        return $lines->map(function ($line) use ($products, $attributes, $invoiced, $received, $backorders, $currencyMeta) {
            $product = $products->get($line->product_id);
            $attribute = $line->product_attribute_id ? $attributes->get($line->product_attribute_id) : null;
            $isAttribute = (bool) $attribute;
            $billed = (int) ($invoiced[$line->id] ?? 0);
            $key = (int) $line->product_id.'|'.(int) ($line->product_attribute_id ?? 0);

            return [
                'line_id' => (int) $line->id,
                'reference' => trim((string) ($attribute->reference ?? $product->reference ?? '')) ?: '-',
                'name' => trim((string) ($product->name ?? '')) ?: 'Product #'.$line->product_id,
                'housing' => trim((string) ($attribute->housing ?? $product->housing ?? '')),
                'dim_verified' => (int) ($product->dim_verify ?? 0) === 1,
                'backorders' => $backorders->get($key, collect())->values(),
                'ordered' => (int) $line->qty_ordered,
                'invoiced' => $billed,
                'remaining' => max(0, (int) $line->qty_ordered - $billed),
                'received' => (int) ($received[$line->id] ?? 0),
                'purchase_supplier' => (float) ($isAttribute ? $attribute->purchase_supplier : $product->purchase_supplier),
                'purchase_eur' => (float) ($isAttribute ? $attribute->purchase_eur : $product->purchase_eur),
                'sales_supplier' => (float) ($isAttribute ? ($product->sales_supplier + $attribute->sales_supplier) : $product->sales_supplier),
                'sales_eur' => (float) ($isAttribute ? ($product->sales_eur + $attribute->sales_eur) : $product->sales_eur),
                'currency_iso' => (string) ($currencyMeta['currency_iso'] ?? 'EUR'),
            ];
        });
    }

    private function backorders($productIds, string $prefix)
    {
        return DB::connection('mysql2')->table($prefix.'order_detail as d')
            ->join($prefix.'orders as o', 'o.id_order', '=', 'd.id_order')
            ->join($prefix.'stock_available as s', function ($join) {
                $join->on('s.id_product', '=', 'd.product_id')->on('s.id_product_attribute', '=', 'd.product_attribute_id')->where('s.id_shop', 0);
            })
            ->where('o.current_state', 15)->where('s.quantity', '<', 0)->whereIn('d.product_id', $productIds)
            ->select(['o.id_order', 'o.reference', 'o.id_shop', 'd.product_id', 'd.product_attribute_id', 's.quantity as stock'])
            ->get()->map(function ($row) {
                $row->store = (int) $row->id_shop === 3 ? 'ASD' : 'ASM';
                $row->url = PrestashopAdminLinkService::dashboardOrderAdminUrl((int) $row->id_order, $row->store);
                return $row;
            })->groupBy(fn ($row) => (int) $row->product_id.'|'.(int) $row->product_attribute_id);
    }
}
