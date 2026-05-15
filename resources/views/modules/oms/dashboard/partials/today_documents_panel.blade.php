@php
    $supplierTab = $state['supplier_tab'] ?? 'order_notes';
    $selectedDocumentType = $state['document_type'] ?? null;
    $selectedDocumentId = (int) ($state['document_id'] ?? 0);

    $orderNotes = collect($documentsPane['order_notes'] ?? []);
    $billedOrders = collect($documentsPane['billed_orders'] ?? []);
    $receivedOrders = collect($documentsPane['received_orders'] ?? []);
    $hasSupplier = !empty($state['supplier_id']);

    $statusIcon = function (string $type) {
        return match ($type) {
            'ok' => 'fa-check',
            'warn' => 'fa-minus',
            default => 'fa-xmark',
        };
    };

    $pairStatus = function (int $base, int $current) {
        if ($current <= 0) {
            return 'bad';
        }
        if ($current >= $base && $base > 0) {
            return 'ok';
        }
        return 'warn';
    };

    $globalStatus = function (int $ordered, int $billed, int $received) {
        if ($billed <= 0 && $received <= 0) {
            return 'bad';
        }
        if ($ordered > 0 && $ordered === $billed && $billed === $received) {
            return 'ok';
        }
        return 'warn';
    };
@endphp

<style>
.status-square{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:5px;font-size:11px;font-weight:700;}
.status-ok{background:#dcfce7;color:#15803d;border:1px solid #86efac;}
.status-warn{background:#ffedd5;color:#c2410c;border:1px solid #fdba74;}
.status-bad{background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;}
.oms-status-stack{display:inline-flex;gap:6px;align-items:center;}
.oms-status-item{display:inline-flex;align-items:center;gap:4px;}
.oms-status-mini{font-size:.68rem;font-weight:700;color:#6b7280;text-transform:uppercase;}
</style>

@if(!$hasSupplier)
    <div class="oms-empty-state">
        <i class="fa-solid fa-hand-pointer fa-2x mb-3"></i>
        <div class="fw-semibold mb-1">Select a supplier</div>
        <div class="text-muted">The central area will open the correct list based on the tab selected on the left.</div>
    </div>
@else

    @if($supplierTab === 'order_notes' || $supplierTab === 'all')
        <div class="oms-section-title {{ $supplierTab === 'all' ? '' : 'border-bottom-0' }}">
            <span>Order</span>
            <span class="badge text-bg-light">{{ $orderNotes->count() }}</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th class="text-center">Lines</th>
                        <th class="text-center">Ordered</th>
                        <th class="text-center">Billed</th>
                        <th class="text-center">Received</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orderNotes as $doc)
                        @php
                            $ordered = (int) (($doc->lines ?? collect())->sum('qty_ordered'));
                            $billed = (int) (($doc->lines ?? collect())->sum(fn ($line) => $line->qty_billed_total ?? 0));
                            $received = (int) (($doc->lines ?? collect())->sum(fn ($line) => $line->qty_received_total ?? 0));
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
                            <td class="text-center">{{ $billed }}</td>
                            <td class="text-center">{{ $received }}</td>
                            <td class="text-center">
                                <div class="oms-status-stack">
                                    <span class="oms-status-item" title="Ordered / Billed">
                                        <span class="status-square status-{{ $ob }}"><i class="fa-solid {{ $statusIcon($ob) }}"></i></span>
                                    </span>
                                    <span class="oms-status-item" title="Billed / Received">
                                        <span class="status-square status-{{ $br }}"><i class="fa-solid {{ $statusIcon($br) }}"></i></span>
                                    </span>
                                    <span class="oms-status-item" title="Global Flow">
                                        <span class="status-square status-{{ $flow }}"><i class="fa-solid {{ $statusIcon($flow) }}"></i></span>
                                    </span>
                                </div>
                            </td>
                            <td class="text-end actions">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('erp.oms.order_notes.show', $doc) }}" class="btn btn-outline-primary" title="Show"><i class="fa-solid fa-eye"></i></a>
                                    @if(Route::has('erp.oms.order_notes.edit'))
                                        <a href="{{ route('erp.oms.order_notes.edit', $doc) }}" class="btn btn-outline-warning" title="Edit"><i class="fa-solid fa-pencil"></i></a>
                                    @endif
                                    <a href="{{ route('erp.oms.invoices.create', $doc) }}" class="btn btn-outline-success" title="Invoice"><i class="fa-solid fa-file-invoice-dollar"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No order notes pending billing for this supplier.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if($supplierTab === 'billed' || $supplierTab === 'all')
        <div class="oms-section-title {{ $supplierTab === 'all' ? 'border-top' : '' }}">
            <span>Billed</span>
            <span class="badge text-bg-light">{{ $billedOrders->count() }}</span>
        </div>

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
                        <th class="text-center">Status</th>
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
                            <td class="text-center">{{ $billed }}</td>
                            <td class="text-center">{{ $received }}</td>
                            <td class="text-center">{{ $missing }}</td>
                            <td class="text-center">
                                <div class="oms-status-stack">
                                    <span class="status-square status-{{ $ob }}" title="Ordered / Billed"><i class="fa-solid {{ $statusIcon($ob) }}"></i></span>
                                    <span class="status-square status-{{ $br }}" title="Billed / Received"><i class="fa-solid {{ $statusIcon($br) }}"></i></span>
                                    <span class="status-square status-{{ $flow }}" title="Global Flow"><i class="fa-solid {{ $statusIcon($flow) }}"></i></span>
                                </div>
                            </td>
                            <td class="text-end actions">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('erp.oms.billed_orders.show', $doc) }}" class="btn btn-outline-primary" title="Show"><i class="fa-solid fa-eye"></i></a>
                                    <a href="{{ route('erp.oms.receptions.index', ['billed_order_id' => $doc->id]) }}" class="btn btn-outline-warning" title="Receive"><i class="fa-solid fa-truck-ramp-box"></i></a>
                                    @if($hasHistory)
                                        <a href="{{ route('erp.oms.receptions.history', $doc) }}" class="btn btn-outline-info" title="History"><i class="fa-solid fa-clock-rotate-left"></i></a>
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
    @endif

    @if($supplierTab === 'received' || $supplierTab === 'all')
        <div class="oms-section-title {{ $supplierTab === 'all' ? 'border-top' : '' }}">
            <span>Received</span>
            <span class="badge text-bg-light">{{ $receivedOrders->count() }}</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Billed Order</th>
                        <th>Invoice</th>
                        <th class="text-center">Lines</th>
                        <th class="text-center">Billed</th>
                        <th class="text-center">Received</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receivedOrders as $doc)
                        @php
                            $billed = (int) (($doc->lines ?? collect())->sum('qty_billed'));
                            $received = (int) (($doc->lines ?? collect())->sum('qty_received'));
                            $ordered = $billed;
                            $selected = $selectedDocumentType === 'billed_order' && $selectedDocumentId === (int) $doc->id;
                            $search = strtolower(trim(($doc->reference ?? '') . ' ' . optional(optional($doc->orderNote)->supplier)->name . ' ' . (optional($doc->invoice)->invoice_reference ?? '')));
                            $ob = 'ok'; $br = 'ok'; $flow = 'ok';
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
                                <div class="oms-status-stack">
                                    <span class="status-square status-ok" title="Ordered / Billed"><i class="fa-solid fa-check"></i></span>
                                    <span class="status-square status-ok" title="Billed / Received"><i class="fa-solid fa-check"></i></span>
                                    <span class="status-square status-ok" title="Global Flow"><i class="fa-solid fa-check"></i></span>
                                </div>
                            </td>
                            <td class="text-end actions">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('erp.oms.billed_orders.show', $doc) }}" class="btn btn-outline-primary" title="Show"><i class="fa-solid fa-eye"></i></a>
                                    @if($hasHistory)
                                        <a href="{{ route('erp.oms.receptions.history', $doc) }}" class="btn btn-outline-info" title="History"><i class="fa-solid fa-clock-rotate-left"></i></a>
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
    @endif

@endif
