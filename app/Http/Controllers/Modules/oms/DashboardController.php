<?php

namespace App\Http\Controllers\Modules\oms;

use App\Http\Controllers\Controller;
use App\Services\oms\DashboardQueryService;
use App\Services\oms\ExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardQueryService $dashboardQueryService,
        protected ExportService $exportService
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $state = $this->dashboardQueryService->resolveState($request->all());
        $documents = $this->dashboardQueryService->getDocumentsPane($state);
        $summary = $this->dashboardQueryService->getSummaryPane($state);
        $stats = $this->dashboardQueryService->getStats($state['supplier_id'] ?? null);
        $supplierSidebar = $this->dashboardQueryService->getSupplierSidebar($state['supplier_tab']);

        return view('modules.oms.dashboard.index', [
            'breadcrumbs' => $this->breadcrumbs('OMS dashboard', route(request()->routeIs('admin.tools.oms.*') ? 'admin.tools.oms.dashboard' : 'erp.oms.dashboard')),
            'state' => $state,
            'documentsPane' => $documents,
            'summaryPane' => $summary,
            'stats' => $stats,
            'supplierSidebar' => $supplierSidebar,
        ]);
    }

    public function documentsFragment(Request $request): JsonResponse
    {
        $state = $this->dashboardQueryService->resolveState($request->all());
        $documents = $this->dashboardQueryService->getDocumentsPane($state);

        $html = view('modules.oms.dashboard.partials.documents_panel', [
            'state' => $state,
            'documentsPane' => $documents,
        ])->render();

        return response()->json([
            'success' => true,
            'state' => $state,
            'html' => $html,
        ]);
    }

    public function summaryFragment(Request $request): JsonResponse
    {
        $state = $this->dashboardQueryService->resolveState($request->all());
        $summary = $this->dashboardQueryService->getSummaryPane($state);

        $html = view('modules.oms.dashboard.partials.summary_pane', [
            'state' => $state,
            'summaryPane' => $summary,
        ])->render();

        return response()->json([
            'success' => true,
            'state' => $state,
            'html' => $html,
            'metrics' => $summary['metrics'] ?? [],
        ]);
    }

    public function statsFragment(Request $request): JsonResponse
    {
        $supplierId = $request->filled('supplier_id') ? (int) $request->supplier_id : null;
        $stats = $this->dashboardQueryService->getStats($supplierId);

        $html = view('modules.oms.dashboard.partials.stats', [
            'stats' => $stats,
        ])->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'stats' => $stats,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $state = $this->dashboardQueryService->resolveState($request->all());
        $documents = $this->dashboardQueryService->getDocumentsPane($state);
        $rows = collect();

        foreach (($documents['order_notes'] ?? collect()) as $doc) {
            $rows->push([
                'type' => 'order_note',
                'reference' => $doc->reference,
                'supplier' => optional($doc->supplier)->name,
                'created_at' => optional($doc->created_at)?->format('Y-m-d H:i:s'),
                'items' => (int) ($doc->lines->count() ?? 0),
                'ordered_units' => (int) ($doc->lines->sum('qty_ordered') ?? 0),
                'billed_units' => (int) $doc->lines->sum(fn ($line) => $line->qty_billed_total ?? 0),
                'received_units' => (int) $doc->lines->sum(fn ($line) => $line->qty_received_total ?? 0),
            ]);
        }

        foreach (($documents['billed_orders'] ?? collect()) as $doc) {
            $rows->push([
                'type' => 'billed_order',
                'reference' => $doc->reference,
                'supplier' => optional(optional($doc->orderNote)->supplier)->name,
                'created_at' => optional($doc->created_at)?->format('Y-m-d H:i:s'),
                'items' => (int) ($doc->lines->count() ?? 0),
                'ordered_units' => null,
                'billed_units' => (int) ($doc->lines->sum('qty_billed') ?? 0),
                'received_units' => (int) ($doc->lines->sum('qty_received') ?? 0),
            ]);
        }

        foreach (($documents['received_orders'] ?? collect()) as $doc) {
            $rows->push([
                'type' => 'received_order',
                'reference' => $doc->reference,
                'supplier' => optional(optional($doc->orderNote)->supplier)->name,
                'created_at' => optional($doc->created_at)?->format('Y-m-d H:i:s'),
                'items' => (int) ($doc->lines->count() ?? 0),
                'ordered_units' => null,
                'billed_units' => (int) ($doc->lines->sum('qty_billed') ?? 0),
                'received_units' => (int) ($doc->lines->sum('qty_received') ?? 0),
            ]);
        }

        return $this->exportService->streamCsv(
            'oms-dashboard.csv',
            ['type', 'reference', 'supplier', 'created_at', 'items', 'ordered_units', 'billed_units', 'received_units'],
            $rows
        );
    }

    private function breadcrumbs(string $currentName, ?string $currentUrl = null): array
    {
        $dashboardRoute = request()->routeIs('admin.tools.oms.*')
            ? 'admin.tools.oms.dashboard'
            : 'erp.oms.dashboard';

        return [
            ['name' => 'administration', 'url' => route('administration.index')],
            ['name' => 'OMS', 'url' => route($dashboardRoute), 'no_translation' => 1],
            ['name' => $currentName, 'url' => $currentUrl, 'no_translation' => 1],
        ];
    }
}
