<?php

namespace App\Http\Controllers\Modules\oms;

use App\Http\Controllers\Controller;
use App\Models\modules\oms\BilledOrder;
use App\Models\modules\oms\BilledOrderLine;
use App\Models\modules\shipping\shipping;
use App\Models\modules\shipping_erp\shipping_erp;
use App\Services\oms\DocumentCommentService;
use App\Services\oms\DocumentLineNoteService;
use App\Services\oms\BilledOrderDisplayService;
use App\Services\oms\ExportService;
use Illuminate\Http\Request;

class BilledOrderController extends Controller
{
    public function __construct(
        protected DocumentCommentService $documentCommentService,
        protected DocumentLineNoteService $documentLineNoteService,
        protected ExportService $exportService,
        protected BilledOrderDisplayService $billedOrderDisplayService
    ) {
        $this->middleware('auth');
    }

    public function index()
    {
        $billedOrders = BilledOrder::with(['orderNote', 'invoice'])->orderByDesc('created_at')->paginate(50);

        return view('modules.oms.billed_orders.index', compact('billedOrders'));
    }
    
    /**
    public function show(BilledOrder $billedOrder)
    {
        $billedOrder->load(['orderNote', 'invoice', 'lines', 'receptions']);

        return view('modules.oms.billed_orders.show', compact('billedOrder'));
    }
    **/

    public function show(BilledOrder $billedOrder)
    {
        $billedOrder->load(['orderNote.supplier', 'invoice', 'lines', 'receptions']);

        $billedOrder->setRelation(
            'lines',
            $this->billedOrderDisplayService->hydrateLines($billedOrder->lines)
        );

        $invoice = $billedOrder->invoice;
        $selectedShipmentId = null;
        $selectedShipment = null;
        $availableShipments = collect();

        if ($invoice) {
            $supplierId = (int) ($invoice->supplier_id ?: optional($billedOrder->orderNote)->supplier_id);

            $availableShipments = shipping::query()
                ->where('supplier', $supplierId)
                ->whereIn('status', [1, 2])
                ->orderByDesc('id')
                ->get()
                ->map(function ($shipment) {
                    $shipment->carrier_name = $shipment->carrier_name;
                    return $shipment;
                });

            $selectedShipmentId = (int) (shipping_erp::query()
                ->where('id_erp', $invoice->id)
                ->value('id_shipping') ?: 0);

            if ($selectedShipmentId > 0) {
                $selectedShipment = $availableShipments->firstWhere('id', $selectedShipmentId);

                if (!$selectedShipment) {
                    $selectedShipment = shipping::query()->where('id', $selectedShipmentId)->first();
                    if ($selectedShipment) {
                        $selectedShipment->carrier_name = $selectedShipment->carrier_name;
                    }
                }
            }
        }

        return view('modules.oms.billed_orders.show', compact(
            'billedOrder',
            'availableShipments',
            'selectedShipmentId',
            'selectedShipment'
        ));
    }

    public function saveNotes(Request $request, BilledOrder $billedOrder)
    {
        $data = $request->validate([
            'internal_note' => ['nullable', 'string'],
            'logistic_note' => ['nullable', 'string'],
        ]);

        $billedOrder = $this->documentCommentService->saveBilledOrderNotes($billedOrder, $data);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Billed order notes updated successfully.',
                'notes' => [
                    'internal_note' => $billedOrder->internal_note,
                    'logistic_note' => $billedOrder->logistic_note,
                    'has_any_note' => !empty($billedOrder->internal_note) || !empty($billedOrder->logistic_note),
                ],
            ]);
        }

        return back()->with('success', 'Billed order notes updated successfully.');
    }

    public function saveLineNotes(Request $request, BilledOrder $billedOrder, BilledOrderLine $line)
    {
        $data = $request->validate([
            'warranty' => ['nullable', 'string'],
            'components' => ['nullable', 'string'],
            'replacement' => ['nullable', 'string'],
        ]);

        $this->documentLineNoteService->save('billed_order', (int) $line->id, $data);
        $notes = $this->documentLineNoteService->getByLine('billed_order', (int) $line->id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Billed order line notes updated successfully.',
                'notes' => $notes,
            ]);
        }

        return response()->back()->with('success', 'Billed order line notes updated successfully.');
    }

    public function saveShipmentRelation(Request $request, BilledOrder $billedOrder)
    {
        $data = $request->validate([
            'shipment_id' => ['nullable', 'integer'],
        ]);

        $billedOrder->loadMissing(['invoice', 'orderNote']);
        $invoice = $billedOrder->invoice;

        if (!$invoice) {
            return back()->with('warning', 'This billed order is not linked to an invoice yet.');
        }

        if (empty($data['shipment_id'])) {
            shipping_erp::query()->where('id_erp', $invoice->id)->delete();
            return back()->with('success', 'Shipment relation removed successfully.');
        }

        $supplierId = (int) ($invoice->supplier_id ?: optional($billedOrder->orderNote)->supplier_id);

        $shipment = shipping::query()
            ->where('id', (int) $data['shipment_id'])
            ->where('supplier', $supplierId)
            ->whereIn('status', [1, 2])
            ->first();

        if (!$shipment) {
            return back()->with('error', 'Selected shipment is not valid for this billed order.');
        }

        shipping_erp::query()->where('id_erp', $invoice->id)->delete();

        $relation = new shipping_erp();
        $relation->id_shipping = (int) $shipment->id;
        $relation->id_erp = (int) $invoice->id;
        $relation->save();

        return back()->with('success', 'Shipment relation saved successfully.');
    }

    public function exportXlsx(BilledOrder $billedOrder)
    {
        $billedOrder->load(['orderNote', 'invoice', 'lines']);

        return $this->exportService->exportBilledOrderXlsx($billedOrder);
    }

    public function exportPdf(BilledOrder $billedOrder)
    {
        return back()->with('warning', 'PDF export is not implemented yet in this tranche.');
    }
}
