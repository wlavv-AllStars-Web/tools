@extends('layouts.app')

@section('content')
@php
    $selected = collect($workOrders)->firstWhere('id', $selectedBilledOrderId) ?? collect($workOrders)->first();
@endphp

@include('modules.oms.includes.js')

<div class="container-fluid py-3 oms-reception-page">
    <div style="border: 1px solid rgba(20, 33, 61, .08); background: #fff; box-shadow: 0 8px 24px rgba(15, 23, 42, .05); padding: 10px;margin-bottom: 10px;">
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap oms-top-strip" style="margin-bottom: 0px;">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('erp.oms.dashboard', ['left_tab' => 'billed_orders', 'document_type' => 'billed_order']) }}" class="btn btn-outline-primary oms-btn-icon">
                    <i class="fa-solid fa-table-columns"></i> Dashboard
                </a>
            </div>
            <div class="fw-semibold text-truncate oms-current-selection" id="omsCurrentSelection">
                {{ $selected ? $selected['reference'] . ' | ' . $selected['supplier_name'] : 'Select a billed order' }}
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('erp.oms.history.reception.stock', $selected['id']) }}" class="btn btn-outline-primary oms-btn-icon"> <i class="fa-solid fa-boxes-stacked"></i> Stock History </a>
                <a href="{{ route('erp.oms.receptions.export.csv') }}" class="btn btn-sm btn-outline-success rounded-2 oms-btn-icon">  <i class="fa-solid fa-file-csv me-1"></i> Export CSV </a>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-2">
            <div class="oms-card h-100">
                <div class="oms-card-header">
                    <div class="oms-soft-label">Reception</div>
                    <div class="fw-bold">Billed Orders</div>
                </div>
                <div class="oms-card-body p-2">
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" id="omsReceptionSearch" class="form-control" placeholder="Reference, barcode or product name..."  style="border-radius: 5px 0 0 5px !important">
                        <span class="input-group-text" style="border-radius: 0 5px 5px 0 !important"><i class="fa-solid fa-magnifying-glass"></i></span>
                    </div>

                    <div id="omsReceptionOrderList" class="oms-order-list">
                        @forelse($workOrders as $order)
                            <button type="button"
                                    class="oms-order-item {{ $selected && $selected['id'] === $order['id'] ? 'active' : '' }}"
                                    data-billed-order-id="{{ $order['id'] }}"
                                    data-search="{{ $order['search_blob'] }}">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <div class="small text-muted">{{ $order['supplier_name'] }}</div>
                                    </div>
                                    <span class="badge text-bg-light border">{{ $order['pending_lines_count'] }}</span>
                                </div>
                                <div class="small text-muted mt-2 d-flex justify-content-between gap-2">
                                    <span>Created: {{ $order['created_at'] }}</span>
                                    <span>Invoice: {{ $order['invoice_date'] }}</span>
                                </div>
                                <div class="small text-muted text-truncate mt-1">Inv: {{ $order['invoice_reference'] }}</div>
                            </button>
                        @empty
                            <div class="oms-empty p-3 text-center">No billed orders pending reception.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            @forelse($workOrders as $order)
                <div class="oms-card oms-order-pane {{ $selected && $selected['id'] === $order['id'] ? '' : 'd-none' }}"
                     id="oms-order-pane-{{ $order['id'] }}"
                     data-order-id="{{ $order['id'] }}"
                     data-order-reference="{{ $order['reference'] }}"
                     data-supplier-name="{{ $order['supplier_name'] }}"
                     data-invoice-reference="{{ $order['invoice_reference'] }}"
                     data-created-at="{{ $order['created_at'] }}"
                     data-invoice-date="{{ $order['invoice_date'] }}">
                    <div class="oms-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <div class="oms-soft-label">Reception workbench</div>
                        </div>
                        <div class="small text-muted d-flex gap-3 flex-wrap">
                            <span><strong>Invoice:</strong> {{ $order['invoice_reference'] }}</span>
                            <span><strong>Created:</strong> {{ $order['created_at'] }}</span>
                            <span><strong>Invoice date:</strong> {{ $order['invoice_date'] }}</span>
                        </div>
                    </div>
                    <div class="oms-card-body p-0">
                        <form method="POST" action="{{ route('erp.oms.receptions.store', $order['id']) }}" class="oms-reception-form">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 oms-reception-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Reference</th>
                                            <th>Name</th>
                                            <th>EAN13</th>
                                            <th class="text-center">Billed</th>
                                            <th class="text-center">Received</th>
                                            <th class="text-center">Remaining</th>
                                            <th class="text-center">Stock</th>
                                            <th class="text-center" style="width: 140px;">Qty to receive</th>
                                            <th class="text-center" style="width: 140px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order['lines'] as $line)
                                            @php
                                                $qtyBilled = (int) ($line['qty_billed'] ?? 0);
                                                $qtyReceived = (int) ($line['qty_received'] ?? 0);
                                                $qtyRemaining = (int) ($line['qty_remaining'] ?? max(0, $qtyBilled - $qtyReceived));

                                                $receivedState = $qtyReceived <= 0 ? 'zero' : ($qtyReceived >= $qtyBilled ? 'complete' : 'partial');
                                                $remainingState = $qtyRemaining <= 0 ? 'complete' : ($qtyReceived > 0 ? 'partial' : 'zero');
                                                $rowState = $qtyRemaining <= 0 ? 'is-complete' : ($qtyReceived > 0 ? 'is-partial' : 'is-open');
                                            @endphp
                                            <tr class="oms-reception-line {{ $rowState }}"
                                                data-line-search="{{ $line['search_blob'] }}"
                                                data-qty-billed="{{ $qtyBilled }}"
                                                data-qty-received="{{ $qtyReceived }}"
                                                data-qty-remaining="{{ $qtyRemaining }}">
                                                <td>
                                                    <span class="oms-copyable oms-reference" data-copy="{{ $line['reference'] }}">{{ $line['reference'] }}</span>
                                                </td>
                                                <td>{{ $line['product_name'] }}</td>
                                                <td>
                                                    <span class="oms-copyable" data-copy="{{ $line['ean13'] !== '-' ? $line['ean13'] : '' }}">{{ $line['ean13'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="oms-metric-cell">
                                                        <span class="oms-metric-value">{{ $qtyBilled }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="oms-metric-cell">
                                                        <span class="oms-metric-value">{{ $qtyReceived }}</span>
                                                        <span class="oms-state-square {{ $receivedState }}">
                                                            <i class="fa-solid {{ $receivedState === 'complete' ? 'fa-check' : ($receivedState === 'partial' ? 'fa-minus' : 'fa-xmark') }}"></i>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="oms-metric-cell">
                                                        <span class="oms-metric-value fw-semibold">{{ $qtyRemaining }}</span>
                                                        <span class="oms-state-square {{ $remainingState }}">
                                                            <i class="fa-solid {{ $remainingState === 'complete' ? 'fa-check' : ($remainingState === 'partial' ? 'fa-minus' : 'fa-xmark') }}"></i>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="text-center">{{ $line['current_stock'] }}</td>
                                                <td class="actions" onclick="event.stopPropagation();">
                                                    <input type="hidden" name="lines[{{ $loop->index }}][billed_order_line_id]" value="{{ $line['id'] }}">
                                                    <input type="number"
                                                           name="lines[{{ $loop->index }}][qty_received]"
                                                           class="form-control form-control-sm text-center oms-qty-input"
                                                           min="0"
                                                           max="{{ $qtyRemaining }}"
                                                           value="0"
                                                           data-line-id="{{ $line['id'] }}"
                                                           data-reference="{{ $line['reference'] }}"
                                                           data-product-name="{{ $line['product_name'] }}"
                                                           data-max="{{ $qtyRemaining }}">
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
                                                            onclick="generateBarcode({{ $billed }}, {{ $received }}, {{ $line['product_id'] }}, {{ $line['product_attribute_id'] ?? 0 }})">
                                                            <i class="fa-solid fa-barcode"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="small text-muted">Only lines with qty greater than zero will be received.</div>
                                <button type="button" class="btn btn-outline-warning btn-sm rounded-2 oms-open-confirmation oms-btn-icon">
                                    <i class="fa-solid fa-boxes-stacked me-1"></i> Confirm Reception
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="oms-card">
                    <div class="oms-card-body p-4 text-center oms-empty">No billed orders pending reception.</div>
                </div>
            @endforelse
        </div>

        <div class="col-xl-2">
            <div class="oms-card">
                <div class="oms-card-header">
                    <div class="oms-soft-label">Summary</div>
                    <div class="fw-bold">Current Reception</div>
                </div>
                <div class="oms-card-body">
                    <div class="oms-summary-list">
                        <div class="oms-summary-row"><div class="oms-summary-key">Supplier</div><div class="oms-summary-value" id="sumSupplier">{{ $selected['supplier_name'] ?? '-' }}</div></div>
                        <div class="oms-summary-row"><div class="oms-summary-key">Invoice</div><div class="oms-summary-value" id="sumInvoice">{{ $selected['invoice_reference'] ?? '-' }}</div></div>
                        <div class="oms-summary-row"><div class="oms-summary-key">Created</div><div class="oms-summary-value" id="sumCreated">{{ $selected['created_at'] ?? '-' }}</div></div>
                        <div class="oms-summary-row"><div class="oms-summary-key">Invoice date</div><div class="oms-summary-value" id="sumInvoiceDate">{{ $selected['invoice_date'] ?? '-' }}</div></div>
                    </div>

                    <div class="oms-stat-grid mt-3">
                        <div class="oms-stat-box"><div class="label">Billed</div><div class="value" id="sumBilled">{{ $selected['total_billed'] ?? 0 }}</div></div>
                        <div class="oms-stat-box"><div class="label">Received</div><div class="value" id="sumReceived">{{ $selected['total_received'] ?? 0 }}</div></div>
                        <div class="oms-stat-box"><div class="label">Remaining</div><div class="value" id="sumRemaining">{{ $selected['total_remaining'] ?? 0 }}</div></div>
                        <div class="oms-stat-box"><div class="label">To receive now</div><div class="value" id="sumNow">0</div></div>
                    </div>

                    <div class="mt-3 small text-muted" id="sumLinesCount">0 lines selected for reception.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="omsReceptionConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-2">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Reception</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="small text-muted mb-3" id="omsReceptionConfirmContext"></div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reference</th>
                                <th>Name</th>
                                <th class="text-center">Qty</th>
                            </tr>
                        </thead>
                        <tbody id="omsReceptionConfirmBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-2" data-bs-dismiss="modal">Back</button>
                <button type="button" class="btn btn-outline-warning btn-sm rounded-2" id="omsReceptionConfirmSubmit">Confirm stock entry</button>
            </div>
        </div>
    </div>
</div>

<style>
.oms-card,.oms-card .btn,.oms-card .form-control,.oms-card .input-group-text,.modal-content,.table,.badge{border-radius:5px !important;}
.oms-card{background:#fff;border:1px solid rgba(15,23,42,.08);box-shadow:0 8px 24px rgba(15,23,42,.06);}
.oms-card-header{padding:12px 14px;border-bottom:1px solid rgba(15,23,42,.06);}
.oms-card-body{padding:14px;}
.oms-soft-label{font-size:.72rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#6b7280;}
.oms-top-strip{min-height:34px;}
.oms-current-selection{max-width:40vw;}
.oms-order-list{display:flex;flex-direction:column;gap:8px;max-height:75vh;overflow:auto;}
.oms-order-item{width:100%;text-align:left;background:#fff;border:1px solid rgba(15,23,42,.08);padding:10px 12px;border-radius:5px;transition:.15s ease;}
.oms-order-item:hover{background:#f8fafc;border-color:rgba(59,130,246,.25);}
.oms-order-item.active{border-color:rgba(59,130,246,.55);background:#eff6ff;box-shadow:inset 0 0 0 1px rgba(59,130,246,.1);}
.oms-summary-list{display:flex;flex-direction:column;gap:8px;}
.oms-summary-row{display:flex;justify-content:space-between;gap:10px;}
.oms-summary-key{font-size:.8rem;color:#6b7280;font-weight:600;}
.oms-summary-value{font-size:.85rem;font-weight:600;text-align:right;}
.oms-stat-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;}
.oms-stat-box{border:1px solid rgba(15,23,42,.08);padding:10px;border-radius:5px;background:#f8fafc;}
.oms-stat-box .label{font-size:.72rem;color:#6b7280;font-weight:700;text-transform:uppercase;}
.oms-stat-box .value{font-size:1.05rem;font-weight:700;}
.oms-copyable{cursor:pointer;}
.oms-copyable:hover{text-decoration:underline;}
.oms-reference{font-weight:600;}
.oms-empty{color:#6b7280;}
.oms-reception-table tbody tr.is-open{background:rgba(248,250,252,.55);}
.oms-reception-table tbody tr.is-partial{background:rgba(255,247,237,.58);}
.oms-reception-table tbody tr.is-complete{background:rgba(240,253,244,.7);}
.oms-metric-cell{display:inline-flex;align-items:center;justify-content:center;gap:8px;white-space:nowrap;}
.oms-metric-value{min-width:18px;display:inline-block;text-align:center;}
.oms-state-square{display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:4px;font-size:.65rem;border:1px solid transparent;}
.oms-state-square.complete{background:rgba(34,197,94,.14);border-color:rgba(34,197,94,.22);color:#15803d;}
.oms-state-square.partial{background:rgba(245,158,11,.14);border-color:rgba(245,158,11,.22);color:#b45309;}
.oms-state-square.zero{background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.2);color:#b91c1c;}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const orderButtons = Array.from(document.querySelectorAll('.oms-order-item'));
    const panes = Array.from(document.querySelectorAll('.oms-order-pane'));
    const searchInput = document.getElementById('omsReceptionSearch');
    let currentPane = document.querySelector('.oms-order-pane:not(.d-none)');
    let currentForm = currentPane ? currentPane.querySelector('form') : null;
    let pendingSubmitForm = null;

    const modalEl = document.getElementById('omsReceptionConfirmModal');
    const modal = (modalEl && window.bootstrap?.Modal)
        ? window.bootstrap.Modal.getOrCreateInstance(modalEl)
        : null;

    function showModal() {
        if (modal) {
            modal.show();
        } else if (window.jQuery && modalEl && typeof window.jQuery(modalEl).modal === 'function') {
            window.jQuery(modalEl).modal('show');
        }
    }

    function updateSummaryFromPane(pane) {
        if (!pane) return;
        currentPane = pane;
        currentForm = pane.querySelector('form');
        document.getElementById('omsCurrentSelection').textContent = pane.dataset.orderReference + ' | ' + pane.dataset.supplierName;
        document.getElementById('sumOrderRef').textContent = pane.dataset.orderReference || '-';
        document.getElementById('sumSupplier').textContent = pane.dataset.supplierName || '-';
        document.getElementById('sumInvoice').textContent = pane.dataset.invoiceReference || '-';
        document.getElementById('sumCreated').textContent = pane.dataset.createdAt || '-';
        document.getElementById('sumInvoiceDate').textContent = pane.dataset.invoiceDate || '-';

        const rows = Array.from(pane.querySelectorAll('.oms-reception-line'));
        let billed = 0, received = 0, remaining = 0, now = 0, count = 0;

        rows.forEach((row) => {
            billed += parseInt(row.dataset.qtyBilled || '0', 10);
            received += parseInt(row.dataset.qtyReceived || '0', 10);
            remaining += parseInt(row.dataset.qtyRemaining || '0', 10);
            const input = row.querySelector('.oms-qty-input');
            const value = parseInt(input?.value || '0', 10);
            if (value > 0) {
                now += value;
                count += 1;
            }
        });

        document.getElementById('sumBilled').textContent = billed;
        document.getElementById('sumReceived').textContent = received;
        document.getElementById('sumRemaining').textContent = remaining;
        document.getElementById('sumNow').textContent = now;
        document.getElementById('sumLinesCount').textContent = count + ' lines selected for reception.';
    }

    function selectOrder(orderId) {
        orderButtons.forEach(btn => btn.classList.toggle('active', String(btn.dataset.billedOrderId) === String(orderId)));
        panes.forEach(pane => pane.classList.toggle('d-none', String(pane.dataset.orderId) !== String(orderId)));
        updateSummaryFromPane(document.getElementById('oms-order-pane-' + orderId));
    }

    orderButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            selectOrder(this.dataset.billedOrderId);
        });
    });

    searchInput?.addEventListener('input', function () {
        const term = this.value.trim().toLowerCase();
        let firstVisible = null;

        orderButtons.forEach(btn => {
            const match = term === '' || (btn.dataset.search || '').includes(term);
            btn.classList.toggle('d-none', !match);
            if (match && !firstVisible) firstVisible = btn;
        });

        if (firstVisible) {
            selectOrder(firstVisible.dataset.billedOrderId);
        } else {
            panes.forEach(p => p.classList.add('d-none'));
            document.getElementById('omsCurrentSelection').textContent = 'No billed order matches the current search';
            document.getElementById('sumOrderRef').textContent = '-';
            document.getElementById('sumSupplier').textContent = '-';
            document.getElementById('sumInvoice').textContent = '-';
            document.getElementById('sumCreated').textContent = '-';
            document.getElementById('sumInvoiceDate').textContent = '-';
            document.getElementById('sumBilled').textContent = '0';
            document.getElementById('sumReceived').textContent = '0';
            document.getElementById('sumRemaining').textContent = '0';
            document.getElementById('sumNow').textContent = '0';
            document.getElementById('sumLinesCount').textContent = '0 lines selected for reception.';
        }
    });

    document.querySelectorAll('.oms-qty-input').forEach(input => {
        input.addEventListener('input', function () {
            const max = parseInt(this.dataset.max || '0', 10);
            let value = parseInt(this.value || '0', 10);

            if (Number.isNaN(value) || value < 0) value = 0;
            if (value > max) value = max;

            this.value = value;

            if (currentPane && currentPane.contains(this)) {
                updateSummaryFromPane(currentPane);
            }
        });

        input.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    });

    document.querySelectorAll('.oms-copyable').forEach(el => {
        el.addEventListener('click', function (e) {
            e.stopPropagation();
            const text = this.dataset.copy || this.textContent.trim();
            if (!text || text === '-') return;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
            } else {
                const helper = document.createElement('textarea');
                helper.value = text;
                document.body.appendChild(helper);
                helper.select();
                document.execCommand('copy');
                document.body.removeChild(helper);
            }
        });
    });

    document.querySelectorAll('.oms-open-confirmation').forEach(btn => {
        btn.addEventListener('click', function () {
            const form = this.closest('form');
            const pane = this.closest('.oms-order-pane');
            const selectedLines = Array.from(form.querySelectorAll('.oms-qty-input'))
                .map(input => ({
                    reference: input.dataset.reference,
                    name: input.dataset.productName,
                    qty: parseInt(input.value || '0', 10),
                }))
                .filter(row => row.qty > 0);

            if (selectedLines.length === 0) {
                alert('Please enter at least one qty to receive.');
                return;
            }

            pendingSubmitForm = form;
            document.getElementById('omsReceptionConfirmContext').textContent = pane.dataset.orderReference + ' | ' + pane.dataset.supplierName + ' | Invoice ' + pane.dataset.invoiceReference;
            const body = document.getElementById('omsReceptionConfirmBody');
            body.innerHTML = selectedLines
                .map(row => `<tr><td>${row.reference}</td><td>${row.name}</td><td class="text-center">${row.qty}</td></tr>`)
                .join('');

            showModal();
        });
    });

    document.getElementById('omsReceptionConfirmSubmit')?.addEventListener('click', function () {
        if (pendingSubmitForm) pendingSubmitForm.submit();
    });

    if (currentPane) updateSummaryFromPane(currentPane);
});
</script>
@endsection
