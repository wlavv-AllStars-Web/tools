<?php

namespace App\Http\Controllers\Modules\oms;

use App\Http\Controllers\Controller;
use App\Models\modules\oms\BilledOrder;
use App\Models\modules\oms\OrderNote;
use App\Models\modules\oms\OrderNoteLine;
use App\Models\modules\oms\SupplierInvoice;
use App\Services\oms\DocumentCommentService;
use App\Services\oms\DocumentLineNoteService;
use App\Services\oms\ExportService;
use App\Services\oms\OrderNoteLogisticsService;
use App\Services\oms\SupplierMapService;
use App\Services\oms\SupplierTermsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class OrderNoteController extends Controller
{
    public function __construct(
        protected DocumentCommentService $documentCommentService,
        protected DocumentLineNoteService $documentLineNoteService,
        protected ExportService $exportService,
        protected SupplierMapService $supplierMapService,
        protected SupplierTermsService $supplierTermsService,
        protected OrderNoteLogisticsService $orderNoteLogisticsService,
    ) {
        $this->middleware('auth');
    }

    public function index()
    {
        $orderNotes = OrderNote::with(['supplier'])->orderByDesc('created_at')->paginate(50);

        return view('modules.oms.order_notes.index', compact('orderNotes'));
    }

    public function create(Request $request)
    {
        $orderNoteId = (int) $request->integer('order_note_id');
        $orderNote = $orderNoteId > 0
            ? OrderNote::with(['supplier', 'lines', 'billedOrders'])->find($orderNoteId)
            : null;

        $supplierTab = 'all';

        $selectedSupplierId = $orderNote?->supplier_id ? (int) $orderNote->supplier_id : (int) $request->integer('supplier_id');
        $supplierSidebar = $this->getSupplierSidebar($supplierTab);
        $selectedSupplier = $selectedSupplierId > 0
            ? $supplierSidebar->firstWhere('supplier_id', $selectedSupplierId)
            : null;

        $supplierMap = $selectedSupplierId > 0
            ? $this->supplierMapService->getSummaryBySupplierId($selectedSupplierId)
            : null;

        $termsSummary = null;
        $orderAmount = 0.0;

        $builderProducts = collect();
        $builderLines = collect();
        $builderSummary = [
            'lines_count' => 0,
            'total_qty' => 0,
            'total_billed' => 0,
            'total_received' => 0,
        ];
        $logisticsSummary = $this->orderNoteLogisticsService->buildEmptySummary();

        if ($orderNote) {
            $builderLines = $this->getOrderNoteProductsForBuilder((int) $orderNote->id);
            $builderSummary = $this->getOrderNoteSummary($orderNote, $builderLines);
            $orderAmount = $this->getOrderNoteAmountFromBuilderLines($builderLines);
            $termsSummary = $this->supplierTermsService->buildProgressSummary((int) $orderNote->supplier_id, $orderAmount);
            $logisticsSummary = $this->orderNoteLogisticsService->buildSummaryFromBuilderLines($builderLines);

            $search = trim((string) $request->get('q', ''));
            $builderProducts = mb_strlen($search) >= 2
                ? $this->getSupplierProductsForBuilder((int) $orderNote->supplier_id, (int) $orderNote->id, $search)
                : collect();
        }

        return view('modules.oms.order_notes.create', compact(
            'orderNote',
            'supplierTab',
            'supplierSidebar',
            'selectedSupplierId',
            'selectedSupplier',
            'supplierMap',
            'builderProducts',
            'builderLines',
            'builderSummary',
            'termsSummary',
            'orderAmount',
            'logisticsSummary'
        ));
    }

    public function createFromSupplier(int $supplierId): RedirectResponse
    {
        $reference = 'ON-' . now()->format('YmdHis');

        $orderNote = OrderNote::create([
            'supplier_id' => $supplierId,
            'reference' => $reference,
            'status' => 'order_note',
        ]);

        return redirect()->route('erp.oms.order_notes.create', [
            'order_note_id' => $orderNote->id,
            'supplier_id' => $supplierId,
        ])->with('success', 'Order note created successfully. You can now add products.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'integer'],
            'reference' => ['nullable', 'string', 'max:191'],
            'internal_note' => ['nullable', 'string'],
            'logistic_note' => ['nullable', 'string'],
        ]);

        $orderNote = OrderNote::create([
            'supplier_id' => (int) $data['supplier_id'],
            'reference' => !empty($data['reference']) ? $data['reference'] : 'ON-' . now()->format('YmdHis'),
            'status' => 'order_note',
            'internal_note' => $data['internal_note'] ?? null,
            'logistic_note' => $data['logistic_note'] ?? null,
        ]);

        return redirect()->route('erp.oms.order_notes.create', [
            'order_note_id' => $orderNote->id,
            'supplier_id' => $orderNote->supplier_id,
        ])->with('success', 'Order note created successfully.');
    }

    public function show(OrderNote $orderNote)
    {
        $orderNote->load(['supplier', 'lines', 'billedOrders']);

        $builderLines = $this->getOrderNoteProductsForBuilder((int) $orderNote->id);
        $orderAmount = $this->getOrderNoteAmountFromBuilderLines($builderLines);
        $termsSummary = $this->supplierTermsService->buildProgressSummary((int) $orderNote->supplier_id, $orderAmount);
        $logisticsSummary = $this->orderNoteLogisticsService->buildSummaryFromBuilderLines($builderLines);

        return view('modules.oms.order_notes.show', compact('orderNote', 'termsSummary', 'orderAmount', 'logisticsSummary'));
    }

    public function edit(OrderNote $orderNote)
    {
        return redirect()->route('erp.oms.order_notes.create', [
            'order_note_id' => $orderNote->id,
            'supplier_id' => $orderNote->supplier_id,
        ]);
    }

    public function update(Request $request, OrderNote $orderNote)
    {
        $data = $request->validate([
            'internal_note' => ['nullable', 'string'],
            'logistic_note' => ['nullable', 'string'],
        ]);

        $orderNote->update($data);

        return redirect()->route('erp.oms.order_notes.show', $orderNote)
            ->with('success', 'Order note updated successfully.');
    }

    public function destroy(OrderNote $orderNote)
    {
        $linesCount = method_exists($orderNote, 'lines') ? $orderNote->lines()->count() : 0;
        $billedOrdersCount = method_exists($orderNote, 'billedOrders') ? $orderNote->billedOrders()->count() : 0;

        if ($linesCount > 0 || $billedOrdersCount > 0 || $orderNote->status !== 'order_note') {
            return back()->with('error', 'This order note cannot be deleted.');
        }

        $orderNote->delete();

        return redirect()->route('erp.oms.dashboard')
            ->with('success', 'Order note deleted successfully.');
    }

    public function addLine(Request $request, OrderNote $orderNote)
    {
        if (!$this->canMutateLines($orderNote)) {
            return $this->lineMutationBlockedResponse($request, 'This order note can no longer be edited because billing already exists.');
        }

        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'product_attribute_id' => ['nullable', 'integer'],
            'qty_ordered' => ['required', 'integer', 'min:1'],
        ]);

        $productId = (int) $data['product_id'];
        $productAttributeId = !empty($data['product_attribute_id']) ? (int) $data['product_attribute_id'] : null;
        $qtyOrdered = (int) $data['qty_ordered'];

        $this->ensurePrestashopCustomProductRows($productId, $productAttributeId);

        $existingLine = OrderNoteLine::query()
            ->where('order_note_id', $orderNote->id)
            ->where('product_id', $productId)
            ->where(function ($query) use ($productAttributeId) {
                if ($productAttributeId === null) {
                    $query->whereNull('product_attribute_id');
                } else {
                    $query->where('product_attribute_id', $productAttributeId);
                }
            })
            ->first();

        if ($existingLine) {
            $existingLine->qty_ordered = (int) $existingLine->qty_ordered + $qtyOrdered;
            $existingLine->save();
        } else {
            OrderNoteLine::create([
                'order_note_id' => $orderNote->id,
                'product_id' => $productId,
                'product_attribute_id' => $productAttributeId,
                'qty_ordered' => $qtyOrdered,
            ]);
        }

        $this->adjustCustomStockArrive($productId, $productAttributeId, $qtyOrdered);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(array_merge([
                'success' => true,
                'message' => 'Product added successfully.',
            ], $this->buildBuilderPayload($orderNote, $request)));
        }

        return back()->with('success', 'Product line added successfully.');
    }

    public function updateLine(Request $request, OrderNote $orderNote, OrderNoteLine $line)
    {
        if ((int) $line->order_note_id !== (int) $orderNote->id) {
            abort(404);
        }

        if (!$this->canMutateLines($orderNote)) {
            return $this->lineMutationBlockedResponse($request, 'This order note can no longer be edited because billing already exists.');
        }

        $validated = $request->validate([
            'qty_ordered' => ['required', 'integer', 'min:0'],
        ]);

        $oldQtyOrdered = (int) $line->qty_ordered;
        $qtyOrdered = (int) $validated['qty_ordered'];
        $deltaStockArrive = $qtyOrdered - $oldQtyOrdered;

        if ($qtyOrdered <= 0) {
            $line->delete();
        } else {
            $line->qty_ordered = $qtyOrdered;
            $line->save();
        }

        $this->adjustCustomStockArrive(
            (int) $line->product_id,
            $line->product_attribute_id ? (int) $line->product_attribute_id : null,
            $deltaStockArrive
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(array_merge([
                'success' => true,
                'message' => 'Order note line updated successfully.',
            ], $this->buildBuilderPayload($orderNote, $request)));
        }

        return back()->with('success', 'Order note line updated successfully.');
    }

    public function destroyLine(Request $request, OrderNote $orderNote, OrderNoteLine $line)
    {
        if ((int) $line->order_note_id !== (int) $orderNote->id) {
            abort(404);
        }

        if (!$this->canMutateLines($orderNote)) {
            return $this->lineMutationBlockedResponse($request, 'This order note can no longer be edited because billing already exists.');
        }

        $this->adjustCustomStockArrive(
            (int) $line->product_id,
            $line->product_attribute_id ? (int) $line->product_attribute_id : null,
            -1 * (int) $line->qty_ordered
        );

        $line->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(array_merge([
                'success' => true,
                'message' => 'Product removed from order note.',
            ], $this->buildBuilderPayload($orderNote, $request)));
        }

        return back()->with('success', 'Product removed from order note.');
    }

    public function supplierProducts(Request $request, OrderNote $orderNote): JsonResponse|string
    {
        $search = trim((string) $request->get('q', ''));
        $products = mb_strlen($search) >= 2
            ? $this->getSupplierProductsForBuilder((int) $orderNote->supplier_id, (int) $orderNote->id, $search)
            : collect();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'supplier_id' => $orderNote->supplier_id,
                'items' => $products,
            ]);
        }

        return view('modules.oms.order_notes.partials.builder_results', [
            'orderNote' => $orderNote,
            'products' => $products,
            'search' => $search,
        ])->render();
    }

    public function importCsvPreview(Request $request, OrderNote $orderNote): RedirectResponse
    {
        if (!$this->canMutateLines($orderNote)) {
            return back()->with('error', 'This order note can no longer be edited because billing already exists.');
        }

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $file = $request->file('csv_file');
        $parsed = $this->parseOrderNoteImportCsv($file->getRealPath(), (int) $orderNote->supplier_id);

        if (empty($parsed['rows'])) {
            return back()->with('error', 'The CSV file is empty or no usable rows were found.');
        }

        $request->session()->put($this->getCsvImportSessionKey((int) $orderNote->id), [
            'filename' => $file->getClientOriginalName(),
            'uploaded_at' => now()->format('Y-m-d H:i:s'),
            'delimiter' => $parsed['delimiter'],
            'rows' => $parsed['rows'],
            'summary' => $parsed['summary'],
        ]);

        return redirect()->route('erp.oms.order_notes.import.verify', $orderNote)
            ->with('success', 'CSV uploaded successfully. Please verify the lines before importing.');
    }

    public function importCsvVerify(Request $request, OrderNote $orderNote)
    {
        $preview = $request->session()->get($this->getCsvImportSessionKey((int) $orderNote->id));

        if (!$preview) {
            return redirect()->route('erp.oms.order_notes.show', $orderNote)
                ->with('warning', 'No CSV import preview found for this order note.');
        }

        return view('modules.oms.order_notes.import_verify', [
            'orderNote' => $orderNote->loadMissing(['supplier', 'lines', 'billedOrders']),
            'preview' => $preview,
            'canMutateLines' => $this->canMutateLines($orderNote),
        ]);
    }

    public function importCsvConfirm(Request $request, OrderNote $orderNote): RedirectResponse
    {
        if (!$this->canMutateLines($orderNote)) {
            return redirect()->route('erp.oms.order_notes.show', $orderNote)
                ->with('error', 'This order note can no longer be edited because billing already exists.');
        }

        $preview = $request->session()->get($this->getCsvImportSessionKey((int) $orderNote->id));

        if (!$preview || empty($preview['rows'])) {
            return redirect()->route('erp.oms.order_notes.show', $orderNote)
                ->with('warning', 'No CSV import preview found for this order note.');
        }

        $validRows = collect($preview['rows'])
            ->filter(fn ($row) => !empty($row['is_valid']) && !empty($row['product_id']) && (int) ($row['qty_ordered'] ?? 0) > 0);

        if ($validRows->isEmpty()) {
            return redirect()->route('erp.oms.order_notes.import.verify', $orderNote)
                ->with('error', 'There are no valid rows to import.');
        }

        $aggregated = [];
        foreach ($validRows as $row) {
            $key = (int) $row['product_id'] . ':' . (int) ($row['product_attribute_id'] ?? 0);
            if (!isset($aggregated[$key])) {
                $aggregated[$key] = [
                    'product_id' => (int) $row['product_id'],
                    'product_attribute_id' => !empty($row['product_attribute_id']) ? (int) $row['product_attribute_id'] : null,
                    'qty_ordered' => 0,
                ];
            }
            $aggregated[$key]['qty_ordered'] += (int) $row['qty_ordered'];
        }

        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($orderNote, $aggregated, &$created, &$updated) {
            foreach ($aggregated as $payload) {
                $this->ensurePrestashopCustomProductRows(
                    (int) $payload['product_id'],
                    !empty($payload['product_attribute_id']) ? (int) $payload['product_attribute_id'] : null
                );

                $existingLine = OrderNoteLine::query()
                    ->where('order_note_id', $orderNote->id)
                    ->where('product_id', $payload['product_id'])
                    ->where(function ($query) use ($payload) {
                        if ($payload['product_attribute_id'] === null) {
                            $query->whereNull('product_attribute_id');
                        } else {
                            $query->where('product_attribute_id', $payload['product_attribute_id']);
                        }
                    })
                    ->first();

                if ($existingLine) {
                    $existingLine->qty_ordered = (int) $existingLine->qty_ordered + (int) $payload['qty_ordered'];
                    $existingLine->save();
                    $updated++;
                } else {
                    OrderNoteLine::create([
                        'order_note_id' => $orderNote->id,
                        'product_id' => $payload['product_id'],
                        'product_attribute_id' => $payload['product_attribute_id'],
                        'qty_ordered' => (int) $payload['qty_ordered'],
                    ]);
                    $created++;
                }

                $this->adjustCustomStockArrive(
                    (int) $payload['product_id'],
                    !empty($payload['product_attribute_id']) ? (int) $payload['product_attribute_id'] : null,
                    (int) $payload['qty_ordered']
                );
            }
        });

        $request->session()->forget($this->getCsvImportSessionKey((int) $orderNote->id));

        return redirect()->route('erp.oms.order_notes.show', $orderNote)
            ->with('success', 'CSV import completed successfully. Created lines: ' . $created . '. Updated lines: ' . $updated . '.');
    }

    protected function getCsvImportSessionKey(int $orderNoteId): string
    {
        return 'oms.order_note_import_preview.' . $orderNoteId;
    }

    protected function parseOrderNoteImportCsv(string $filePath, int $supplierId): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ['delimiter' => ',', 'rows' => [], 'summary' => []];
        }

        $firstDataLine = '';
        while (($line = fgets($handle)) !== false) {
            if (trim((string) $line) !== '') {
                $firstDataLine = $line;
                break;
            }
        }
        rewind($handle);

        $delimiter = $this->detectCsvDelimiter($firstDataLine);
        $header = fgetcsv($handle, 0, $delimiter) ?: [];
        $header = array_map(fn ($value) => $this->normalizeCsvHeader((string) $value), $header);

        $rows = [];
        $seenKeys = [];
        $rowNumber = 1;

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;

            if ($this->csvRowIsEmpty($data)) {
                continue;
            }

            $rawRow = [];
            foreach ($header as $index => $key) {
                $rawRow[$key ?: ('column_' . $index)] = isset($data[$index]) ? trim((string) $data[$index]) : null;
            }

            $resolved = $this->resolveOrderNoteImportRow($rawRow, $supplierId);
            $lineKey = (int) ($resolved['product_id'] ?? 0) . ':' . (int) ($resolved['product_attribute_id'] ?? 0);
            $duplicateInFile = !empty($resolved['is_valid']) && isset($seenKeys[$lineKey]);

            if (!empty($resolved['is_valid'])) {
                $seenKeys[$lineKey] = true;
            }

            $rows[] = array_merge($resolved, [
                'row_number' => $rowNumber,
                'duplicate_in_file' => $duplicateInFile,
                'status_label' => !$resolved['is_valid'] ? 'Invalid' : ($duplicateInFile ? 'Duplicate' : 'Ready'),
                'status_class' => !$resolved['is_valid'] ? 'danger' : ($duplicateInFile ? 'warning' : 'success'),
                'message' => $duplicateInFile
                    ? trim(($resolved['message'] ?? 'Matched successfully.') . ' Duplicate combination in CSV; quantities will be merged on import.')
                    : ($resolved['message'] ?? 'Matched successfully.'),
            ]);
        }

        fclose($handle);

        return [
            'delimiter' => $delimiter,
            'rows' => $rows,
            'summary' => [
                'total_rows' => count($rows),
                'valid_rows' => collect($rows)->where('is_valid', true)->count(),
                'invalid_rows' => collect($rows)->where('is_valid', false)->count(),
                'duplicate_rows' => collect($rows)->where('duplicate_in_file', true)->count(),
                'total_qty' => (int) collect($rows)->where('is_valid', true)->sum('qty_ordered'),
            ],
        ];
    }

    protected function resolveOrderNoteImportRow(array $row, int $supplierId): array
    {
        $reference = $this->csvString($row, ['reference', 'sku', 'product_reference', 'attribute_reference', 'supplier_reference', 'ref']);
        $qtyOrdered = $this->csvInt($row, ['qty_ordered', 'qty', 'quantity', 'ordered', 'qtd']);

        $base = [
            'input_reference' => $reference,
            'input_product_id' => null,
            'input_product_attribute_id' => null,
            'qty_ordered' => $qtyOrdered,
            'product_id' => null,
            'product_attribute_id' => null,
            'resolved_reference' => null,
            'resolved_name' => null,
            'is_valid' => false,
            'message' => null,
        ];

        if ($reference === '') {
            $base['message'] = 'Missing reference. The CSV must contain a reference column.';
            return $base;
        }

        if ($qtyOrdered <= 0) {
            $base['message'] = 'Quantity must be greater than zero.';
            return $base;
        }

        $matched = $this->findSupplierProductByReference($supplierId, $reference);
        if (!$matched) {
            $base['message'] = 'Reference not found for this supplier.';
            return $base;
        }

        $base['product_id'] = (int) $matched->product_id;
        $base['product_attribute_id'] = !empty($matched->product_attribute_id) ? (int) $matched->product_attribute_id : 0;
        $base['resolved_reference'] = (string) ($matched->resolved_reference ?? '');
        $base['resolved_name'] = (string) ($matched->display_name ?? ('Product #' . $matched->product_id));
        $base['is_valid'] = true;
        $base['message'] = $base['product_attribute_id'] > 0
            ? 'Matched successfully to product attribute reference.'
            : 'Matched successfully to main product reference.';

        return $base;
    }

    protected function findSupplierProductByProductId(int $supplierId, int $productId): ?object
    {
        return DB::connection('mysql2')
            ->table('ps_product as p')
            ->leftJoin('ps_product_lang as pl', function ($join) {
                $join->on('pl.id_product', '=', 'p.id_product')
                    ->where('pl.id_lang', '=', 1)
                    ->where('pl.id_shop', '=', 1);
            })
            ->where('p.id_supplier', $supplierId)
            ->where('p.id_product', $productId)
            ->selectRaw('p.id_product as product_id, 0 as product_attribute_id, COALESCE(NULLIF(p.reference, ""), NULLIF(p.supplier_reference, ""), CAST(p.id_product as CHAR)) as resolved_reference, COALESCE(NULLIF(pl.name, ""), CONCAT("Product #", p.id_product)) as display_name')
            ->first();
    }

    protected function findSupplierProductByAttributeId(int $supplierId, int $productAttributeId): ?object
    {
        return DB::connection('mysql2')
            ->table('ps_product_attribute as pa')
            ->join('ps_product as p', 'p.id_product', '=', 'pa.id_product')
            ->leftJoin('ps_product_lang as pl', function ($join) {
                $join->on('pl.id_product', '=', 'p.id_product')
                    ->where('pl.id_lang', '=', 1)
                    ->where('pl.id_shop', '=', 1);
            })
            ->where('p.id_supplier', $supplierId)
            ->where('pa.id_product_attribute', $productAttributeId)
            ->selectRaw('p.id_product as product_id, pa.id_product_attribute as product_attribute_id, COALESCE(NULLIF(pa.reference, ""), NULLIF(p.reference, ""), NULLIF(p.supplier_reference, ""), CAST(p.id_product as CHAR)) as resolved_reference, COALESCE(NULLIF(pl.name, ""), CONCAT("Product #", p.id_product)) as display_name')
            ->first();
    }

    protected function findSupplierProductByReference(int $supplierId, string $reference): ?object
    {
        $reference = trim($reference);
        if ($reference === '') {
            return null;
        }

        $normalizedReference = mb_strtolower($reference);

        $attributeMatch = DB::connection('mysql2')
            ->table('ps_product_attribute as pa')
            ->join('ps_product as p', 'p.id_product', '=', 'pa.id_product')
            ->leftJoin('ps_product_lang as pl', function ($join) {
                $join->on('pl.id_product', '=', 'p.id_product')
                    ->where('pl.id_lang', '=', 1)
                    ->where('pl.id_shop', '=', 1);
            })
            ->leftJoin('ps_product_supplier as ps_attr', function ($join) use ($supplierId) {
                $join->on('ps_attr.id_product', '=', 'pa.id_product')
                    ->on('ps_attr.id_product_attribute', '=', 'pa.id_product_attribute')
                    ->where('ps_attr.id_supplier', '=', $supplierId);
            })
            ->where(function ($query) use ($supplierId) {
                $query->where('p.id_supplier', $supplierId)
                    ->orWhereNotNull('ps_attr.id_product_supplier');
            })
            ->where(function ($query) use ($normalizedReference) {
                $query->whereRaw('LOWER(TRIM(COALESCE(pa.reference, ""))) = ?', [$normalizedReference])
                    ->orWhereRaw('LOWER(TRIM(COALESCE(ps_attr.product_supplier_reference, ""))) = ?', [$normalizedReference]);
            })
            ->selectRaw('p.id_product as product_id, pa.id_product_attribute as product_attribute_id, COALESCE(NULLIF(pa.reference, ""), NULLIF(ps_attr.product_supplier_reference, ""), NULLIF(p.reference, ""), NULLIF(p.supplier_reference, ""), CAST(p.id_product as CHAR)) as resolved_reference, COALESCE(NULLIF(pl.name, ""), CONCAT("Product #", p.id_product)) as display_name')
            ->orderByRaw('CASE WHEN LOWER(TRIM(COALESCE(pa.reference, ""))) = ? THEN 0 WHEN LOWER(TRIM(COALESCE(ps_attr.product_supplier_reference, ""))) = ? THEN 1 ELSE 2 END', [$normalizedReference, $normalizedReference])
            ->first();

        if ($attributeMatch) {
            return $attributeMatch;
        }

        return DB::connection('mysql2')
            ->table('ps_product as p')
            ->leftJoin('ps_product_lang as pl', function ($join) {
                $join->on('pl.id_product', '=', 'p.id_product')
                    ->where('pl.id_lang', '=', 1)
                    ->where('pl.id_shop', '=', 1);
            })
            ->leftJoin('ps_product_supplier as ps_main', function ($join) use ($supplierId) {
                $join->on('ps_main.id_product', '=', 'p.id_product')
                    ->where('ps_main.id_product_attribute', '=', 0)
                    ->where('ps_main.id_supplier', '=', $supplierId);
            })
            ->where(function ($query) use ($supplierId) {
                $query->where('p.id_supplier', $supplierId)
                    ->orWhereNotNull('ps_main.id_product_supplier');
            })
            ->where(function ($query) use ($normalizedReference) {
                $query->whereRaw('LOWER(TRIM(COALESCE(p.reference, ""))) = ?', [$normalizedReference])
                    ->orWhereRaw('LOWER(TRIM(COALESCE(p.supplier_reference, ""))) = ?', [$normalizedReference])
                    ->orWhereRaw('LOWER(TRIM(COALESCE(ps_main.product_supplier_reference, ""))) = ?', [$normalizedReference]);
            })
            ->selectRaw('p.id_product as product_id, 0 as product_attribute_id, COALESCE(NULLIF(p.reference, ""), NULLIF(ps_main.product_supplier_reference, ""), NULLIF(p.supplier_reference, ""), CAST(p.id_product as CHAR)) as resolved_reference, COALESCE(NULLIF(pl.name, ""), CONCAT("Product #", p.id_product)) as display_name')
            ->orderByRaw('CASE WHEN LOWER(TRIM(COALESCE(p.reference, ""))) = ? THEN 0 WHEN LOWER(TRIM(COALESCE(ps_main.product_supplier_reference, ""))) = ? THEN 1 WHEN LOWER(TRIM(COALESCE(p.supplier_reference, ""))) = ? THEN 2 ELSE 3 END', [$normalizedReference, $normalizedReference, $normalizedReference])
            ->first();
    }

    protected function detectCsvDelimiter(string $line): string
    {
        $delimiters = [';' => substr_count($line, ';'), ',' => substr_count($line, ','), "\t" => substr_count($line, "\t")];
        arsort($delimiters);
        $delimiter = array_key_first($delimiters);
        return $delimiter === "\t" ? "\t" : $delimiter;
    }

    protected function normalizeCsvHeader(string $header): string
    {
        $header = mb_strtolower(trim($header));
        $header = preg_replace('/[^a-z0-9]+/i', '_', $header);
        return trim((string) $header, '_');
    }

    protected function csvRowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }
        return true;
    }

    protected function csvString(array $row, array $keys): string
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && trim((string) $row[$key]) !== '') {
                return trim((string) $row[$key]);
            }
        }
        return '';
    }

    protected function csvInt(array $row, array $keys): int
    {
        $value = $this->csvString($row, $keys);
        if ($value === '') {
            return 0;
        }

        $value = preg_replace('/[^0-9\-]/', '', $value);

        return is_numeric($value) ? (int) $value : 0;
    }

    public function saveNotes(Request $request, OrderNote $orderNote)
    {
        $data = $request->validate([
            'internal_note' => ['nullable', 'string'],
            'logistic_note' => ['nullable', 'string'],
        ]);

        $orderNote = $this->documentCommentService->saveOrderNoteNotes($orderNote, $data);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order note notes updated successfully.',
                'notes' => [
                    'internal_note' => $orderNote->internal_note,
                    'logistic_note' => $orderNote->logistic_note,
                    'has_any_note' => !empty($orderNote->internal_note) || !empty($orderNote->logistic_note),
                ],
            ]);
        }

        return back()->with('success', 'Order note notes updated successfully.');
    }

    public function saveLineNotes(Request $request, OrderNote $orderNote, OrderNoteLine $line)
    {
        $data = $request->validate([
            'warranty' => ['nullable', 'string'],
            'components' => ['nullable', 'string'],
            'replacement' => ['nullable', 'string'],
        ]);

        $this->documentLineNoteService->save('order_note', (int) $line->id, $data);
        $notes = $this->documentLineNoteService->getByLine('order_note', (int) $line->id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order note line notes updated successfully.',
                'notes' => $notes,
            ]);
        }

        return back()->with('success', 'Order note line notes updated successfully.');
    }

    public function exportCsv(OrderNote $orderNote)
    {
        $orderNote->load(['lines', 'supplier', 'billedOrders']);

        return $this->exportService->exportOrderNoteCsv($orderNote);
    }

    public function exportPdf(OrderNote $orderNote)
    {
        return back()->with('warning', 'PDF export is not implemented yet in this tranche.');
    }

    protected function buildBuilderPayload(OrderNote $orderNote, Request $request): array
    {
        $search = trim((string) $request->get('q', ''));
        $lines = $this->getOrderNoteProductsForBuilder((int) $orderNote->id);
        $summary = $this->getOrderNoteSummary($orderNote->fresh(['supplier', 'lines', 'billedOrders']), $lines);
        $products = mb_strlen($search) >= 2
            ? $this->getSupplierProductsForBuilder((int) $orderNote->supplier_id, (int) $orderNote->id, $search)
            : collect();
        $supplierMap = $this->supplierMapService->getSummaryBySupplierId((int) $orderNote->supplier_id);
        $orderAmount = $this->getOrderNoteAmountFromBuilderLines($lines);
        $termsSummary = $this->supplierTermsService->buildProgressSummary((int) $orderNote->supplier_id, $orderAmount);
        $logisticsSummary = $this->orderNoteLogisticsService->buildSummaryFromBuilderLines($lines);

        return [
            'products_html' => View::make('modules.oms.order_notes.partials.builder_results', [
                'orderNote' => $orderNote,
                'products' => $products,
                'search' => $search,
            ])->render(),
            'sidebar_html' => View::make('modules.oms.order_notes.partials.builder_sidebar', [
                'orderNote' => $orderNote,
                'builderLines' => $lines,
                'builderSummary' => $summary,
                'supplierMap' => $supplierMap,
                'termsSummary' => $termsSummary,
                'orderAmount' => $orderAmount,
                'supplierTermsService' => $this->supplierTermsService,
                'logisticsSummary' => $logisticsSummary,
            ])->render(),
        ];
    }

    protected function getSupplierSidebar(string $supplierTab): Collection
    {
        $supplierNames = DB::connection('mysql2')
            ->table('ps_supplier')
            ->select(['id_supplier as supplier_id', 'name as supplier_name'])
            ->orderBy('name')
            ->get()
            ->keyBy('supplier_id');

        $orderNoteStats = OrderNote::query()
            ->selectRaw('supplier_id, COUNT(*) as total')
            ->groupBy('supplier_id')
            ->pluck('total', 'supplier_id');

        $billedStats = BilledOrder::query()
            ->join('oms_order_notes as onote', 'onote.id', '=', 'oms_billed_orders.order_note_id')
            ->selectRaw('onote.supplier_id as supplier_id, COUNT(*) as total')
            ->groupBy('onote.supplier_id')
            ->pluck('total', 'supplier_id');

        $invoiceStats = SupplierInvoice::query()
            ->selectRaw('supplier_id, COUNT(*) as total')
            ->groupBy('supplier_id')
            ->pluck('total', 'supplier_id');

        return $supplierNames
            ->map(function ($supplier) use ($orderNoteStats, $billedStats, $invoiceStats) {
                $supplierId = (int) $supplier->supplier_id;
                return (object) [
                    'supplier_id' => $supplierId,
                    'supplier_name' => (string) $supplier->supplier_name,
                    'order_notes' => (int) ($orderNoteStats[$supplierId] ?? 0),
                    'billed_orders' => (int) ($billedStats[$supplierId] ?? 0),
                    'invoices' => (int) ($invoiceStats[$supplierId] ?? 0),
                ];
            })
            ->filter(function ($supplier) use ($supplierTab) {
                return match ($supplierTab) {
                    'order_notes' => $supplier->order_notes > 0,
                    'invoicing' => $supplier->billed_orders > 0 || $supplier->invoices > 0,
                    default => true,
                };
            })
            ->values();
    }

    protected function getSupplierProductsForBuilder(int $supplierId, int $orderNoteId, string $search): Collection
    {
        $searchLike = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';

        $addedKeys = OrderNoteLine::query()
            ->where('order_note_id', $orderNoteId)
            ->get(['product_id', 'product_attribute_id'])
            ->mapWithKeys(function (OrderNoteLine $line) {
                $key = (int) $line->product_id . ':' . (int) ($line->product_attribute_id ?? 0);
                return [$key => true];
            });

        $products = DB::connection('mysql2')
            ->table('ps_product as p')
            ->leftJoin('ps_product_lang as pl', function ($join) {
                $join->on('pl.id_product', '=', 'p.id_product')
                    ->where('pl.id_lang', '=', 1)
                    ->where('pl.id_shop', '=', 1);
            })
            ->leftJoin('ps_product_attribute as pa', 'pa.id_product', '=', 'p.id_product')
            ->selectRaw('
                p.id_product as product_id,
                pa.id_product_attribute as product_attribute_id,
                COALESCE(NULLIF(pa.reference, ""), NULLIF(p.reference, ""), NULLIF(p.supplier_reference, ""), CAST(p.id_product as CHAR)) as sku,
                COALESCE(NULLIF(pl.name, ""), NULLIF(p.reference, ""), CONCAT("Product #", p.id_product)) as display_name,
                p.id_supplier as supplier_id
            ')
            ->where('p.id_supplier', $supplierId)
            ->where(function ($query) use ($searchLike) {
                $query->where('p.reference', 'like', $searchLike)
                    ->orWhere('p.supplier_reference', 'like', $searchLike)
                    ->orWhere('pl.name', 'like', $searchLike)
                    ->orWhere('pa.reference', 'like', $searchLike)
                    ->orWhere('p.ean13', 'like', $searchLike)
                    ->orWhere('p.upc', 'like', $searchLike);
            })
            ->orderBy('pl.name')
            ->orderBy('p.reference')
            ->limit(120)
            ->get();

        return $products->map(function ($row) use ($addedKeys) {
            $row->product_id = (int) $row->product_id;
            $row->product_attribute_id = $row->product_attribute_id ? (int) $row->product_attribute_id : 0;
            $row->already_added = $addedKeys->has($row->product_id . ':' . $row->product_attribute_id) ? 1 : 0;
            $row->is_new_candidate = $row->already_added ? 0 : 1;
            return $row;
        });
    }

    protected function getOrderNoteProductsForBuilder(int $orderNoteId): Collection
    {
        $lines = OrderNoteLine::query()
            ->where('order_note_id', $orderNoteId)
            ->orderByDesc('id')
            ->get();

        if ($lines->isEmpty()) {
            return collect();
        }

        $productIds = $lines->pluck('product_id')->filter()->unique()->values();
        $attributeIds = $lines->pluck('product_attribute_id')->filter()->unique()->values();

        $products = DB::connection('mysql2')
            ->table('ps_product as p')
            ->leftJoin('ps_product_lang as pl', function ($join) {
                $join->on('pl.id_product', '=', 'p.id_product')
                    ->where('pl.id_lang', '=', 1)
                    ->where('pl.id_shop', '=', 1);
            })
            ->leftJoin('ps_stock_available as sa', function ($join) {
                $join->on('sa.id_product', '=', 'p.id_product')->where('sa.id_shop', 3)->whereNull('sa.id_product_attribute');
            })
            ->leftJoin('ps_custom_product as cp', 'cp.id_product', '=', 'p.id_product')
            ->whereIn('p.id_product', $productIds)
            ->selectRaw('p.id_product, p.reference, pl.name, COALESCE(sa.quantity, 0) as stock_qty, COALESCE(cp.wholesale_price_base_currency, p.wholesale_price, 0) as unit_cost, cp.wholesale_price_base_currency as wholesale_price_base_currency, p.wholesale_price as wholesale_price')
            ->get()
            ->keyBy('id_product');

        $attributes = $attributeIds->isNotEmpty()
            ? DB::connection('mysql2')
                ->table('ps_product_attribute as pa')
                ->leftJoin('ps_stock_available as sa', function ($join) {
                    $join->on('sa.id_product', '=', 'pa.id_product')->on('sa.id_product_attribute', '=', 'pa.id_product_attribute')->where('sa.id_shop', 3);
                })
                ->leftJoin('ps_custom_product_attribute as cpa', 'cpa.id_product_attribute', '=', 'pa.id_product_attribute')
                ->whereIn('pa.id_product_attribute', $attributeIds)
                ->selectRaw('pa.id_product_attribute, pa.id_product, pa.reference, COALESCE(sa.quantity, 0) as stock_qty, COALESCE(cpa.wholesale_price_base_currency, pa.wholesale_price, 0) as unit_cost, cpa.wholesale_price_base_currency as wholesale_price_base_currency, pa.wholesale_price as wholesale_price')
                ->get()
                ->keyBy('id_product_attribute')
            : collect();

        return $lines->map(function (OrderNoteLine $line) use ($products, $attributes) {
            $product = $products->get($line->product_id);
            $attribute = $line->product_attribute_id ? $attributes->get($line->product_attribute_id) : null;
            $sku = trim((string) ($attribute->reference ?? $product->reference ?? ''));
            $name = trim((string) ($product->name ?? ('Product #' . $line->product_id)));
            if ($line->product_attribute_id) {
                $name .= ' / Attr. #' . (int) $line->product_attribute_id;
            }

            $attributeBaseCurrency = $attribute ? (float) ($attribute->wholesale_price_base_currency ?? 0) : 0.0;
            $attributeWholesale = $attribute ? (float) ($attribute->wholesale_price ?? 0) : 0.0;
            $productBaseCurrency = (float) ($product->wholesale_price_base_currency ?? 0);
            $productWholesale = (float) ($product->wholesale_price ?? 0);
            $resolvedUnitCost = $attributeBaseCurrency > 0
                ? $attributeBaseCurrency
                : ($attributeWholesale > 0
                    ? $attributeWholesale
                    : ($productBaseCurrency > 0
                        ? $productBaseCurrency
                        : ($productWholesale > 0 ? $productWholesale : 0)));

            return (object) [
                'id' => (int) $line->id,
                'product_id' => (int) $line->product_id,
                'product_attribute_id' => $line->product_attribute_id ? (int) $line->product_attribute_id : 0,
                'sku' => $sku !== '' ? $sku : '—',
                'name' => $name,
                'qty_ordered' => (int) $line->qty_ordered,
                'qty_billed' => (int) $line->qty_billed_total,
                'qty_received' => (int) $line->qty_received_total,
                'stock_qty' => (int) ($attribute->stock_qty ?? $product->stock_qty ?? 0),
                'wholesale_price_base_currency' => $attributeBaseCurrency > 0 ? $attributeBaseCurrency : $productBaseCurrency,
                'wholesale_price' => $attributeWholesale > 0 ? $attributeWholesale : $productWholesale,
                'unit_cost' => (float) $resolvedUnitCost,
                'sold_12m' => 0,
                'suggested_qty' => 0,
                'search_text' => strtolower(implode(' ', array_filter([
                    $sku,
                    $name,
                    (string) $line->product_id,
                    $line->product_attribute_id ? (string) $line->product_attribute_id : null,
                ]))),
            ];
        });
    }

    protected function getOrderNoteSummary(OrderNote $orderNote, ?Collection $builderLines = null): array
    {
        $builderLines = $builderLines ?? $this->getOrderNoteProductsForBuilder((int) $orderNote->id);

        return [
            'lines_count' => (int) $builderLines->count(),
            'total_qty' => (int) $builderLines->sum('qty_ordered'),
            'total_billed' => (int) $builderLines->sum('qty_billed'),
            'total_received' => (int) $builderLines->sum('qty_received'),
            'reference' => (string) $orderNote->reference,
            'status' => (string) $orderNote->status,
            'created_at' => optional($orderNote->created_at)?->format('Y-m-d H:i'),
        ];
    }

    protected function canMutateLines(OrderNote $orderNote): bool
    {
        return $orderNote->status === 'order_note' && !$orderNote->billedOrders()->exists();
    }

    protected function lineMutationBlockedResponse(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return back()->with('error', $message);
    }
    
    protected function ensurePrestashopCustomProductRows(int $productId, ?int $productAttributeId = null): void
    {
        $prefix = $this->psPrefix();
        $productAttributeId = (int) ($productAttributeId ?? 0);

        $productExists = DB::connection('mysql2')
            ->table($prefix . 'product')
            ->where('id_product', $productId)
            ->exists();

        if (!$productExists) {
            throw new \RuntimeException('Invalid PrestaShop product ID: ' . $productId);
        }

        DB::connection('mysql2')
            ->table($prefix . 'custom_product')
            ->updateOrInsert(
                ['id_product' => $productId],
                ['id_product' => $productId]
            );

        if ($productAttributeId > 0) {
            $attributeExists = DB::connection('mysql2')
                ->table($prefix . 'product_attribute')
                ->where('id_product', $productId)
                ->where('id_product_attribute', $productAttributeId)
                ->exists();

            if (!$attributeExists) {
                throw new \RuntimeException('Invalid PrestaShop product attribute ID: ' . $productAttributeId);
            }

            DB::connection('mysql2')
                ->table($prefix . 'custom_product_attribute')
                ->updateOrInsert(
                    ['id_product_attribute' => $productAttributeId],
                    [
                        'id_product_attribute' => $productAttributeId,
                        'id_product' => $productId,
                    ]
                );
        }
    }

    protected function adjustCustomStockArrive(int $productId, ?int $productAttributeId, int $delta): void
    {
        if ($delta === 0) {
            return;
        }

        $prefix = $this->psPrefix();
        $productAttributeId = (int) ($productAttributeId ?? 0);

        $this->ensurePrestashopCustomProductRows($productId, $productAttributeId > 0 ? $productAttributeId : null);

        DB::connection('mysql2')
            ->table($prefix . 'custom_product')
            ->where('id_product', $productId)
            ->update([
                'stock_arrive' => DB::raw('COALESCE(stock_arrive, 0) + ' . (int) $delta),
            ]);

        if ($productAttributeId > 0) {
            DB::connection('mysql2')
                ->table($prefix . 'custom_product_attribute')
                ->where('id_product_attribute', $productAttributeId)
                ->update([
                    'id_product' => $productId,
                    'stock_arrive' => DB::raw('COALESCE(stock_arrive, 0) + ' . (int) $delta),
                ]);
        }
    }

    protected function psPrefix(): string
    {
        return (string) (env('DB2_prefix') ?: env('DB2_DB_prefix') ?: 'ps_');
    }
        protected function getOrderNoteAmountFromBuilderLines(iterable $lines): float
    {
        $total = 0.0;

        foreach ($lines as $line) {
            $qty = (float) data_get($line, 'qty_ordered', 0);

            $attributeBaseCurrency = (float) data_get($line, 'attribute_wholesale_price_base_currency', data_get($line, 'wholesale_price_base_currency', 0));
            $attributeWholesale = (float) data_get($line, 'attribute_wholesale_price', 0);
            $productBaseCurrency = (float) data_get($line, 'product_wholesale_price_base_currency', (($attributeBaseCurrency > 0) ? 0 : data_get($line, 'wholesale_price_base_currency', 0)));
            $productWholesale = (float) data_get($line, 'product_wholesale_price', data_get($line, 'wholesale_price', 0));

            $unitCost = $attributeBaseCurrency > 0
                ? $attributeBaseCurrency
                : ($attributeWholesale > 0
                    ? $attributeWholesale
                    : ($productBaseCurrency > 0
                        ? $productBaseCurrency
                        : ($productWholesale > 0 ? $productWholesale : (float) data_get($line, 'unit_cost', 0))));

            $total += $qty * $unitCost;
        }

        return round($total, 2);
    }
}
