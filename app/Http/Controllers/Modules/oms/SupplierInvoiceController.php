<?php

namespace App\Http\Controllers\Modules\oms;

use App\Http\Controllers\Controller;
use App\Models\modules\oms\BilledOrderLine;
use App\Models\modules\oms\OmsDocumentLineHistory;
use App\Models\modules\oms\OrderNote;
use App\Models\modules\oms\SupplierInvoice;
use App\Models\modules\shipping\shipping;
use App\Models\modules\shipping_erp\shipping_erp;
use App\Services\oms\BilledOrderDisplayService;
use App\Services\oms\BilledOrderReversalService;
use App\Services\oms\ExportService;
use App\Services\oms\SupplierInvoiceWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierInvoiceController extends Controller
{
    public function __construct(
        protected ExportService $exportService,
        protected SupplierInvoiceWorkflowService $workflowService,
        protected BilledOrderDisplayService $billedOrderDisplayService,
        protected BilledOrderReversalService $billedOrderReversalService,
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = SupplierInvoice::query()
            ->with(['supplier', 'billedOrders']);

        if ($request->filled('ref')) {
            $query->where('invoice_reference', 'like', '%'.trim((string) $request->get('ref')).'%');
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->get('status'));
        }

        if ($request->filled('supplier')) {
            $supplierTerm = trim((string) $request->get('supplier'));

            $query->whereHas('supplier', function ($q) use ($supplierTerm) {
                $q->where('name', 'like', '%'.$supplierTerm.'%');
            });
        }

        $invoices = $query
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('modules.oms.invoices.index', [
            'invoices' => $invoices,
        ]);
    }

    public function create(Request $request, OrderNote $orderNote)
    {
        $orderNote->load(['supplier', 'lines', 'billedOrders']);

        $invoiceableLines = $this->workflowService->getInvoiceableLines($orderNote);
        $draftInvoices = $this->workflowService->getDraftInvoicesForSupplier((int) $orderNote->supplier_id);
        $currencyMeta = $this->workflowService->resolveCurrencyForOrderNote($orderNote, $orderNote->lines);
        $combinationPrices = $this->workflowService->getOtherCombinationPrices($invoiceableLines);

        $invoiceableLines->each(function ($line) use ($combinationPrices) {
            $line->other_combinations = collect($combinationPrices->get((int) $line->product_id, []))
                ->reject(fn ($combination) => (int) $combination->product_attribute_id === (int) ($line->product_attribute_id ?? 0))
                ->values();
        });

        return view('modules.oms.invoices.create', compact(
            'orderNote',
            'invoiceableLines',
            'draftInvoices',
            'currencyMeta'
        ));
    }

    public function updateCombinationPrice(Request $request, OrderNote $orderNote, int $productAttributeId)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
        ]);

        $prices = $this->workflowService->updateCombinationPrices(
            $orderNote,
            (int) $data['product_id'],
            $productAttributeId,
            round((float) $data['purchase_price'], 6),
            round((float) $data['sale_price'], 6)
        );

        return response()->json([
            'success' => true,
            'message' => 'Combination prices updated successfully.',
            'prices' => $prices,
        ]);
    }

    public function store(Request $request, OrderNote $orderNote)
    {
        $data = $request->validate([
            'existing_invoice_id' => ['nullable', 'integer'],
            'invoice_reference' => ['nullable', 'string', 'max:100'],
            'invoice_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'internal_note' => ['nullable', 'string'],
            'logistic_note' => ['nullable', 'string'],
            'invoice_action' => ['required', 'in:save_draft,close_invoice'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.order_note_line_id' => ['required', 'integer'],
            'lines.*.qty_billed' => ['nullable', 'integer', 'min:0'],
            'lines.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'lines.*.sale_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $invoice = $this->workflowService->confirmInvoiceForOrderNote($orderNote, $data);
        $skippedInvalidPriceLines = collect($invoice->getAttribute('skipped_invalid_price_lines') ?? []);

        $message = $data['invoice_action'] === 'close_invoice'
            ? 'Supplier invoice closed successfully.'
            : 'Supplier invoice saved as draft successfully.';

        $redirect = redirect()->route('erp.oms.invoices.show', $invoice)
            ->with('success', $message);

        if ($skippedInvalidPriceLines->isNotEmpty()) {
            $references = $skippedInvalidPriceLines->pluck('reference')->filter()->implode(', ');

            $redirect->with(
                'warning',
                'The following products were not marked as invoiced because their purchase price was invalid (must be greater than zero): '.$references
            );
        }

        return $redirect;
    }

    public function show(SupplierInvoice $invoice)
    {
        $invoice->load(['supplier', 'billedOrders.lines.orderNoteLine', 'billedOrders.orderNote']);

        $invoice->billedOrders->each(function ($billedOrder) {
            $billedOrder->setRelation(
                'lines',
                $this->billedOrderDisplayService->hydrateLines($billedOrder->lines)
            );
        });

        $lineIds = $invoice->billedOrders
            ->flatMap(fn ($billedOrder) => $billedOrder->lines->pluck('id'))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $lineHistoryMap = OmsDocumentLineHistory::query()
            ->where('context_type', 'billed_order_line')
            ->whereIn('context_id', $lineIds->all())
            ->orderByDesc('id')
            ->get()
            ->groupBy('context_id')
            ->map(fn ($rows) => $rows->first());

        $linkedShipmentIds = shipping_erp::getShippingIdsForErp((int) $invoice->id)
            ->map(fn ($id) => (int) $id)
            ->values();

        $availableShipments = shipping::query()
            ->with('supplier_info')
            ->where('supplier', (int) $invoice->supplier_id)
            ->whereIn('status', [1, 2])
            ->orderByDesc('id')
            ->get();

        $selectedShipmentId = (int) ($linkedShipmentIds->first() ?? 0);

        $canReverseInvoices = $this->canReverseInvoices();

        return view('modules.oms.invoices.show', compact(
            'invoice',
            'availableShipments',
            'selectedShipmentId',
            'linkedShipmentIds',
            'lineHistoryMap',
            'canReverseInvoices'
        ));
    }

    public function update(Request $request, SupplierInvoice $invoice)
    {
        $data = $request->validate([
            'invoice_reference' => ['required', 'string', 'max:100'],
        ]);

        $invoice->update($data);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice name updated successfully.',
                'invoice_reference' => $invoice->invoice_reference,
            ]);
        }

        return back()->with('success', 'Invoice name updated successfully.');
    }

    public function updateLine(Request $request, SupplierInvoice $invoice, BilledOrderLine $line)
    {
        $billedOrder = $line->billedOrder()->with('orderNote')->firstOrFail();

        if ((int) $billedOrder->supplier_invoice_id !== (int) $invoice->id) {
            abort(404);
        }

        if (($invoice->status ?? 'draft') === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Cancelled invoices cannot be edited.'], 422);
        }

        $data = $request->validate([
            'qty_billed' => ['required', 'integer', 'min:1'],
        ]);

        $orderNoteLine = $line->orderNoteLine;
        if (! $orderNoteLine && $billedOrder->orderNote) {
            $orderNoteLine = $billedOrder->orderNote->lines()
                ->where('product_id', (int) $line->product_id)
                ->where(function ($query) use ($line) {
                    $attributeId = (int) ($line->product_attribute_id ?? 0);
                    $attributeId > 0
                        ? $query->where('product_attribute_id', $attributeId)
                        : $query->where(function ($attributeQuery) {
                            $attributeQuery->whereNull('product_attribute_id')->orWhere('product_attribute_id', 0);
                        });
                })
                ->first();
        }

        if (! $orderNoteLine) {
            return response()->json(['success' => false, 'message' => 'The related order note line could not be found.'], 422);
        }

        $received = max((int) ($line->qty_received ?? 0), (int) $line->qty_received_calculated);
        $otherBilled = (int) DB::table('oms_billed_order_lines as other')
            ->join('oms_billed_orders as bo', 'bo.id', '=', 'other.billed_order_id')
            ->leftJoin('oms_supplier_invoices as si', 'si.id', '=', 'bo.supplier_invoice_id')
            ->where(function ($query) use ($orderNoteLine, $line, $billedOrder) {
                $query->where('other.order_note_line_id', (int) $orderNoteLine->id)
                    ->orWhere(function ($fallback) use ($line, $billedOrder) {
                        $fallback->whereNull('other.order_note_line_id')
                            ->where('bo.order_note_id', (int) $billedOrder->order_note_id)
                            ->where('other.product_id', (int) $line->product_id)
                            ->where(function ($attributeQuery) use ($line) {
                                $attributeId = (int) ($line->product_attribute_id ?? 0);
                                $attributeId > 0
                                    ? $attributeQuery->where('other.product_attribute_id', $attributeId)
                                    : $attributeQuery->where(function ($emptyAttribute) {
                                        $emptyAttribute->whereNull('other.product_attribute_id')->orWhere('other.product_attribute_id', 0);
                                    });
                            });
                    });
            })
            ->where('other.id', '<>', (int) $line->id)
            ->where(function ($query) {
                $query->whereNull('si.status')->orWhere('si.status', '<>', 'cancelled');
            })
            ->sum('other.qty_billed');

        $maximum = max($received, (int) $orderNoteLine->qty_ordered - $otherBilled);
        $qtyBilled = (int) $data['qty_billed'];

        if ($qtyBilled < $received) {
            return response()->json(['success' => false, 'message' => 'Billed quantity cannot be lower than the quantity already received ('.$received.').'], 422);
        }

        if ($qtyBilled > $maximum) {
            return response()->json(['success' => false, 'message' => 'Billed quantity cannot exceed the available ordered quantity ('.$maximum.').'], 422);
        }

        $line->update([
            'order_note_line_id' => (int) $orderNoteLine->id,
            'qty_billed' => $qtyBilled,
        ]);

        $this->workflowService->refreshOrderNoteStatus($billedOrder->orderNote->fresh(['lines', 'billedOrders']));

        return response()->json(['success' => true, 'message' => 'Invoice line updated successfully.']);
    }

    public function destroyLine(Request $request, SupplierInvoice $invoice, BilledOrderLine $line)
    {
        $billedOrder = $line->billedOrder()->with('orderNote')->firstOrFail();

        if ((int) $billedOrder->supplier_invoice_id !== (int) $invoice->id) {
            abort(404);
        }

        if (($invoice->status ?? 'draft') === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Cancelled invoices cannot be edited.'], 422);
        }

        if (max((int) ($line->qty_received ?? 0), (int) $line->qty_received_calculated) > 0) {
            return response()->json(['success' => false, 'message' => 'This invoice line cannot be removed because it already has received quantities.'], 422);
        }

        $line->delete();

        if ($billedOrder->orderNote) {
            $this->workflowService->refreshOrderNoteStatus($billedOrder->orderNote->fresh(['lines', 'billedOrders']));
        }

        return response()->json(['success' => true, 'message' => 'Invoice line removed successfully.']);
    }

    public function reverseLine(Request $request, SupplierInvoice $invoice, BilledOrderLine $line)
    {
        $this->ensureReversalAuthorized();
        $billedOrder = $line->billedOrder()->firstOrFail();
        if ((int) $billedOrder->supplier_invoice_id !== (int) $invoice->id) abort(404);
        if (($invoice->status ?? 'draft') === 'cancelled') return $this->reversalError($request, 'Cancelled invoices cannot be reversed again.');
        $data = $request->validate(['qty_to_remove' => ['required', 'integer', 'min:1'], 'confirm_negative_stock' => ['nullable', 'boolean']]);
        $summary = $this->billedOrderReversalService->revertLine($line, (int) $data['qty_to_remove'], (bool) ($data['confirm_negative_stock'] ?? false));
        return $this->reversalSuccess($request, 'Invoice line reversed.', $summary);
    }

    public function reverseBilledOrder(Request $request, SupplierInvoice $invoice, \App\Models\modules\oms\BilledOrder $billedOrder)
    {
        $this->ensureReversalAuthorized();
        if ((int) $billedOrder->supplier_invoice_id !== (int) $invoice->id) abort(404);
        if (($invoice->status ?? 'draft') === 'cancelled') return $this->reversalError($request, 'Cancelled invoices cannot be reversed again.');
        $data = $request->validate(['confirm_negative_stock' => ['nullable', 'boolean']]);
        $summary = $this->billedOrderReversalService->revertBilledOrder($billedOrder, (bool) ($data['confirm_negative_stock'] ?? false));
        return $this->reversalSuccess($request, 'Billed order reversed.', $summary);
    }

    public function reverseInvoice(Request $request, SupplierInvoice $invoice)
    {
        $this->ensureReversalAuthorized();
        if (($invoice->status ?? 'draft') === 'cancelled') return $this->reversalError($request, 'Cancelled invoices cannot be reversed again.');
        $data = $request->validate(['confirm_negative_stock' => ['nullable', 'boolean']]);
        $summary = $this->billedOrderReversalService->revertInvoice($invoice, (bool) ($data['confirm_negative_stock'] ?? false));
        return $this->reversalSuccess($request, 'Invoice reversed.', $summary);
    }

    public function canReverseInvoices(): bool
    {
        return in_array((int) Auth::id(), [2, 43, 78], true);
    }

    private function ensureReversalAuthorized(): void
    {
        abort_unless($this->canReverseInvoices(), 403, 'You are not authorized to reverse OMS invoices.');
    }

    private function reversalSuccess(Request $request, string $message, array $summary)
    {
        if ($request->expectsJson() || $request->ajax()) return response()->json(['success' => true, 'message' => $message, 'summary' => $summary]);
        return back()->with('success', $message);
    }

    private function reversalError(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) return response()->json(['success' => false, 'message' => $message], 422);
        return back()->with('error', $message);
    }
    public function saveShipmentRelation(Request $request, SupplierInvoice $invoice)
    {
        $data = $request->validate([
            'shipment_id' => ['nullable', 'integer'],
        ]);

        $shipmentId = ! empty($data['shipment_id']) ? (int) $data['shipment_id'] : null;

        if ($shipmentId !== null) {
            $shipment = shipping::query()
                ->where('id', $shipmentId)
                ->where('supplier', (int) $invoice->supplier_id)
                ->whereIn('status', [1, 2])
                ->first();

            if (! $shipment) {
                return back()->with('error', 'Selected shipment is not valid for this invoice supplier or is no longer open.');
            }
        }

        shipping_erp::replaceErpRelation((int) $invoice->id, $shipmentId);

        return back()->with('success', $shipmentId
            ? 'Shipment relation saved successfully.'
            : 'Shipment relation removed successfully.');
    }

    public function close(SupplierInvoice $invoice)
    {
        $this->workflowService->closeInvoice($invoice);

        return back()->with('success', 'Invoice closed successfully. No more order notes can be added to this document.');
    }

    public function cancel(SupplierInvoice $invoice)
    {
        if (($invoice->status ?? 'draft') === 'cancelled') {
            return back()->with('warning', 'Invoice is already cancelled.');
        }

        $this->workflowService->cancelInvoice($invoice);

        return back()->with('success', 'Invoice cancelled. Operational invoiced quantities were reverted.');
    }

    public function exportXlsx(SupplierInvoice $invoice)
    {
        $invoice->load(['billedOrders.lines', 'billedOrders.orderNote']);

        return $this->exportService->exportInvoiceXlsx($invoice);
    }

    public function exportPdf(SupplierInvoice $invoice)
    {
        return back()->with('warning', 'PDF export is not implemented yet in this tranche.');
    }
}
