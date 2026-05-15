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

    $logisticsSummary = $logisticsSummary ?? [];
    $logisticsTotals = data_get($logisticsSummary, 'totals', []);
    $logisticsSuggestions = collect(data_get($logisticsSummary, 'suggestions', []));
    $logisticsMissing = collect(data_get($logisticsSummary, 'missing_items', []));
    $logisticsContainers = collect(data_get($logisticsSummary, 'containers', []));

    $formatNumber = function ($value, int $decimals = 2) {
        return number_format((float) $value, $decimals, ',', '.');
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
.oms-logistics-card{border:1px solid rgba(20,33,61,.08);border-radius:5px;background:#fff;padding:.85rem .95rem;margin-top:1rem;margin-bottom:1rem;}
.oms-logistics-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.65rem;margin-bottom:.8rem;}
.oms-logistics-kpi{border:1px solid rgba(20,33,61,.08);border-radius:5px;padding:.65rem .8rem;background:#f8fafc;}
.oms-logistics-kpi .label{display:block;font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:.2rem;}
.oms-logistics-kpi .value{display:block;font-weight:800;color:#111827;}
.oms-logistics-list{display:flex;flex-direction:column;gap:.45rem;}
.oms-logistics-row{display:flex;justify-content:space-between;gap:.75rem;padding:.45rem .55rem;border:1px solid rgba(20,33,61,.08);border-radius:5px;background:#fff;}
.oms-logistics-row small{display:block;color:#6b7280;}
.oms-logistics-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.22rem .5rem;border-radius:999px;font-size:.74rem;font-weight:700;border:1px solid rgba(20,33,61,.08);}
.oms-logistics-badge-ok{background:#dcfce7;color:#15803d;border-color:#86efac;}
.oms-logistics-badge-warn{background:#fef3c7;color:#b45309;border-color:#fcd34d;}
.oms-logistics-note{font-size:.8rem;color:#6b7280;}
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
            <div>
                <label class="oms-line-label" style="width: calc( 100% - 200px); float: left;">Order note products list</label>
                <div class="d-flex" style="width: 157px; float: right;">
                    <button type="button" class="btn btn-sm {{ !empty($orderNote->internal_note) ? 'btn-outline-danger' : 'btn-outline-secondary' }}" data-bs-toggle="modal" data-bs-target="#omsInternalNoteModal">
                        <i class="fa-solid fa-note-sticky"></i> Internal
                    </button>
                    <button type="button" class="btn btn-sm {{ !empty($orderNote->logistic_note) ? 'btn-outline-danger' : 'btn-outline-secondary' }}" data-bs-toggle="modal" data-bs-target="#omsLogisticNoteModal">
                        <i class="fa-solid fa-truck-fast"></i> Logistic
                    </button>
                </div>
            </div>
            <input id="omsBuilderLinesSearch" class="form-control" placeholder="Search added products by SKU or name" style="border-radius: 0 !important">
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 oms-added-table" style="text-align: center;">
                <colgroup>
                    <col style="width:10%">
                    <col style="width:30%">
                    <col style="width:12%">
                    <col style="width:12%">
                    <col style="width:12%">
                    <col style="width:12%">
                    <col style="width:12%">
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-center"></th>
                        <th>SKU</th>
                        <th>Purchase</th>
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
                        <td title="{{ $line->name }}">{{ $line->sku }}</td>
                        <td> {{ $line->unit_cost_formatted ?? number_format($line->unit_cost ?? 0, 2, ',', '.') }} </td>
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
                    <tr><td colspan="7" class="text-muted text-center py-4">No products added yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="oms-logistics-card">
            <div class="oms-terms-title">Logistics estimate</div>

            <div class="oms-logistics-grid">
                <div class="oms-logistics-kpi">
                    <span class="label">Volume</span>
                    <span class="value">{{ $formatNumber(data_get($logisticsTotals, 'volume_m3', 0), 3) }} m³</span>
                </div>
                <div class="oms-logistics-kpi">
                    <span class="label">Weight</span>
                    <span class="value">{{ $formatNumber(data_get($logisticsTotals, 'weight_kg', 0), 2) }} kg</span>
                </div>
                <div class="oms-logistics-kpi">
                    <span class="label">Lines with data</span>
                    <span class="value">{{ (int) data_get($logisticsTotals, 'lines_with_logistics', 0) }}/{{ (int) data_get($logisticsTotals, 'line_count', 0) }}</span>
                </div>
                <div class="oms-logistics-kpi">
                    <span class="label">Missing logistics</span>
                    <span class="value">{{ (int) data_get($logisticsTotals, 'missing_count', 0) }}</span>
                </div>
            </div>

            @if($logisticsSuggestions->isNotEmpty())
                <div class="oms-logistics-list">
                    @foreach($logisticsSuggestions as $suggestion)
                        <div class="oms-logistics-row">
                            <div>
                                <div class="fw-semibold">{{ data_get($suggestion, 'units_needed', 0) }}× {{ data_get($suggestion, 'name', 'Container') }}</div>
                                <small>{{ ucfirst((string) data_get($suggestion, 'type', 'container')) }} · Volume {{ $formatNumber(data_get($suggestion, 'volume_usage_pct', 0), 1) }}% · Weight {{ $formatNumber(data_get($suggestion, 'weight_usage_pct', 0), 1) }}%</small>
                            </div>
                            @if(data_get($suggestion, 'fits'))
                                <span class="oms-logistics-badge oms-logistics-badge-ok"><i class="fa-solid fa-check"></i> Fits</span>
                            @else
                                <span class="oms-logistics-badge oms-logistics-badge-warn"><i class="fa-solid fa-triangle-exclamation"></i> Limit</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="oms-logistics-note">No active logistics containers configured yet.</div>
            @endif

            @if($logisticsMissing->isNotEmpty())
                <div class="oms-logistics-note mt-2">
                    Missing logistics data for:
                    {{ $logisticsMissing->pluck('sku')->filter()->take(5)->implode(', ') }}
                    @if($logisticsMissing->count() > 5) … @endif
                </div>
            @endif
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
