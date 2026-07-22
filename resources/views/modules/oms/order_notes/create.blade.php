@extends('layouts.app')

@section('content')
<div class="container-fluid py-3 oms-premium oms-order-note-create">
    <style>
        .oms-order-note-create .oms-card{border:1px solid rgba(20,33,61,.10);border-radius:5px;box-shadow:0 8px 24px rgba(15,23,42,.06);overflow:hidden;background:#fff;}
        .oms-order-note-create .oms-card-header{padding:.85rem 1rem;border-bottom:1px solid rgba(20,33,61,.08);background:#f8fafc;}
        .oms-order-note-create .oms-card-body{padding:1rem;}
        .oms-order-note-create .oms-soft-label{font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;font-weight:700;}
        .oms-order-note-create .oms-btn-icon{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;border-radius:5px;padding:.55rem .85rem;font-weight:600;}
        .oms-order-note-create .oms-kpi{border:1px solid rgba(20,33,61,.08);border-radius:5px;background:#fff;padding:.7rem .9rem;}
        .oms-order-note-create .oms-supplier-row{display:flex;align-items:center;justify-content:space-between;padding:.6rem .75rem;border-bottom:1px solid rgba(20,33,61,.08);text-decoration:none;color:#1f2937;background:#fff;}
        .oms-order-note-create .oms-supplier-row:hover{background:#f8fafc;}
        .oms-order-note-create .oms-supplier-row.active{background:#eff6ff;color:#1d4ed8;}
        .oms-order-note-create .oms-main-tab{display:inline-flex;align-items:center;gap:.45rem;border:1px solid rgba(20,33,61,.08);border-radius:5px;padding:.45rem .8rem;text-decoration:none;color:#374151;background:#fff;text-align:center;}
        .oms-order-note-create .oms-main-tab.active{color:#1d4ed8;background:#eff6ff;border-color:rgba(37,99,235,.32);}
        .oms-order-note-create .badge,.oms-order-note-create .btn,.oms-order-note-create .form-control,.oms-order-note-create .input-group-text,.oms-order-note-create .form-select,.oms-order-note-create .table{border-radius:5px !important;}
        .oms-order-note-create .oms-line-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;font-weight:700;}
        .oms-order-note-create .oms-summary-list .item{display:flex;justify-content:space-between;gap:.75rem;padding:.45rem 0;border-bottom:1px solid rgba(20,33,61,.06);}
        .oms-order-note-create .oms-summary-list .item:last-child{border-bottom:none;}
        .oms-order-note-create .oms-empty{padding:2rem 1rem;text-align:center;color:#6b7280;}
        .oms-order-note-create .status-square{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:5px;font-size:11px;font-weight:700;}
        .oms-order-note-create .status-ok{background:#dcfce7;color:#15803d;border:1px solid #86efac;}
        .oms-order-note-create .status-warn{background:#ffedd5;color:#c2410c;border:1px solid #fdba74;}
        .oms-order-note-create .status-bad{background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;}
        .oms-order-note-create .oms-create-helper{border:1px dashed rgba(20,33,61,.15);border-radius:5px;background:#fbfdff;padding:1rem;}
        .oms-order-note-create .oms-create-stage-grid{display:grid;grid-template-columns:3fr 7fr 2fr;gap:1rem;align-items:start;}
        .oms-order-note-create .oms-builder-grid{display:grid;grid-template-columns:minmax(0,5fr) minmax(0,7fr);gap:1rem;align-items:start;}
        @media (max-width:1399px){.oms-order-note-create .oms-create-stage-grid{grid-template-columns:1fr;}.oms-order-note-create .oms-builder-grid{grid-template-columns:1fr;}}
    </style>

@php
    $builderLines = $builderLines ?? collect();
    $builderSummary = $builderSummary ?? ['lines_count' => 0, 'total_qty' => 0, 'total_billed' => 0, 'total_received' => 0];
    $supplierMap = $supplierMap ?? null;
    $ordered = (int) ($builderSummary['total_qty'] ?? 0);
    $billed = (int) ($builderSummary['total_billed'] ?? 0);
    $received = (int) ($builderSummary['total_received'] ?? 0);
    $progress = $billed > 0 ? (int) round(($received / max($billed, 1)) * 100) : 0;
    $progress = max(0, min(100, $progress));

    $termsSummary = $termsSummary ?? [];
    $orderAmount = (float) data_get($termsSummary, 'amount', $orderAmount ?? 0);
    $currentLevel = data_get($termsSummary, 'current_level');
    $nextLevel = data_get($termsSummary, 'next_level');
    $missingToNext = (float) data_get($termsSummary, 'missing_to_next', 0);

    $supplierTermsService = app(\App\Services\oms\SupplierTermsService::class);
    $currentLevelLabel = $supplierTermsService->buildLabel($currentLevel);
    $nextLevelLabel = $nextLevel ? $supplierTermsService->buildLabel($nextLevel) : 'No next level';

    $formatMoney = function ($value) {
        return number_format((float) $value, 2, ',', '.');
    };
@endphp

    @php
        $orderNote = $orderNote ?? null;
        $selectedSupplierId = $selectedSupplierId ?? null;
        $selectedSupplier = $selectedSupplier ?? null;
        $builderSummary = $builderSummary ?? ['lines_count' => 0, 'total_qty' => 0, 'total_billed' => 0, 'total_received' => 0];
        $builderLines = $builderLines ?? collect();
        $builderProducts = $builderProducts ?? collect();
        $supplierMap = $supplierMap ?? null;
    @endphp

    <div class="oms-card mb-3">
        <div class="oms-card-body py-2 px-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <a href="{{ route('erp.oms.dashboard') }}" class="btn btn-sm btn-outline-primary oms-btn-icon">
                        <i class="fa-solid fa-table-columns"></i> Dashboard
                    </a>
                </div>
                
                @if($orderNote || $selectedSupplier)
                    <style>
                        .oms-terms-inline { padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border-soft); background: var(--card-bg); font-size: 13px; }
                        .oms-terms-muted { color: var(--text-muted); }
                        .oms-terms-warning { color: #f59e0b; font-weight: 500; }
                        .oms-terms-success { color: #10b981; font-weight: 500; }
                    </style>

                    <div id="omsOrderNoteTermsHeader" class="text-center flex-grow-1 px-2" style="min-width:260px;text-align: center;">
                        @include('modules.oms.order_notes.partials.builder_terms_header', [
                            'orderAmount' => $orderAmount,
                            'currentLevel' => $currentLevel,
                            'nextLevel' => $nextLevel,
                            'missingToNext' => $missingToNext,
                            'currentLevelLabel' => $currentLevelLabel,
                            'nextLevelLabel' => $nextLevelLabel,
                            'formatMoney' => $formatMoney,
                        ])
                    </div>
                @else
                    <div class="text-muted">Select a supplier</div>
                @endif

                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    @if($orderNote)
                        <a href="{{ route('erp.oms.order_notes.show', $orderNote) }}" class="btn btn-sm btn-outline-primary oms-btn-icon"><i class="fa-solid fa-up-right-from-square"></i> Open</a>
                        <a href="{{ route('erp.oms.order_notes.export.xlsx', $orderNote) }}" class="btn btn-sm btn-outline-success oms-btn-icon"><i class="fa-solid fa-file-excel"></i> Export</a>
                        <a href="{{ route('erp.oms.order_notes.export.pdf', $orderNote) }}" class="btn btn-sm btn-outline-danger oms-btn-icon"><i class="fa-solid fa-file-pdf"></i> PRINT</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(!$orderNote)
        <div class="row g-3">
            <div class="col-xl-3">
                <div class="oms-card">
                    <div class="oms-card-header">
                        <strong>Suppliers</strong>
                    </div>
                    <div class="oms-card-body p-0">
                        <div class="border-bottom">
                            <div class="input-group">
                                <input type="text" id="omsSupplierSearch" class="form-control" placeholder="Search supplier..." style="border-radius:0 !important;">
                                <span class="input-group-text" style="border-radius:0 !important;"><i class="fa-solid fa-magnifying-glass"></i></span>
                            </div>
                        </div>
                        <div id="omsSupplierList" style="max-height:760px;overflow:auto;">
                            @forelse($supplierSidebar as $supplier)
                                <a href="{{ route('erp.oms.order_notes.create', ['supplier_id' => $supplier->supplier_id]) }}"
                                   class="oms-supplier-row oms-supplier-link {{ (int) $selectedSupplierId === (int) $supplier->supplier_id ? 'active' : '' }}"
                                   data-search="{{ strtolower($supplier->supplier_name) }}">
                                    <span class="d-block fw-semibold">{{ $supplier->supplier_name }}</span>
                                </a>
                            @empty
                                <div class="p-3 text-muted">No suppliers found.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-7">
                <div class="oms-card">
                    <div class="oms-card-header d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <div class="oms-soft-label">Order Note</div>
                            <div class="fw-bold">Create order note</div>
                        </div>
                    </div>
                    <div class="oms-card-body">
                        @if($selectedSupplier)
                            <div class="oms-create-helper mb-3">
                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                    <div>
                                        <div class="oms-soft-label">Selected supplier</div>
                                        <div class="h5 mb-1">{{ $selectedSupplier->supplier_name }}</div>
                                    </div>
                                    <form method="post" action="{{ route('erp.oms.order_notes.create_from_supplier', $selectedSupplier->supplier_id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-primary oms-btn-icon">
                                            <i class="fa-solid fa-plus"></i> Create Order Note
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="oms-empty">
                                <i class="fa-solid fa-truck-field fa-2x mb-3"></i>
                                <div class="fw-semibold mb-1">Select a supplier on the left</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-2">
                <div class="oms-card">
                    <div class="oms-card-header"><strong>Summary</strong></div>
                    <div class="oms-card-body">
                        <div class="oms-summary-list">
                            <div class="item"><span class="text-muted">Document</span><strong>Order Note</strong></div>
                            <div class="item"><span class="text-muted">Supplier</span><strong>{{ $selectedSupplier?->supplier_name ?? '—' }}</strong></div>
                            <div class="item"><span class="text-muted">Currency</span><strong>{{ data_get($supplierMap, 'currency', '—') }}</strong></div>
                            <div class="item"><span class="text-muted">Flow</span><strong>Create → Add products</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="oms-builder-grid" id="omsOrderNoteBuilder" data-products-endpoint="{{ route('erp.oms.order_notes.supplier_products', $orderNote) }}">

            <div class="oms-card">
                <div class="oms-card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                    <div>
                        <div class="oms-soft-label">Supplier catalogue</div>
                        <div class="fw-bold">Add products to order note</div>
                    </div>
                </div>
                <div class="oms-card-body">
                    <div class="row g-2 align-items-end">
                        <div class="col-lg-12 col-12">
                            <label class="oms-line-label mb-2">Search products</label>
                            <input id="omsSupplierProductSearch" class="form-control" placeholder="Reference, EAN, attribute reference or product name" autocomplete="off" style="border-radius: 0 !important">
                        </div>
                    </div>

                    <div id="omsAddProductsResults" data-endpoint="{{ route('erp.oms.order_notes.supplier_products', $orderNote) }}">
                        @include('modules.oms.order_notes.partials.builder_results', [
                            'orderNote' => $orderNote,
                            'products' => $builderProducts,
                            'search' => request('q', ''),
                        ])
                    </div>
                </div>
            </div>
            <div id="omsOrderNoteBuilderSidebar">
                @include('modules.oms.order_notes.partials.builder_sidebar', [
                    'orderNote' => $orderNote,
                    'builderLines' => $builderLines,
                    'builderSummary' => $builderSummary,
                    'supplierMap' => $supplierMap,
                ])
            </div>
        </div>


        <div class="modal fade" id="omsInternalNoteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius:5px;">
                    <form method="post" action="{{ route('erp.oms.order_notes.notes.save', $orderNote) }}" class="js-oms-document-notes-form" data-note-kind="internal">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Internal note</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <textarea name="internal_note" id="omsInternalNoteField" class="form-control" rows="8" placeholder="Internal context for this order note...">{{ $orderNote->internal_note }}</textarea>
                            <input type="hidden" name="logistic_note" value="{{ $orderNote->logistic_note }}">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary oms-btn-icon"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="omsLogisticNoteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius:5px;">
                    <form method="post" action="{{ route('erp.oms.order_notes.notes.save', $orderNote) }}" class="js-oms-document-notes-form" data-note-kind="logistic">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Logistic note</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <textarea name="logistic_note" id="omsLogisticNoteField" class="form-control" rows="8" placeholder="Logistic information for this order note...">{{ $orderNote->logistic_note }}</textarea>
                            <input type="hidden" name="internal_note" value="{{ $orderNote->internal_note }}">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-warning oms-btn-icon"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script>
    (function () {
        const supplierSearch = document.getElementById('omsSupplierSearch');
        if (supplierSearch) {
            supplierSearch.addEventListener('input', function () {
                const q = this.value.toLowerCase().trim();
                document.querySelectorAll('.oms-supplier-link').forEach(function (el) {
                    el.style.display = !q || (el.dataset.search || '').includes(q) ? '' : 'none';
                });
            });
        }

        const builder = document.getElementById('omsOrderNoteBuilder');
        if (!builder || builder.dataset.initialized === '1') {
            return;
        }
        builder.dataset.initialized = '1';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const searchInput = document.getElementById('omsSupplierProductSearch');
        const resultsBox = document.getElementById('omsAddProductsResults');
        const sidebarBox = document.getElementById('omsOrderNoteBuilderSidebar');
        const headerTermsBox = document.getElementById('omsOrderNoteTermsHeader');
        const productsEndpoint = builder.dataset.productsEndpoint || '';
        const qtyTimers = {};
        let searchTimer = null;

        function getSyncStatusEl() {
            return document.getElementById('omsBuilderSyncStatus');
        }

        function setSyncState(state, text) {
            const syncStatus = getSyncStatusEl();
            if (!syncStatus) return;
            syncStatus.classList.remove('text-danger', 'text-warning');
            if (state === 'saving') {
                syncStatus.classList.add('text-warning');
                syncStatus.innerHTML = '<i class="fa-solid fa-rotate fa-spin"></i> ' + (text || 'Saving...');
                return;
            }
            if (state === 'error') {
                syncStatus.classList.add('text-danger');
                syncStatus.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> ' + (text || 'Save error');
                return;
            }
            syncStatus.innerHTML = '<i class="fa-solid fa-check"></i> ' + (text || 'Updated');
        }

        async function loadSupplierProducts() {
            if (!productsEndpoint || !resultsBox) return;

            const params = new URLSearchParams();
            if (searchInput?.value) params.set('q', searchInput.value);

            const url = productsEndpoint + (params.toString() ? ('?' + params.toString()) : '');

            try {
                const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } });
                if (!response.ok) throw new Error('products');
                resultsBox.innerHTML = await response.text();
            } catch (error) {
                resultsBox.innerHTML = '<div class="alert alert-danger mb-0">Unable to load supplier products.</div>';
            }
        }

        async function refreshTermsHeaderFromPage() {
            if (!headerTermsBox) return;

            try {
                const response = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                });
                if (!response.ok) return;

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const freshHeader = doc.getElementById('omsOrderNoteTermsHeader');

                if (freshHeader) {
                    headerTermsBox.innerHTML = freshHeader.innerHTML;
                }
            } catch (error) {
                console.error('terms_header_refresh_failed', error);
            }
        }

        async function refreshBuilderFromResponse(response) {
            if (!response) return;
            if (resultsBox && response.products_html) {
                resultsBox.innerHTML = response.products_html;
            } else {
                await loadSupplierProducts();
            }
            if (sidebarBox && response.sidebar_html) {
                sidebarBox.innerHTML = response.sidebar_html;
            }

            await refreshTermsHeaderFromPage();
        }

        async function sendBuilderRequest(url, method, body, trigger) {
            setSyncState('saving', 'Saving...');
            if (trigger) trigger.disabled = true;

            if (searchInput?.value && !body.has('q')) body.append('q', searchInput.value);

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: body,
                });

                const payload = await response.json();
                if (!response.ok || !payload.success) {
                    throw new Error(payload.message || 'request_failed');
                }

                await refreshBuilderFromResponse(payload);
                if (payload.warning) {
                    alert(payload.warning);
                }
                setSyncState('saved', payload.message || 'Updated');
                return true;
            } catch (error) {
                console.error(error);
                setSyncState('error', error.message || 'Save error');
                return false;
            } finally {
                if (trigger) trigger.disabled = false;
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(loadSupplierProducts, 300);
            });

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    loadSupplierProducts();
                }
            });
        }

        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;

            if (form.classList.contains('js-oms-document-notes-form')) {
                event.preventDefault();
                const button = form.querySelector('button[type="submit"]');

                sendBuilderRequest(form.action, 'POST', new FormData(form), button).then(function (success) {
                    if (!success) return;

                    const kind = form.dataset.noteKind || '';
                    
                    const modalEl = document.getElementById('omsLogisticNoteModal');

                    if (kind == 'internal') {
                        const modalEl = document.getElementById('omsInternalNoteModal');
                        if (modalEl && window.bootstrap?.Modal) bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    }

                    if (kind == 'logistic') {
                        const modalEl = document.getElementById('omsLogisticNoteModal');
                        if (modalEl && window.bootstrap?.Modal) bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    }
                });
                
                document.querySelector('.modal.show')?.classList.remove('show');
                document.querySelector('.modal-backdrop')?.remove();
                document.body.classList.remove('modal-open');                
                return;
            }

            if (form.classList.contains('js-oms-add-line-form')) {
                event.preventDefault();
                const button = form.querySelector('button[type="submit"]');
                sendBuilderRequest(form.action, 'POST', new FormData(form), button);
                return;
            }

            if (form.classList.contains('js-oms-remove-line-form')) {
                event.preventDefault();
                const button = form.querySelector('button[type="submit"]');
                const body = new FormData(form);
                body.append('_method', 'DELETE');
                sendBuilderRequest(form.action, 'POST', body, button);
            }
        });

        document.addEventListener('input', function (event) {
            const input = event.target;
            if (!(input instanceof HTMLInputElement)) return;
            if (!input.classList.contains('js-oms-line-qty')) return;

            const form = input.closest('form');
            if (!form) return;

            const lineId = input.dataset.lineId || input.name;
            clearTimeout(qtyTimers[lineId]);

            qtyTimers[lineId] = setTimeout(function () {
                const value = parseInt(input.value || '0', 10);
                if (Number.isNaN(value) || value < 0) {
                    input.value = input.dataset.lastValue || '1';
                    setSyncState('error', 'Invalid quantity');
                    return;
                }

                input.dataset.lastValue = String(value);
                const body = new FormData(form);
                body.append('_method', 'PATCH');
                sendBuilderRequest(form.action, 'POST', body, null);
            }, 350);
        });

        document.addEventListener('input', function (event) {
            const input = event.target;
            if (!(input instanceof HTMLInputElement)) return;
            if (!input.id || input.id !== 'omsBuilderLinesSearch') return;

            const q = input.value.toLowerCase().trim();
            document.querySelectorAll('.oms-builder-line-row').forEach(function (row) {
                row.style.display = !q || (row.dataset.filterText || '').includes(q) ? '' : 'none';
            });
        });
    })();
    </script>
</div>
@endsection
