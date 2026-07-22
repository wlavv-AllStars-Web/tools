@extends('layouts.app')

@section('content')

@include('modules.oms.includes.js')

<div class="container-fluid py-3 oms-on-show">
    <style>
        .oms-on-show .oms-card,
        .oms-on-show .card,
        .oms-on-show .btn,
        .oms-on-show .badge,
        .oms-on-show .form-control,
        .oms-on-show .form-select,
        .oms-on-show .input-group-text,
        .oms-on-show .table,
        .oms-on-show .modal-content,
        .oms-on-show .list-group-item { border-radius: 5px !important; }

        .oms-on-show .oms-card {
            border: 1px solid rgba(20, 33, 61, .08);
            background: #fff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .05);
            overflow: hidden;
            height: 100%;
        }

        .oms-on-show .oms-card-header {
            padding: .85rem 1rem;
            border-bottom: 1px solid rgba(20, 33, 61, .07);
            background: #f8fafc;
        }

        .oms-on-show .oms-card-body { padding: 1rem; }
        .oms-on-show .oms-soft-label {
            font-size: .69rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #6b7280;
            font-weight: 700;
            margin-bottom: .2rem;
        }

        .oms-on-show .oms-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .oms-on-show .oms-topbar-block {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .oms-on-show .oms-count-chip,
        .oms-on-show .oms-selected-chip {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .5rem .7rem;
            border: 1px solid rgba(20, 33, 61, .10);
            background: #fff;
            border-radius: 5px;
            font-weight: 600;
            color: #1f2937;
            min-height: 38px;
        }

        .oms-on-show .oms-selected-chip { min-width: 280px; justify-content: center; }
        .oms-on-show .oms-compact-btn { border-radius: 5px; font-weight: 600; }
        .oms-on-show .oms-list-search { margin-bottom: .75rem; }

        .oms-on-show .oms-link-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .65rem .75rem;
            border: 1px solid rgba(20, 33, 61, .07);
            background: #fff;
            text-decoration: none;
            color: #1f2937;
            margin-bottom: .5rem;
            transition: all .18s ease;
        }

        .oms-on-show .oms-link-row:hover,
        .oms-on-show .oms-link-row.active {
            background: #eff6ff;
            border-color: rgba(37, 99, 235, .22);
            color: #1d4ed8;
        }

        .oms-on-show .oms-link-row-main { min-width: 0; }
        .oms-on-show .oms-link-row-title {
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .oms-on-show .oms-link-row-sub { font-size: .78rem; color: #6b7280; }

        .oms-on-show .oms-tab-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            width: calc(33.333% - .35rem);
            min-height: 38px;
            padding: .55rem .7rem;
            border: 1px solid rgba(20, 33, 61, .08);
            background: #fff;
            color: #374151;
            text-decoration: none;
            font-weight: 600;
            border-radius: 5px;
        }

        .oms-on-show .oms-tab-btn.active {
            color: #1d4ed8;
            background: #eff6ff;
            border-color: rgba(37, 99, 235, .24);
        }

        .oms-on-show .oms-summary-list {
            display: grid;
            gap: .55rem;
            margin-bottom: 1rem;
        }

        .oms-on-show .oms-summary-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .85rem;
            padding-bottom: .45rem;
            border-bottom: 1px dashed rgba(20, 33, 61, .08);
        }

        .oms-on-show .oms-summary-row:last-child { border-bottom: 0; padding-bottom: 0; }
        .oms-on-show .oms-summary-key { color: #6b7280; font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
        .oms-on-show .oms-summary-value { color: #111827; font-weight: 700; text-align: right; }

        .oms-on-show .oms-stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .5rem;
            margin-bottom: .9rem;
        }

        .oms-on-show .oms-stat-box {
            border: 1px solid rgba(20, 33, 61, .08);
            background: #fff;
            padding: .75rem .5rem;
            text-align: center;
            border-radius: 5px;
        }

        .oms-on-show .oms-stat-box .label { color: #6b7280; font-size: .72rem; font-weight: 700; text-transform: uppercase; }
        .oms-on-show .oms-stat-box .value { color: #111827; font-size: 1.1rem; font-weight: 800; line-height: 1.2; }

        .oms-on-show .progress { height: 10px; background: #e5e7eb; border-radius: 5px; overflow: hidden; }
        .oms-on-show .progress-bar { border-radius: 5px; }
        .oms-on-show .table > :not(caption) > * > * { vertical-align: middle; }
        .oms-on-show .table .actions { width: 1%; white-space: nowrap; text-align: right; }
        .oms-on-show .oms-clickable-row { cursor: pointer; }
        .oms-on-show .oms-clickable-row.oms-row-selected > td { background: #eff6ff !important; }

        .oms-on-show .oms-status-square {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            border-radius: 4px;
            font-size: .68rem;
            margin-left: .35rem;
            color: #fff;
        }

        .oms-on-show .oms-status-square.complete { background: #16a34a; }
        .oms-on-show .oms-status-square.partial { background: #f59e0b; }
        .oms-on-show .oms-status-square.zero { background: #dc2626; }
        .oms-on-show .oms-note-btn-empty { border-color: #ced4da !important; color: #6b7280 !important; }
        .oms-on-show .oms-note-btn-empty:hover { border-color: #ced4da !important; color: #6b7280 !important; background-color:#ddd; }
        .oms-on-show .oms-note-btn-filled { border-color: #dc2626 !important; color: #dc2626 !important; }
        .oms-on-show .oms-collapse-card { border: 1px dashed rgba(20, 33, 61, .12); border-radius: 5px; padding: .75rem; background: #fcfcfd; }
        .oms-on-show .oms-muted-panel { border: 1px dashed rgba(20, 33, 61, .10); background: #fcfcfd; border-radius: 5px; padding: .75rem; }
        .oms-on-show .oms-line-preview { border-top: 1px solid rgba(20, 33, 61, .08); margin-top: 1rem; padding-top: 1rem; }
        .oms-on-show .oms-empty { padding: 1.5rem .75rem; text-align: center; color: #6b7280; }
        .oms-on-show .oms-ref-copy,
        .oms-on-show .oms-copyable {
            cursor: copy;
            user-select: all;
        }
        .oms-on-show .oms-ref-copy {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-weight: 700;
        }
        .oms-on-show .oms-ref-copy:hover,
        .oms-on-show .oms-copyable:hover { color: #1d4ed8; }
        .oms-on-show .oms-detail-row > td {
            background: #f8fafc !important;
            border-top: 0;
            padding: 0 !important;
        }
        .oms-on-show .oms-detail-wrap {
            padding: .9rem 1rem;
            border-top: 1px dashed rgba(20, 33, 61, .08);
        }
        .oms-on-show .oms-detail-layout {
            display: grid;
            grid-template-columns: 160px minmax(0, 1fr);
            gap: .85rem;
            align-items: stretch;
        }
        .oms-on-show .oms-tech-image {
            border: 1px solid rgba(20, 33, 61, .08);
            border-radius: 5px;
            background: #fff;
            min-height: 130px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .oms-on-show .oms-tech-image img {
            width: 100%;
            height: 130px;
            object-fit: contain;
            background: #fff;
        }
        .oms-on-show .oms-tech-image .placeholder {
            color: #9ca3af;
            font-size: 1.4rem;
        }
        .oms-on-show .oms-detail-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
        }
        .oms-on-show .oms-detail-box {
            border: 1px solid rgba(20, 33, 61, .08);
            background: #fff;
            border-radius: 5px;
            padding: .7rem .8rem;
            min-height: 100%;
        }
        .oms-on-show .oms-detail-box .detail-label {
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #6b7280;
            font-weight: 700;
            margin-bottom: .2rem;
        }
        .oms-on-show .oms-detail-box .detail-value {
            font-weight: 700;
            color: #111827;
            word-break: break-word;
        }
        .oms-on-show .oms-toggle-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .oms-on-show .oms-toggle-btn i {
            transition: transform .18s ease;
        }
        .oms-on-show .oms-toggle-btn[aria-expanded="true"] i {
            transform: rotate(180deg);
        }

        @media (max-width: 991.98px) {
            .oms-on-show .oms-detail-layout { grid-template-columns: 1fr; }
            .oms-on-show .oms-detail-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 1199.98px) {
            .oms-on-show .oms-selected-chip { min-width: auto; justify-content: flex-start; }
        }
    </style>

    @php
        $tab = request('tab', 'lines');
        $supplierName = trim((string) optional($orderNote->supplier)->name) ?: ('Supplier #' . $orderNote->supplier_id);
        $ordered = (int) ($orderNote->total_ordered ?? 0);
        $billed = (int) ($orderNote->total_billed ?? 0);
        $received = (int) ($orderNote->total_received ?? 0);
        $remainingToBill = max(0, $ordered - $billed);
        $progress = $billed > 0 ? (int) round(($received / max($billed, 1)) * 100) : 0;
        $progress = max(0, min(100, $progress));
        $progressClass = $progress >= 100 ? 'bg-success' : ($progress > 0 ? 'bg-warning' : 'bg-danger');
        $createdAt = optional($orderNote->created_at)->format('Y-m-d H:i');
        $updatedAt = optional($orderNote->updated_at)->format('Y-m-d H:i');
        $billedOrders = $orderNote->billedOrders ?? collect();
        $hasBilledOrders = $billedOrders->count() > 0;

        $billedRows = collect();
        foreach ($billedOrders as $bo) {
            foreach (($bo->lines ?? collect()) as $line) {
                $billedRows->push([
                    'billed_order_id' => $bo->id,
                    'billed_order_reference' => $bo->reference,
                    'invoice_reference' => optional($bo->invoice)->invoice_reference,
                    'product_id' => $line->product_id,
                    'product_attribute_id' => $line->product_attribute_id,
                    'qty_billed' => $line->qty_billed,
                    'qty_received' => $line->qty_received ?? 0,
                ]);
            }
        }

        $receptionRows = collect();
        foreach ($billedOrders as $bo) {
            foreach (($bo->receptions ?? collect()) as $reception) {
                foreach (($reception->lines ?? collect()) as $rline) {
                    $receptionRows->push([
                        'reception_id' => $reception->id,
                        'billed_order_reference' => $bo->reference,
                        'billed_order_line_id' => $rline->billed_order_line_id,
                        'qty_received' => $rline->qty_received,
                        'created_by' => $reception->created_by,
                        'created_at' => optional($reception->created_at)->format('Y-m-d H:i'),
                    ]);
                }
            }
        }

        $supplierMap = \Illuminate\Support\Facades\DB::table('supplier_map')
            ->where('id_supplier', $orderNote->supplier_id)
            ->first();

        $lineCollection = collect($orderNote->lines ?? []);
        $productIds = $lineCollection->pluck('product_id')->filter()->map(fn($v) => (int) $v)->unique()->values();
        $attributeIds = $lineCollection->pluck('product_attribute_id')->filter()->map(fn($v) => (int) $v)->unique()->values();

        $productMeta = collect();
        $attributeMeta = collect();
        $stockMeta = collect();
        $coverMeta = collect();

        if ($productIds->isNotEmpty()) {
            $productMeta = \Illuminate\Support\Facades\DB::connection('mysql2')
                ->table('ps_product as p')
                ->leftJoin('ps_product_lang as pl', 'pl.id_product', '=', 'p.id_product')
                ->whereIn('p.id_product', $productIds->all())
                ->groupBy('p.id_product', 'p.reference', 'p.ean13', 'p.location', 'p.supplier_reference')
                ->selectRaw('p.id_product, p.reference, p.ean13, p.location, p.supplier_reference, MIN(pl.name) as name')
                ->get()
                ->keyBy('id_product');

            $coverMeta = \Illuminate\Support\Facades\DB::connection('mysql2')
                ->table('ps_image')
                ->whereIn('id_product', $productIds->all())
                ->where('cover', 1)
                ->pluck('id_image', 'id_product');

            $stockMeta = \Illuminate\Support\Facades\DB::connection('mysql2')
                ->table('ps_stock_available')
                ->whereIn('id_product', $productIds->all())
                ->selectRaw('id_product, COALESCE(id_product_attribute, 0) as id_product_attribute, MIN(quantity) as quantity')
                ->groupBy('id_product', 'id_product_attribute')
                ->get()
                ->mapWithKeys(fn($row) => [((int) $row->id_product) . '|' . ((int) $row->id_product_attribute) => (int) $row->quantity]);
        }

        if ($attributeIds->isNotEmpty()) {
            $attributeMeta = \Illuminate\Support\Facades\DB::connection('mysql2')
                ->table('ps_product_attribute as pa')
                ->leftJoin('ps_custom_product_attribute as cpa', function ($join) {
                    $join->on('cpa.id_product_attribute', '=', 'pa.id_product_attribute')
                        ->on('cpa.id_product', '=', 'pa.id_product');
                })
                ->whereIn('pa.id_product_attribute', $attributeIds->all())
                ->select([
                    'pa.id_product_attribute',
                    'pa.id_product',
                    'pa.reference as attribute_reference',
                    'pa.ean13 as attribute_ean13',
                    'cpa.location as attribute_location',
                    'pa.supplier_reference as attribute_supplier_reference',
                ])
                ->get()
                ->keyBy('id_product_attribute');
        }
    @endphp
    
    <div style="border: 1px solid rgba(20, 33, 61, .08); background: #fff; box-shadow: 0 8px 24px rgba(15, 23, 42, .05); padding: 10px;margin-bottom: 10px;">
        <div class="oms-topbar" style="margin-bottom: 0;">
            <div class="oms-topbar-block">
                <a href="{{ route('erp.oms.dashboard', ['left_tab' => 'order_notes', 'document_type' => 'order_note', 'document_id' => $orderNote->id, 'supplier_id' => $orderNote->supplier_id]) }}" class="btn btn-sm btn-outline-primary oms-compact-btn">
                    <i class="fa-solid fa-table-columns me-1"></i> Dashboard
                </a>
                <a href="{{ route('erp.oms.invoices.create', $orderNote) }}" class="btn btn-sm btn-outline-warning oms-compact-btn">
                    <i class="fa-solid fa-file-invoice-dollar me-1"></i> Invoice
                </a>
            </div>
    
            <div class="oms-topbar-block flex-grow-1 justify-content-center">
                <div class="oms-selected-chip">
                    <i class="fa-solid fa-file-lines text-primary"></i>
                    <button type="button" class="btn btn-link p-0 border-0 fw-semibold js-edit-order-note-reference">{{ $orderNote->reference }} <i class="fa-solid fa-pen ms-1 small"></i></button>
                    <span class="text-muted">·</span>
                    <span>{{ $supplierName }}</span>
                </div>
            </div>
    
            <div class="oms-topbar-block justify-content-end">
                <a href="{{ route('erp.oms.order_notes.edit', $orderNote) }}" class="btn btn-sm btn-outline-warning oms-btn-icon oms-compact-btn"> <i class="fa-solid fa-plus"></i> Add products </a>
                @if($orderNote->status === 'order_note' && !$hasBilledOrders)
                    <button type="button" class="btn btn-sm btn-outline-primary oms-compact-btn" data-bs-toggle="modal" data-bs-target="#omsImportCsvModal">
                        <i class="fa-solid fa-file-arrow-up me-1"></i> Import CSV
                    </button>
                @endif

                <a href="{{ route('erp.oms.order_notes.export.csv', $orderNote) }}" class="btn btn-sm btn-outline-success oms-compact-btn">
                    <i class="fa-solid fa-file-csv me-1"></i> Export CSV
                </a>
                

                @if($orderNote->can_delete)
                    <form action="{{ route('erp.oms.order_notes.destroy', $orderNote) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete order note?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger oms-compact-btn">
                            <i class="fa-solid fa-trash me-1"></i> Delete
                        </button>
                    </form>
                @endif
                
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger py-2">{{ session('error') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning py-2">{{ session('warning') }}</div>
    @endif

    <div class="row g-3">
        <div class="col-xl-2">
            <div class="oms-card">
                <div class="oms-card-header">
                    <div class="oms-soft-label">Supplier</div>
                    <div class="fw-bold">Document Context</div>
                </div>
                <div class="oms-card-body">
                    <div class="oms-summary-list mb-3">
                        <div class="oms-summary-row">
                            <div class="oms-summary-key">Type</div>
                            <div class="oms-summary-value">Order Note</div>
                        </div>
                        <div class="oms-summary-row">
                            <div class="oms-summary-key">Reference</div>
                            <div class="oms-summary-value">{{ $orderNote->reference }}</div>
                        </div>
                        <div class="oms-summary-row">
                            <div class="oms-summary-key">Supplier</div>
                            <div class="oms-summary-value">{{ $supplierName }}</div>
                        </div>
                        <div class="oms-summary-row">
                            <div class="oms-summary-key">Status</div>
                            <div class="oms-summary-value text-capitalize">{{ str_replace('_', ' ', (string) $orderNote->status) }}</div>
                        </div>
                    </div>


                    @php
                        $orderNoteLogistics = app(\App\Services\oms\OrderNoteLogisticsService::class)->calculateFromOrderNote($orderNote);
                    @endphp

                    <div class="oms-summary-list mb-3">
                        <div class="oms-summary-row">
                            <div class="oms-summary-key">Volume</div>
                            <div class="oms-summary-value">{{ number_format((float) data_get($orderNoteLogistics, 'totals.volume_m3', 0), 3, ',', '.') }} m³</div>
                        </div>
                        <div class="oms-summary-row">
                            <div class="oms-summary-key">Weight</div>
                            <div class="oms-summary-value">{{ number_format((float) data_get($orderNoteLogistics, 'totals.weight_kg', 0), 2, ',', '.') }} kg</div>
                        </div>
                        <div class="oms-summary-row">
                            <div class="oms-summary-key">Missing logistics</div>
                            <div class="oms-summary-value">{{ (int) data_get($orderNoteLogistics, 'totals.missing_count', 0) }}</div>
                        </div>
                    </div>

                    @if(!empty(data_get($orderNoteLogistics, 'suggestions')))
                        <div class="oms-muted-panel small mb-3">
                            <div class="fw-semibold mb-1">Best fit</div>
                            @foreach(data_get($orderNoteLogistics, 'suggestions', []) as $container)
                                <div class="d-flex justify-content-between {{ !$loop->last ? 'mb-1' : '' }}">
                                    <span>{{ ucfirst((string) data_get($container, 'type', 'container')) }} · {{ data_get($container, 'name', '-') }}</span>
                                    <strong>{{ (int) data_get($container, 'units_needed', 0) }}x</strong>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    <div class="oms-list-search">
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="omsBilledOrderSearch" placeholder="Search billed order..." style="border-radius: 5px 0 0 5px !important">
                            <span class="input-group-text"  style="border-radius: 0 5px 5px 0 !important"><i class="fa-solid fa-magnifying-glass"></i></span>
                        </div>
                    </div>

                    <div id="omsBilledOrdersList">
                        @forelse($billedOrders as $index => $billedOrder)
                            <a href="{{ route('erp.oms.billed_orders.show', $billedOrder) }}" class="oms-link-row billed-order-item {{ $index === 0 ? 'active' : '' }}" data-search="{{ strtolower(trim(($billedOrder->reference ?? '') . ' ' . (optional($billedOrder->invoice)->invoice_reference ?? ''))) }}">
                                <div class="oms-link-row-main">
                                    <div class="oms-link-row-title">{{ $billedOrder->reference ?: 'Billed order #' . $billedOrder->id }}</div>
                                    <div class="oms-link-row-sub">
                                        {{ optional($billedOrder->invoice)->invoice_reference ?: 'No invoice linked' }}
                                    </div>
                                </div>
                                <span class="badge text-bg-light">{{ ($billedOrder->lines ?? collect())->count() }}</span>
                            </a>
                        @empty
                            <div class="oms-muted-panel small text-muted">
                                No billed orders linked yet. This order note is still in the pre-billing phase.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="oms-card">
                <div class="oms-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <div class="oms-soft-label">Operational Detail</div>
                        <div class="fw-bold">Order Note Flow</div>
                    </div>
                    <div class="d-flex flex-wrap gap-2" style="width: min(520px, 100%);">
                        <a href="{{ route('erp.oms.order_notes.show', ['orderNote' => $orderNote->id, 'tab' => 'lines']) }}" class="oms-tab-btn {{ $tab === 'lines' ? 'active' : '' }}">
                            <i class="fa-solid fa-list"></i> Order Notes
                        </a>
                        <a href="{{ route('erp.oms.order_notes.show', ['orderNote' => $orderNote->id, 'tab' => 'billed']) }}" class="oms-tab-btn {{ $tab === 'billed' ? 'active' : '' }}">
                            <i class="fa-solid fa-file-invoice"></i> Invoiced
                        </a>
                        <a href="{{ route('erp.oms.order_notes.show', ['orderNote' => $orderNote->id, 'tab' => 'receptions']) }}" class="oms-tab-btn {{ $tab === 'receptions' ? 'active' : '' }}">
                            <i class="fa-solid fa-box-open"></i> Received
                        </a>
                    </div>
                </div>
                <div class="oms-card-body">
                    <div class="input-group mb-3">
                        <input type="text" id="omsDetailSearch" class="form-control" placeholder="Search current list..." style="border-radius: 5px 0 0 5px !important">
                        <span class="input-group-text" style="border-radius: 0 5px 5px 0 !important"><i class="fa-solid fa-magnifying-glass"></i></span>
                    </div>

                    @if($tab === 'lines')
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width:52px;"></th>
                                        <th>Reference</th>
                                        <th>Name</th>
                                        <th class="text-center">Ordered</th>
                                        <th class="text-center">Invoiced</th>
                                        <th class="text-center">Received</th>
                                        <th class="text-center">Remaining</th>
                                        <th class="text-center"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orderNote->lines as $index => $line)
                                        @php
                                            $lineOrdered = (int) $line->qty_ordered;
                                            $lineBilled = (int) ($line->qty_billed_total ?? 0);
                                            $lineReceived = (int) ($line->qty_received_total ?? 0);
                                            $lineRemaining = max(0, $lineOrdered - $lineBilled);
                                            $billStatus = $lineBilled >= $lineOrdered && $lineOrdered > 0 ? 'complete' : ($lineBilled > 0 ? 'partial' : 'zero');
                                            $recvStatus = $lineReceived >= $lineBilled && $lineBilled > 0 ? 'complete' : ($lineReceived > 0 ? 'partial' : 'zero');

                                            $lineProductId = (int) ($line->product_id ?? 0);
                                            $lineAttributeId = (int) ($line->product_attribute_id ?? 0);
                                            $lineLineId = $line->id ?? ($line->id_order_note_line ?? '-');

                                            $productRow = $productMeta->get($lineProductId);
                                            $attributeRow = $lineAttributeId > 0 ? $attributeMeta->get($lineAttributeId) : null;

                                            $productReference = trim((string) data_get($productRow, 'reference', ''));
                                            $attributeReference = trim((string) data_get($attributeRow, 'attribute_reference', ''));
                                            $lineRef = $attributeReference !== '' ? $attributeReference : ($productReference !== '' ? $productReference : '-');

                                            $lineName = trim((string) ($line->product_name ?? ''));
                                            if ($lineName === '') {
                                                $lineName = trim((string) data_get($productRow, 'name', ''));
                                            }
                                            $lineName = $lineName !== '' ? $lineName : 'Product line';

                                            $lineEan13 = trim((string) data_get($attributeRow, 'attribute_ean13', '')) ?: trim((string) data_get($productRow, 'ean13', ''));
                                            $lineLocation = trim((string) data_get($attributeRow, 'attribute_location', '')) ?: trim((string) data_get($productRow, 'location', ''));
                                            $lineLocation = $lineLocation !== '' ? $lineLocation : '—';

                                            $stockKey = $lineProductId . '|' . max(0, $lineAttributeId);
                                            $fallbackStockKey = $lineProductId . '|0';
                                            $lineCurrentStock = (int) ($stockMeta[$stockKey] ?? ($stockMeta[$fallbackStockKey] ?? 0));

                                            $coverId = $coverMeta[$lineProductId] ?? null;
                                            $coverUrl = null;
                                            if ($coverId) {
                                                $coverPath = implode('/', str_split((string) $coverId));
                                                $coverUrl = rtrim((string) config('allstars.stores.ASD.base_url'), '/') . '/img/p/' . $coverPath . '/' . $coverId . '.jpg';
                                            }

                                            $detailRowId = 'omsLineDetailRow_' . $index;
                                            $lineSearch = strtolower(implode(' ', [
                                                $lineRef,
                                                $lineName,
                                                $lineProductId,
                                                $lineAttributeId,
                                                $lineEan13,
                                                $lineLocation,
                                                $lineOrdered,
                                                $lineBilled,
                                                $lineReceived,
                                                $lineRemaining,
                                                $lineCurrentStock,
                                            ]));
                                        @endphp
                                        <tr class="oms-clickable-row oms-main-row"
                                            data-search="{{ $lineSearch }}"
                                            data-detail-target="#{{ $detailRowId }}">
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary oms-toggle-btn js-toggle-line-detail" data-target="#{{ $detailRowId }}" aria-expanded="false" title="Show details">
                                                    <i class="fa-solid fa-angle-down"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <span class="oms-ref-copy js-copy-on-click" data-copy-text="{{ e($lineRef) }}" title="Copy reference">
                                                    <span>{{ $lineRef }}</span>
                                                    <i class="fa-regular fa-copy small"></i>
                                                </span>
                                            </td>
                                            <td>{{ $lineName }}</td>
                                            <td class="text-center fw-semibold"><button type="button" class="btn btn-link p-0 border-0 fw-semibold js-edit-order-note-line" data-line-id="{{ $line->id }}" data-quantity="{{ $lineOrdered }}" data-minimum="{{ max($lineBilled, $lineReceived) }}">{{ $lineOrdered }} <i class="fa-solid fa-pen ms-1 small"></i></button></td>
                                            <td class="text-center">
                                                {{ $lineBilled }}
                                                <span class="oms-status-square {{ $billStatus }}">
                                                    <i class="fa-solid {{ $billStatus === 'complete' ? 'fa-check' : ($billStatus === 'partial' ? 'fa-minus' : 'fa-xmark') }}"></i>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                {{ $lineReceived }}
                                                <span class="oms-status-square {{ $recvStatus }}">
                                                    <i class="fa-solid {{ $recvStatus === 'complete' ? 'fa-check' : ($recvStatus === 'partial' ? 'fa-minus' : 'fa-xmark') }}"></i>
                                                </span>
                                            </td>
                                            <td class="text-center">{{ $lineRemaining }}</td>
                                            <td>
                                                @php
                                                    $ordered = (int) $line->qty_ordered;
                                                @endphp
                                                
                                                @if($ordered > 0)
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary oms-btn-icon"
                                                        title="Print EAN"
                                                        onclick="generateBarcode({{ $ordered }}, 0, {{ $line->product_id }}, {{ $line->product_attribute_id ?? 0 }})">
                                                        <i class="fa-solid fa-barcode"></i>
                                                    </button>
                                                @endif
                                                @if($lineBilled === 0 && $lineReceived === 0)
                                                    <button type="button" class="btn btn-sm btn-outline-danger oms-btn-icon js-remove-order-note-line" data-line-id="{{ $line->id }}" title="Remove line"><i class="fa-solid fa-trash"></i></button>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr id="{{ $detailRowId }}" class="oms-detail-row" hidden data-parent-row="{{ $detailRowId }}" data-search="{{ $lineSearch }}">
                                            <td colspan="7">
                                                <div class="oms-detail-wrap">
                                                    <div class="oms-detail-layout">
                                                        <div class="oms-tech-image">
                                                            @if($coverUrl)
                                                                <img src="{{ $coverUrl }}" alt="{{ $lineName }}">
                                                            @else
                                                                <div class="placeholder"><i class="fa-solid fa-image"></i></div>
                                                            @endif
                                                        </div>
                                                        <div class="oms-detail-grid">
                                                            <div class="oms-detail-box">
                                                                <div class="detail-label">ID produto | ID product_attribute</div>
                                                                <div class="detail-value">
                                                                    <span class="oms-copyable js-copy-on-click" data-copy-text="{{ $lineProductId }}">{{ $lineProductId }}</span>
                                                                    <span class="text-muted mx-1">|</span>
                                                                    <span class="oms-copyable js-copy-on-click" data-copy-text="{{ $lineAttributeId > 0 ? $lineAttributeId : 0 }}">{{ $lineAttributeId > 0 ? $lineAttributeId : 0 }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="oms-detail-box">
                                                                <div class="detail-label">Nome do produto</div>
                                                                <div class="detail-value oms-copyable js-copy-on-click" data-copy-text="{{ e($lineName) }}">{{ $lineName }}</div>
                                                            </div>
                                                            <div class="oms-detail-box">
                                                                <div class="detail-label">Housing / Location</div>
                                                                <div class="detail-value oms-copyable js-copy-on-click" data-copy-text="{{ e($lineLocation) }}">{{ $lineLocation }}</div>
                                                            </div>
                                                            <div class="oms-detail-box">
                                                                <div class="detail-label">EAN13</div>
                                                                <div class="detail-value oms-copyable js-copy-on-click" data-copy-text="{{ e($lineEan13 ?: '—') }}">{{ $lineEan13 ?: '—' }}</div>
                                                            </div>
                                                            <div class="oms-detail-box">
                                                                <div class="detail-label">Current stock</div>
                                                                <div class="detail-value oms-copyable js-copy-on-click" data-copy-text="{{ $lineCurrentStock }}">{{ $lineCurrentStock }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="oms-empty">No product lines found for this order note.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @elseif($tab === 'billed')
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Billed Order</th>
                                        <th>Invoice</th>
                                        <th>Product ID</th>
                                        <th>Attribute</th>
                                        <th class="text-center">Qty Billed</th>
                                        <th class="text-center">Qty Received</th>
                                        <th class="actions">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($billedRows as $row)
                                        <tr data-search="{{ strtolower(implode(' ', $row)) }}">
                                            <td>{{ $row['billed_order_reference'] }}</td>
                                            <td>{{ $row['invoice_reference'] ?: '-' }}</td>
                                            <td>{{ $row['product_id'] }}</td>
                                            <td>{{ $row['product_attribute_id'] ?: '-' }}</td>
                                            <td class="text-center">{{ $row['qty_billed'] }}</td>
                                            <td class="text-center">{{ $row['qty_received'] }}</td>
                                            <td class="actions" onclick="event.stopPropagation();">
                                                <a href="{{ route('erp.oms.billed_orders.show', $row['billed_order_id']) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fa-solid fa-up-right-from-square"></i>
                                                </a>

                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="oms-empty">No billed lines linked to this order note.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Reception ID</th>
                                        <th>Billed Order</th>
                                        <th>Billed Line</th>
                                        <th class="text-center">Qty Received</th>
                                        <th>User</th>
                                        <th>Created</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($receptionRows as $row)
                                        <tr data-search="{{ strtolower(implode(' ', $row)) }}">
                                            <td>{{ $row['reception_id'] }}</td>
                                            <td>{{ $row['billed_order_reference'] }}</td>
                                            <td>{{ $row['billed_order_line_id'] }}</td>
                                            <td class="text-center">{{ $row['qty_received'] }}</td>
                                            <td>{{ $row['created_by'] }}</td>
                                            <td>{{ $row['created_at'] }}</td>
                                            <td>                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="oms-empty">No receptions found for this order note.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-2">
            <div class="oms-card">
                <div class="oms-card-header">
                    <div class="oms-soft-label">Summary</div>
                    <div class="fw-bold">Operational Summary</div>
                </div>
                <div class="oms-card-body">
                    <div class="oms-summary-list">
                        <div class="oms-summary-row">
                            <div class="oms-summary-key">Created</div>
                            <div class="oms-summary-value">{{ $createdAt ?: '-' }}</div>
                        </div>
                        <div class="oms-summary-row">
                            <div class="oms-summary-key">Updated</div>
                            <div class="oms-summary-value">{{ $updatedAt ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="small text-muted fw-semibold">Reception vs Invoiced</div>
                            <div class="fw-semibold">{{ $progress }}%</div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar {{ $progressClass }}" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>

                    <div class="oms-summary-list mb-3">
                        <div class="oms-summary-row">
                            <div class="oms-summary-key">Remaining</div>
                            <div class="oms-summary-value">{{ $remainingToBill }}</div>
                        </div>
                        <div class="oms-summary-row">
                            <div class="oms-summary-key">Billed orders</div>
                            <div class="oms-summary-value">{{ $billedOrders->count() }}</div>
                        </div>
                    </div>

                    <div class="oms-stat-grid">
                        <div class="oms-stat-box">
                            <div class="label">Ordered</div>
                            <div class="value">{{ $ordered }}</div>
                        </div>
                        <div class="oms-stat-box">
                            <div class="label">Invoiced</div>
                            <div class="value">{{ $billed }}</div>
                        </div>
                        <div class="oms-stat-box">
                            <div class="label">Received</div>
                            <div class="value">{{ $received }}</div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary {{ !empty($orderNote->internal_note) ? 'oms-note-btn-filled' : 'oms-note-btn-empty' }}" data-bs-toggle="modal" data-bs-target="#omsInternalNoteModal">
                            <i class="fa-solid fa-note-sticky me-1"></i> Internal
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary {{ !empty($orderNote->logistic_note) ? 'oms-note-btn-filled' : 'oms-note-btn-empty' }}" data-bs-toggle="modal" data-bs-target="#omsLogisticNoteModal">
                            <i class="fa-solid fa-truck-fast me-1"></i> Logistic
                        </button>
                    </div>

                    <div class="accordion mb-3" id="omsOrderNoteExtraAccordion">
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingExtraInfo">
                                <button class="accordion-button collapsed shadow-none px-0 py-2 bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExtraInfo" aria-expanded="false" aria-controls="collapseExtraInfo" style="padding: 5px !important; border: 1px solid #ccc; border-radius: 2px !important;">
                                    Supplier map
                                </button>
                            </h2>
                            <div id="collapseExtraInfo" class="accordion-collapse collapse" aria-labelledby="headingExtraInfo" data-bs-parent="#omsOrderNoteExtraAccordion">
                                <div class="oms-collapse-card small">
                                    @if($supplierMap)
                                        <div class="oms-summary-list mb-0">
                                            @foreach([
                                                'contact' => 'Contact',
                                                'email' => 'Email',
                                                'phone' => 'Phone',
                                                'currency' => 'Currency',
                                                'incoterm' => 'Incoterm',
                                                'country' => 'Country',
                                                'website' => 'Website',
                                                'dealer_website' => 'Dealer website',
                                                'address' => 'Address',
                                                'terms' => 'Terms',
                                                'discount' => 'Discount',
                                            ] as $field => $label)
                                                @if(!empty(data_get($supplierMap, $field)))
                                                    <div class="oms-summary-row">
                                                        <div class="oms-summary-key">{{ $label }}</div>
                                                        <div class="oms-summary-value oms-copyable js-copy-on-click" data-copy-text="{{ e((string) data_get($supplierMap, $field)) }}">{{ data_get($supplierMap, $field) }}</div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-muted">No supplier_map information found for this supplier.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="omsInternalNoteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Internal note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" rows="9" id="omsInternalNoteInput">{{ $orderNote->internal_note }}</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-outline-primary" id="omsSaveInternalNoteBtn">Save</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="omsLogisticNoteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Logistic note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" rows="9" id="omsLogisticNoteInput">{{ $orderNote->logistic_note }}</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-outline-primary" id="omsSaveLogisticNoteBtn">Save</button>
                </div>
            </div>
        </div>
    </div>

    @if($orderNote->status === 'order_note' && !$hasBilledOrders)
    <div class="modal fade" id="omsImportCsvModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="{{ route('erp.oms.order_notes.import.preview', $orderNote) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Import products from CSV</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="oms-muted-panel mb-3">
                            <div class="fw-semibold mb-2">Accepted columns</div>
                            <div class="small text-muted">
                                The CSV must contain only a <strong>reference</strong> column and a <strong>quantity</strong> column.
                                The reference may belong either to a <strong>product attribute</strong> or to the <strong>main product</strong>.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">CSV file</label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv,.txt,text/csv">
                        </div>

                        <div class="oms-muted-panel small">
                            <div><strong>Examples of valid headers</strong></div>
                            <div class="mt-2">
                                <code>reference;qty_ordered</code><br>
                                <code>reference;qty</code><br>
                                <code>sku,quantity</code>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-outline-success">
                            <i class="fa-solid fa-check-to-slot me-1"></i> Verify import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <div class="modal fade" id="omsInternalNoteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Internal note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" rows="9" id="omsInternalNoteInput">{{ $orderNote->internal_note }}</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-outline-primary" id="omsSaveInternalNoteBtn">Save</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="omsLogisticNoteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Logistic note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" rows="9" id="omsLogisticNoteInput">{{ $orderNote->logistic_note }}</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-outline-primary" id="omsSaveLogisticNoteBtn">Save</button>
                </div>
            </div>
        </div>
    </div>


    <script>
        (function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            async function omsRequest(url, method, payload) {
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

            document.querySelectorAll('.js-edit-order-note-reference').forEach(button => {
                button.addEventListener('click', async function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    const currentValue = this.textContent.trim();
                    const result = await Swal.fire({
                        title: 'Edit order note name', input: 'text', inputValue: currentValue,
                        inputAttributes: { maxlength: 191 }, showCancelButton: true,
                        confirmButtonText: 'Save', cancelButtonText: 'Cancel',
                        inputValidator: value => !String(value || '').trim() ? 'The name is required.' : undefined,
                    });
                    if (!result.isConfirmed) return;
                    try {
                        await omsRequest(window.location.pathname, 'PUT', { reference: String(result.value).trim() });
                        window.location.reload();
                    } catch (error) { Swal.fire('Error', error.message, 'error'); }
                });
            });

            document.querySelectorAll('.js-edit-order-note-line').forEach(button => {
                button.addEventListener('click', async function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    const minimum = Number(this.dataset.minimum || 0);
                    const result = await Swal.fire({
                        title: 'Edit ordered quantity', input: 'number', inputValue: this.dataset.quantity || 0,
                        inputAttributes: { min: minimum, step: 1 }, showCancelButton: true,
                        confirmButtonText: 'Save', cancelButtonText: 'Cancel',
                        inputValidator: value => {
                            const quantity = Number(value);
                            if (!Number.isInteger(quantity) || quantity < minimum) return 'Quantity must be an integer equal to or greater than ' + minimum + '.';
                        },
                    });
                    if (!result.isConfirmed) return;
                    try {
                        await omsRequest(window.location.pathname + '/lines/' + this.dataset.lineId, 'PATCH', { qty_ordered: Number(result.value) });
                        window.location.reload();
                    } catch (error) { Swal.fire('Error', error.message, 'error'); }
                });
            });

            document.querySelectorAll('.js-remove-order-note-line').forEach(button => {
                button.addEventListener('click', async function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    const result = await Swal.fire({
                        title: 'Remove order note line?', icon: 'warning', showCancelButton: true,
                        confirmButtonText: 'Remove', cancelButtonText: 'Cancel', confirmButtonColor: '#dc3545',
                    });
                    if (!result.isConfirmed) return;
                    try {
                        await omsRequest(window.location.pathname + '/lines/' + this.dataset.lineId, 'DELETE');
                        window.location.reload();
                    } catch (error) { Swal.fire('Error', error.message, 'error'); }
                });
            });
            const detailSearch = document.getElementById('omsDetailSearch');
            if (detailSearch) {
                detailSearch.addEventListener('input', function() {
                    const q = (this.value || '').toLowerCase().trim();

                    document.querySelectorAll('.oms-main-row').forEach(function(row) {
                        const hay = (row.dataset.search || '').toLowerCase();
                        const visible = !q || hay.includes(q);
                        row.style.display = visible ? '' : 'none';

                        const target = row.dataset.detailTarget;
                        if (target) {
                            const detailRow = document.querySelector(target);
                            if (detailRow && !visible) {
                                detailRow.style.display = 'none';
                                detailRow.hidden = true;
                            }
                        }
                    });

                    document.querySelectorAll('tbody tr[data-search]:not(.oms-main-row):not(.oms-detail-row)').forEach(function(row) {
                        const hay = (row.dataset.search || '').toLowerCase();
                        row.style.display = !q || hay.includes(q) ? '' : 'none';
                    });
                });
            }

            const billedOrderSearch = document.getElementById('omsBilledOrderSearch');
            if (billedOrderSearch) {
                billedOrderSearch.addEventListener('input', function() {
                    const q = (this.value || '').toLowerCase().trim();
                    document.querySelectorAll('#omsBilledOrdersList .billed-order-item').forEach(function(item) {
                        const hay = (item.dataset.search || '').toLowerCase();
                        item.style.display = !q || hay.includes(q) ? '' : 'none';
                    });
                });
            }

            function bindCopyOnClick() {
                document.querySelectorAll('.js-copy-on-click').forEach(function(el) {
                    el.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const text = this.dataset.copyText || this.textContent || '';
                        if (!text) return;
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(text);
                        } else {
                            const input = document.createElement('textarea');
                            input.value = text;
                            document.body.appendChild(input);
                            input.select();
                            document.execCommand('copy');
                            input.remove();
                        }
                    });
                });
            }

            function bindLineSelection() {
                const rows = document.querySelectorAll('.oms-clickable-row');
                if (!rows.length) return;

                function closeOtherDetails(exceptId) {
                    document.querySelectorAll('.oms-detail-row').forEach(function(detailRow) {
                        if (!exceptId || ('#' + detailRow.id) !== exceptId) {
                            detailRow.hidden = true;
                            detailRow.style.display = 'none';
                        }
                    });
                    document.querySelectorAll('.js-toggle-line-detail').forEach(function(btn) {
                        if (!exceptId || btn.dataset.target !== exceptId) {
                            btn.setAttribute('aria-expanded', 'false');
                        }
                    });
                }

                function setSelected(row) {
                    rows.forEach(r => r.classList.remove('oms-row-selected'));
                    row.classList.add('oms-row-selected');
                }

                function toggleDetail(row, forceOpen = null) {
                    const target = row.dataset.detailTarget;
                    if (!target) return;
                    const detailRow = document.querySelector(target);
                    const toggleBtn = row.querySelector('.js-toggle-line-detail');
                    if (!detailRow) return;

                    const currentlyOpen = !detailRow.hidden;
                    const willOpen = forceOpen === null ? !currentlyOpen : !!forceOpen;

                    closeOtherDetails(willOpen ? target : null);
                    detailRow.hidden = !willOpen;
                    detailRow.style.display = willOpen ? '' : 'none';

                    if (toggleBtn) {
                        toggleBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                    }
                }

                rows.forEach(function(row) {
                    row.addEventListener('click', function(e) {
                        if (e.target.closest('.js-copy-on-click')) return;
                        setSelected(row);
                        toggleDetail(row);
                    });

                    const toggleBtn = row.querySelector('.js-toggle-line-detail');
                    if (toggleBtn) {
                        toggleBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            setSelected(row);
                            const isOpen = this.getAttribute('aria-expanded') === 'true';
                            toggleDetail(row, !isOpen);
                        });
                    }
                });

                closeOtherDetails(null);
            }

            bindCopyOnClick();
            bindLineSelection();

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            const notesUrl = @json(route('erp.oms.order_notes.notes.save', $orderNote));

            function saveNotes(payload, onDone) {
                fetch(notesUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(function(data) {
                    if (!data || !data.success) {
                        alert('Could not save notes.');
                        return;
                    }
                    window.location.reload();
                })
                .catch(function() {
                    alert('Could not save notes.');
                });
            }

            const saveInternalBtn = document.getElementById('omsSaveInternalNoteBtn');
            if (saveInternalBtn) {
                saveInternalBtn.addEventListener('click', function() {
                    saveNotes({
                        internal_note: document.getElementById('omsInternalNoteInput')?.value || '',
                        logistic_note: document.getElementById('omsLogisticNoteInput')?.value || ''
                    });
                });
            }

            const saveLogisticBtn = document.getElementById('omsSaveLogisticNoteBtn');
            if (saveLogisticBtn) {
                saveLogisticBtn.addEventListener('click', function() {
                    saveNotes({
                        internal_note: document.getElementById('omsInternalNoteInput')?.value || '',
                        logistic_note: document.getElementById('omsLogisticNoteInput')?.value || ''
                    });
                });
            }
        })();
    </script>
</div>
@endsection
