@extends('layouts.app')

@section('content')
@php
    $status = $invoice->status ?? 'draft';
    $supplierName = optional($invoice->supplier)->name ?: ('Supplier #' . $invoice->supplier_id);
    $lineCount = $invoice->billedOrders->sum(fn($bo) => $bo->lines->count());
    $qtyBilled = $invoice->billedOrders->sum(fn($bo) => $bo->lines->sum('qty_billed'));
    $qtyReceived = $invoice->billedOrders->sum(fn($bo) => $bo->lines->sum(fn($line) => $line->qty_received_calculated ?? $line->qty_received ?? 0));
    $qtyRemaining = max(0, (int) $qtyBilled - (int) $qtyReceived);
    $progress = $qtyBilled > 0 ? min(100, round(($qtyReceived / $qtyBilled) * 100)) : 0;
    $availableShipments = $availableShipments ?? collect();
    $linkedShipmentIds = $linkedShipmentIds ?? collect();
    $selectedShipmentId = (int) ($selectedShipmentId ?? 0);
    $selectedShipment = $availableShipments->firstWhere('id', $selectedShipmentId);
@endphp

<div class="container-fluid py-3 oms-premium">
    @if(session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning py-2">{{ session('warning') }}</div>
    @endif
    <div style="border: 1px solid rgba(20, 33, 61, .08); background: #fff; box-shadow: 0 8px 24px rgba(15, 23, 42, .05); padding: 10px;margin-bottom: 10px;">
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap oms-top-strip">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('erp.oms.dashboard', ['left_tab' => 'invoices']) }}" class="btn btn-outline-primary btn-sm"> <i class="fa-solid fa-table-columns me-1"></i> Dashboard </a>
                @if($status === 'draft' && $invoice->billedOrders->count() > 0)
                    <form action="{{ route('erp.oms.invoices.close', $invoice) }}" method="POST" class="d-inline" onsubmit="return confirm('Close this invoice? After closing it, no more order notes can be added.');">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning btn-sm rounded-2 oms-btn-icon">
                            <i class="fa-solid fa-lock me-1"></i> Close Invoice
                        </button>
                    </form>
                @endif
                <a href="{{ route('erp.oms.receptions.index', ['invoice_id' => $invoice->id]) }}" class="btn btn-outline-warning btn-sm rounded-2 oms-btn-icon">
                    <i class="fa-solid fa-boxes-stacked me-1"></i> Reception
                </a>
                <a href="{{ route('erp.oms.history.invoice.prices', $invoice->id) }}" style="padding-top: .1rem;padding-bottom: .1rem;" class="btn btn-outline-primary oms-btn-icon"> <i class="fa-solid fa-chart-line"></i> Price History </a>
            </div>
            <div class="fw-semibold text-truncate oms-current-selection">
                <button type="button" class="btn btn-link p-0 border-0 fw-semibold js-edit-invoice-reference">{{ $invoice->invoice_reference }} <i class="fa-solid fa-pen ms-1 small"></i></button> | {{ $supplierName }}
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('erp.oms.receptions.invoice_history', $invoice) }}" class="btn btn-outline-primary btn-sm rounded-2 oms-btn-icon">
                    <i class="fa-solid fa-box-open me-1"></i> Reception History
                </a>
                <a href="{{ route('erp.oms.invoices.export.csv', $invoice) }}" class="btn btn-outline-success btn-sm rounded-2 oms-btn-icon">
                    <i class="fa-solid fa-file-csv me-1"></i> Export CSV
                </a>
                @if($status !== 'cancelled')
                    <form action="{{ route('erp.oms.invoices.cancel', $invoice) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel invoice? This will revert operational invoiced quantities, but will not revert product costs.');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-2 oms-btn-icon">
                            <i class="fa-solid fa-ban me-1"></i> Cancel Invoice
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-3">
            <div class="oms-section-stack">
                <div class="oms-card">
                    <div class="oms-card-header">
                        <div class="oms-soft-label">Invoice</div>
                        <div class="fw-bold">Document Context</div>
                    </div>
                    <div class="oms-card-body">
                        <div class="oms-summary-list">
                            <div class="oms-summary-row"><div class="oms-summary-key">Reference</div><div class="oms-summary-value"><button type="button" class="btn btn-link p-0 border-0 fw-semibold js-edit-invoice-reference">{{ $invoice->invoice_reference }} <i class="fa-solid fa-pen ms-1 small"></i></button></div></div>
                            <div class="oms-summary-row"><div class="oms-summary-key">Supplier</div><div class="oms-summary-value">{{ $supplierName }}</div></div>
                            <div class="oms-summary-row"><div class="oms-summary-key">Status</div><div class="oms-summary-value">{{ ucfirst($status) }}</div></div>
                            <div class="oms-summary-row"><div class="oms-summary-key">Currency</div><div class="oms-summary-value">{{ $invoice->currency_iso ?: 'EUR' }}</div></div>
                            <div class="oms-summary-row"><div class="oms-summary-key">Invoice date</div><div class="oms-summary-value">{{ $invoice->invoice_date ?: '-' }}</div></div>
                            <div class="oms-summary-row"><div class="oms-summary-key">Due date</div><div class="oms-summary-value">{{ $invoice->due_date ?: '-' }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="oms-card">
                    <div class="oms-card-header">
                        <div class="oms-soft-label">Logistics</div>
                        <div class="fw-bold">Shipment Relation</div>
                    </div>
                    <div class="oms-card-body">
                        <form method="POST" action="{{ route('erp.oms.invoices.shipment.save', $invoice) }}" class="d-flex flex-column gap-2">
                            @csrf
                            <div>
                                <label class="oms-soft-label mb-1">Shipment</label>
                                <select name="shipment_id" class="form-select">
                                    <option value="">No shipment linked</option>
                                    @foreach($availableShipments as $shipment)
                                        <option value="{{ $shipment->id }}" {{ $selectedShipmentId === (int) $shipment->id ? 'selected' : '' }}>
                                            #{{ $shipment->id }} | {{ $shipment->tracking ?: ($shipment->invoice_number ?: 'No tracking') }} | Status: {{ ($shipment->status == 1) ? 'Waiting' : 'In transit'  }}
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
                                <div class="oms-summary-row"><div class="oms-summary-key">Status</div><div class="oms-summary-value">{{ $selectedShipment->status }}</div></div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="oms-card">
                    <div class="oms-card-header">
                        <div class="oms-soft-label">Links</div>
                        <div class="fw-bold">Related Billed Orders</div>
                    </div>
                    <div class="oms-card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($invoice->billedOrders as $billedOrder)
                                <a href="{{ route('erp.oms.billed_orders.show', $billedOrder) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">{{ $billedOrder->display_reference }}</span>
                                    <span class="text-muted small">{{ $billedOrder->lines->count() }} lines</span>
                                </a>
                            @empty
                                <div class="list-group-item text-muted">No billed orders linked.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="oms-card">
                <div class="oms-card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                    <div>
                        <div class="oms-soft-label">Invoice</div>
                        <div class="fw-bold">Billed Orders</div>
                    </div>
                    <span class="badge text-bg-light border">{{ $invoice->billedOrders->count() }} documents</span>
                </div>
                <div class="oms-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 oms-main-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Billed Order</th>
                                    <th>Order Note</th>
                                    <th class="text-center">Lines</th>
                                    <th class="text-center">Billed</th>
                                    <th class="text-center">Received</th>
                                    <th class="text-center">Remaining</th>
                                    <th class="text-end actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->billedOrders as $billedOrder)
                                    @php
                                        $boQtyBilled = (int) $billedOrder->lines->sum('qty_billed');
                                        $boQtyReceived = (int) $billedOrder->lines->sum(fn($line) => $line->qty_received_calculated ?? $line->qty_received ?? 0);
                                        $boQtyRemaining = max(0, $boQtyBilled - $boQtyReceived);

                                        $receivedState = $boQtyReceived <= 0 ? 'zero' : ($boQtyReceived >= $boQtyBilled ? 'complete' : 'partial');
                                        $remainingState = $boQtyRemaining <= 0 ? 'complete' : ($boQtyReceived > 0 ? 'partial' : 'zero');
                                    @endphp
                                    <tr class="oms-toggle-row" data-target="#bo-lines-{{ $billedOrder->id }}">
                                        <td class="fw-semibold">{{ $billedOrder->reference }}</td>
                                        <td>{{ optional($billedOrder->orderNote)->reference ?: '-' }}</td>
                                        <td class="text-center">{{ $billedOrder->lines->count() }}</td>
                                        <td class="text-center">{{ $boQtyBilled }}</td>
                                        <td class="text-center">
                                            <div class="oms-metric-cell">
                                                <span class="oms-metric-value">{{ $boQtyReceived }}</span>
                                                <span class="oms-state-square {{ $receivedState }}">
                                                    <i class="fa-solid {{ $receivedState === 'complete' ? 'fa-check' : ($receivedState === 'partial' ? 'fa-minus' : 'fa-xmark') }}"></i>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="oms-metric-cell">
                                                <span class="oms-metric-value fw-semibold">{{ $boQtyRemaining }}</span>
                                                <span class="oms-state-square {{ $remainingState }}">
                                                    <i class="fa-solid {{ $remainingState === 'complete' ? 'fa-check' : ($remainingState === 'partial' ? 'fa-minus' : 'fa-xmark') }}"></i>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-end actions">
                                            <a href="{{ route('erp.oms.billed_orders.show', $billedOrder) }}" class="btn btn-outline-primary btn-sm rounded-2 oms-btn-icon" onclick="event.stopPropagation();">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr id="bo-lines-{{ $billedOrder->id }}" class="oms-hidden-row d-none">
                                        <td colspan="7">
                                            <div class="p-2">
                                                <div class="table-responsive">
                                                    <table class="table table-sm align-middle mb-0 oms-inner-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Reference</th>
                                                                <th>Product</th>
                                                                <th class="text-center">Billed</th>
                                                                <th class="text-end">Unit Price</th>
                                                                <th class="text-end">Unit Price EUR</th>
                                                                <th class="text-end">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($billedOrder->lines as $line)
                                                                @php
                                                                    $lineHistory = $lineHistoryMap->get((int) $line->id);
                                                            
                                                                    $unitPriceInvoiceCurrency = (float) data_get($lineHistory, 'unit_price_invoice_currency', 0);
                                                                    $unitPriceEur = (float) data_get($lineHistory, 'unit_price_eur', 0);
                                                                    $lineCurrencyIso = data_get($lineHistory, 'invoice_currency_iso')
                                                                        ?: ($invoice->currency_iso ?: 'EUR');
                                                                    $lineReceived = (int) ($line->qty_received_calculated ?? $line->qty_received ?? 0);
                                                                @endphp
                                                                <tr>
                                                                    <td><span class="oms-copyable" data-copy="{{ $line->display_reference ?: '' }}">{{ $line->display_reference ?: '-' }}</span></td>
                                                                    <td>{{ $line->display_product_name ?: ('Product #' . $line->product_id) }}</td>
                                                                    <td class="text-center">
                                                                        @if($status !== 'cancelled')
                                                                            <button type="button" class="btn btn-link p-0 border-0 fw-semibold js-edit-invoice-line" data-line-id="{{ $line->id }}" data-quantity="{{ $line->qty_billed }}" data-minimum="{{ $lineReceived }}">{{ $line->qty_billed }} <i class="fa-solid fa-pen ms-1 small"></i></button>
                                                                        @else
                                                                            {{ $line->qty_billed }}
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-end">{{ number_format($unitPriceInvoiceCurrency, 2, '.', '') }} {{ $lineCurrencyIso }}</td>
                                                                    <td class="text-end">{{ number_format($unitPriceEur, 2, '.', '') }} EUR</td>
                                                                    <td class="text-end">
                                                                        @if($status !== 'cancelled' && $lineReceived === 0)
                                                                            <button type="button" class="btn btn-sm btn-outline-danger oms-btn-icon js-remove-invoice-line" data-line-id="{{ $line->id }}" title="Remove line"><i class="fa-solid fa-trash"></i></button>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="oms-empty text-center py-4">No billed orders linked to this invoice.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2">
            <div class="oms-card">
                <div class="oms-card-header">
                    <div class="oms-soft-label">Summary</div>
                    <div class="fw-bold">Invoice Totals</div>
                </div>
                <div class="oms-card-body">
                    <div class="oms-stat-grid">
                        <div class="oms-stat-box"><div class="label">Lines</div><div class="value">{{ $lineCount }}</div></div>
                        <div class="oms-stat-box"><div class="label">Billed</div><div class="value">{{ $qtyBilled }}</div></div>
                        <div class="oms-stat-box"><div class="label">Received</div><div class="value">{{ $qtyReceived }}</div></div>
                        <div class="oms-stat-box"><div class="label">Remaining</div><div class="value">{{ $qtyRemaining }}</div></div>
                    </div>

                    <div class="mt-3">
                        <div class="oms-soft-label mb-1">Reception Progress</div>
                        <div class="progress" style="height:10px; border-radius:5px;">
                            <div class="progress-bar {{ $progress >= 100 ? 'bg-success' : ($progress > 0 ? 'bg-warning' : 'bg-danger') }}" role="progressbar" style="width: {{ $progress }}%"></div>
                        </div>
                        <div class="small text-muted mt-1">{{ $progress }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.oms-card,.oms-card .btn,.oms-card .form-control,.oms-card .form-select,.oms-card .input-group-text,.modal-content,.table,.badge{border-radius:5px !important;}
.oms-card{background:#fff;border:1px solid rgba(15,23,42,.08);box-shadow:0 8px 24px rgba(15,23,42,.06);}
.oms-card-header{padding:12px 14px;border-bottom:1px solid rgba(15,23,42,.06);}
.oms-card-body{padding:14px;}
.oms-soft-label{font-size:.72rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#6b7280;}
.oms-top-strip{min-height:34px;}
.oms-current-selection{max-width:40vw;}
.oms-section-stack{display:flex;flex-direction:column;gap:12px;}
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
.oms-hidden-row > td{background:#fbfdff;}
.oms-metric-cell{display:inline-flex;align-items:center;justify-content:center;gap:8px;white-space:nowrap;}
.oms-metric-value{min-width:18px;display:inline-block;text-align:center;}
.oms-state-square{display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:4px;font-size:.65rem;border:1px solid transparent;}
.oms-state-square.complete{background:rgba(34,197,94,.14);border-color:rgba(34,197,94,.22);color:#15803d;}
.oms-state-square.partial{background:rgba(245,158,11,.14);border-color:rgba(245,158,11,.22);color:#b45309;}
.oms-state-square.zero{background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.2);color:#b91c1c;}
.oms-toggle-row{cursor:pointer;}
.oms-empty{color:#6b7280;}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    document.querySelectorAll('.js-edit-invoice-reference').forEach(function (button) {
        button.addEventListener('click', async function (event) {
            event.preventDefault();
            event.stopPropagation();
            const result = await Swal.fire({
                title: 'Edit invoice name', input: 'text', inputValue: this.textContent.trim(),
                inputAttributes: { maxlength: 100 }, showCancelButton: true,
                confirmButtonText: 'Save', cancelButtonText: 'Cancel',
                inputValidator: value => !String(value || '').trim() ? 'The name is required.' : undefined,
            });
            if (!result.isConfirmed) return;
            try {
                const response = await fetch(window.location.pathname, {
                    method: 'PATCH',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ invoice_reference: String(result.value).trim() }),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok || data.success === false) {
                    const validationMessage = data.errors ? Object.values(data.errors).flat()[0] : null;
                    throw new Error(validationMessage || data.message || 'Unable to save the change.');
                }
                window.location.reload();
            } catch (error) { Swal.fire('Error', error.message, 'error'); }
        });
    });
    async function invoiceLineRequest(url, method, payload) {
        const response = await fetch(url, {
            method,
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(payload || {}),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok || data.success === false) {
            const validationMessage = data.errors ? Object.values(data.errors).flat()[0] : null;
            throw new Error(validationMessage || data.message || 'Unable to save the change.');
        }
        return data;
    }

    document.querySelectorAll('.js-edit-invoice-line').forEach(function (button) {
        button.addEventListener('click', async function (event) {
            event.preventDefault();
            event.stopPropagation();
            const minimum = Math.max(1, Number(this.dataset.minimum || 0));
            const result = await Swal.fire({
                title: 'Edit billed quantity', input: 'number', inputValue: this.dataset.quantity || 1,
                inputAttributes: { min: minimum, step: 1 }, showCancelButton: true,
                confirmButtonText: 'Save', cancelButtonText: 'Cancel',
                inputValidator: value => {
                    const quantity = Number(value);
                    if (!Number.isInteger(quantity) || quantity < minimum) return 'Quantity must be an integer equal to or greater than ' + minimum + '.';
                },
            });
            if (!result.isConfirmed) return;
            try {
                await invoiceLineRequest(window.location.pathname + '/lines/' + this.dataset.lineId, 'PATCH', { qty_billed: Number(result.value) });
                window.location.reload();
            } catch (error) { Swal.fire('Error', error.message, 'error'); }
        });
    });

    document.querySelectorAll('.js-remove-invoice-line').forEach(function (button) {
        button.addEventListener('click', async function (event) {
            event.preventDefault();
            event.stopPropagation();
            const result = await Swal.fire({
                title: 'Remove invoice line?', icon: 'warning', showCancelButton: true,
                confirmButtonText: 'Remove', cancelButtonText: 'Cancel', confirmButtonColor: '#dc3545',
            });
            if (!result.isConfirmed) return;
            try {
                await invoiceLineRequest(window.location.pathname + '/lines/' + this.dataset.lineId, 'DELETE');
                window.location.reload();
            } catch (error) { Swal.fire('Error', error.message, 'error'); }
        });
    });
    document.querySelectorAll('.oms-toggle-row').forEach(function (row) {
        row.addEventListener('click', function (e) {
            if (e.target.closest('.actions')) return;
            const targetSelector = this.dataset.target;
            const target = document.querySelector(targetSelector);
            if (target) target.classList.toggle('d-none');
        });
    });

    document.querySelectorAll('.oms-copyable').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const value = this.dataset.copy || this.textContent.trim();
            if (!value) return;
            navigator.clipboard?.writeText(value);
        });
    });
});
</script>
@endsection
