@extends('layouts.app')

@section('content')
<div class="container-fluid py-3 oms-premium oms-billed-order-show">
    <style>
        .oms-billed-order-show .oms-card{border:1px solid rgba(20,33,61,.10);border-radius:5px;box-shadow:0 8px 24px rgba(15,23,42,.06);overflow:hidden;background:#fff;}
        .oms-billed-order-show .oms-card-header{padding:.85rem 1rem;border-bottom:1px solid rgba(20,33,61,.08);background:#f8fafc;}
        .oms-billed-order-show .oms-card-body{padding:1rem;}
        .oms-billed-order-show .oms-soft-label{font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;font-weight:700;}
        .oms-billed-order-show .oms-btn-icon{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;border-radius:5px;padding:.55rem .85rem;font-weight:600;}
        .oms-billed-order-show .badge,.oms-billed-order-show .btn,.oms-billed-order-show .form-control,.oms-billed-order-show .input-group-text,.oms-billed-order-show .form-select,.oms-billed-order-show .table,.oms-billed-order-show .accordion-button,.oms-billed-order-show .accordion-item{border-radius:5px !important;}
        .oms-billed-order-show .oms-selected-chip{display:inline-flex;align-items:center;gap:.55rem;min-height:42px;padding:.55rem .8rem;border:1px solid rgba(20,33,61,.08);background:#fff;border-radius:5px;color:#111827;font-weight:700;}
        .oms-billed-order-show .oms-kpi{border:1px solid rgba(20,33,61,.08);border-radius:5px;background:#fff;padding:.7rem .9rem;}
        .oms-billed-order-show .oms-summary-list .item{display:flex;justify-content:space-between;gap:.75rem;padding:.45rem 0;border-bottom:1px solid rgba(20,33,61,.06);}
        .oms-billed-order-show .oms-summary-list .item:last-child{border-bottom:none;}
        .oms-billed-order-show .oms-summary-row{display:flex;justify-content:space-between;gap:.75rem;padding:.5rem 0;border-bottom:1px solid rgba(20,33,61,.06);}
        .oms-billed-order-show .oms-summary-row:last-child{border-bottom:none;}
        .oms-billed-order-show .oms-summary-key{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;font-weight:700;}
        .oms-billed-order-show .oms-summary-value{font-weight:700;color:#111827;text-align:right;word-break:break-word;}
        .oms-billed-order-show .oms-muted-panel{border:1px dashed rgba(20,33,61,.12);border-radius:5px;background:#fbfdff;padding:.9rem;}
        .oms-billed-order-show .oms-link-row{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.7rem .85rem;border-bottom:1px solid rgba(20,33,61,.08);text-decoration:none;color:#1f2937;background:#fff;}
        .oms-billed-order-show .oms-link-row:hover{background:#f8fafc;}
        .oms-billed-order-show .oms-link-row-main{min-width:0;}
        .oms-billed-order-show .oms-link-row-title{font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .oms-billed-order-show .oms-link-row-sub{font-size:.78rem;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .oms-billed-order-show .oms-code{display:inline-flex;align-items:center;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;background:#f8fafc;border:1px solid rgba(20,33,61,.08);padding:.18rem .42rem;border-radius:5px;font-size:.79rem;cursor:pointer;}
        .oms-billed-order-show .status-square{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:5px;font-size:11px;font-weight:700;}
        .oms-billed-order-show .status-ok{background:#dcfce7;color:#15803d;border:1px solid #86efac;}
        .oms-billed-order-show .status-warn{background:#ffedd5;color:#c2410c;border:1px solid #fdba74;}
        .oms-billed-order-show .status-bad{background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;}
        .oms-billed-order-show .actions{white-space:nowrap;}
        .oms-billed-order-show .line-note{font-size:.78rem;color:#6b7280;}
        .oms-billed-order-show .oms-empty{padding:2rem 1rem;text-align:center;color:#6b7280;}
        .oms-billed-order-show .oms-copyable{cursor:pointer;}
        .oms-billed-order-show .table > :not(caption) > * > *{vertical-align:middle;}
        .oms-billed-order-show .oms-mini-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.75rem;}
        .oms-billed-order-show .oms-mini-box{border:1px solid rgba(20,33,61,.08);border-radius:5px;background:#fff;padding:.7rem .8rem;}
        .oms-billed-order-show .oms-mini-box .label{font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;font-weight:700;margin-bottom:.18rem;}
        .oms-billed-order-show .oms-mini-box .value{font-weight:800;color:#111827;}
        @media (max-width:1399px){.oms-billed-order-show .oms-main-grid{grid-template-columns:1fr !important;}}
    </style>

    @php
        $createdAt = optional($billedOrder->created_at)->format('Y-m-d H:i');
        $updatedAt = optional($billedOrder->updated_at)->format('Y-m-d H:i');
        $invoiceRef = optional($billedOrder->invoice)->invoice_reference ?? '-';
        $invoiceDate = optional(optional($billedOrder->invoice)->invoice_date)->format('Y-m-d') ?: '-';
        $orderNoteRef = optional($billedOrder->orderNote)->reference ?? '-';
        $lines = $billedOrder->lines ?? collect();
        $receptions = $billedOrder->receptions ?? collect();
        $supplierName = optional(optional($billedOrder->orderNote)->supplier)->name ?: ('Supplier #' . ($billedOrder->supplier_id ?? optional($billedOrder->orderNote)->supplier_id ?? '-'));
        $totalBilled = (int) $lines->sum('qty_billed');
        $totalReceived = (int) $lines->sum('qty_received');
        $totalMissing = max(0, $totalBilled - $totalReceived);
        $progress = $totalBilled > 0 ? (int) round(($totalReceived / max($totalBilled, 1)) * 100) : 0;
        $progress = max(0, min(100, $progress));
        $progressClass = $progress >= 100 ? 'bg-success' : ($progress > 0 ? 'bg-warning' : 'bg-danger');
        $hasPendingReception = $lines->contains(function ($line) {
            return ((int) ($line->qty_missing_to_receive ?? max(0, (int) $line->qty_billed - (int) $line->qty_received))) > 0;
        });
    @endphp
    
    @include('modules.oms.includes.js')
    
    <div class="oms-card mb-3">
        <div class="oms-card-body py-2 px-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <a href="{{ route('erp.oms.dashboard', ['left_tab' => 'billed_orders', 'document_type' => 'billed_order', 'document_id' => $billedOrder->id]) }}" class="btn btn-outline-primary oms-btn-icon">
                        <i class="fa-solid fa-table-columns"></i> Dashboard
                    </a>
                    @if($hasPendingReception)
                        <a href="{{ route('erp.oms.receptions.index', ['billed_order_id' => $billedOrder->id]) }}" class="btn btn-outline-warning oms-btn-icon">
                            <i class="fa-solid fa-truck-ramp-box"></i> Reception
                        </a>
                    @endif
                </div>

                <div class="text-center flex-grow-1 px-2" style="min-width:280px;">
                    <div class="oms-selected-chip justify-content-center">
                        <i class="fa-solid fa-file-invoice"></i>
                        <span>{{ $supplierName }}</span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <a href="{{ route('erp.oms.history.reception.stock', $billedOrder) }}" class="btn btn-outline-primary oms-btn-icon"> <i class="fa-solid fa-boxes-stacked"></i> Stock History </a>
                    <a href="{{ route('erp.oms.receptions.history', $billedOrder) }}" class="btn btn-outline-primary oms-btn-icon">
                        <i class="fa-solid fa-box-open"></i> Reception History
                    </a>
                    <a href="{{ route('erp.oms.billed_orders.export.csv', $billedOrder) }}" class="btn btn-outline-success oms-btn-icon">
                        <i class="fa-solid fa-file-csv"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-3"><div class="oms-kpi"><div class="oms-soft-label">Status</div><div class="h5 fw-bold mb-0 text-capitalize">{{ str_replace('_', ' ', (string) $billedOrder->status) }}</div></div></div>
        <div class="col-lg-3"><div class="oms-kpi"><div class="oms-soft-label">Created</div><div class="h5 fw-bold mb-0">{{ $createdAt ?: '-' }}</div></div></div>
        <div class="col-lg-3"><div class="oms-kpi"><div class="oms-soft-label">Invoice Date</div><div class="h5 fw-bold mb-0">{{ $invoiceDate }}</div></div></div>
        <div class="col-lg-3"><div class="oms-kpi"><div class="oms-soft-label">Reception Progress</div><div class="h5 fw-bold mb-0">{{ $progress }}%</div></div></div>
    </div>

    <div class="oms-main-grid" style="display:grid;grid-template-columns:minmax(0,2fr) minmax(0,8fr) minmax(0,2fr);gap:1rem;align-items:start;">
        <div>
            <div class="oms-card">
                <div class="oms-card-header">
                    <div class="oms-soft-label">Logistics</div>
                    <div class="fw-bold">Shipment Relation</div>
                </div>
                <div class="oms-card-body">
                    @if($billedOrder->invoice)
                        <form method="POST" action="{{ route('erp.oms.billed_orders.shipment.save', $billedOrder) }}" class="d-flex flex-column gap-2">
                            @csrf
                            <div>
                                <label class="oms-soft-label mb-1">Shipment</label>
                                <select name="shipment_id" class="form-select">
                                    <option value="">No shipment linked</option>
                                    @foreach($availableShipments as $shipment)
                                        <option value="{{ $shipment->id }}" {{ (int) ($selectedShipmentId ?? 0) === (int) $shipment->id ? 'selected' : '' }}>
                                            #{{ $shipment->id }} | {{ $shipment->tracking ?: ($shipment->invoice_number ?: 'No tracking') }} | Status: {{ ((int) $shipment->status === 1) ? 'Waiting' : 'In transit' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="small text-muted">
                                Available shipments with status "Waiting" or "In Transit" only.
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="submit" class="btn btn-outline-primary btn-sm rounded-2 oms-btn-icon">
                                    <i class="fa-solid fa-link me-1"></i> Save Relation
                                </button>
                            </div>
                        </form>

                        @if($selectedShipment)
                            <div class="oms-summary-list mt-3 pt-3 border-top">
                                <div class="oms-summary-row"><div class="oms-summary-key">Linked shipment</div><div class="oms-summary-value">#{{ $selectedShipment->id }}</div></div>
                                <div class="oms-summary-row"><div class="oms-summary-key">Tracking</div><div class="oms-summary-value">{{ $selectedShipment->tracking ?: '-' }}</div></div>
                                <div class="oms-summary-row"><div class="oms-summary-key">Invoice no.</div><div class="oms-summary-value">{{ $selectedShipment->invoice_number ?: '-' }}</div></div>
                                <div class="oms-summary-row"><div class="oms-summary-key">Carrier</div><div class="oms-summary-value">{{ $selectedShipment->carrier_name ?? 'UNKNOWN' }}</div></div>
                                <div class="oms-summary-row"><div class="oms-summary-key">Status</div><div class="oms-summary-value">{{ ((int) $selectedShipment->status === 1) ? 'Waiting' : (((int) $selectedShipment->status === 2) ? 'In transit' : (string) $selectedShipment->status) }}</div></div>
                            </div>
                        @endif
                    @else
                        <div class="oms-muted-panel small">
                            This billed order is not linked to an invoice yet, so no shipment relation can be stored.
                        </div>
                    @endif
                </div>
            </div>
            <div class="oms-card mt-3">
                <div class="oms-card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="oms-soft-label">Reception Log</div>
                        <div class="fw-bold">Registered Receptions</div>
                    </div>
                    <span class="badge text-bg-light border">{{ $receptions->count() }}</span>
                </div>
                <div class="oms-card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($receptions as $reception)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">#{{ $reception->id }}</div>
                                    <div class="small text-muted">{{ optional($reception->created_at)->format('Y-m-d H:i') }}</div>
                                </div>
                                <span class="badge text-bg-light border">{{ ($reception->lines ?? collect())->count() }} lines</span>
                            </div>
                        @empty
                            <div class="oms-empty">No receptions registered.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="oms-card">
                <div class="oms-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <div class="oms-soft-label">Operational Detail</div>
                        <div class="fw-bold">Billed Order Lines</div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge text-bg-light border">{{ $lines->count() }} lines</span>
                        <span class="badge text-bg-light border">{{ $totalBilled }} billed</span>
                        <span class="badge text-bg-light border">{{ $totalReceived }} received</span>
                    </div>
                </div>
                <div class="oms-card-body">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" id="omsBilledLineSearch" class="form-control" placeholder="Search current list...">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="omsBilledLinesTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width:52px;"></th>
                                    <th>Product</th>
                                    <th class="text-center">Product ID</th>
                                    <th class="text-center">Attr.</th>
                                    <th class="text-center">Billed</th>
                                    <th class="text-center">Received</th>
                                    <th class="text-center">Missing</th>
                                    <th class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lines as $index => $line)
                                    @php
                                        $missing = (int) ($line->qty_missing_to_receive ?? max(0, (int)$line->qty_billed - (int)$line->qty_received));
                                        $productLabel = trim((string) ($line->display_product_name ?? $line->display_reference ?? ('Product #' . $line->product_id)));
                                        $statusClass = $missing <= 0 ? 'status-ok' : (((int)$line->qty_received > 0) ? 'status-warn' : 'status-bad');
                                        $statusIcon = $missing <= 0 ? 'fa-check' : (((int)$line->qty_received > 0) ? 'fa-minus' : 'fa-xmark');
                                        $search = strtolower(trim(($line->display_reference  ?? '') . ' ' . ($productLabel ?? '') . ' ' . ($line->product_id ?? '') . ' ' . ($line->product_attribute_id ?? '')));
                                    @endphp
                                    <tr class="oms-main-line" data-search="{{ $search }}" data-line-id="{{ $line->id }}">
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary oms-toggle-btn" type="button" data-bs-toggle="collapse" data-bs-target="#boLine{{ $line->id }}" aria-expanded="false" aria-controls="boLine{{ $line->id }}">
                                                <i class="fa-solid fa-chevron-down"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $productLabel }}</div>
                                            @if(!empty($line->display_reference ))
                                                <div class="line-note"><span class="oms-code oms-copyable" data-copy="{{ $line->display_reference  }}">{{ $line->display_reference  }}</span></div>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $line->product_id }}</td>
                                        <td class="text-center">{{ $line->product_attribute_id ?: '-' }}</td>
                                        <td class="text-center fw-semibold">{{ $line->qty_billed }}</td>
                                        <td class="text-center">{{ $line->qty_received }}</td>
                                        <td class="text-center">
                                            <div class="d-inline-flex align-items-center gap-2">
                                                <span>{{ $missing }}</span>
                                                <span class="status-square {{ $statusClass }}"><i class="fa-solid {{ $statusIcon }}"></i></span>
                                            </div>
                                        </td>
                                        <td>
@php
    $billed = (int) ($line['qty_billed'] ?? 0);
    $received = (int) ($line['qty_received'] ?? 0);
    $pending = max(0, $billed - $received);
@endphp
                                            
                                            @if($pending > 0)
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-primary oms-btn-icon"
                                                    title="Print EAN"
                                                    onclick="generateBarcode({{ $billed }}, {{ $received }}, {{ $line->product_id }}, {{ $line->product_attribute_id ?? 0 }})">
                                                    <i class="fa-solid fa-barcode"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="collapse bg-light-subtle" id="boLine{{ $line->id }}">
                                        <td></td>
                                        <td colspan="7">
                                            <div class="py-2">
                                                <div class="oms-mini-grid">
                                                    <div class="oms-mini-box"><div class="label">Line ID</div><div class="value oms-copyable" data-copy="{{ $line->id }}">{{ $line->id }}</div></div>
                                                    <div class="oms-mini-box"><div class="label">Reference</div><div class="value oms-copyable" data-copy="{{ $line->display_reference  ?? '' }}">{{ $line->display_reference  ?? '-' }}</div></div>
                                                    <div class="oms-mini-box"><div class="label">Product</div><div class="value">{{ $productLabel }}</div></div>
                                                    <div class="oms-mini-box"><div class="label">Product / Attribute</div><div class="value oms-copyable" data-copy="{{ $line->product_id }} | {{ $line->product_attribute_id ?: 0 }}">{{ $line->product_id }} | {{ $line->product_attribute_id ?: 0 }}</div></div>
                                                    <div class="oms-mini-box"><div class="label">Qty Billed</div><div class="value">{{ $line->qty_billed }}</div></div>
                                                    <div class="oms-mini-box"><div class="label">Qty Received</div><div class="value">{{ $line->qty_received }}</div></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="oms-empty">No lines found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="oms-card">
                <div class="oms-card-header">
                    <div class="oms-soft-label">Summary</div>
                    <div class="fw-bold">Document Summary</div>
                </div>
                <div class="oms-card-body">
                    <div class="oms-summary-list mb-3">
                        <div class="item"><span class="text-muted">Type</span><strong>Billed Order</strong></div>
                        <div class="item"><span class="text-muted">Supplier</span><strong>{{ $supplierName }}</strong></div>
                        <div class="item"><span class="text-muted">Updated</span><strong>{{ $updatedAt ?: '-' }}</strong></div>
                        <div class="item"><span class="text-muted">Invoice</span><strong>{{ $invoiceRef }}</strong></div>
                        <div class="item"><span class="text-muted">Order Note</span><strong>{{ $orderNoteRef }}</strong></div>
                    </div>


                    @php
                        $billedLogistics = app(\App\Services\oms\OrderNoteLogisticsService::class)->calculateFromBilledOrder($billedOrder);
                    @endphp

                    <div class="oms-summary-list mb-3">
                        <div class="oms-summary-row"><div class="oms-summary-key">Volume</div><div class="oms-summary-value">{{ number_format((float) data_get($billedLogistics, 'totals.volume_m3', 0), 3, ',', '.') }} m³</div></div>
                        <div class="oms-summary-row"><div class="oms-summary-key">Weight</div><div class="oms-summary-value">{{ number_format((float) data_get($billedLogistics, 'totals.weight_kg', 0), 2, ',', '.') }} kg</div></div>
                        <div class="oms-summary-row"><div class="oms-summary-key">Missing logistics</div><div class="oms-summary-value">{{ (int) data_get($billedLogistics, 'totals.missing_count', 0) }}</div></div>
                    </div>

                    @if(!empty(data_get($billedLogistics, 'suggestions')))
                        <div class="oms-muted-panel mb-3 small">
                            <div class="fw-semibold mb-1">Best fit</div>
                            @foreach(data_get($billedLogistics, 'suggestions', []) as $container)
                                <div class="d-flex justify-content-between {{ !$loop->last ? 'mb-1' : '' }}">
                                    <span>{{ ucfirst((string) data_get($container, 'type', 'container')) }} · {{ data_get($container, 'name', '-') }}</span>
                                    <strong>{{ (int) data_get($container, 'units_needed', 0) }}x</strong>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    <div class="oms-mini-grid mb-3">
                        <div class="oms-mini-box"><div class="label">Lines</div><div class="value">{{ $lines->count() }}</div></div>
                        <div class="oms-mini-box"><div class="label">Receptions</div><div class="value">{{ $receptions->count() }}</div></div>
                        <div class="oms-mini-box"><div class="label">Billed</div><div class="value">{{ $totalBilled }}</div></div>
                        <div class="oms-mini-box"><div class="label">Received</div><div class="value">{{ $totalReceived }}</div></div>
                    </div>

                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <span class="oms-soft-label" style="font-size:.72rem;">Reception Progress</span>
                        <strong>{{ $progress }}%</strong>
                    </div>
                    <div class="progress" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100" style="height:12px;border-radius:999px;">
                        <div class="progress-bar {{ $progressClass }}" style="width: {{ $progress }}%"></div>
                    </div>

                    <div class="oms-muted-panel mt-3 small">
                        <div class="fw-semibold mb-1">Notes</div>
                        <div><strong>Internal:</strong> {{ $billedOrder->internal_note ?: '-' }}</div>
                        <div class="mt-2"><strong>Logistic:</strong> {{ $billedOrder->logistic_note ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('omsBilledLineSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const q = (this.value || '').toLowerCase().trim();
                document.querySelectorAll('#omsBilledLinesTable tbody tr.oms-main-line').forEach(function (row) {
                    const match = !q || (row.dataset.search || '').includes(q);
                    row.style.display = match ? '' : 'none';
                    const detail = document.getElementById('boLine' + row.dataset.lineId);
                    if (detail) {
                        detail.style.display = match ? '' : 'none';
                        if (!match) {
                            detail.classList.remove('show');
                            const toggle = row.querySelector('.oms-toggle-btn');
                            if (toggle) {
                                toggle.setAttribute('aria-expanded', 'false');
                            }
                        }
                    }
                });
            });
        }

        document.querySelectorAll('.oms-copyable').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const value = this.dataset.copy || this.textContent || '';
                if (!value) return;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(value.trim());
                }
            });
        });
    });
</script>
@endsection
