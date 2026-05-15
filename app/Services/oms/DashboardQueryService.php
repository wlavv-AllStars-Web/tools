<?php

namespace App\Services\oms;

use App\Models\modules\oms\BilledOrder;
use App\Models\modules\oms\OrderNote;
use App\Models\modules\oms\SupplierInvoice;
use App\Models\prestashop\suppliers;
use App\Services\oms\SupplierTermsService;
use App\Services\oms\OrderNoteLogisticsService; 
use Illuminate\Support\Facades\DB;

class DashboardQueryService
{
    public function __construct(
        protected EtaService $etaService,
        protected SupplierMapService $supplierMapService,
        protected SupplierTermsService $supplierTermsService
    ) {
    }

    public function resolveState(array $filters): array
    {
        $supplierTab = $filters['supplier_tab'] ?? 'order_notes';
        if (!in_array($supplierTab, ['order_notes', 'billed', 'received', 'all'], true)) {
            $supplierTab = 'order_notes';
        }

        $centerTab = $filters['center_tab'] ?? $this->defaultCenterTabForSupplierTab($supplierTab);
        if ($centerTab === 'received') {
            $centerTab = 'receptions_history';
        }
        if (!in_array($centerTab, ['order_notes', 'billed', 'open_receptions', 'receptions_history'], true)) {
            $centerTab = $this->defaultCenterTabForSupplierTab($supplierTab);
        }

        $supplierId = !empty($filters['supplier_id']) ? (int) $filters['supplier_id'] : null;
        $documentType = $filters['document_type'] ?? null;
        $documentId = !empty($filters['document_id']) ? (int) $filters['document_id'] : null;

        if (!$documentType || !$documentId) {
            $fallback = $this->getFirstAvailableDocument($centerTab, $supplierId);
            $documentType = $fallback['document_type'];
            $documentId = $fallback['document_id'];
        }

        return [
            'supplier_tab' => $supplierTab,
            'center_tab' => $centerTab,
            'supplier_id' => $supplierId,
            'document_type' => $documentType,
            'document_id' => $documentId,
        ];
    }

    public function getStats(?int $supplierId = null): array
    {
        $openOrderNotes = OrderNote::query()
            ->with('lines')
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->where('status', 'order_note')
            ->get()
            ->filter(fn ($doc) => $this->getOrderNoteOrderedUnits($doc) > $this->getOrderNoteBilledUnits($doc))
            ->count();

        $billedOrders = $this->baseBilledOrdersQuery($supplierId)->get();

        $billedPending = $billedOrders
            ->filter(fn ($doc) => $this->getBilledOrderBilledUnits($doc) > 0
                && $this->getBilledOrderReceivedUnits($doc) < $this->getBilledOrderBilledUnits($doc))
            ->count();

        $openReceptions = $billedOrders
            ->filter(fn ($doc) => $this->getBilledOrderBilledUnits($doc) > 0
                && $this->getBilledOrderReceivedUnits($doc) < $this->getBilledOrderBilledUnits($doc))
            ->count();

        $history = $billedOrders
            ->filter(fn ($doc) => $this->getBilledOrderBilledUnits($doc) > 0
                && $this->getBilledOrderReceivedUnits($doc) >= $this->getBilledOrderBilledUnits($doc))
            ->count();

        return [
            'order_notes' => (int) $openOrderNotes,
            'billed' => (int) $billedPending,
            'open_receptions' => (int) $openReceptions,
            'receptions_history' => (int) $history,
            'invoices' => (int) SupplierInvoice::query()
                ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
                ->count(),
        ];
    }

    public function getSupplierSidebar(string $supplierTab = 'order_notes')
    {
        $allSuppliers = suppliers::query()
            ->select(['id_supplier', 'name'])
            ->orderBy('name')
            ->get()
            ->map(function ($supplier) {
                return (object) [
                    'supplier_id' => (int) $supplier->id_supplier,
                    'supplier_name' => $supplier->name,
                    'open_order_notes_count' => 0,
                    'billed_count' => 0,
                    'open_receptions_count' => 0,
                    'receptions_history_count' => 0,
                    'invoice_count' => 0,
                ];
            })
            ->keyBy('supplier_id');

        $orderNotes = OrderNote::query()->with('lines')->where('status', 'order_note')->get();
        foreach ($orderNotes as $doc) {
            $sid = (int) $doc->supplier_id;
            if (!isset($allSuppliers[$sid])) {
                continue;
            }
            if ($this->getOrderNoteOrderedUnits($doc) > $this->getOrderNoteBilledUnits($doc)) {
                $allSuppliers[$sid]->open_order_notes_count++;
            }
        }

        $billedOrders = BilledOrder::query()->with(['lines', 'orderNote'])->get();
        foreach ($billedOrders as $doc) {
            $sid = (int) optional($doc->orderNote)->supplier_id;
            if (!$sid || !isset($allSuppliers[$sid])) {
                continue;
            }

            $billedUnits = $this->getBilledOrderBilledUnits($doc);
            $receivedUnits = $this->getBilledOrderReceivedUnits($doc);

            if ($billedUnits > 0) {
                $allSuppliers[$sid]->billed_count++;
            }
            if ($billedUnits > 0 && $receivedUnits < $billedUnits) {
                $allSuppliers[$sid]->open_receptions_count++;
            }
            if ($billedUnits > 0 && $receivedUnits >= $billedUnits) {
                $allSuppliers[$sid]->receptions_history_count++;
            }
        }

        $invoiceCounts = SupplierInvoice::query()
            ->selectRaw('supplier_id, COUNT(*) as invoice_count')
            ->groupBy('supplier_id')
            ->pluck('invoice_count', 'supplier_id');

        foreach ($invoiceCounts as $sid => $count) {
            $sid = (int) $sid;
            if (isset($allSuppliers[$sid])) {
                $allSuppliers[$sid]->invoice_count = (int) $count;
            }
        }

        return $allSuppliers
            ->values()
            ->filter(function ($row) use ($supplierTab) {
                return match ($supplierTab) {
                    'order_notes' => $row->open_order_notes_count > 0,
                    'billed' => $row->open_receptions_count > 0,
                    'received' => $row->receptions_history_count > 0,
                    default => true,
                };
            })
            ->values();
    }

    public function getDocumentsPane(array $state): array
    {
        $supplierId = $state['supplier_id'] ?? null;

        $orderNotes = OrderNote::query()
            ->with(['supplier', 'lines'])
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->where('status', 'order_note')
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn ($doc) => $this->getOrderNoteOrderedUnits($doc) > $this->getOrderNoteBilledUnits($doc))
            ->values();

        $allBilledOrders = $this->baseBilledOrdersQuery($supplierId)
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn ($doc) => $this->getBilledOrderBilledUnits($doc) > 0)
            ->values();

        $openReceptions = $allBilledOrders
            ->filter(fn ($doc) => $this->getBilledOrderReceivedUnits($doc) < $this->getBilledOrderBilledUnits($doc))
            ->values();

        $receptionsHistory = $allBilledOrders
            ->filter(fn ($doc) => $this->getBilledOrderReceivedUnits($doc) >= $this->getBilledOrderBilledUnits($doc))
            ->values();

        return [
            'order_notes' => $orderNotes,
            'billed_orders' => $openReceptions,
            'open_receptions' => $openReceptions,
            'received_orders' => $receptionsHistory,
            'receptions_history' => $receptionsHistory,
            'eta_map' => [],
        ];
    }


    protected function getOrderNoteAmount(OrderNote $orderNote): float
    {
        $orderNote->loadMissing('lines');

        if ($orderNote->lines->isEmpty()) {
            return 0.0;
        }

        $productIds = $orderNote->lines->pluck('product_id')->filter()->unique()->values();
        $attributeIds = $orderNote->lines->pluck('product_attribute_id')->filter()->unique()->values();

        $productCosts = $productIds->isNotEmpty()
            ? DB::connection('mysql2')
                ->table('ps_product as p')
                ->leftJoin('ps_custom_product as cp', 'cp.id_product', '=', 'p.id_product')
                ->whereIn('p.id_product', $productIds)
                ->selectRaw('p.id_product, COALESCE(cp.wholesale_price_base_currency, 0) as base_currency_cost, COALESCE(p.wholesale_price, 0) as euro_cost')
                ->get()
                ->keyBy('id_product')
            : collect();

        $attributeCosts = $attributeIds->isNotEmpty()
            ? DB::connection('mysql2')
                ->table('ps_product_attribute as pa')
                ->leftJoin('ps_custom_product_attribute as cpa', 'cpa.id_product_attribute', '=', 'pa.id_product_attribute')
                ->whereIn('pa.id_product_attribute', $attributeIds)
                ->selectRaw('pa.id_product_attribute, COALESCE(cpa.wholesale_price_base_currency, 0) as base_currency_cost, COALESCE(pa.wholesale_price, 0) as euro_cost')
                ->get()
                ->keyBy('id_product_attribute')
            : collect();

        return round((float) $orderNote->lines->sum(function ($line) use ($productCosts, $attributeCosts) {
            $attributeRow = $attributeCosts->get((int) ($line->product_attribute_id ?? 0));
            $productRow = $productCosts->get((int) ($line->product_id ?? 0));

            $attributeBaseCurrencyCost = (float) data_get($attributeRow, 'base_currency_cost', 0);
            $attributeEuroCost = (float) data_get($attributeRow, 'euro_cost', 0);
            $productBaseCurrencyCost = (float) data_get($productRow, 'base_currency_cost', 0);
            $productEuroCost = (float) data_get($productRow, 'euro_cost', 0);

            $unitCost = match (true) {
                $attributeBaseCurrencyCost > 0 => $attributeBaseCurrencyCost,
                $attributeEuroCost > 0 => $attributeEuroCost,
                $productBaseCurrencyCost > 0 => $productBaseCurrencyCost,
                $productEuroCost > 0 => $productEuroCost,
                default => 0.0,
            };

            return ((float) ($line->qty_ordered ?? 0)) * $unitCost;
        }), 2);
    }

    public function getSummaryPane(array $state): array
    {
        $documentType = $state['document_type'] ?? null;
        $documentId = $state['document_id'] ?? null;

        $summary = [
            'document_type' => $documentType,
            'document_id' => $documentId,
            'document' => null,
            'eta' => null,
            'supplier_map' => null,
            'metrics' => [],
            'terms_summary' => null,
            'logistics' => null,             
        ];

        if ($documentType === 'order_note' && $documentId) {
            $doc = OrderNote::with(['supplier', 'lines', 'billedOrders'])->find($documentId);

            if ($doc) {
                $summary['document'] = $doc;
                $summary['supplier_map'] = $this->supplierMapService->getSummaryBySupplierId((int) $doc->supplier_id);
                $summary['metrics'] = [
                    'items' => (int) $doc->lines->count(),
                    'ordered_units' => $this->getOrderNoteOrderedUnits($doc),
                    'billed_units' => $this->getOrderNoteBilledUnits($doc),
                    'received_units' => $this->getOrderNoteReceivedUnits($doc),
                ];
                $summary['terms_summary'] = $this->supplierTermsService->buildProgressSummary(
                    (int) $doc->supplier_id,
                    $this->getOrderNoteAmount($doc)
                );
                $summary['logistics'] = app(OrderNoteLogisticsService::class)->calculateFromOrderNote($doc); 
            }
        }

        if ($documentType === 'billed_order' && $documentId) {
            $doc = BilledOrder::with(['lines', 'orderNote.supplier', 'invoice'])->find($documentId);

            if ($doc) {
                $summary['document'] = $doc;
                $summary['supplier_map'] = $doc->orderNote
                    ? $this->supplierMapService->getSummaryBySupplierId((int) $doc->orderNote->supplier_id)
                    : null;

                $summary['metrics'] = [
                    'items' => (int) $doc->lines->count(),
                    'billed_units' => $this->getBilledOrderBilledUnits($doc),
                    'received_units' => $this->getBilledOrderReceivedUnits($doc),
                    'missing_units' => max(0, $this->getBilledOrderBilledUnits($doc) - $this->getBilledOrderReceivedUnits($doc)),
                ];
                $summary['logistics'] = app(OrderNoteLogisticsService::class)->calculateFromBilledOrder($doc); 
            }
        }

        return $summary;
    }

    protected function getFirstAvailableDocument(string $centerTab, ?int $supplierId): array
    {
        if ($centerTab === 'order_notes') {
            $doc = OrderNote::query()
                ->with('lines')
                ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
                ->where('status', 'order_note')
                ->orderByDesc('created_at')
                ->get()
                ->first(fn ($row) => $this->getOrderNoteOrderedUnits($row) > $this->getOrderNoteBilledUnits($row));

            return [
                'document_type' => $doc ? 'order_note' : null,
                'document_id' => $doc?->id,
            ];
        }

        $doc = $this->baseBilledOrdersQuery($supplierId)
            ->orderByDesc('created_at')
            ->get();

        if ($centerTab === 'open_receptions') {
            $doc = $doc->first(fn ($row) => $this->getBilledOrderBilledUnits($row) > 0
                && $this->getBilledOrderReceivedUnits($row) < $this->getBilledOrderBilledUnits($row));
        } elseif ($centerTab === 'receptions_history') {
            $doc = $doc->first(fn ($row) => $this->getBilledOrderBilledUnits($row) > 0
                && $this->getBilledOrderReceivedUnits($row) >= $this->getBilledOrderBilledUnits($row));
        } else {
            $doc = $doc->first(fn ($row) => $this->getBilledOrderBilledUnits($row) > 0);
        }

        return [
            'document_type' => $doc ? 'billed_order' : null,
            'document_id' => $doc?->id,
        ];
    }

    protected function defaultCenterTabForSupplierTab(string $supplierTab): string
    {
        return match ($supplierTab) {
            'billed' => 'billed',
            'received' => 'receptions_history',
            default => 'order_notes',
        };
    }

    protected function baseBilledOrdersQuery(?int $supplierId = null)
    {
        return BilledOrder::query()
            ->with(['orderNote.supplier', 'invoice', 'lines'])
            ->when($supplierId, function ($q) use ($supplierId) {
                $q->whereHas('orderNote', fn ($qq) => $qq->where('supplier_id', $supplierId));
            });
    }

    protected function getOrderNoteOrderedUnits($doc): int
    {
        return (int) (($doc->lines ?? collect())->sum('qty_ordered'));
    }

    protected function getOrderNoteBilledUnits($doc): int
    {
        return (int) (($doc->lines ?? collect())->sum(fn ($line) => $line->qty_billed_total ?? 0));
    }

    protected function getOrderNoteReceivedUnits($doc): int
    {
        return (int) (($doc->lines ?? collect())->sum(fn ($line) => $line->qty_received_total ?? 0));
    }

    protected function getBilledOrderBilledUnits($doc): int
    {
        return (int) (($doc->lines ?? collect())->sum('qty_billed'));
    }

    protected function getBilledOrderReceivedUnits($doc): int
    {
        return (int) (($doc->lines ?? collect())->sum('qty_received'));
    }
}
