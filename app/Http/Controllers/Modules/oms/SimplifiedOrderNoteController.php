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
use Illuminate\Support\Facades\Schema;

class SimplifiedOrderNoteController extends Controller
{
    public function __construct(private readonly SupplierInvoiceWorkflowService $invoiceWorkflow)
    {
    }

    public function index(Request $request)
    {
        $supplierId = (int) $request->integer('supplier_id');
        $suppliers = suppliers::select(['id_supplier', 'name'])->orderBy('name')->get();
        $documentScope = $request->get('document_scope') === 'closed' ? 'closed' : 'open';
        // Reception quantity is authoritative: old documents can have a stale status.
        $receivedByNote = DB::table('oms_reception_lines as reception')
            ->join('oms_billed_order_lines as billed_line', 'billed_line.id', '=', 'reception.billed_order_line_id')
            ->join('oms_billed_orders as billed_order', 'billed_order.id', '=', 'billed_line.billed_order_id')
            ->selectRaw('billed_order.order_note_id, SUM(reception.qty_received) as qty_received')
            ->groupBy('billed_order.order_note_id');

        $orderNotes = $supplierId
            ? OrderNote::query()
                ->leftJoin('oms_order_note_lines as line', 'line.order_note_id', '=', 'oms_order_notes.id')
                ->leftJoinSub($receivedByNote, 'received', fn ($join) => $join->on('received.order_note_id', '=', 'oms_order_notes.id'))
                ->where('oms_order_notes.supplier_id', $supplierId)
                ->groupBy('oms_order_notes.id', 'oms_order_notes.supplier_id', 'oms_order_notes.reference', 'oms_order_notes.status', 'oms_order_notes.created_at')
                ->selectRaw('oms_order_notes.id, oms_order_notes.supplier_id, oms_order_notes.reference, oms_order_notes.status, oms_order_notes.created_at, COALESCE(SUM(line.qty_ordered), 0) as total_ordered, COALESCE(MAX(received.qty_received), 0) as total_received')
                ->havingRaw($documentScope === 'closed'
                    ? 'COALESCE(SUM(line.qty_ordered), 0) > 0 AND COALESCE(MAX(received.qty_received), 0) >= COALESCE(SUM(line.qty_ordered), 0)'
                    : "(COALESCE(SUM(line.qty_ordered), 0) = 0 AND oms_order_notes.status = 'order_note') OR COALESCE(MAX(received.qty_received), 0) < COALESCE(SUM(line.qty_ordered), 0)")
                ->latest('oms_order_notes.created_at')
                ->get()
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
            'documentScope' => $documentScope,
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
        $schema = Schema::connection('mysql2');
        $hasProductTechnicalImage = $schema->hasColumn($prefix . 'custom_product', 'technical_image_id');
        $hasAttributeTechnicalImage = $schema->hasColumn($prefix . 'custom_product_attribute', 'technical_image_id');
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
            ->leftJoin($prefix.'image as cover', function ($join) {
                $join->on('cover.id_product', '=', 'p.id_product')->where('cover.cover', '=', 1);
            })
            ->leftJoin($prefix.'stock_available as stock', function ($join) {
                $join->on('stock.id_product', '=', 'p.id_product')
                    ->where('stock.id_product_attribute', '=', 0)
                    ->where('stock.id_shop', '=', 0);
            })
            ->whereIn('p.id_product', $productIds)
            ->groupBy('p.id_product', 'p.reference', 'p.ean13', 'cover.id_image', 'p.location', 'p.wholesale_price', 'p.price', 'stock.quantity', 'cp.dim_verify', 'cp.wholesale_price_base_currency', 'cp.price_base_currency')
            ->when($hasProductTechnicalImage, fn ($query) => $query->groupBy('cp.technical_image_id'))
            ->selectRaw('p.id_product, p.reference, p.ean13 as barcode, cover.id_image as cover_image_id, ' . ($hasProductTechnicalImage ? 'cp.technical_image_id' : 'NULL') . ' as technical_image_id, p.location as housing, COALESCE(stock.quantity, 0) as stock_qty, p.wholesale_price as purchase_eur, p.price as sales_eur, COALESCE(cp.dim_verify, 0) as dim_verify, COALESCE(cp.wholesale_price_base_currency, 0) as purchase_supplier, COALESCE(cp.price_base_currency, 0) as sales_supplier, MIN(l.name) as name')
            ->get()->keyBy('id_product');
        $attributes = $attributeIds->isEmpty() ? collect() : DB::connection('mysql2')->table($prefix.'product_attribute as a')
            ->leftJoin($prefix.'custom_product_attribute as ca', function ($join) {
                $join->on('ca.id_product_attribute', '=', 'a.id_product_attribute')->on('ca.id_product', '=', 'a.id_product');
            })
            ->leftJoin($prefix.'stock_available as stock', function ($join) {
                $join->on('stock.id_product', '=', 'a.id_product')
                    ->on('stock.id_product_attribute', '=', 'a.id_product_attribute')
                    ->where('stock.id_shop', '=', 0);
            })
            ->whereIn('a.id_product_attribute', $attributeIds)
            ->selectRaw('a.id_product_attribute, a.reference, a.ean13 as barcode, ' . ($hasAttributeTechnicalImage ? 'ca.technical_image_id' : 'NULL') . ' as technical_image_id, ca.location as housing, COALESCE(stock.quantity, 0) as stock_qty, a.wholesale_price as purchase_eur, a.price as sales_eur, COALESCE(ca.wholesale_price_base_currency, 0) as purchase_supplier, COALESCE(ca.price_base_currency, 0) as sales_supplier')
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
                'barcode' => trim((string) ($attribute?->barcode ?: ($product?->barcode ?? ''))),
                'stock_qty' => (int) ($attribute->stock_qty ?? $product->stock_qty ?? 0),
                'image_url' => $this->productImageUrl((int) ($attribute?->technical_image_id ?: ($product?->technical_image_id ?: ($product?->cover_image_id ?? 0)))),
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

    private function productImageUrl(int $imageId): ?string
    {
        if ($imageId <= 0) {
            return null;
        }

        $path = implode('/', str_split((string) $imageId));
        return rtrim((string) config('allstars.stores.ASD.base_url'), '/') . '/img/p/' . $path . '/' . $imageId . '.jpg';
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
