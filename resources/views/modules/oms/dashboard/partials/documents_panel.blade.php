@php
    $supplierTab = $state['supplier_tab'] ?? 'order_notes';
    $selectedDocumentType = $state['document_type'] ?? null;
    $selectedDocumentId = (int) ($state['document_id'] ?? 0);

    $orderNotes = collect($documentsPane['order_notes'] ?? []);
    $billedOrders = collect($documentsPane['billed_orders'] ?? []);
    $receivedOrders = collect($documentsPane['received_orders'] ?? []);
    $hasSupplier = !empty($state['supplier_id']);

    $iconFor = function (string $type) {
        return match ($type) {
            'ok' => 'fa-check',
            'warn' => 'fa-minus',
            default => 'fa-xmark',
        };
    };

    $pairStatus = function (int $base, int $current): string {
        if ($current <= 0) {
            return 'bad';
        }
        if ($base > 0 && $current >= $base) {
            return 'ok';
        }
        return 'warn';
    };

    $globalStatus = function (int $ordered, int $billed, int $received): string {
        if ($billed <= 0 && $received <= 0) {
            return 'bad';
        }
        if ($ordered > 0 && $ordered === $billed && $billed === $received) {
            return 'ok';
        }
        return 'warn';
    };

    $isAllTab = $supplierTab === 'all';
@endphp

<style>
.status-square{display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:22px;padding:0 6px;border-radius:5px;font-size:11px;font-weight:700;}
.status-ok{background:#dcfce7;color:#15803d;border:1px solid #86efac;}
.status-warn{background:#ffedd5;color:#c2410c;border:1px solid #fdba74;}
.status-bad{background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;}
.metric-with-status{display:inline-flex;align-items:center;justify-content:center;gap:6px;white-space:nowrap;}

.oms-collapse-trigger{cursor:pointer;user-select:none;}
.oms-collapse-trigger:hover{background:rgba(37,99,235,.04);}
.oms-collapse-icon{transition:transform .18s ease;}
.oms-collapse-trigger[aria-expanded="true"] .oms-collapse-icon{transform:rotate(180deg);}
.oms-collapse-panel.is-collapsed{display:none;}
</style>

@if(!$hasSupplier)
    <div class="oms-empty-state">
        <i class="fa-solid fa-hand-pointer fa-2x mb-3"></i>
        <div class="fw-semibold mb-1">Select a supplier</div>
        <div class="text-muted">The central area will open the correct list based on the tab selected on the left.</div>
    </div>
@else

    @if($supplierTab === 'order_notes' || $supplierTab === 'all')
        <div class="oms-section-title {{ $supplierTab === 'all' ? '' : 'border-bottom-0' }} {{ $isAllTab ? 'oms-collapse-trigger' : '' }}"
             @if($isAllTab) data-target="oms-order-panel" aria-expanded="false" @endif>
            <span>Order</span>
            <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-light">{{ $orderNotes->count() }}</span>
                @if($isAllTab)
                    <i class="fa-solid fa-chevron-down oms-collapse-icon"></i>
                @endif
            </div>
        </div>

        <div id="oms-order-panel" class="oms-collapse-panel {{ $isAllTab ? 'is-collapsed' : '' }}">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th class="text-center">Lines</th>
                            <th class="text-center">Ordered</th>
                            <th class="text-center">Billed</th>
                            <th class="text-center">Received</th>
                            <th class="text-center">Global</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orderNotes as $doc)
                            @php
                                $ordered = (int) (($doc->lines ?? collect())->sum('qty_ordered'));
                                $billed = (int) (collect($doc->lines ?? [])->sum('qty_billed_total'));
                                $received = (int) (collect($doc->lines ?? [])->sum('qty_received_total'));
                                $selected = $selectedDocumentType === 'order_note' && $selectedDocumentId === (int) $doc->id;
                                $search = strtolower(trim(($doc->reference ?? '') . ' ' . optional($doc->supplier)->name));
                                $ob = $pairStatus($ordered, $billed);
                                $br = $pairStatus(max($billed, 1), $received);
                                if ($billed <= 0) { $br = 'bad'; }
                                $flow = $globalStatus($ordered, $billed, $received);
                            @endphp
                            <tr class="oms-clickable-row {{ $selected ? 'oms-row-selected' : '' }}"
                                data-search="{{ $search }}"
                                data-summary-url="{{ route('erp.oms.dashboard.fragments.summary', [
                                    'supplier_tab' => request('supplier_tab', 'order_notes'),
                                    'supplier_id' => request('supplier_id'),
                                    'center_tab' => request('center_tab', 'order_notes'),
                                    'document_type' => 'order_note',
                                    'document_id' => $doc->id
                                ]) }}">
                                <td>
                                    <div class="fw-semibold">{{ $doc->reference }}</div>
                                    <div class="small text-muted">{{ optional($doc->created_at)->format('Y-m-d H:i') }}</div>
                                </td>
                                <td class="text-center">{{ $doc->lines->count() }}</td>
                                <td class="text-center">{{ $ordered }}</td>
                                <td class="text-center">
                                    <span class="metric-with-status">
                                        <span>{{ $billed }}</span>
                                        <span class="status-square status-{{ $ob }}" title="Billed vs Ordered">
                                            <i class="fa-solid {{ $iconFor($ob) }}"></i>
                                        </span>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="metric-with-status">
                                        <span>{{ $received }}</span>
                                        <span class="status-square status-{{ $br }}" title="Received vs Billed">
                                            <i class="fa-solid {{ $iconFor($br) }}"></i>
                                        </span>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="status-square status-{{ $flow }}" title="Global flow">
                                        <i class="fa-solid {{ $iconFor($flow) }}"></i>
                                    </span>
                                </td>
                                <td class="text-end actions">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('erp.oms.order_notes.show', $doc) }}" class="btn btn-outline-primary" title="Show" style="margin: 0 5px;"><i class="fa-solid fa-eye"></i></a>
                                        @if(Route::has('erp.oms.order_notes.edit'))
                                            <a href="{{ route('erp.oms.order_notes.edit', $doc) }}" class="btn btn-outline-warning" title="Edit" style="margin: 0 5px;"><i class="fa-solid fa-pencil"></i></a>
                                        @endif
                                        <a href="{{ route('erp.oms.invoices.create', $doc) }}" class="btn btn-outline-success" title="Invoice" style="margin: 0 5px;"><i class="fa-solid fa-file-invoice-dollar"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No order notes pending billing for this supplier.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($supplierTab === 'billed' || $supplierTab === 'all')
        <div class="oms-section-title {{ $supplierTab === 'all' ? 'border-top' : '' }} {{ $isAllTab ? 'oms-collapse-trigger' : '' }}"
             @if($isAllTab) data-target="oms-billed-panel" aria-expanded="false" @endif>
            <span>Billed</span>
            <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-light">{{ $billedOrders->count() }}</span>
                @if($isAllTab)
                    <i class="fa-solid fa-chevron-down oms-collapse-icon"></i>
                @endif
            </div>
        </div>

        <div id="oms-billed-panel" class="oms-collapse-panel {{ $isAllTab ? 'is-collapsed' : '' }}">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Billed Order</th>
                            <th>Invoice</th>
                            <th class="text-center">Lines</th>
                            <th class="text-center">Billed</th>
                            <th class="text-center">Received</th>
                            <th class="text-center">Missing</th>
                            <th class="text-center">Global</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($billedOrders as $doc)
                            @php
                                $billed = (int) (($doc->lines ?? collect())->sum('qty_billed'));
                                $received = (int) (($doc->lines ?? collect())->sum('qty_received'));
                                $ordered = $billed;
                                $missing = max(0, $billed - $received);
                                $selected = $selectedDocumentType === 'billed_order' && $selectedDocumentId === (int) $doc->id;
                                $search = strtolower(trim(($doc->reference ?? '') . ' ' . optional(optional($doc->orderNote)->supplier)->name . ' ' . (optional($doc->invoice)->invoice_reference ?? '')));
                                $ob = 'ok';
                                $br = $pairStatus(max($billed, 1), $received);
                                if ($received <= 0) { $br = 'bad'; }
                                $flow = $globalStatus($ordered, $billed, $received);
                                $hasHistory = (($doc->receptions ?? collect())->count() > 0) || $received > 0;
                            @endphp
                            <tr class="oms-clickable-row {{ $selected ? 'oms-row-selected' : '' }}"
                                data-search="{{ $search }}"
                                data-summary-url="{{ route('erp.oms.dashboard.fragments.summary', [
                                    'supplier_tab' => request('supplier_tab', 'billed'),
                                    'supplier_id' => request('supplier_id'),
                                    'center_tab' => request('center_tab', 'billed'),
                                    'document_type' => 'billed_order',
                                    'document_id' => $doc->id
                                ]) }}">
                                <td>
                                    <div class="fw-semibold">{{ $doc->reference ?? ('#'.$doc->id) }}</div>
                                    <div class="small text-muted">{{ optional($doc->created_at)->format('Y-m-d H:i') }}</div>
                                </td>
                                <td>{{ optional($doc->invoice)->invoice_reference ?? '-' }}</td>
                                <td class="text-center">{{ $doc->lines->count() }}</td>
                                <td class="text-center">
                                    <span class="metric-with-status">
                                        <span>{{ $billed }}</span>
                                        <span class="status-square status-{{ $ob }}" title="Billed vs Ordered">
                                            <i class="fa-solid {{ $iconFor($ob) }}"></i>
                                        </span>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="metric-with-status">
                                        <span>{{ $received }}</span>
                                        <span class="status-square status-{{ $br }}" title="Received vs Billed">
                                            <i class="fa-solid {{ $iconFor($br) }}"></i>
                                        </span>
                                    </span>
                                </td>
                                <td class="text-center">{{ $missing }}</td>
                                <td class="text-center">
                                    <span class="status-square status-{{ $flow }}" title="Global flow">
                                        <i class="fa-solid {{ $iconFor($flow) }}"></i>
                                    </span>
                                </td>
                                <td class="text-end actions">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('erp.oms.billed_orders.show', $doc) }}" class="btn btn-outline-primary" title="Show" style="margin: 0 5px;"><i class="fa-solid fa-eye"></i></a>
                                        <a href="{{ route('erp.oms.receptions.index', ['billed_order_id' => $doc->id]) }}" class="btn btn-outline-warning" title="Receive" style="margin: 0 5px;"><i class="fa-solid fa-truck-ramp-box"></i></a>
                                        @if($hasHistory)
                                            <a href="{{ route('erp.oms.receptions.history', $doc) }}" class="btn btn-outline-dark" title="History" style="margin: 0 5px;"><i class="fa-solid fa-clock-rotate-left"></i></a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No billed orders pending reception for this supplier.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($supplierTab === 'received' || $supplierTab === 'all')
        <div class="oms-section-title {{ $supplierTab === 'all' ? 'border-top' : '' }} {{ $isAllTab ? 'oms-collapse-trigger' : '' }}"
             @if($isAllTab) data-target="oms-received-panel" aria-expanded="false" @endif>
            <span>Received</span>
            <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-light">{{ $receivedOrders->count() }}</span>
                @if($isAllTab)
                    <i class="fa-solid fa-chevron-down oms-collapse-icon"></i>
                @endif
            </div>
        </div>

        <div id="oms-received-panel" class="oms-collapse-panel {{ $isAllTab ? 'is-collapsed' : '' }}">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Billed Order</th>
                            <th>Invoice</th>
                            <th class="text-center">Lines</th>
                            <th class="text-center">Billed</th>
                            <th class="text-center">Received</th>
                            <th class="text-center">Global</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receivedOrders as $doc)
                            @php
                                $billed = (int) (($doc->lines ?? collect())->sum('qty_billed'));
                                $received = (int) (($doc->lines ?? collect())->sum('qty_received'));
                                $selected = $selectedDocumentType === 'billed_order' && $selectedDocumentId === (int) $doc->id;
                                $search = strtolower(trim(($doc->reference ?? '') . ' ' . optional(optional($doc->orderNote)->supplier)->name . ' ' . (optional($doc->invoice)->invoice_reference ?? '')));
                                $hasHistory = (($doc->receptions ?? collect())->count() > 0) || $received > 0;
                            @endphp
                            <tr class="oms-clickable-row {{ $selected ? 'oms-row-selected' : '' }}"
                                data-search="{{ $search }}"
                                data-summary-url="{{ route('erp.oms.dashboard.fragments.summary', [
                                    'supplier_tab' => request('supplier_tab', 'received'),
                                    'supplier_id' => request('supplier_id'),
                                    'center_tab' => request('center_tab', 'received'),
                                    'document_type' => 'billed_order',
                                    'document_id' => $doc->id
                                ]) }}">
                                <td>
                                    <div class="fw-semibold">{{ $doc->reference ?? ('#'.$doc->id) }}</div>
                                    <div class="small text-muted">{{ optional($doc->created_at)->format('Y-m-d H:i') }}</div>
                                </td>
                                <td>{{ optional($doc->invoice)->invoice_reference ?? '-' }}</td>
                                <td class="text-center">{{ $doc->lines->count() }}</td>
                                <td class="text-center">{{ $billed }}</td>
                                <td class="text-center">{{ $received }}</td>
                                <td class="text-center">
                                    <span class="status-square status-ok" title="Global flow">
                                        <i class="fa-solid fa-check"></i>
                                    </span>
                                </td>
                                <td class="text-end actions">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('erp.oms.billed_orders.show', $doc) }}" class="btn btn-outline-primary" title="Show" style="margin: 0 5px;"><i class="fa-solid fa-eye"></i></a>
                                        @if($hasHistory)
                                            <a href="{{ route('erp.oms.receptions.history', $doc) }}" class="btn btn-outline-info" title="History" style="margin: 0 5px;"><i class="fa-solid fa-clock-rotate-left"></i></a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No fully received billed orders for this supplier.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@endif

@if($isAllTab)
<script>
(function () {
    if (window.__omsAllCollapseInit) return;
    window.__omsAllCollapseInit = true;

    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('.oms-collapse-trigger');
        if (!trigger) return;

        const targetId = trigger.getAttribute('data-target');
        if (!targetId) return;

        const panel = document.getElementById(targetId);
        if (!panel) return;

        const isCollapsed = panel.classList.contains('is-collapsed');
        panel.classList.toggle('is-collapsed', !isCollapsed);
        trigger.setAttribute('aria-expanded', isCollapsed ? 'true' : 'false');
    });
})();
</script>
@endif
