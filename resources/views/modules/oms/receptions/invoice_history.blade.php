@extends('layouts.app')

@section('content')
<div class="container-fluid py-3 oms-premium oms-invoice-history-index">
    <style>
        .oms-invoice-history-index .oms-card{border:1px solid rgba(20,33,61,.10);border-radius:5px;box-shadow:0 8px 24px rgba(15,23,42,.06);overflow:hidden;background:#fff;}
        .oms-invoice-history-index .oms-card-header{padding:.85rem 1rem;border-bottom:1px solid rgba(20,33,61,.08);background:#f8fafc;}
        .oms-invoice-history-index .oms-card-body{padding:1rem;}
        .oms-invoice-history-index .oms-soft-label{font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;font-weight:700;}
        .oms-invoice-history-index .oms-btn-icon{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;border-radius:5px;padding:.55rem .85rem;font-weight:600;}
        .oms-invoice-history-index .oms-kpi{border:1px solid rgba(20,33,61,.08);border-radius:5px;background:#fff;padding:.75rem .9rem;}
        .oms-invoice-history-index .oms-kpi-value{font-size:1.1rem;font-weight:700;color:#111827;line-height:1.15;}
        .oms-invoice-history-index .oms-kpi-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;font-weight:700;}
        .oms-invoice-history-index .oms-table-wrap{overflow:auto;}
        .oms-invoice-history-index .oms-table{width:100%;margin:0;border-collapse:separate;border-spacing:0;}
        .oms-invoice-history-index .oms-table thead th{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;font-weight:700;background:#f8fafc;border-bottom:1px solid rgba(20,33,61,.08);padding:.85rem .9rem;white-space:nowrap;}
        .oms-invoice-history-index .oms-table tbody td{padding:.9rem;border-bottom:1px solid rgba(20,33,61,.06);vertical-align:middle;}
        .oms-invoice-history-index .oms-table tbody tr:hover{background:#fbfdff;}
        .oms-invoice-history-index .oms-table tbody tr:last-child td{border-bottom:none;}
        .oms-invoice-history-index .oms-ref{font-weight:700;color:#111827;}
        .oms-invoice-history-index .oms-muted{color:#6b7280;}
        .oms-invoice-history-index .oms-empty{padding:2rem 1rem;text-align:center;color:#6b7280;}
        .oms-invoice-history-index .oms-pagination .pagination{margin-bottom:0;}
        .oms-invoice-history-index .btn,.oms-invoice-history-index .badge,.oms-invoice-history-index .form-control,.oms-invoice-history-index .form-select,.oms-invoice-history-index .input-group-text{border-radius:5px !important;}
        .oms-invoice-history-index .oms-filter-grid{display:grid;grid-template-columns:repeat(9,minmax(140px,1fr));gap:.75rem;}
        .oms-invoice-history-index .oms-no-match{display:none;padding:1.5rem;text-align:center;color:#6b7280;border-top:1px solid rgba(20,33,61,.06);}
        @media (max-width: 1600px){
            .oms-invoice-history-index .oms-filter-grid{grid-template-columns:repeat(4,minmax(140px,1fr));}
        }
        @media (max-width: 991px){
            .oms-invoice-history-index .oms-filter-grid{grid-template-columns:repeat(2,minmax(140px,1fr));}
        }
        @media (max-width: 575px){
            .oms-invoice-history-index .oms-filter-grid{grid-template-columns:1fr;}
        }
    </style>

    @php
        $rowsCollection = collect($rows ?? []);
        $totalRows = (int) $rowsCollection->count();
        $totalQtyReceived = (int) $rowsCollection->sum('qty_received');
        $uniqueBilledOrders = (int) $rowsCollection->pluck('billed_order_reference')->filter()->unique()->count();
        $uniqueProducts = (int) $rowsCollection->map(fn($row) => (($row->product_id ?? '') . '-' . ($row->product_attribute_id ?? '0')))->filter()->unique()->count();
    @endphp

    <div class="oms-card mb-3">
        <div class="oms-card-body py-2 px-3">
            <div class="d-flex justify-content-between align-items-center mb-0 gap-2 flex-wrap">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="{{ route('erp.oms.dashboard', ['left_tab' => 'billed_orders', 'document_type' => 'billed_order']) }}" class="btn btn-outline-primary oms-btn-icon">
                        <i class="fa-solid fa-table-columns"></i> Dashboard
                    </a>
                </div>
                <div style="text-align: center;">
                    <div class="oms-soft-label">OMS / Invoice Reception History</div>
                    <div class="text-muted">Invoice {{ $invoice->invoice_reference }}</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('erp.oms.invoices.show', $invoice) }}" class="btn btn-outline-primary oms-btn-icon">
                        <i class="fa-solid fa-angle-left"></i> Invoice
                    </a>
                    <a href="{{ route('erp.oms.receptions.export.csv', ['invoice_id' => $invoice->id]) }}" class="btn btn-outline-success oms-btn-icon">
                        <i class="fa-solid fa-file-csv"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="oms-card mb-3">
        <div class="oms-card-body">
            <div class="oms-filter-grid">
                <div>
                    <label class="oms-soft-label mb-1">Billed order</label>
                    <input type="text" id="filterBilledOrder" class="form-control oms-history-filter" placeholder="Billed order ref" data-field="billed_order_reference">
                </div>
                <div>
                    <label class="oms-soft-label mb-1">Invoice</label>
                    <input type="text" id="filterInvoiceRef" class="form-control oms-history-filter" placeholder="Invoice reference" data-field="invoice_reference">
                </div>
                <div>
                    <label class="oms-soft-label mb-1">Billed order line</label>
                    <input type="text" id="filterBilledOrderLineId" class="form-control oms-history-filter" placeholder="Billed order line ID" data-field="billed_order_line_id">
                </div>
                <div>
                    <label class="oms-soft-label mb-1">Product ID</label>
                    <input type="text" id="filterProductId" class="form-control oms-history-filter" placeholder="Product ID" data-field="product_id">
                </div>
                <div>
                    <label class="oms-soft-label mb-1">Attribute ID</label>
                    <input type="text" id="filterAttributeId" class="form-control oms-history-filter" placeholder="Attribute ID" data-field="product_attribute_id">
                </div>
                <div>
                    <label class="oms-soft-label mb-1">Qty received</label>
                    <input type="text" id="filterQtyReceived" class="form-control oms-history-filter" placeholder="Qty received" data-field="qty_received">
                </div>
                <div>
                    <label class="oms-soft-label mb-1">User</label>
                    <input type="text" id="filterCreatedBy" class="form-control oms-history-filter" placeholder="User" data-field="created_by">
                </div>
                <div>
                    <button type="button" id="omsHistoryResetFilters" class="btn btn-outline-secondary oms-btn-icon" style="margin-top: 27px;padding: 5px 10px;">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <div class="oms-card-body p-0">
            <div class="oms-table-wrap">
                <table class="oms-table">
                    <thead>
                        <tr>
                            <th>Reception ID</th>
                            <th>Billed Order</th>
                            <th>Invoice</th>
                            <th>Billed Order Line</th>
                            <th>Product ID</th>
                            <th>Attribute ID</th>
                            <th>Qty Received</th>
                            <th>User</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody id="omsHistoryTableBody">
                        @forelse($rows as $row)
                            <tr class="oms-history-row"
                                data-reception_id="{{ strtolower((string) $row->reception_id) }}"
                                data-billed_order_reference="{{ strtolower((string) $row->billed_order_reference) }}"
                                data-invoice_reference="{{ strtolower((string) $row->invoice_reference) }}"
                                data-billed_order_line_id="{{ strtolower((string) $row->billed_order_line_id) }}"
                                data-product_id="{{ strtolower((string) $row->product_id) }}"
                                data-product_attribute_id="{{ strtolower((string) $row->product_attribute_id) }}"
                                data-qty_received="{{ strtolower((string) $row->qty_received) }}"
                                data-created_by="{{ strtolower((string) $row->created_by) }}"
                                data-created_at="{{ strtolower((string) $row->created_at) }}">
                                <td>{{ $row->reception_id }}</td>
                                <td class="oms-ref">{{ $row->billed_order_reference }}</td>
                                <td>{{ $row->invoice_reference }}</td>
                                <td>{{ $row->billed_order_line_id }}</td>
                                <td>{{ $row->product_id }}</td>
                                <td>{{ $row->product_attribute_id }}</td>
                                <td class="fw-semibold">{{ $row->qty_received }}</td>
                                <td>{{ $row->created_by }}</td>
                                <td class="oms-muted">{{ $row->created_at }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="oms-empty">No reception history found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div id="omsHistoryNoMatch" class="oms-no-match">
                <i class="fa-solid fa-filter-circle-xmark fa-2x mb-3"></i>
                <div class="fw-semibold mb-1">No rows match the current filters</div>
                <div>Adjust or reset the filters to see reception history rows again.</div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const filters = Array.from(document.querySelectorAll('.oms-history-filter'));
        const rows = Array.from(document.querySelectorAll('.oms-history-row'));
        const resetBtn = document.getElementById('omsHistoryResetFilters');
        const noMatch = document.getElementById('omsHistoryNoMatch');
        const visibleRowsEl = document.getElementById('omsHistoryVisibleRows');
        const visibleQtyEl = document.getElementById('omsHistoryVisibleQty');
        const visibleBilledEl = document.getElementById('omsHistoryVisibleBilled');
        const visibleProductsEl = document.getElementById('omsHistoryVisibleProducts');

        function normalize(value) {
            return String(value || '').toLowerCase().trim();
        }

        function updateMetrics(visibleRows) {
            if (visibleRowsEl) {
                visibleRowsEl.textContent = String(visibleRows.length);
            }

            if (visibleQtyEl) {
                const qty = visibleRows.reduce((sum, row) => {
                    const value = parseInt(row.dataset.qty_received || '0', 10);
                    return sum + (Number.isNaN(value) ? 0 : value);
                }, 0);
                visibleQtyEl.textContent = String(qty);
            }

            if (visibleBilledEl) {
                const billedOrders = new Set(
                    visibleRows.map((row) => normalize(row.dataset.billed_order_reference)).filter(Boolean)
                );
                visibleBilledEl.textContent = String(billedOrders.size);
            }

            if (visibleProductsEl) {
                const products = new Set(
                    visibleRows.map((row) => {
                        return normalize(row.dataset.product_id) + '-' + normalize(row.dataset.product_attribute_id || '0');
                    }).filter(Boolean)
                );
                visibleProductsEl.textContent = String(products.size);
            }
        }

        function applyFilters() {
            const activeFilters = {};
            filters.forEach((filter) => {
                const field = filter.dataset.field;
                const value = normalize(filter.value);
                if (field && value !== '') {
                    activeFilters[field] = value;
                }
            });

            const visibleRows = [];

            rows.forEach((row) => {
                const matches = Object.entries(activeFilters).every(([field, value]) => {
                    return normalize(row.dataset[field]).includes(value);
                });

                row.style.display = matches ? '' : 'none';
                if (matches) {
                    visibleRows.push(row);
                }
            });

            if (noMatch) {
                noMatch.style.display = rows.length > 0 && visibleRows.length === 0 ? 'block' : 'none';
            }

            updateMetrics(visibleRows);
        }

        filters.forEach((filter) => {
            filter.addEventListener('input', applyFilters);
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                filters.forEach((filter) => {
                    filter.value = '';
                });
                applyFilters();
            });
        }

        applyFilters();
    })();
    </script>
</div>
@endsection
