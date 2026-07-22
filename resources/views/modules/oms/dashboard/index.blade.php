@extends('layouts.app')

@section('content')
<div class="container-fluid py-3 oms-premium">
    <style>
        .oms-premium .oms-card{border:1px solid rgba(20,33,61,.10);border-radius:5px;box-shadow:0 8px 24px rgba(15,23,42,.06);overflow:hidden;background:var(--bs-body-bg, #fff);}
        .oms-premium .oms-card-header{padding:.85rem 1rem;border-bottom:1px solid rgba(20,33,61,.08);}
        .oms-premium .oms-card-body{padding:1rem;}
        .oms-premium .oms-btn-icon{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;border-radius:5px;padding:.55rem .85rem;font-weight:600;}
        .oms-premium .oms-kpi{border:1px solid rgba(20,33,61,.08);border-radius:5px;padding:.85rem 1rem;}
        .oms-premium .oms-sidebar-tab{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.5rem .65rem;border:1px solid rgba(20,33,61,.08);border-radius:5px;text-decoration:none;font-size:.82rem;font-weight:600;flex:1 1 0;text-align:center;}
        .oms-premium .oms-sidebar-tab.active{background:#eff6ff;border-color:rgba(37,99,235,.32);color:#1d4ed8;}
        .oms-premium .oms-supplier-row{display:flex;align-items:center;justify-content:space-between;padding:.65rem .75rem;border-bottom:1px solid rgba(20,33,61,.08);text-decoration:none;color:inherit;}
        .oms-premium .oms-supplier-row:hover{background:rgba(37,99,235,.04);}
        .oms-premium .oms-supplier-row.active{background:#eff6ff;color:#1d4ed8;}
        .oms-premium .oms-supplier-name{font-weight:600;}
        .oms-premium .oms-supplier-meta{font-size:.75rem;opacity:.8;}
        .oms-premium .oms-row-selected{background:#eff6ff !important;}
        .oms-premium .badge,.oms-premium .btn,.oms-premium .form-control,.oms-premium .input-group-text,.oms-premium .nav-link{border-radius:5px !important;}
        .oms-premium .table > :not(caption) > * > *{vertical-align:middle;}
        .oms-premium .oms-clickable-row td:not(.actions){cursor:pointer;}
        .oms-premium .oms-section-title{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.9rem 1rem;border-bottom:1px solid rgba(20,33,61,.08);font-weight:700;}
        .oms-premium .oms-context-pill{display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .65rem;border:1px solid rgba(20,33,61,.08);border-radius:999px;font-size:.78rem;font-weight:600;}
        .oms-premium .oms-empty-state{padding:2rem 1rem;text-align:center;opacity:.75;}
        .oms-premium .oms-summary-subtitle{font-size:.82rem;opacity:.8;}
    </style>

    @php
        $supplierSidebar = $supplierSidebar ?? collect();
        $supplierTab = $state['supplier_tab'] ?? 'order_notes';
        $selectedSupplierId = $state['supplier_id'] ?? null;
        $selectedSupplier = $selectedSupplierId ? collect($supplierSidebar)->firstWhere('supplier_id', $selectedSupplierId) : null;
        $currentSupplierName = $selectedSupplier->supplier_name ?? null;
    @endphp

    <div class="row g-3">
        <div class="col-xl-3">
            <div class="oms-card">
                <div class="oms-card-header">
                    <div class="d-flex gap-2">
                        <a href="{{ route('erp.oms.dashboard', ['supplier_tab' => 'order_notes']) }}" class="oms-sidebar-tab {{ $supplierTab === 'order_notes' ? 'active' : '' }}">Order</a>
                        <a href="{{ route('erp.oms.dashboard', ['supplier_tab' => 'billed']) }}" class="oms-sidebar-tab {{ $supplierTab === 'billed' ? 'active' : '' }}">Billed</a>
                        <a href="{{ route('erp.oms.dashboard', ['supplier_tab' => 'received']) }}" class="oms-sidebar-tab {{ $supplierTab === 'received' ? 'active' : '' }}">Received</a>
                        <a href="{{ route('erp.oms.dashboard', ['supplier_tab' => 'all']) }}" class="oms-sidebar-tab {{ $supplierTab === 'all' ? 'active' : '' }}">All</a>
                    </div>
                </div>
                <div class="oms-card-body p-0">
                    @forelse($supplierSidebar as $supplier)
                        @php
                            $isActive = (int) $supplier->supplier_id === (int) $selectedSupplierId;
                            $defaultCenterTab = match($supplierTab) {
                                'billed' => 'billed',
                                'received' => 'received',
                                default => 'order_notes',
                            };
                        @endphp
                        <a class="oms-supplier-row {{ $isActive ? 'active' : '' }}"
                           href="{{ route('erp.oms.dashboard', ['supplier_tab' => $supplierTab, 'supplier_id' => $supplier->supplier_id, 'center_tab' => $defaultCenterTab]) }}">
                            <div>
                                <div class="oms-supplier-name">{{ $supplier->supplier_name }}</div>
                            </div>
                            <span class="badge text-bg-light">
                                @if($supplierTab === 'order_notes')
                                    {{ (int) ($supplier->open_order_notes_count ?? 0) }}
                                @elseif($supplierTab === 'billed')
                                    {{ (int) ($supplier->open_receptions_count ?? 0) }}
                                @elseif($supplierTab === 'received')
                                    {{ (int) ($supplier->receptions_history_count ?? 0) }}
                                @else
                                    @php
                                        $allCount =
                                            (int) ($supplier->open_order_notes_count ?? 0)
                                            + (int) ($supplier->open_receptions_count ?? 0)
                                            + (int) ($supplier->receptions_history_count ?? 0);
                                    @endphp
                                
                                    @if($allCount > 0)
                                        <span style="background-color: dodgerblue; border: 1px solid blue; border-radius: 999px; padding: 2px 5px; color: #FFF;">
                                            {{ $allCount }}
                                        </span>
                                    @else
                                        {{ $allCount }}
                                    @endif
                                @endif
                            </span>
                        </a>
                    @empty
                        <div class="oms-empty-state">
                            <i class="fa-solid fa-building-circle-xmark fa-2x mb-2"></i>
                            <div>No suppliers found for this tab.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="oms-card">
                <div class="oms-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <div class="small text-muted">Supplier workspace</div>
                        <div class="fw-bold">{{ $currentSupplierName ?: 'Select a supplier' }}</div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('erp.oms.history.prices') }}" class="btn btn-outline-primary oms-btn-icon">
                            <i class="fa-solid fa-chart-line"></i> Price History
                        </a>
                        
                        <a href="{{ route('erp.oms.history.stock') }}" class="btn btn-outline-primary oms-btn-icon">
                            <i class="fa-solid fa-boxes-stacked"></i> Stock History
                        </a>

                        <a href="{{ route('erp.oms.dashboard.export.xlsx', request()->query()) }}" class="btn btn-outline-success oms-btn-icon">
                            <i class="fa-solid fa-file-excel"></i> Export
                        </a>

                        @if($selectedSupplierId)
                            <form method="post" action="{{ route('erp.oms.order_notes.create_from_supplier', $selectedSupplierId) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary oms-btn-icon">
                                    <i class="fa-solid fa-plus"></i> New Order Note
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn btn-primary oms-btn-icon" disabled>
                                <i class="fa-solid fa-plus"></i> New Order Note
                            </button>
                        @endif
                    </div>
                </div>

                <div class="oms-card-body p-0" id="oms-dashboard-documents">
                    @include('modules.oms.dashboard.partials.documents_panel', [
                        'state' => $state,
                        'documentsPane' => $documentsPane
                    ])
                </div>
            </div>
        </div>

        <div class="col-xl-2">
            <div class="oms-card">
                <div class="oms-card-body" id="oms-dashboard-summary">
                    @include('modules.oms.dashboard.partials.summary_pane', [
                        'state' => $state,
                        'summaryPane' => $summaryPane
                    ])
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function (event) {
        const row = event.target.closest('.oms-clickable-row');
        if (!row) return;
        if (event.target.closest('a, button, .actions')) return;

        const url = row.dataset.summaryUrl;
        if (!url) return;

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.json())
            .then(data => {
                if (!data || !data.success) return;

                const summaryNode = document.getElementById('oms-dashboard-summary');
                if (summaryNode) {
                    summaryNode.innerHTML = data.html;
                }

                document.querySelectorAll('.oms-clickable-row.oms-row-selected').forEach(function (el) {
                    el.classList.remove('oms-row-selected');
                });
                row.classList.add('oms-row-selected');
            })
            .catch(() => {});
    });
});
</script>
@endsection
