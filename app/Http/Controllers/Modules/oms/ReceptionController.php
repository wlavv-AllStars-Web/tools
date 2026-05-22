<?php

namespace App\Http\Controllers\Modules\oms;

use App\Http\Controllers\Controller;
use App\Models\modules\oms\BilledOrder;
use App\Models\modules\oms\Reception;
use App\Models\modules\oms\SupplierInvoice;
use App\Services\oms\ExportService;
use App\Services\oms\ReceptionHistoryService;
use App\Services\oms\StockArriveService;
use App\Services\oms\SupplierInvoiceWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceptionController extends Controller
{
    public function __construct(
        protected ReceptionHistoryService $receptionHistoryService,
        protected ExportService $exportService,
        protected StockArriveService $stockArriveService,
        protected SupplierInvoiceWorkflowService $supplierInvoiceWorkflowService
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $prefix = $this->psPrefix();

        $billedOrders = BilledOrder::query()
            ->with(['invoice', 'orderNote', 'lines'])
            ->orderByDesc('created_at')
            ->get();

        $lineIds = $billedOrders->flatMap(fn ($order) => $order->lines->pluck('id'))->filter()->values();

        $receivedRows = DB::table('oms_reception_lines')
            ->select('billed_order_line_id', DB::raw('SUM(qty_received) as qty_received'), DB::raw('COUNT(*) as receptions_count'))
            ->when($lineIds->isNotEmpty(), fn ($q) => $q->whereIn('billed_order_line_id', $lineIds->all()))
            ->groupBy('billed_order_line_id')
            ->get()
            ->keyBy('billed_order_line_id');

        $productIds = $billedOrders->flatMap(fn ($order) => $order->lines->pluck('product_id'))->filter()->unique()->values();
        $attributeIds = $billedOrders->flatMap(fn ($order) => $order->lines->pluck('product_attribute_id'))->filter()->unique()->values();
        $supplierIds = $billedOrders->map(fn ($order) => (int) data_get($order, 'orderNote.supplier_id'))->filter()->unique()->values();

        $productRows = collect();
        $attributeRows = collect();
        $stockRows = collect();
        $supplierRows = collect();

        if ($productIds->isNotEmpty()) {
            $productRows = DB::connection('mysql2')
                ->table($prefix . 'product as p')
                ->leftJoin($prefix . 'product_lang as pl', function ($join) {
                    $join->on('pl.id_product', '=', 'p.id_product')
                        ->where('pl.id_lang', '=', 1);
                })
                ->whereIn('p.id_product', $productIds->all())
                ->select('p.id_product', 'p.reference', 'p.ean13', 'pl.name')
                ->get()
                ->keyBy('id_product');

            $stockRows = DB::connection('mysql2')
                ->table($prefix . 'stock_available')
                ->whereIn('id_product', $productIds->all())
                ->select('id_product', 'id_product_attribute', 'quantity')
                ->groupBy('id_product', 'id_product_attribute')
                ->get()
                ->keyBy(fn ($row) => ((int) $row->id_product) . ':' . ((int) $row->id_product_attribute));
        }

        if ($attributeIds->isNotEmpty()) {
            $attributeRows = DB::connection('mysql2')
                ->table($prefix . 'product_attribute as pa')
                ->whereIn('pa.id_product_attribute', $attributeIds->all())
                ->select('pa.id_product_attribute', 'pa.id_product', DB::raw('pa.reference as attribute_reference'), DB::raw('pa.ean13 as attribute_ean13'))
                ->get()
                ->keyBy('id_product_attribute');
        }

        if ($supplierIds->isNotEmpty()) {
            $supplierRows = DB::connection('mysql2')
                ->table($prefix . 'supplier')
                ->whereIn('id_supplier', $supplierIds->all())
                ->select('id_supplier', 'name')
                ->get()
                ->keyBy('id_supplier');
        }

        $workOrders = $billedOrders->map(function ($order) use ($receivedRows, $productRows, $attributeRows, $stockRows, $supplierRows) {
            $invoiceReference = trim((string) data_get($order, 'invoice.invoice_reference', ''));
            $invoiceDate = data_get($order, 'invoice.invoice_date');
            $supplierId = (int) data_get($order, 'orderNote.supplier_id', 0);
            $supplierName = trim((string) data_get($supplierRows->get($supplierId), 'name', ''));
            $orderCreatedAt = optional($order->created_at)->format('Y-m-d H:i');
            $invoiceDateFormatted = $invoiceDate ? date('Y-m-d', strtotime((string) $invoiceDate)) : null;

            $lines = collect($order->lines)->map(function ($line) use ($receivedRows, $productRows, $attributeRows, $stockRows) {
                $productId = (int) ($line->product_id ?? 0);
                $attributeId = (int) ($line->product_attribute_id ?? 0);
                $receivedRow = $receivedRows->get($line->id);
                $qtyReceived = (int) ($receivedRow->qty_received ?? $line->qty_received ?? 0);
                $qtyBilled = (int) ($line->qty_billed ?? 0);
                $qtyRemaining = max(0, $qtyBilled - $qtyReceived);
                $productRow = $productRows->get($productId);
                $attributeRow = $attributeId > 0 ? $attributeRows->get($attributeId) : null;
                $reference = trim((string) ($line->reference ?? ''));
                if ($reference === '') {
                    $reference = trim((string) data_get($attributeRow, 'attribute_reference', data_get($productRow, 'reference', '')));
                }
                $ean13 = trim((string) data_get($attributeRow, 'attribute_ean13', data_get($productRow, 'ean13', '')));
                $name = trim((string) ($line->product_name ?? ''));
                if ($name === '') {
                    $name = trim((string) data_get($productRow, 'name', 'Product'));
                }
                $currentStock = (int) data_get($stockRows->get($productId . ':' . $attributeId), 'quantity', 0);
                $searchBlob = strtolower(implode(' ', array_filter([$reference, $ean13, $name, $productId, $attributeId])));

                return [
                    'id' => (int) $line->id,
                    'product_id' => $productId,
                    'product_attribute_id' => $attributeId,
                    'reference' => $reference !== '' ? $reference : '-',
                    'ean13' => $ean13 !== '' ? $ean13 : '-',
                    'product_name' => $name !== '' ? $name : 'Product',
                    'qty_billed' => $qtyBilled,
                    'qty_received' => $qtyReceived,
                    'qty_remaining' => $qtyRemaining,
                    'current_stock' => $currentStock,
                    'receptions_count' => (int) ($receivedRow->receptions_count ?? 0),
                    'search_blob' => $searchBlob,
                ];
            })->filter(fn ($line) => $line['qty_remaining'] > 0)->values();

            return [
                'id' => (int) $order->id,
                'reference' => (string) ($order->reference ?? ('BILLED-' . $order->id)),
                'supplier_name' => $supplierName !== '' ? $supplierName : ($supplierId > 0 ? 'Supplier #' . $supplierId : '-'),
                'invoice_reference' => $invoiceReference !== '' ? $invoiceReference : '-',
                'invoice_date' => $invoiceDateFormatted ?: '-',
                'created_at' => $orderCreatedAt ?: '-',
                'lines' => $lines,
                'pending_lines_count' => $lines->count(),
                'total_billed' => (int) $lines->sum('qty_billed'),
                'total_received' => (int) $lines->sum('qty_received'),
                'total_remaining' => (int) $lines->sum('qty_remaining'),
                'search_blob' => strtolower(implode(' ', array_filter([
                    $order->reference,
                    $invoiceReference,
                    $supplierName,
                    $orderCreatedAt,
                    $invoiceDateFormatted,
                    $lines->pluck('search_blob')->implode(' '),
                ]))),
            ];
        })->filter(fn ($order) => $order['pending_lines_count'] > 0)->values();

        $selectedBilledOrderId = (int) ($request->integer('billed_order_id') ?: data_get($workOrders->first(), 'id', 0));

        return view('modules.oms.receptions.index', compact('workOrders', 'selectedBilledOrderId'));
    }

    public function store(Request $request, BilledOrder $billedOrder)
    {
        $data = $request->validate([
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.billed_order_line_id' => ['required', 'integer'],
            'lines.*.qty_received' => ['required', 'integer', 'min:0'],
        ]);

        $requestedLines = collect($data['lines'])
            ->map(fn ($line) => [
                'billed_order_line_id' => (int) ($line['billed_order_line_id'] ?? 0),
                'qty_received' => (int) ($line['qty_received'] ?? 0),
            ])
            ->filter(fn ($line) => $line['billed_order_line_id'] > 0 && $line['qty_received'] > 0)
            ->values();

        if ($requestedLines->isEmpty()) {
            return back()->with('error', 'Please enter at least one qty to receive.');
        }

        $billedOrder->loadMissing(['invoice', 'orderNote', 'lines']);
        $lineModels = $billedOrder->lines()->whereIn('id', $requestedLines->pluck('billed_order_line_id')->all())->get()->keyBy('id');

        foreach ($requestedLines as $lineData) {
            $line = $lineModels->get($lineData['billed_order_line_id']);
            if (!$line) {
                return back()->with('error', 'Invalid billed order line selected.');
            }
            $alreadyReceived = (int) DB::table('oms_reception_lines')->where('billed_order_line_id', $line->id)->sum('qty_received');
            $remaining = max(0, (int) $line->qty_billed - $alreadyReceived);
            if ($lineData['qty_received'] > $remaining) {
                return back()->with('error', 'Qty to receive cannot be greater than remaining qty.');
            }
        }

        DB::transaction(function () use ($requestedLines, $billedOrder, $lineModels) {
            $reception = Reception::create([
                'billed_order_id' => $billedOrder->id,
                'created_by' => Auth::id(),
            ]);

            $userSnapshot = $this->getUserSnapshot();
            $orderNoteId = (int) data_get($billedOrder, 'orderNote.id', data_get($billedOrder, 'order_note_id', 0));
            $supplierInvoiceId = (int) data_get($billedOrder, 'invoice.id', data_get($billedOrder, 'supplier_invoice_id', 0));

            foreach ($requestedLines as $lineData) {
                $line = $lineModels->get($lineData['billed_order_line_id']);
                $qty = (int) $lineData['qty_received'];

                DB::table('oms_reception_lines')->insert([
                    'reception_id' => $reception->id,
                    'billed_order_line_id' => (int) $line->id,
                    'qty_received' => $qty,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('oms_billed_order_lines')
                    ->where('id', $line->id)
                    ->update([
                        'qty_received' => DB::raw('COALESCE(qty_received,0) + ' . $qty),
                        'updated_at' => now(),
                    ]);

                $productId = (int) $line->product_id;
                $productAttributeId = (int) ($line->product_attribute_id ?? 0);

                $stockBefore = $this->getPrestashopQuantity($productId, $productAttributeId);
                $arriveBefore = $this->getPrestashopStockArrive($productId, $productAttributeId);

                $this->incrementPrestashopStock($productId, $productAttributeId, $qty);

                /*
                |--------------------------------------------------------------------------
                | Stock arrive update
                |--------------------------------------------------------------------------
                | Receiving stock must also reduce the custom stock_arrive value.
                | These fields live in ps_custom_product / ps_custom_product_attribute,
                | not in ps_stock_available.
                */
                $this->decreaseCustomStockArrive($productId, $productAttributeId, $qty);

                $stockAfter = $this->getPrestashopQuantity($productId, $productAttributeId);
                $arriveAfter = $this->getPrestashopStockArrive($productId, $productAttributeId);
                $referenceSnapshot = $this->getProductReferenceSnapshot($productId, $productAttributeId);

                DB::table('oms_stock_history')->insert([
                    'source_type' => 'reception_line',
                    'source_id' => (int) $line->id,
                    'order_note_id' => $orderNoteId > 0 ? $orderNoteId : null,
                    'billed_order_id' => (int) $billedOrder->id,
                    'supplier_invoice_id' => $supplierInvoiceId > 0 ? $supplierInvoiceId : null,
                    'reception_id' => (int) $reception->id,
                    'product_id' => $productId,
                    'product_attribute_id' => $productAttributeId,
                    'product_reference_snapshot' => $referenceSnapshot['product_reference_snapshot'],
                    'attribute_reference_snapshot' => $referenceSnapshot['attribute_reference_snapshot'],
                    'display_reference_snapshot' => $referenceSnapshot['display_reference_snapshot'],
                    'ps_quantity_before' => $stockBefore,
                    'ps_quantity_delta' => $qty,
                    'ps_quantity_after' => $stockAfter,
                    'ps_quantity_arrive_before' => $arriveBefore,
                    'ps_quantity_arrive_delta' => -$qty,
                    'ps_quantity_arrive_after' => $arriveAfter,
                    'user_id' => $userSnapshot['user_id'],
                    'user_name_snapshot' => $userSnapshot['user_name_snapshot'],
                    'user_email_snapshot' => $userSnapshot['user_email_snapshot'],
                    'created_at' => now(),
                ]);
            }

            if ($billedOrder->orderNote) {
                $this->supplierInvoiceWorkflowService->refreshOrderNoteStatus(
                    $billedOrder->orderNote->fresh(['lines', 'billedOrders'])
                );
            }
        });

        return redirect()->route('erp.oms.receptions.index', ['billed_order_id' => $billedOrder->id])->with('success', 'Reception registered successfully.');
    }

    public function history(BilledOrder $billedOrder)
    {
        $rows = $this->receptionHistoryService->getByBilledOrder((int) $billedOrder->id);
        return view('modules.oms.receptions.history', compact('billedOrder', 'rows'));
    }

    public function invoiceHistory(SupplierInvoice $invoice)
    {
        $rows = $this->receptionHistoryService->getByInvoice((int) $invoice->id);
        return view('modules.oms.receptions.invoice_history', compact('invoice', 'rows'));
    }

    public function exportCsv(Request $request)
    {
        $rows = $this->receptionHistoryService->getFlatExportRows($request->all());

        return $this->exportService->streamCsv(
            'oms-receptions.csv',
            ['reception_id','created_at','created_by','supplier_id','order_note_reference','billed_order_reference','invoice_reference','product_id','product_attribute_id','qty_received'],
            $rows
        );
    }

    protected function getProductReferenceSnapshot(int $productId, int $productAttributeId): array
    {
        $prefix = $this->psPrefix();

        $product = DB::connection('mysql2')
            ->table($prefix . 'product')
            ->where('id_product', $productId)
            ->select('reference')
            ->first();

        $attribute = null;

        if ($productAttributeId > 0) {
            $attribute = DB::connection('mysql2')
                ->table($prefix . 'product_attribute')
                ->where('id_product_attribute', $productAttributeId)
                ->select('reference')
                ->first();
        }

        $productReference = trim((string) ($product->reference ?? ''));
        $attributeReference = trim((string) ($attribute->reference ?? ''));

        $productReference = $productReference !== '' ? $productReference : null;
        $attributeReference = $attributeReference !== '' ? $attributeReference : null;

        return [
            'product_reference_snapshot' => $productReference,
            'attribute_reference_snapshot' => $attributeReference,
            'display_reference_snapshot' => $attributeReference ?: $productReference,
        ];
    }

    protected function getUserSnapshot(): array
    {
        $user = Auth::user();

        return [
            'user_id' => $user?->id,
            'user_name_snapshot' => $user?->name,
            'user_email_snapshot' => $user?->email,
        ];
    }

    protected function getPrestashopQuantity(int $productId, int $productAttributeId): int
    {
        return (int) DB::connection('mysql2')
            ->table($this->psPrefix() . 'stock_available')
            ->where('id_product', $productId)
            ->where('id_product_attribute', $productAttributeId)
            ->value('quantity');
    }

    protected function getPrestashopStockArrive(int $productId, int $productAttributeId): int
    {
        $prefix = $this->psPrefix();

        if ($productAttributeId > 0) {
            return (int) DB::connection('mysql2')
                ->table($prefix . 'custom_product_attribute')
                ->where('id_product_attribute', $productAttributeId)
                ->value('stock_arrive');
        }

        return (int) DB::connection('mysql2')
            ->table($prefix . 'custom_product')
            ->where('id_product', $productId)
            ->value('stock_arrive');
    }

    protected function incrementPrestashopStock(int $productId, int $productAttributeId, int $qty): void
    {
        $prefix = $this->psPrefix();

        foreach ($this->stockTargetsForReference($productId, $productAttributeId) as $target) {
            $query = DB::connection('mysql2')->table($prefix . 'stock_available')
                ->where('id_product', (int) $target->id_product)
                ->where('id_product_attribute', (int) $target->id_product_attribute);

            if ($query->exists()) {
                $query->increment('quantity', $qty);
            }
        }
    }

    protected function stockTargetsForReference(int $productId, int $productAttributeId)
    {
        $prefix = $this->psPrefix();

        if ($productAttributeId > 0) {
            $reference = trim((string) DB::connection('mysql2')
                ->table($prefix . 'product_attribute')
                ->where('id_product_attribute', $productAttributeId)
                ->value('reference'));

            if ($reference === '') {
                return collect([(object) [
                    'id_product' => $productId,
                    'id_product_attribute' => $productAttributeId,
                ]]);
            }

            return DB::connection('mysql2')
                ->table($prefix . 'product_attribute')
                ->where('reference', $reference)
                ->get(['id_product', 'id_product_attribute'])
                ->map(fn ($row) => (object) [
                    'id_product' => (int) $row->id_product,
                    'id_product_attribute' => (int) $row->id_product_attribute,
                ])
                ->unique(fn ($row) => $row->id_product . ':' . $row->id_product_attribute)
                ->values();
        }

        $reference = trim((string) DB::connection('mysql2')
            ->table($prefix . 'product')
            ->where('id_product', $productId)
            ->value('reference'));

        if ($reference === '') {
            return collect([(object) [
                'id_product' => $productId,
                'id_product_attribute' => 0,
            ]]);
        }

        return DB::connection('mysql2')
            ->table($prefix . 'product')
            ->where('reference', $reference)
            ->get(['id_product'])
            ->map(fn ($row) => (object) [
                'id_product' => (int) $row->id_product,
                'id_product_attribute' => 0,
            ])
            ->unique(fn ($row) => $row->id_product . ':0')
            ->values();
    }

    protected function decreaseCustomStockArrive(int $productId, int $productAttributeId, int $qty): void
    {
        if ($qty <= 0) {
            return;
        }

        $prefix = $this->psPrefix();

        /*
        |--------------------------------------------------------------------------
        | Base product custom row
        |--------------------------------------------------------------------------
        */
        $productQuery = DB::connection('mysql2')
            ->table($prefix . 'custom_product')
            ->where('id_product', $productId);

        if ($productQuery->exists()) {
            $productQuery->update([
                'stock_arrive' => DB::raw('COALESCE(stock_arrive, 0) - ' . (int) $qty),
            ]);
        } else {
            DB::connection('mysql2')
                ->table($prefix . 'custom_product')
                ->insert([
                    'id_product' => $productId,
                    'stock_arrive' => -1 * (int) $qty,
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Attribute custom row
        |--------------------------------------------------------------------------
        */
        if ($productAttributeId > 0) {
            $attributeQuery = DB::connection('mysql2')
                ->table($prefix . 'custom_product_attribute')
                ->where('id_product_attribute', $productAttributeId);

            if ($attributeQuery->exists()) {
                $attributeQuery->update([
                    'id_product' => $productId,
                    'stock_arrive' => DB::raw('COALESCE(stock_arrive, 0) - ' . (int) $qty),
                ]);
            } else {
                DB::connection('mysql2')
                    ->table($prefix . 'custom_product_attribute')
                    ->insert([
                        'id_product_attribute' => $productAttributeId,
                        'id_product' => $productId,
                        'stock_arrive' => -1 * (int) $qty,
                    ]);
            }
        }
    }

    protected function psPrefix(): string
    {
        return (string) (env('DB2_prefix') ?: env('DB2_DB_prefix') ?: 'ps_');
    }
}
