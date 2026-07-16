<?php

namespace App\Http\Controllers\Modules\oms;

use App\Http\Controllers\Controller;
use App\Models\modules\oms\OrderNote;
use App\Models\modules\oms\SupplierInvoice;
use App\Models\modules\oms\OmsDocumentLineHistory;
use App\Models\modules\shipping\shipping;
use App\Models\modules\shipping_erp\shipping_erp;
use App\Services\oms\ExportService;
use App\Services\oms\SupplierInvoiceWorkflowService;
use App\Services\oms\BilledOrderDisplayService;
use Illuminate\Http\Request;

class SupplierInvoiceController extends Controller
{
    public function __construct(
        protected ExportService $exportService,
        protected SupplierInvoiceWorkflowService $workflowService,
        protected BilledOrderDisplayService $billedOrderDisplayService,
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = SupplierInvoice::query()
            ->with(['supplier', 'billedOrders']);

        if ($request->filled('ref')) {
            $query->where('invoice_reference', 'like', '%' . trim((string) $request->get('ref')) . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->get('status'));
        }

        if ($request->filled('supplier')) {
            $supplierTerm = trim((string) $request->get('supplier'));

            $query->whereHas('supplier', function ($q) use ($supplierTerm) {
                $q->where('name', 'like', '%' . $supplierTerm . '%');
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

        return view('modules.oms.invoices.create', compact(
            'orderNote',
            'invoiceableLines',
            'draftInvoices',
            'currencyMeta'
        ));
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
                'The following products were not marked as invoiced because their purchase price was invalid (must be greater than zero): ' . $references
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

        return view('modules.oms.invoices.show', compact(
            'invoice',
            'availableShipments',
            'selectedShipmentId',
            'linkedShipmentIds',
            'lineHistoryMap'
        ));
    }

    public function saveShipmentRelation(Request $request, SupplierInvoice $invoice)
    {
        $data = $request->validate([
            'shipment_id' => ['nullable', 'integer'],
        ]);

        $shipmentId = !empty($data['shipment_id']) ? (int) $data['shipment_id'] : null;

        if ($shipmentId !== null) {
            $shipment = shipping::query()
                ->where('id', $shipmentId)
                ->where('supplier', (int) $invoice->supplier_id)
                ->whereIn('status', [1, 2])
                ->first();

            if (!$shipment) {
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

    public function exportCsv(SupplierInvoice $invoice)
    {
        $invoice->load(['billedOrders.lines', 'billedOrders.orderNote']);

        return $this->exportService->exportInvoiceCsv($invoice);
    }

    public function exportPdf(SupplierInvoice $invoice)
    {
        return back()->with('warning', 'PDF export is not implemented yet in this tranche.');
    }
}
