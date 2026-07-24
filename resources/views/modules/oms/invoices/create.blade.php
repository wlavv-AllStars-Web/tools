@extends('layouts.app')

@section('content')
@php
    $supplierName = optional($orderNote->supplier)->name ?: ('Supplier #' . $orderNote->supplier_id);
    $draftCount = $draftInvoices->count();
@endphp
<div class="container-fluid py-3 px-0 oms-invoice-builder">
    <style>
        .oms-invoice-builder .oms-card,
        .oms-invoice-builder .card,
        .oms-invoice-builder .btn,
        .oms-invoice-builder .badge,
        .oms-invoice-builder .form-control,
        .oms-invoice-builder .form-select,
        .oms-invoice-builder .table,
        .oms-invoice-builder .modal-content,
        .oms-invoice-builder .list-group-item { border-radius: 5px !important; }
        .oms-invoice-builder .oms-card { border:1px solid rgba(20,33,61,.08); background:#fff; box-shadow:0 8px 24px rgba(15,23,42,.05); overflow:hidden; }
        .oms-invoice-builder .oms-card-header { padding:.85rem 1rem; border-bottom:1px solid rgba(20,33,61,.07); background:#f8fafc; }
        .oms-invoice-builder .oms-card-body { padding:1rem; }
        .oms-invoice-builder .oms-soft-label { font-size:.69rem; text-transform:uppercase; letter-spacing:.08em; color:#6b7280; font-weight:700; margin-bottom:.2rem; }
        .oms-invoice-builder .oms-topbar { display:flex; align-items:center; justify-content:space-between; gap:.75rem; margin-bottom:1rem; flex-wrap:wrap; }
        .oms-invoice-builder .oms-topbar-block { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
        .oms-invoice-builder .oms-count-chip, .oms-invoice-builder .oms-selected-chip { display:inline-flex; align-items:center; gap:.45rem; padding:.5rem .7rem; border:1px solid rgba(20,33,61,.10); background:#fff; border-radius:5px; font-weight:600; color:#1f2937; min-height:38px; }
        .oms-invoice-builder .oms-selected-chip { min-width:280px; justify-content:center; }
        .oms-invoice-builder .table tbody tr.is-selected { background:#eff6ff; }
        .oms-invoice-builder .qty-input, .oms-invoice-builder .price-input { min-width:90px; }
        .oms-invoice-builder .oms-price-column { min-width:140px !important; width:140px !important; }
        .oms-invoice-builder .oms-price-group { flex-wrap:nowrap !important; width:130px; max-width:130px; }
        .oms-invoice-builder .oms-price-group .price-input { min-width:0; width:95px; }
        .oms-invoice-builder .oms-product-column { width:38%; max-width:420px; }
        .oms-invoice-builder .oms-product-name { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .oms-invoice-builder .oms-margin-column { min-width:140px !important; width:140px !important; }
        .oms-invoice-builder .js-margin-eur { display:inline-block; min-width:max-content; white-space:nowrap !important; word-break:normal !important; overflow-wrap:normal !important; }
        .oms-invoice-builder .copyable { cursor:pointer; }
        .oms-invoice-builder .copyable:hover { color:#2563eb; }
        .oms-invoice-builder .actions { white-space: nowrap; width:1%; }
        .oms-invoice-builder .oms-help-box { border:1px solid rgba(245,158,11,.22); background:rgba(255,251,235,.95); border-radius:5px; padding:.75rem .85rem; font-size:.84rem; color:#92400e; }
        .oms-invoice-builder .oms-combinations-panel { background:#f8fafc; border-top:1px solid #e5e7eb; }
        .oms-invoice-builder .oms-combinations-table th { font-size:.72rem; text-transform:uppercase; color:#64748b; }
        .oms-invoice-builder .oms-combinations-table td { vertical-align:middle; }
    </style>

    <div style="border: 1px solid rgba(20, 33, 61, .08); background: #fff; box-shadow: 0 8px 24px rgba(15, 23, 42, .05); padding: 10px;margin-bottom: 10px;">
        <div class="oms-topbar" style="margin-bottom: 0;">
            <div class="oms-topbar-block">
                <a href="{{ route('erp.oms.dashboard', ['left_tab' => 'order_notes', 'document_type' => 'order_note', 'document_id' => $orderNote->id, 'supplier_id' => $orderNote->supplier_id]) }}" class="btn btn-sm btn-outline-primary oms-compact-btn">
                    <i class="fa-solid fa-table-columns me-1"></i> Dashboard
                </a>
            </div>
            <div class="oms-topbar-block flex-grow-1 justify-content-center">
                <div class="oms-selected-chip">
                    <i class="fa-solid fa-file-invoice-dollar text-warning"></i>
                    <span>{{ $orderNote->reference }}</span>
                    <span class="text-muted"> | </span>
                    <span>{{ $supplierName }}</span>
                </div>
            </div>
            <div class="oms-topbar-block justify-content-end">
                <a href="{{ route('erp.oms.order_notes.show', $orderNote) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-angle-left me-1"></i> Order Note</a>
                <a href="{{ route('erp.oms.invoices.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-receipt me-1"></i> Invoices</a>
            </div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

    <form action="{{ route('erp.oms.invoices.store', $orderNote) }}" method="POST" id="invoiceBuilderForm">
        @csrf
        <div class="row g-3">
            <div class="col-lg-2">
                <div class="oms-card h-100">
                    <div class="oms-card-header">
                        <div class="oms-soft-label">Invoice Header</div>
                        <div class="fw-bold">Document and cost settings</div>
                    </div>
                    <div class="oms-card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Use existing draft invoice</label>
                            <select name="existing_invoice_id" class="form-select">
                                <option value="">Create a new draft invoice</option>
                                @foreach($draftInvoices as $draftInvoice)
                                    <option value="{{ $draftInvoice->id }}" @selected((string) old('existing_invoice_id') === (string) $draftInvoice->id)>
                                        {{ $draftInvoice->invoice_reference }} · {{ optional($draftInvoice->invoice_date)->format('Y-m-d') ?: 'No date' }} · {{ $draftInvoice->billed_orders_count }} billed docs
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Invoice reference</label>
                                <input type="text" name="invoice_reference" class="form-control" value="{{ old('invoice_reference') }}" placeholder="Supplier invoice reference">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Invoice date</label>
                                <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', now()->toDateString()) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Due date</label>
                                <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                            </div>
                        </div>

                        <div class="row g-2 mb-3 text-center">
                            <div class="col-md-4">
                                <div class="oms-soft-label">Supplier Currency</div>
                                <div class="fw-semibold">{{ $currencyMeta['currency_iso'] }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="oms-soft-label">Conversion Rate</div>
                                <div class="fw-semibold">{{ number_format((float) $currencyMeta['conversion_rate'], 6, '.', '') }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="oms-soft-label">Base</div>
                                <div class="fw-semibold">EUR</div>
                            </div>
                        </div>


                        <div class="mb-3">
                            <label class="form-label fw-semibold">Internal note</label>
                            <textarea name="internal_note" class="form-control" rows="3">{{ old('internal_note') }}</textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Logistic note</label>
                            <textarea name="logistic_note" class="form-control" rows="3">{{ old('logistic_note') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-10">
                <div class="oms-card h-100">
                    <div class="oms-card-header d-flex justify-content-between align-items-center">
                        <div>
                            <div class="oms-soft-label">Invoice Lines</div>
                            <div class="fw-bold">Select quantities and unit prices</div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" name="invoice_action" value="save_draft" class="btn btn-outline-primary btn-sm">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save Draft
                            </button>
                            <button type="submit" name="invoice_action" value="close_invoice" class="btn btn-outline-warning btn-sm">
                                <i class="fa-solid fa-lock me-1"></i> Close Invoice
                            </button>
                        </div>
                    </div>
                    <div class="oms-card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Reference</th>
                                        <th class="oms-product-column">Product</th>
                                        <th class="text-center">Ordered</th>
                                        <th class="text-center">Invoiced</th>
                                        <th class="text-center">Remaining</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end oms-price-column">Purchase price</th>
                                        <th class="text-end oms-margin-column">Margin</th>
                                        <th class="text-end oms-price-column">Sale price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($invoiceableLines as $line)
                                        @php
                                            $purchaseEur = (float) $line->current_purchase_eur;
                                            $saleEur = (float) $line->current_sale_eur;
                                            $purchaseSupplierPrice = (float) $line->current_purchase_supplier_currency;
                                            $saleSupplierPrice = (float) $line->current_sale_supplier_currency;
                                            $marginSupplierCurrency = $saleSupplierPrice - $purchaseSupplierPrice;
                                            $marginPercent = $saleSupplierPrice > 0
                                                ? ($marginSupplierCurrency / $saleSupplierPrice) * 100
                                                : 0;
                                        @endphp
                                        <tr
                                            class="js-price-row {{ $line->qty_remaining > 0 ? '' : 'table-light text-muted' }}"
                                            data-purchase-rate="{{ (float) $currencyMeta['purchase_conversion_rate'] }}"
                                            data-sale-rate="{{ (float) $currencyMeta['sale_conversion_rate'] }}"
                                            data-purchase-eur="{{ $purchaseEur }}"
                                            data-sale-eur="{{ $saleEur }}"
                                        >
                                            <td>
                                                <span class="copyable fw-semibold" data-copy="{{ $line->reference }}">{{ $line->reference ?: '-' }}</span>
                                                @if($line->product_attribute_id)
                                                    <div class="small text-warning"><i class="fa-solid fa-layer-group me-1"></i>Attribute</div>
                                                @endif
                                            </td>
                                            <td class="oms-product-column">
                                                <div class="fw-semibold oms-product-name" title="{{ $line->product_name }}">{{ $line->product_name }}</div>
                                                <div class="small text-muted">#{{ $line->product_id }}@if($line->product_attribute_id) | {{ $line->product_attribute_id }}@endif · Stock {{ $line->current_stock }}</div>

                                            </td>
                                            <td class="text-center align-top">{{ $line->qty_ordered }}</td>
                                            <td class="text-center align-top">{{ $line->qty_billed }}</td>
                                            <td class="text-center align-top fw-semibold">{{ $line->qty_remaining }}</td>
                                            <td class="text-center align-top">
                                                <input type="hidden" name="lines[{{ $loop->index }}][order_note_line_id]" value="{{ $line->order_note_line_id }}">
                                                <input type="number" min="0" max="{{ $line->qty_remaining }}" name="lines[{{ $loop->index }}][qty_billed]" class="form-control form-control-sm qty-input mx-auto" style="max-width:95px;" value="{{ old('lines.'.$loop->index.'.qty_billed', 0) }}" {{ $line->qty_remaining > 0 ? '' : 'disabled' }}>
                                            </td>
                                            <td class="text-end oms-price-column">
                                                <div class="input-group input-group-sm oms-price-group ms-auto">
                                                    <span class="input-group-text">{{ $currencyMeta['currency_symbol'] }}</span>
                                                    <input type="number" min="0" step="0.01" name="lines[{{ $loop->index }}][unit_price]" class="form-control price-input js-purchase-price" value="{{ number_format((float) $line->current_wholesale_price, 2, '.', '') }}" {{ $line->qty_remaining > 0 ? '' : 'disabled' }}>
                                                </div>
                                                <div class="small text-muted mt-1 js-purchase-eur">
                                                    {{ number_format($purchaseEur, 2, ',', ' ') }} EUR
                                                </div>
                                            </td>
                                            <td class="text-end oms-margin-column">
                                                <div class="fw-semibold js-margin-percent {{ $marginSupplierCurrency >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ number_format($marginPercent, 2, ',', ' ') }}%
                                                </div>
                                                <div class="small text-muted mt-1 js-margin-eur">
                                                    {{ number_format($marginSupplierCurrency, 2, ',', ' ') }} {{ $currencyMeta['currency_iso'] }}
                                                </div>
                                            </td>
                                            <td class="text-end oms-price-column">
                                                <div class="input-group input-group-sm oms-price-group ms-auto">
                                                    <span class="input-group-text">{{ $currencyMeta['currency_symbol'] }}</span>
                                                    <input type="number" min="0" step="0.01" name="lines[{{ $loop->index }}][sale_price]" class="form-control price-input js-sale-price" value="{{ number_format((float) $line->current_sale_supplier_currency, 2, '.', '') }}" {{ $line->qty_remaining > 0 ? '' : 'disabled' }}>
                                                </div>
                                                <div class="d-flex justify-content-end align-items-center gap-2 mt-1">
                                                    <div class="small text-muted js-sale-eur">
                                                        {{ number_format($saleEur, 2, ',', ' ') }} EUR
                                                    </div>
                                                    @if(($line->other_combinations ?? collect())->isNotEmpty())
                                                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" data-bs-toggle="collapse" data-bs-target="#other-combinations-{{ $line->order_note_line_id }}" aria-expanded="false" title="Show combinations" aria-label="Show combinations">
                                                            <i class="fa-solid fa-chevron-down"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @if(($line->other_combinations ?? collect())->isNotEmpty())
                                            <tr class="border-0"><td colspan="9" class="p-0 border-0">
                                                <div class="collapse oms-combinations-panel" id="other-combinations-{{ $line->order_note_line_id }}"><div class="p-3">
                                                    <div class="small fw-semibold text-muted mb-2"><i class="fa-solid fa-layer-group me-1"></i>Other combinations</div>
                                                    <div class="table-responsive"><table class="table table-sm oms-combinations-table mb-0">
                                                        <thead><tr><th>Reference</th><th>Combination</th><th class="text-end">Purchase price</th><th class="text-end">Margin</th><th class="text-end">Sale price</th><th></th></tr></thead><tbody>
                                                        @foreach($line->other_combinations as $combination)
                                                            <tr class="js-combination-price-row" data-product-id="{{ $combination->product_id }}" data-purchase-rate="{{ (float) $currencyMeta['purchase_conversion_rate'] }}" data-sale-rate="{{ (float) $currencyMeta['sale_conversion_rate'] }}" data-update-url="{{ route('erp.oms.invoices.combination-prices.update', [$orderNote, $combination->product_attribute_id]) }}">
                                                                <td><span class="fw-semibold">{{ $combination->reference ?: '-' }}</span><div class="small text-muted">#{{ $combination->product_attribute_id }}</div></td>
                                                                <td>{{ $combination->combination_label }} @if($combination->is_default)<span class="badge bg-primary ms-1">Base</span>@endif</td>
                                                                <td class="text-end oms-price-column"><div class="input-group input-group-sm oms-price-group ms-auto"><span class="input-group-text">{{ $currencyMeta['currency_symbol'] }}</span><input type="number" min="0" step="0.01" class="form-control price-input js-combination-purchase" value="{{ number_format($combination->purchase_supplier_currency, 2, '.', '') }}"></div><div class="small text-muted mt-1 js-combination-purchase-eur">{{ number_format($combination->purchase_eur, 2, ',', ' ') }} EUR</div></td>
                                                                <td class="text-end oms-margin-column"><div class="fw-semibold js-combination-margin-percent {{ $combination->margin_supplier_currency >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($combination->margin_percent, 2, ',', ' ') }}%</div><div class="small text-muted mt-1 js-combination-margin-value">{{ number_format($combination->margin_supplier_currency, 2, ',', ' ') }} {{ $currencyMeta['currency_iso'] }}</div></td>
                                                                <td class="text-end oms-price-column"><div class="input-group input-group-sm oms-price-group ms-auto"><span class="input-group-text">{{ $currencyMeta['currency_symbol'] }}</span><input type="number" min="0" step="0.01" class="form-control price-input js-combination-sale" value="{{ number_format($combination->sale_supplier_currency, 2, '.', '') }}"></div><div class="small text-muted mt-1 js-combination-sale-eur">{{ number_format($combination->sale_eur, 2, ',', ' ') }} EUR</div></td>
                                                                <td class="text-end"><button type="button" class="btn btn-sm btn-outline-primary js-save-combination-price"><i class="fa-solid fa-floppy-disk me-1"></i>Save</button></td>
                                                            </tr>
                                                        @endforeach
                                                        </tbody></table></div>
                                                </div></div>
                                            </td></tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">No invoiceable lines available for this order note.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const eurFormatter = new Intl.NumberFormat('pt-PT', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    function updateMargin(row) {
        const purchasePrice = Number.parseFloat(row.querySelector('.js-purchase-price')?.value || '0') || 0;
        const salePrice = Number.parseFloat(row.querySelector('.js-sale-price')?.value || '0') || 0;
        const margin = salePrice - purchasePrice;
        const marginPercent = salePrice > 0 ? (margin / salePrice) * 100 : 0;
        const marginPercentElement = row.querySelector('.js-margin-percent');
        const supplierCurrency = @json($currencyMeta['currency_iso']);

        row.querySelector('.js-margin-eur').textContent = eurFormatter.format(margin) + ' ' + supplierCurrency;
        marginPercentElement.textContent = eurFormatter.format(marginPercent) + '%';
        marginPercentElement.classList.toggle('text-success', margin >= 0);
        marginPercentElement.classList.toggle('text-danger', margin < 0);
    }

    document.querySelectorAll('.js-price-row').forEach(function (row) {
        const purchaseInput = row.querySelector('.js-purchase-price');
        const saleInput = row.querySelector('.js-sale-price');

        purchaseInput?.addEventListener('input', function () {
            const supplierPrice = Number.parseFloat(this.value || '0') || 0;
            const purchaseRate = Number.parseFloat(row.dataset.purchaseRate || '1') || 1;
            const purchaseEur = supplierPrice / purchaseRate;

            row.dataset.purchaseEur = purchaseEur.toString();
            row.querySelector('.js-purchase-eur').textContent = eurFormatter.format(purchaseEur) + ' EUR';
            updateMargin(row);
        });

        saleInput?.addEventListener('input', function () {
            const supplierPrice = Number.parseFloat(this.value || '0') || 0;
            const saleRate = Number.parseFloat(row.dataset.saleRate || '1') || 1;
            const saleEur = supplierPrice / saleRate;

            row.dataset.saleEur = saleEur.toString();
            row.querySelector('.js-sale-eur').textContent = eurFormatter.format(saleEur) + ' EUR';
            updateMargin(row);
        });
    });

    function refreshCombinationRow(row) {
        const purchase = Number.parseFloat(row.querySelector('.js-combination-purchase').value || '0') || 0;
        const sale = Number.parseFloat(row.querySelector('.js-combination-sale').value || '0') || 0;
        const purchaseRate = Number.parseFloat(row.dataset.purchaseRate || '1') || 1;
        const saleRate = Number.parseFloat(row.dataset.saleRate || '1') || 1;
        const margin = sale - purchase;
        const percent = sale > 0 ? (margin / sale) * 100 : 0;
        const percentElement = row.querySelector('.js-combination-margin-percent');
        row.querySelector('.js-combination-purchase-eur').textContent = eurFormatter.format(purchase / purchaseRate) + ' EUR';
        row.querySelector('.js-combination-sale-eur').textContent = eurFormatter.format(sale / saleRate) + ' EUR';
        row.querySelector('.js-combination-margin-value').textContent = eurFormatter.format(margin) + ' ' + @json($currencyMeta['currency_iso']);
        percentElement.textContent = eurFormatter.format(percent) + '%';
        percentElement.classList.toggle('text-success', margin >= 0);
        percentElement.classList.toggle('text-danger', margin < 0);
    }

    document.querySelectorAll('.js-combination-price-row').forEach(function (row) {
        row.querySelectorAll('.js-combination-purchase, .js-combination-sale').forEach(function (input) {
            input.addEventListener('input', () => refreshCombinationRow(row));
        });
        row.querySelector('.js-save-combination-price').addEventListener('click', async function () {
            const button = this;
            button.disabled = true;
            try {
                const response = await fetch(row.dataset.updateUrl, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        product_id: Number.parseInt(row.dataset.productId, 10),
                        purchase_price: Number.parseFloat(row.querySelector('.js-combination-purchase').value || '0') || 0,
                        sale_price: Number.parseFloat(row.querySelector('.js-combination-sale').value || '0') || 0,
                    }),
                });
                const result = await response.json();
                if (!response.ok || !result.success) throw new Error(result.message || Object.values(result.errors || {}).flat().join(' ') || 'Unable to save combination prices.');
                refreshCombinationRow(row);
                if (window.Swal) Swal.fire({ icon: 'success', title: 'Prices saved', text: result.message, timer: 1400, showConfirmButton: false });
            } catch (error) {
                if (window.Swal) Swal.fire({ icon: 'error', title: 'Unable to save', text: error.message });
                else alert(error.message);
            } finally {
                button.disabled = false;
            }
        });
    });
    document.querySelectorAll('.copyable').forEach(function (el) {
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
