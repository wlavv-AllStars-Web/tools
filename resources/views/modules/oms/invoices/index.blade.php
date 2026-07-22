@extends('layouts.app')

@section('content')
<div class="container-fluid py-3 oms-premium oms-invoices-index">
    <style>
        .oms-invoices-index .oms-card{border:1px solid rgba(20,33,61,.10);border-radius:5px;box-shadow:0 8px 24px rgba(15,23,42,.06);overflow:hidden;background:#fff;}
        .oms-invoices-index .oms-card-header{padding:.85rem 1rem;border-bottom:1px solid rgba(20,33,61,.08);background:#f8fafc;}
        .oms-invoices-index .oms-card-body{padding:1rem;}
        .oms-invoices-index .oms-soft-label{font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;font-weight:700;}
        .oms-invoices-index .oms-btn-icon{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;border-radius:5px;padding:.55rem .85rem;font-weight:600;}
        .oms-invoices-index .oms-kpi{border:1px solid rgba(20,33,61,.08);border-radius:5px;background:#fff;padding:.75rem .9rem;}
        .oms-invoices-index .oms-kpi-value{font-size:1.1rem;font-weight:700;color:#111827;line-height:1.15;}
        .oms-invoices-index .oms-kpi-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;font-weight:700;}
        .oms-invoices-index .oms-table-wrap{overflow:auto;}
        .oms-invoices-index .oms-table{width:100%;margin:0;border-collapse:separate;border-spacing:0;}
        .oms-invoices-index .oms-table thead th{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;font-weight:700;background:#f8fafc;border-bottom:1px solid rgba(20,33,61,.08);padding:.85rem .9rem;white-space:nowrap;}
        .oms-invoices-index .oms-table tbody td{padding:.9rem;border-bottom:1px solid rgba(20,33,61,.06);vertical-align:middle;}
        .oms-invoices-index .oms-table tbody tr:hover{background:#fbfdff;}
        .oms-invoices-index .oms-table tbody tr:last-child td{border-bottom:none;}
        .oms-invoices-index .oms-ref{font-weight:700;color:#111827;}
        .oms-invoices-index .oms-supplier{font-weight:600;color:#1f2937;}
        .oms-invoices-index .oms-muted{color:#6b7280;}
        .oms-invoices-index .oms-status{display:inline-flex;align-items:center;gap:.45rem;padding:.3rem .55rem;border-radius:999px;font-size:.74rem;font-weight:700;border:1px solid transparent;}
        .oms-invoices-index .oms-status::before{content:'';display:inline-block;width:8px;height:8px;border-radius:999px;background:currentColor;opacity:.85;}
        .oms-invoices-index .oms-status-draft{background:#eef2ff;color:#4338ca;border-color:#c7d2fe;}
        .oms-invoices-index .oms-status-confirmed{background:#dcfce7;color:#15803d;border-color:#86efac;}
        .oms-invoices-index .oms-status-cancelled{background:#fee2e2;color:#dc2626;border-color:#fca5a5;}
        .oms-invoices-index .oms-status-default{background:#f3f4f6;color:#4b5563;border-color:#d1d5db;}
        .oms-invoices-index .oms-actions{display:flex;justify-content:flex-end;gap:.45rem;flex-wrap:wrap;}
        .oms-invoices-index .oms-empty{padding:2rem 1rem;text-align:center;color:#6b7280;}
        .oms-invoices-index .oms-pagination .pagination{margin-bottom:0;}
        .oms-invoices-index .btn,.oms-invoices-index .badge,.oms-invoices-index .form-control,.oms-invoices-index .form-select,.oms-invoices-index .input-group-text{border-radius:5px !important;}
        @media (max-width: 991px){
            .oms-invoices-index .oms-actions{justify-content:flex-start;}
        }
    </style>

    @php
        $totalInvoices = method_exists($invoices, 'total') ? (int) $invoices->total() : (is_countable($invoices) ? count($invoices) : 0);
        $currentCollection = method_exists($invoices, 'getCollection') ? $invoices->getCollection() : collect($invoices);
        $draftCount = (int) $currentCollection->where('status', 'draft')->count();
        $confirmedCount = (int) $currentCollection->where('status', 'confirmed')->count();
        $cancelledCount = (int) $currentCollection->where('status', 'cancelled')->count();
    @endphp

    <div class="oms-card mb-3">
        <div class="oms-card-body py-2 px-3">
            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <div class="d-flex flex-wrap ">
                    <a href="{{ route('erp.oms.dashboard') }}" class="btn btn-outline-primary oms-btn-icon">
                        <i class="fa-solid fa-table-columns"></i> Dashboard
                    </a>
                </div>
            
                <div>
                    <form method="GET" action="{{ route('erp.oms.invoices.index') }}" class="row g-2 align-items-end">
                        <div class="col-xl-3 col-md-6">
                            <label class="oms-soft-label mb-1">Invoice ref</label>
                            <input type="text" name="ref" value="{{ request('ref') }}" class="form-control" placeholder="Invoice reference">
                        </div>
        
                        <div class="col-xl-3 col-md-6">
                            <label class="oms-soft-label mb-1">Supplier</label>
                            <input type="text"
                                   name="supplier"
                                   value="{{ request('supplier') }}"
                                   class="form-control"
                                   placeholder="Supplier name">
                        </div>
        
                        <div class="col-xl-2 col-md-4">
                            <label class="oms-soft-label mb-1">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All status</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
        
                        <div class="col-xl-4 col-md-8">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary oms-btn-icon">
                                    <i class="fa-solid fa-filter"></i> Filter
                                </button>
        
                                <a href="{{ route('erp.oms.invoices.index') }}" class="btn btn-outline-secondary oms-btn-icon">
                                    <i class="fa-solid fa-rotate-left"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="oms-card">
        <div class="oms-card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <div>
                <div class="oms-soft-label">Documents</div>
                <div class="fw-bold">Invoice list</div>
            </div>
            <div class="text-muted small">
                {{ method_exists($invoices, 'firstItem') && $invoices->firstItem() ? $invoices->firstItem() . ' - ' . $invoices->lastItem() : '0' }}
                / {{ $totalInvoices }}
            </div>
        </div>

        <div class="oms-card-body p-0">
            <div class="oms-table-wrap">
                <table class="oms-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Supplier</th>
                            <th>Status</th>
                            <th>Currency</th>
                            <th>Invoice Date</th>
                            <th class="text-center">Billed Orders</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            @php
                                $status = $invoice->status ?? 'draft';
                                $statusClass = match($status) {
                                    'draft' => 'oms-status-draft',
                                    'confirmed' => 'oms-status-confirmed',
                                    'cancelled' => 'oms-status-cancelled',
                                    default => 'oms-status-default',
                                };
                                $invoiceDate = $invoice->invoice_date;
                            @endphp
                            <tr>
                                <td>
                                    <div class="oms-ref">{{ $invoice->invoice_reference }}</div>
                                </td>
                                <td>
                                    <div class="oms-supplier">{{ optional($invoice->supplier)->name ?: (optional($invoice->supplier)->supplier_name ?: ('Supplier #' . $invoice->supplier_id)) }}</div>
                                </td>
                                <td>
                                    <span class="oms-status {{ $statusClass }}">{{ ucfirst($status) }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $invoice->currency_iso ?: 'EUR' }}</span>
                                </td>
                                <td>
                                    <span class="oms-muted">
                                        {{ $invoiceDate ? (\Illuminate\Support\Carbon::parse($invoiceDate)->format('Y-m-d')) : '—' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-semibold">{{ $invoice->billedOrders->count() }}</span>
                                </td>
                                <td>
                                    <div class="oms-actions">
                                        <a href="{{ route('erp.oms.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary oms-btn-icon" title="Show">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('erp.oms.receptions.invoice_history', $invoice) }}" class="btn btn-sm btn-outline-warning oms-btn-icon" title="Receptions">
                                            <i class="fa-solid fa-box-open"></i>
                                        </a>
                                        <a href="{{ route('erp.oms.invoices.export.xlsx', $invoice) }}" class="btn btn-sm btn-outline-success oms-btn-icon" title="Export XLSX">
                                            <i class="fa-solid fa-file-excel"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="oms-empty">
                                        <i class="fa-solid fa-file-invoice fa-2x mb-3"></i>
                                        <div class="fw-semibold mb-1">No invoices found</div>
                                        <div>No supplier invoices are available for the current filters.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(method_exists($invoices, 'links'))
        <div class="mt-3 oms-pagination">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection
