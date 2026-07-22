@extends('layouts.app')

@section('content')


@php
    $rowsCollection = collect($rows ?? []);
    $totalEntries = $rowsCollection->count();
    $totalQty = (int) $rowsCollection->sum('qty_received');
    $lastEntry = optional($rowsCollection->sortByDesc('created_at')->first())->created_at;
    $supplierName = optional(optional($billedOrder->orderNote)->supplier)->name ?: ('Supplier #' . ($billedOrder->supplier_id ?? optional($billedOrder->orderNote)->supplier_id ?? '-'));

    $userIds = $rowsCollection->pluck('created_by')->filter()->unique()->values();
    $userNames = class_exists(\App\Models\User::class)
        ? \App\Models\User::query()->whereIn('id', $userIds)->pluck('name', 'id')
        : collect();

    $lineIds = $rowsCollection->pluck('billed_order_line_id')->filter()->unique()->values();
    $lineMap = \App\Models\modules\oms\BilledOrderLine::query()
        ->whereIn('id', $lineIds)
        ->get(['id', 'product_id', 'product_attribute_id'])
        ->keyBy('id');

    $productIds = $lineMap->pluck('product_id')->filter()->unique()->values();
    $attributeIds = $lineMap->pluck('product_attribute_id')->filter()->unique()->values();

    $productMap = collect();
    if ($productIds->isNotEmpty()) {
        $productMap = \Illuminate\Support\Facades\DB::connection('mysql2')
            ->table('ps_product as p')
            ->leftJoin('ps_product_lang as pl', function ($join) {
                $join->on('pl.id_product', '=', 'p.id_product')
                     ->where('pl.id_lang', '=', 1);
            })
            ->whereIn('p.id_product', $productIds->all())
            ->select([
                'p.id_product',
                'p.reference as product_reference',
                'pl.name as product_name',
            ])
            ->get()
            ->keyBy('id_product');
    }

    $attributeMap = collect();
    if ($attributeIds->isNotEmpty()) {
        $attributeMap = \Illuminate\Support\Facades\DB::connection('mysql2')
            ->table('ps_product_attribute as pa')
            ->whereIn('pa.id_product_attribute', $attributeIds->all())
            ->select([
                'pa.id_product_attribute',
                'pa.id_product',
                'pa.reference as attribute_reference',
            ])
            ->get()
            ->keyBy('id_product_attribute');
    }
@endphp


<div class="container-fluid py-3 oms-premium">
    <div class="oms-card mb-3">
        <div class="oms-card-body py-2 px-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <a href="{{ route('erp.oms.dashboard', ['left_tab' => 'billed_orders', 'document_type' => 'billed_order', 'document_id' => $billedOrder->id]) }}" class="btn btn-outline-primary oms-btn-icon">
                        <i class="fa-solid fa-table-columns"></i> Dashboard
                    </a>
                    <a href="{{ route('erp.oms.receptions.index', ['billed_order_id' => $billedOrder->id]) }}" class="btn btn-outline-warning oms-btn-icon">
                        <i class="fa-solid fa-truck-ramp-box"></i> Reception
                    </a>
                </div>

                <div class="text-center flex-grow-1 px-2" style="min-width:280px;">
                    <div class="oms-selected-chip justify-content-center">
                        <i class="fa-solid fa-file-invoice"></i>
                        <span>{{ $supplierName }}</span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <a href="{{ route('erp.oms.billed_orders.export.xlsx', $billedOrder) }}" class="btn btn-outline-success oms-btn-icon">
                        <i class="fa-solid fa-file-excel"></i> Export XLSX
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-2">
            <div class="oms-section-stack">
                <div class="oms-card">
                    <div class="oms-card-header">
                        <div class="oms-soft-label">Billed Order</div>
                        <div class="fw-bold">Document Context</div>
                    </div>
                    <div class="oms-card-body">
                        <div class="oms-summary-list">
                            <div class="oms-summary-row"><div class="oms-summary-key">Invoice</div><div class="oms-summary-value">{{ optional($billedOrder->invoice)->invoice_reference ?? '-' }}</div></div>
                            <div class="oms-summary-row"><div class="oms-summary-key">Order Note</div><div class="oms-summary-value">{{ optional($billedOrder->orderNote)->display_reference ?? '-' }}</div></div>
                            <div class="oms-summary-row"><div class="oms-summary-key">Status</div><div class="oms-summary-value">{{ $billedOrder->status ?? '-' }}</div></div>
                            <div class="oms-summary-row"><div class="oms-summary-key">Created</div><div class="oms-summary-value">{{ optional($billedOrder->created_at)->format('Y-m-d H:i') }}</div></div>
                            <div class="oms-summary-row"><div class="oms-summary-key">Last entry</div><div class="oms-summary-value">{{ $lastEntry ?: '-' }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="oms-card">
                    <div class="oms-card-header">
                        <div class="oms-soft-label">Summary</div>
                        <div class="fw-bold">Reception Metrics</div>
                    </div>
                    <div class="oms-card-body">
                        <div class="oms-stat-grid">
                            <div class="oms-stat-box"><div class="label">Entries</div><div class="value">{{ $totalEntries }}</div></div>
                            <div class="oms-stat-box"><div class="label">Qty</div><div class="value">{{ $totalQty }}</div></div>
                            <div class="oms-stat-box"><div class="label">Lines</div><div class="value">{{ $rowsCollection->pluck('billed_order_line_id')->unique()->count() }}</div></div>
                            <div class="oms-stat-box"><div class="label">Users</div><div class="value">{{ $rowsCollection->map(fn($row) => $userNames[$row->created_by] ?? $row->created_by)->filter()->unique()->count() }}</div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-10">
            <div class="oms-card">
                <div class="oms-card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                    <div>
                        <div class="oms-soft-label">History</div>
                        <div class="fw-bold">Reception Register</div>
                    </div>
                    <span class="badge text-bg-light border">{{ $totalEntries }} entries</span>
                </div>
                <div class="oms-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 oms-main-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Reception</th>
                                    <th>Invoice</th>
                                    <th>Line</th>
                                    <th>Product</th>
                                    <th class="text-center">Qty Received</th>
                                    <th>User</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    @php
                                        $lineData = $lineMap->get($row->billed_order_line_id);
                                    
                                        $productId = $lineData->product_id ?? $row->product_id ?? null;
                                        $attributeId = (int) ($lineData->product_attribute_id ?? $row->product_attribute_id ?? 0);
                                    
                                        $product = $productMap->get($productId);
                                        $attribute = $attributeId > 0 ? $attributeMap->get($attributeId) : null;
                                    
                                        $productLabel = $productId ?: '-';
                                        if ($attributeId > 0) {
                                            $productLabel .= ' | ' . $attributeId;
                                        }
                                    
                                        $productReference = $attribute->attribute_reference
                                            ?? $product->product_reference
                                            ?? null;
                                    
                                        $productName = $product->product_name ?? null;
                                    
                                        $userLabel = $userNames[$row->created_by] ?? $row->created_by ?? '-';
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">#{{ $row->reception_id }}</td>
                                        <td>{{ $row->invoice_reference }}</td>
                                        <td> <span class="oms-copyable" data-copy="{{ $row->billed_order_line_id }}">{{ $row->billed_order_line_id }}</span> </td>
                                        <td> <span class="small text-muted">{{ $productLabel }} </span> @if(!empty($productReference)) <span class="oms-copyable fw-semibold" data-copy="{{ $productReference }}"> | {{ $productReference }}</span> @endif</div> </td>
                                        <td class="text-center">
                                            <div class="oms-metric-cell">
                                                <span class="oms-metric-value fw-semibold">{{ $row->qty_received }}</span>
                                                <span class="oms-state-square complete"><i class="fa-solid fa-check"></i></span>
                                            </div>
                                        </td>
                                        <td>{{ $userLabel }}</td>
                                        <td>{{ $row->created_at }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="oms-empty text-center py-4">No reception history found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
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
.oms-metric-cell{display:inline-flex;align-items:center;justify-content:center;gap:8px;white-space:nowrap;}
.oms-metric-value{min-width:18px;display:inline-block;text-align:center;}
.oms-state-square{display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:4px;font-size:.65rem;border:1px solid transparent;}
.oms-state-square.complete{background:rgba(34,197,94,.14);border-color:rgba(34,197,94,.22);color:#15803d;}
.oms-empty{color:#6b7280;}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
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
});
</script>
@endsection
