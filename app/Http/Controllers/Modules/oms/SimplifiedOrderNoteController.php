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
        $documentScope = in_array($request->get('document_scope'), ['open', 'billed', 'closed'], true) ? $request->get('document_scope') : 'open';
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
                ->groupBy('oms_order_notes.id', 'oms_order_notes.supplier_id', 'oms_order_notes.reference', 'oms_order_notes.status', 'oms_order_notes.internal_note', 'oms_order_notes.logistic_note', 'oms_order_notes.created_at')
                ->selectRaw('oms_order_notes.id, oms_order_notes.supplier_id, oms_order_notes.reference, oms_order_notes.status, oms_order_notes.internal_note, oms_order_notes.logistic_note, oms_order_notes.created_at, COALESCE(SUM(line.qty_ordered), 0) as total_ordered, COALESCE(MAX(received.qty_received), 0) as total_received')
                ->havingRaw(match ($documentScope) {
                    'closed' => 'COALESCE(SUM(line.qty_ordered), 0) > 0 AND COALESCE(MAX(received.qty_received), 0) >= COALESCE(SUM(line.qty_ordered), 0)',
                    'billed' => "oms_order_notes.status = 'billed' AND COALESCE(MAX(received.qty_received), 0) < COALESCE(SUM(line.qty_ordered), 0)",
                    default => "oms_order_notes.status = 'order_note'",
                })
                ->latest('oms_order_notes.created_at')
                ->get()
            : collect();

        if ($supplierId > 0 && $documentScope === 'open' && $orderNotes->isEmpty()) {
            $request->merge(['document_scope' => 'closed']);
            return $this->index($request);
        }

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
            'invoicedInvoices' => $this->invoicedInvoices($rows),
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

    private function invoicedInvoices($rows)
    {
        return $rows->flatMap(function (array $row) {
            return $row['invoices']->map(fn ($invoice) => [
                'id' => (int) $invoice->invoice_id,
                'reference' => (string) $invoice->invoice_reference,
                'line_id' => (int) $row['line_id'],
                'reference_product' => (string) $row['reference'],
                'name' => (string) $row['name'],
                'qty_billed' => (int) $invoice->qty_billed,
            ]);
        })->groupBy('id')->map(function ($entries) {
            $first = $entries->first();

            return [
                'id' => $first['id'],
                'reference' => $first['reference'],
                'lines' => $entries->values(),
                'qty_billed' => (int) $entries->sum('qty_billed'),
            ];
        })->values();
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
        $lineInvoices = BilledOrderLine::query()
            ->join('oms_billed_orders as billed_order', 'billed_order.id', '=', 'oms_billed_order_lines.billed_order_id')
            ->join('oms_supplier_invoices as invoice', 'invoice.id', '=', 'billed_order.supplier_invoice_id')
            ->whereIn('oms_billed_order_lines.order_note_line_id', $lineIds)
            ->selectRaw('oms_billed_order_lines.order_note_line_id, MIN(oms_billed_order_lines.id) as billed_line_id, SUM(oms_billed_order_lines.qty_billed) as qty_billed, invoice.id as invoice_id, invoice.invoice_reference')
            ->groupBy('oms_billed_order_lines.order_note_line_id', 'invoice.id', 'invoice.invoice_reference')
            ->get()
            ->groupBy('order_note_line_id');
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
            ->leftJoin($prefix.'manufacturer as manufacturer', 'manufacturer.id_manufacturer', '=', 'p.id_manufacturer')
            ->leftJoin($prefix.'image as cover', function ($join) {
                $join->on('cover.id_product', '=', 'p.id_product')->where('cover.cover', '=', 1);
            })
            ->leftJoin($prefix.'stock_available as stock', function ($join) {
                $join->on('stock.id_product', '=', 'p.id_product')
                    ->where('stock.id_product_attribute', '=', 0)
                    ->where('stock.id_shop', '=', 0);
            })
            ->whereIn('p.id_product', $productIds)
            ->groupBy('p.id_product', 'p.reference', 'p.ean13', 'cover.id_image', 'p.location', 'p.weight', 'p.width', 'p.height', 'p.depth', 'manufacturer.name', 'cp.wmdeprecated', 'p.wholesale_price', 'p.price', 'stock.quantity', 'cp.dim_verify', 'cp.wholesale_price_base_currency', 'cp.price_base_currency', 'cp.discount_percentage')
            ->when($hasProductTechnicalImage, fn ($query) => $query->groupBy('cp.technical_image_id'))
            ->selectRaw('p.id_product, p.reference, p.ean13 as barcode, cover.id_image as cover_image_id, ' . ($hasProductTechnicalImage ? 'cp.technical_image_id' : 'NULL') . ' as technical_image_id, p.location as housing, p.weight, p.width, p.height, p.depth, manufacturer.name as manufacturer_name, COALESCE(cp.wmdeprecated, 0) as end_of_life, COALESCE(stock.quantity, 0) as stock_qty, COALESCE(cp.stock_arrive, 0) as stock_arrive, p.wholesale_price as purchase_eur, p.price as sales_eur, COALESCE(cp.dim_verify, 0) as dim_verify, COALESCE(cp.wholesale_price_base_currency, 0) as purchase_supplier, COALESCE(cp.price_base_currency, 0) as sales_supplier, COALESCE(cp.discount_percentage, 0) as discount_percentage, MIN(l.name) as name')
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
            ->selectRaw('a.id_product_attribute, a.reference, a.ean13 as barcode, ' . ($hasAttributeTechnicalImage ? 'ca.technical_image_id' : 'NULL') . ' as technical_image_id, ca.location as housing, COALESCE(stock.quantity, 0) as stock_qty, COALESCE(ca.stock_arrive, 0) as stock_arrive, a.wholesale_price as purchase_eur, a.price as sales_eur, COALESCE(ca.wholesale_price_base_currency, 0) as purchase_supplier, COALESCE(ca.price_base_currency, 0) as sales_supplier')
            ->get()->keyBy('id_product_attribute');
        $backorders = $this->backorders($productIds, $prefix);

        return $lines->map(function ($line) use ($products, $attributes, $invoiced, $received, $lineInvoices, $backorders, $currencyMeta, $prefix) {
            $product = $products->get($line->product_id);
            $attribute = $line->product_attribute_id ? $attributes->get($line->product_attribute_id) : null;
            $isAttribute = (bool) $attribute;
            $billed = (int) ($invoiced[$line->id] ?? 0);
            $key = (int) $line->product_id.'|'.(int) ($line->product_attribute_id ?? 0);
            $salesEur = (float) ($isAttribute ? ($product->sales_eur + $attribute->sales_eur) : $product->sales_eur);
            $saleConversionRate = (float) ($currencyMeta['sale_conversion_rate'] ?? 1);
            if ($saleConversionRate <= 0) {
                $saleConversionRate = 1.0;
            }

            // Supplier-currency sales are stored as the parent price plus the combination
            // impact. Re-converting the final EUR value here applies the SALE rate twice.
            // The EUR core impact is only a fallback for legacy combinations without a
            // custom supplier-currency impact yet.
            $parentSalesSupplier = (float) ($product->sales_supplier ?? 0);
            if ($parentSalesSupplier == 0.0 && (float) ($product->sales_eur ?? 0) != 0.0) {
                $parentSalesSupplier = (float) $product->sales_eur * $saleConversionRate;
            }

            $attributeSalesSupplierImpact = 0.0;
            if ($isAttribute) {
                $attributeSalesSupplierImpact = (float) ($attribute->sales_supplier ?? 0);
                if ($attributeSalesSupplierImpact == 0.0 && (float) ($attribute->sales_eur ?? 0) != 0.0) {
                    $attributeSalesSupplierImpact = (float) $attribute->sales_eur * $saleConversionRate;
                }
            }

            $salesSupplier = $parentSalesSupplier + $attributeSalesSupplierImpact;

            return [
                'line_id' => (int) $line->id,
                'reference' => trim((string) ($attribute->reference ?? $product->reference ?? '')) ?: '-',
                'name' => trim((string) ($product->name ?? '')) ?: 'Product #'.$line->product_id,
                'housing' => trim((string) ($attribute->housing ?? $product->housing ?? '')),
                'barcode' => trim((string) ($attribute?->barcode ?: ($product?->barcode ?? ''))),
                'stock_qty' => (int) ($attribute->stock_qty ?? $product->stock_qty ?? 0),
                'stock_arrive' => (int) ($attribute->stock_arrive ?? $product->stock_arrive ?? 0),
                'image_url' => $this->productImageUrl((int) ($attribute?->technical_image_id ?: ($product?->technical_image_id ?: ($product?->cover_image_id ?? 0)))),
                'dim_verified' => (int) ($product->dim_verify ?? 0) === 1,
                'weight' => (float) ($product->weight ?? 0), 'width' => (float) ($product->width ?? 0), 'height' => (float) ($product->height ?? 0), 'depth' => (float) ($product->depth ?? 0),
                'manufacturer' => trim((string) ($product->manufacturer_name ?? '')), 'end_of_life' => (int) ($product->end_of_life ?? 0) === 1,
                'related_products' => $this->relatedProducts((int) $line->product_id, (int) ($line->product_attribute_id ?? 0), $prefix),
                'backorders' => $backorders->get($key, collect())->values(),
                'ordered' => (int) $line->qty_ordered,
                'invoiced' => $billed,
                'remaining' => max(0, (int) $line->qty_ordered - $billed),
                'received' => (int) ($received[$line->id] ?? 0),
                'invoices' => ($lineInvoices->get($line->id, collect()))->values(),
                'purchase_supplier' => (float) ($isAttribute ? $attribute->purchase_supplier : $product->purchase_supplier),
                'purchase_eur' => (float) ($isAttribute ? $attribute->purchase_eur : $product->purchase_eur),
                'discount_percentage' => (float) ($product->discount_percentage ?? 0),
                'sales_supplier' => round($salesSupplier, 6),
                'sales_eur' => $salesEur,
                'currency_iso' => (string) ($currencyMeta['currency_iso'] ?? 'EUR'),
            ];
        });
    }


    /**
     * Returns combinations of the same parent and packs using this exact component.
     * The stock fields remain scoped to the product/attribute shown in each row.
     */
    private function relatedProducts(int $productId, int $attributeId, string $prefix)
    {
        $connection = DB::connection('mysql2');
        $attributeNames = "GROUP_CONCAT(DISTINCT attribute_lang.name ORDER BY attribute_lang.name SEPARATOR ', ') as attributes";

        $siblings = collect();
        if ($attributeId > 0) {
            $siblings = $connection->table($prefix.'product_attribute as attribute')
                ->join($prefix.'product as product', 'product.id_product', '=', 'attribute.id_product')
                ->leftJoin($prefix.'custom_product as custom_product', 'custom_product.id_product', '=', 'product.id_product')
                ->leftJoin($prefix.'custom_product_attribute as custom_attribute', function ($join) {
                    $join->on('custom_attribute.id_product', '=', 'attribute.id_product')
                        ->on('custom_attribute.id_product_attribute', '=', 'attribute.id_product_attribute');
                })
                ->leftJoin($prefix.'stock_available as stock', function ($join) {
                    $join->on('stock.id_product', '=', 'attribute.id_product')
                        ->on('stock.id_product_attribute', '=', 'attribute.id_product_attribute')
                        ->where('stock.id_shop', '=', 0);
                })
                ->leftJoin($prefix.'product_attribute_combination as attribute_combination', 'attribute_combination.id_product_attribute', '=', 'attribute.id_product_attribute')
                ->leftJoin($prefix.'attribute_lang as attribute_lang', function ($join) {
                    $join->on('attribute_lang.id_attribute', '=', 'attribute_combination.id_attribute')
                        ->where('attribute_lang.id_lang', '=', 1);
                })
                ->where('attribute.id_product', $productId)
                ->groupBy('attribute.id_product_attribute', 'attribute.reference', 'attribute.ean13', 'attribute.wholesale_price', 'attribute.price', 'custom_attribute.location', 'custom_attribute.stock_arrive', 'custom_attribute.wholesale_price_base_currency', 'custom_attribute.price_base_currency', 'stock.quantity', 'product.reference', 'product.ean13', 'product.location', 'product.wholesale_price', 'product.price', 'custom_product.stock_arrive', 'custom_product.wholesale_price_base_currency', 'custom_product.price_base_currency')
                ->selectRaw("'Combination' as relationship, attribute.id_product_attribute as relationship_id, attribute.id_product as product_id, attribute.id_product_attribute as product_attribute_id, COALESCE(NULLIF(attribute.reference, ''), NULLIF(product.reference, ''), CAST(product.id_product AS CHAR)) as reference, {$attributeNames}, COALESCE(NULLIF(attribute.ean13, ''), product.ean13, '') as barcode, COALESCE(NULLIF(custom_attribute.location, ''), product.location, '') as housing, COALESCE(stock.quantity, 0) as stock_qty, COALESCE(custom_attribute.stock_arrive, custom_product.stock_arrive, 0) as stock_arrive, attribute.wholesale_price as purchase_eur, (product.price + attribute.price) as sales_eur, COALESCE(custom_attribute.wholesale_price_base_currency, custom_product.wholesale_price_base_currency, 0) as purchase_supplier, (COALESCE(custom_product.price_base_currency, 0) + COALESCE(custom_attribute.price_base_currency, 0)) as sales_supplier")
                ->get();
        }

        $packs = $connection->table($prefix.'pack as pack')
            ->join($prefix.'product as product', 'product.id_product', '=', 'pack.id_product_pack')
            ->leftJoin($prefix.'custom_product as custom_product', 'custom_product.id_product', '=', 'product.id_product')
            ->leftJoin($prefix.'stock_available as stock', function ($join) {
                $join->on('stock.id_product', '=', 'product.id_product')
                    ->where('stock.id_product_attribute', '=', 0)
                    ->where('stock.id_shop', '=', 0);
            })
            ->where('pack.id_product_item', $productId)
            ->when($attributeId > 0, function ($query) use ($attributeId) {
                $query->where(function ($component) use ($attributeId) {
                    $component->where('pack.id_product_attribute_item', '=', 0)
                        ->orWhere('pack.id_product_attribute_item', '=', $attributeId);
                });
            })
            ->groupBy('product.id_product', 'product.reference', 'product.ean13', 'product.location', 'product.wholesale_price', 'product.price', 'custom_product.stock_arrive', 'custom_product.wholesale_price_base_currency', 'custom_product.price_base_currency', 'stock.quantity')
            ->selectRaw("'Pack' as relationship, product.id_product as relationship_id, product.id_product as product_id, 0 as product_attribute_id, COALESCE(NULLIF(product.reference, ''), CAST(product.id_product AS CHAR)) as reference, '' as attributes, COALESCE(product.ean13, '') as barcode, COALESCE(product.location, '') as housing, COALESCE(stock.quantity, 0) as stock_qty, COALESCE(custom_product.stock_arrive, 0) as stock_arrive, product.wholesale_price as purchase_eur, product.price as sales_eur, COALESCE(custom_product.wholesale_price_base_currency, 0) as purchase_supplier, COALESCE(custom_product.price_base_currency, 0) as sales_supplier")
            ->get();

        return $siblings->concat($packs)
            ->unique(fn ($row) => $row->relationship.':'.$row->relationship_id)
            ->values();
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
