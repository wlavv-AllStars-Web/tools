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

<style>
.oms-builder-sidebar-card{border:1px solid rgba(20,33,61,.10);border-radius:5px;overflow:hidden;background:#fff;box-shadow:0 8px 24px rgba(15,23,42,.06);}
.oms-builder-sidebar-card .card-head{padding:.85rem 1rem;border-bottom:1px solid rgba(20,33,61,.08);display:flex;justify-content:space-between;align-items:center;gap:.75rem;flex-wrap:wrap;background:#f8fafc;}
.oms-builder-sidebar-card .card-body{padding:1rem;}
.oms-builder-kpis{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.65rem;margin-bottom:1rem;}
.oms-builder-kpi{border:1px solid rgba(20,33,61,.08);border-radius:5px;padding:.65rem .8rem;background:#fff;}
.oms-builder-kpi .label{display:block;font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:.2rem;}
.oms-builder-kpi .value{display:block;font-weight:800;color:#111827;}
.oms-added-table{table-layout:fixed;}
.oms-added-table thead th{font-size:.74rem;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;white-space:nowrap;background:#f8fafc;}
.oms-added-table tbody td{white-space:nowrap;vertical-align:middle;}
.oms-code{display:inline-block;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;background:#f8fafc;border:1px solid rgba(20,33,61,.08);padding:.18rem .42rem;border-radius:5px;font-size:.79rem;}
.oms-remove-btn{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:5px;border:1px solid rgba(220,38,38,.16);background:#fef2f2;color:#dc2626;}
.oms-inline-input{max-width:76px;margin:0 auto;text-align:center;}
.oms-terms-card{border:1px solid rgba(20,33,61,.08);border-radius:5px;background:#fff;padding:.85rem .95rem;margin-top:1rem;margin-bottom:1rem;}
.oms-terms-title{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;font-weight:700;margin-bottom:.35rem;}
.oms-terms-block + .oms-terms-block{margin-top:.75rem;padding-top:.75rem;border-top:1px solid rgba(20,33,61,.08);}
.oms-terms-main{font-size:1.05rem;font-weight:800;color:#111827;}
.oms-terms-muted{font-size:.84rem;color:#6b7280;}
.oms-terms-highlight{display:inline-flex;align-items:center;gap:.45rem;padding:.38rem .62rem;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-weight:700;font-size:.82rem;border:1px solid rgba(37,99,235,.18);}
</style>

<div class="oms-builder-sidebar-card">
    <div class="card-head">
        <div>
            <div class="fw-bold">Order note summary</div>
            <div class="text-muted small">{{ $orderNote->reference }} · {{ optional($orderNote->supplier)->name ?? ('Supplier #' . $orderNote->supplier_id) }}</div>
        </div>
        <span id="omsBuilderSyncStatus"><i class="fa-solid fa-check"></i> Updated</span>
    </div>

    <div class="card-body">
        <div>
            <label class="oms-line-label mb-2">Order note products list</label>
            <input id="omsBuilderLinesSearch" class="form-control" placeholder="Search added products by SKU or name" style="border-radius: 0 !important">
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 oms-added-table">
                <colgroup>
                    <col style="width:10%">
                    <col style="width:26%">
                    <col style="width:16%">
                    <col style="width:14%">
                    <col style="width:18%">
                    <col style="width:16%">
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-center"></th>
                        <th>SKU</th>
                        <th class="text-center">Invoiced</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Received</th>
                        <th class="text-center">Qty</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($builderLines as $line)
                    <tr class="oms-builder-line-row" data-filter-text="{{ $line->search_text }}">
                        <td class="text-center">
                            <form class="js-oms-remove-line-form" method="post" action="{{ route('erp.oms.order_notes.lines.destroy', [$orderNote, $line->id]) }}">
                                @csrf
                                @method('DELETE')
                                <button class="oms-remove-btn" type="submit" title="Remove from order note" aria-label="Remove from order note">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </form>
                        </td>
                        <td title="{{ $line->name }}">
                            <span class="oms-code">{{ $line->sku }}</span>
                        </td>
                        <td class="text-center">{{ (int) ($line->qty_billed ?? 0) }}</td>
                        <td class="text-center">{{ (int) ($line->stock_qty ?? 0) }}</td>
                        <td class="text-center">{{ (int) ($line->qty_received ?? 0) }}</td>
                        <td class="text-center">
                            <form class="js-oms-line-update-form" method="post" action="{{ route('erp.oms.order_notes.lines.update', [$orderNote, $line->id]) }}">
                                @csrf
                                @method('PATCH')
                                <input
                                    class="form-control form-control-sm js-oms-line-qty oms-inline-input"
                                    data-line-id="{{ $line->id }}"
                                    data-last-value="{{ (int) $line->qty_ordered }}"
                                    type="number"
                                    min="0"
                                    name="qty_ordered"
                                    value="{{ (int) $line->qty_ordered }}"
                                >
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center py-4">No products added yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex gap-2 mb-3" style="margin-top: 20px;">
            <button type="button" class="btn btn-sm {{ !empty($orderNote->internal_note) ? 'btn-outline-danger' : 'btn-outline-secondary' }}" data-bs-toggle="modal" data-bs-target="#omsInternalNoteModal">
                <i class="fa-solid fa-note-sticky"></i> Internal
            </button>
            <button type="button" class="btn btn-sm {{ !empty($orderNote->logistic_note) ? 'btn-outline-danger' : 'btn-outline-secondary' }}" data-bs-toggle="modal" data-bs-target="#omsLogisticNoteModal">
                <i class="fa-solid fa-truck-fast"></i> Logistic
            </button>
        </div>

        <div class="accordion mb-3" id="omsSupplierMapAccordion" style="margin-top: 10px;">
            <div class="accordion-item" style="border-radius:5px;overflow:hidden;">
                <h2 class="accordion-header" id="omsSupplierMapHeading">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#omsSupplierMapCollapse" aria-expanded="false" aria-controls="omsSupplierMapCollapse">
                        Supplier map
                    </button>
                </h2>
                <div id="omsSupplierMapCollapse" class="accordion-collapse collapse" aria-labelledby="omsSupplierMapHeading" data-bs-parent="#omsSupplierMapAccordion">
                    <div class="accordion-body py-2 px-3">
                        @if($supplierMap && data_get($supplierMap, 'exists'))
                            <div class="small">
                                @foreach([
                                    'contact' => 'Contact',
                                    'email' => 'Email',
                                    'phone' => 'Phone',
                                    'currency' => 'Currency',
                                    'incoterm' => 'Incoterm',
                                    'country' => 'Country',
                                    'website' => 'Website',
                                    'dealer_website' => 'Dealer website',
                                ] as $key => $label)
                                    @if(data_get($supplierMap, $key))
                                        <div class="d-flex justify-content-between gap-2 py-1 border-bottom"><span class="text-muted">{{ $label }}</span><strong class="text-end">{{ data_get($supplierMap, $key) }}</strong></div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted small">No supplier map data found.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
