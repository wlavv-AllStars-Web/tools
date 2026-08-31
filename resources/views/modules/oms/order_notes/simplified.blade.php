{{--
    Private OMS UI prototype.  This view deliberately has no route, menu item or controller entry point.

    When it is wired for a review, provide:
      $orderNote          App\Models\modules\oms\OrderNote|null
      $simplifiedOmsRows  array of normalized rows (see the fallback structure below)

    The action buttons are intentionally data hooks only.  They must be connected to transactional
    application actions, not to direct database updates: receiving changes PrestaShop stock and a
    reversal can also need to undo a reception and its stock movement.
--}}
@extends('layouts.app')

@section('content')
@php
    $prototypeOrderNote = $orderNote ?? null;
    $prototypeRows = $simplifiedOmsRows ?? [];

    // Keeps the blade easy to review in isolation, before a dedicated presenter/query is introduced.
    if (empty($prototypeRows)) {
        $prototypeRows = [[
            'line_id' => null,
            'product_id' => null,
            'product_attribute_id' => null,
            'reference' => 'EXAMPLE-REFERENCE',
            'name' => 'Example product — prototype row',
            'ordered' => 12,
            'invoiced' => 8,
            'received' => 5,
            'purchase_supplier' => 20.00,
            'purchase_eur' => 18.35,
            'sales_supplier' => 34.50,
            'sales_eur' => 31.65,
            'currency_iso' => 'USD',
        ]];
    }

    $reference = $prototypeOrderNote?->reference ?? 'New order note';
    $supplierName = data_get($prototypeOrderNote, 'supplier.name', 'Select supplier');
    $isPrototypeData = empty($simplifiedOmsRows ?? []);
@endphp

<div class="container-fluid py-3 oms-simple" data-order-note-id="{{ $prototypeOrderNote?->id }}">
    <style>
        .oms-simple { --oms-blue:#175cd3; --oms-line:#e5e7eb; --oms-muted:#667085; color:#1d2939; }
        .oms-simple .simple-card { background:#fff; border:1px solid var(--oms-line); border-radius:8px; box-shadow:0 2px 8px rgba(16,24,40,.04); }
        .oms-simple .simple-topbar { display:flex; align-items:end; gap:12px; padding:16px; flex-wrap:wrap; }
        .oms-simple .simple-title { flex:1 1 300px; }
        .oms-simple .simple-label { display:block; margin:0 0 4px; color:var(--oms-muted); font-size:.72rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; }
        .oms-simple .simple-reference { max-width:290px; font-size:1.1rem; font-weight:700; }
        .oms-simple .simple-supplier { color:var(--oms-muted); font-size:.9rem; }
        .oms-simple .simple-table-wrap { overflow-x:auto; }
        .oms-simple .simple-table { min-width:1260px; margin:0; }
        .oms-simple .simple-table th { background:#f9fafb; border-bottom:1px solid var(--oms-line); color:#475467; font-size:.7rem; letter-spacing:.05em; text-transform:uppercase; white-space:nowrap; }
        .oms-simple .simple-table td, .oms-simple .simple-table th { padding:.7rem .6rem; vertical-align:middle; }
        .oms-simple .simple-table tbody tr:hover { background:#fcfcfd; }
        .oms-simple .simple-product { min-width:270px; }
        .oms-simple .simple-ref { display:block; font-weight:750; color:#101828; }
        .oms-simple .simple-name { display:block; color:var(--oms-muted); font-size:.78rem; line-height:1.25; margin-top:2px; }
        .oms-simple .simple-qty { width:74px; min-width:74px; text-align:center; font-weight:700; }
        .oms-simple .simple-qty[readonly] { background:#fff; }
        .oms-simple .simple-qty-group { display:flex; align-items:center; gap:3px; }
        .oms-simple .simple-qty-group .btn { width:26px; height:30px; padding:0; line-height:1; }
        .oms-simple .simple-money { min-width:100px; }
        .oms-simple .simple-money input { min-width:92px; text-align:right; }
        .oms-simple .simple-money small { display:block; margin-top:3px; color:var(--oms-muted); font-size:.67rem; white-space:nowrap; }
        .oms-simple .simple-price-head { border-left:1px solid var(--oms-line); }
        .oms-simple .simple-price-cell { border-left:1px solid #f2f4f7; }
        .oms-simple .simple-progress { min-width:122px; }
        .oms-simple .simple-progress .progress { height:7px; background:#eaecf0; }
        .oms-simple .simple-progress .progress-bar { background:var(--oms-blue); }
        .oms-simple .simple-progress small { color:var(--oms-muted); font-size:.69rem; }
        .oms-simple .simple-actions { white-space:nowrap; }
        .oms-simple .simple-actions .btn { width:29px; height:29px; padding:0; margin-left:2px; }
        .oms-simple .simple-footer { display:flex; align-items:center; justify-content:space-between; gap:12px; border-top:1px solid var(--oms-line); padding:12px 16px; flex-wrap:wrap; }
        .oms-simple .simple-rule { color:var(--oms-muted); font-size:.78rem; }
        .oms-simple .simple-rule i { color:#d92d20; }
        .oms-simple .simple-demo-note { background:#fffaeb; border:1px solid #fedf89; border-radius:6px; color:#7a2e0e; font-size:.8rem; margin-bottom:12px; padding:9px 12px; }
        .oms-simple .simple-add-row { background:#f8fbff; border-top:1px dashed #b2ccff; }
        .oms-simple .simple-add-row td { padding:10px; }
        .oms-simple .simple-legend { display:flex; gap:10px; flex-wrap:wrap; color:var(--oms-muted); font-size:.75rem; }
        .oms-simple .simple-legend span { display:inline-flex; align-items:center; gap:4px; }
        .oms-simple .simple-dot { width:8px; height:8px; border-radius:50%; display:inline-block; }
        @media (max-width: 768px) { .oms-simple .simple-topbar { align-items:stretch; } .oms-simple .simple-topbar .btn { flex:1; } }
    </style>

    @if($isPrototypeData)
        <div class="simple-demo-note">
            <i class="fa-solid fa-flask me-1"></i>
            Private visual prototype. The example row is only shown because no normalized OMS data was provided to this view.
        </div>
    @endif

    <div class="simple-card">
        <div class="simple-topbar">
            <div class="simple-title">
                <span class="simple-label">Order note</span>
                <input class="form-control simple-reference" value="{{ $reference }}" aria-label="Order note reference">
                <div class="simple-supplier mt-1"><i class="fa-solid fa-truck-field me-1"></i>{{ $supplierName }}</div>
            </div>
            <div class="simple-legend">
                <span><i class="simple-dot bg-secondary"></i> Ordered</span>
                <span><i class="simple-dot bg-warning"></i> Invoiced</span>
                <span><i class="simple-dot bg-success"></i> Received</span>
            </div>
            <button type="button" class="btn btn-outline-primary js-simple-add-product" data-action="add-product">
                <i class="fa-solid fa-plus me-1"></i>Add product
            </button>
            <button type="button" class="btn btn-primary js-simple-save" data-action="save-document">
                <i class="fa-solid fa-floppy-disk me-1"></i>Save changes
            </button>
        </div>

        <div class="simple-table-wrap">
            <table class="table simple-table align-middle">
                <thead>
                    <tr>
                        <th rowspan="2">Product</th>
                        <th colspan="3" class="text-center">Quantities</th>
                        <th colspan="2" class="text-center simple-price-head">Purchase price</th>
                        <th colspan="2" class="text-center">Sales price</th>
                        <th rowspan="2">Progress</th>
                        <th rowspan="2" class="text-end">Actions</th>
                    </tr>
                    <tr>
                        <th class="text-center">Ordered</th>
                        <th class="text-center">Invoiced</th>
                        <th class="text-center">Received</th>
                        <th class="text-center simple-price-head">Supplier currency</th>
                        <th class="text-center">EUR</th>
                        <th class="text-center">Supplier currency</th>
                        <th class="text-center">EUR</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prototypeRows as $rowIndex => $row)
                        @php
                            $ordered = max(0, (int) data_get($row, 'ordered', 0));
                            $invoiced = max(0, (int) data_get($row, 'invoiced', 0));
                            $received = max(0, (int) data_get($row, 'received', 0));
                            $invoiceProgress = $ordered > 0 ? min(100, round(($invoiced / $ordered) * 100)) : 0;
                            $receiveProgress = $invoiced > 0 ? min(100, round(($received / $invoiced) * 100)) : 0;
                            $lineKey = data_get($row, 'line_id') ?? 'prototype-' . $rowIndex;
                            $currency = data_get($row, 'currency_iso', 'EUR');
                        @endphp
                        <tr class="js-simple-row" data-line-id="{{ $lineKey }}" data-product-id="{{ data_get($row, 'product_id') }}" data-attribute-id="{{ data_get($row, 'product_attribute_id') }}">
                            <td class="simple-product">
                                <span class="simple-ref">{{ data_get($row, 'reference', '—') }}</span>
                                <span class="simple-name">{{ data_get($row, 'name', 'Product') }}</span>
                            </td>
                            <td>
                                <div class="simple-qty-group">
                                    <button class="btn btn-sm btn-outline-secondary js-simple-change" type="button" data-action="decrease-ordered" title="Remove ordered quantity"><i class="fa-solid fa-minus"></i></button>
                                    <input class="form-control form-control-sm simple-qty js-simple-qty" data-field="ordered" type="number" min="{{ $invoiced }}" value="{{ $ordered }}">
                                    <button class="btn btn-sm btn-outline-primary js-simple-change" type="button" data-action="increase-ordered" title="Add ordered quantity"><i class="fa-solid fa-plus"></i></button>
                                </div>
                            </td>
                            <td>
                                <div class="simple-qty-group">
                                    <button class="btn btn-sm btn-outline-secondary js-simple-change" type="button" data-action="decrease-invoiced" title="Reverse invoiced quantity"><i class="fa-solid fa-minus"></i></button>
                                    <input class="form-control form-control-sm simple-qty js-simple-qty" data-field="invoiced" type="number" min="{{ $received }}" max="{{ $ordered }}" value="{{ $invoiced }}">
                                    <button class="btn btn-sm btn-outline-warning js-simple-change" type="button" data-action="increase-invoiced" title="Add invoiced quantity"><i class="fa-solid fa-plus"></i></button>
                                </div>
                            </td>
                            <td>
                                <div class="simple-qty-group">
                                    <button class="btn btn-sm btn-outline-secondary js-simple-change" type="button" data-action="decrease-received" title="Reverse received quantity"><i class="fa-solid fa-minus"></i></button>
                                    <input class="form-control form-control-sm simple-qty js-simple-qty" data-field="received" type="number" min="0" max="{{ $invoiced }}" value="{{ $received }}">
                                    <button class="btn btn-sm btn-outline-success js-simple-change" type="button" data-action="increase-received" title="Add received quantity"><i class="fa-solid fa-plus"></i></button>
                                </div>
                            </td>
                            <td class="simple-money simple-price-cell">
                                <input class="form-control form-control-sm js-simple-price" data-field="purchase_supplier" type="number" min="0" step="0.000001" value="{{ number_format((float) data_get($row, 'purchase_supplier', 0), 6, '.', '') }}">
                                <small>{{ $currency }}</small>
                            </td>
                            <td class="simple-money">
                                <input class="form-control form-control-sm js-simple-price" data-field="purchase_eur" type="number" min="0" step="0.000001" value="{{ number_format((float) data_get($row, 'purchase_eur', 0), 6, '.', '') }}">
                                <small>EUR converted value</small>
                            </td>
                            <td class="simple-money">
                                <input class="form-control form-control-sm js-simple-price" data-field="sales_supplier" type="number" min="0" step="0.000001" value="{{ number_format((float) data_get($row, 'sales_supplier', 0), 6, '.', '') }}">
                                <small>{{ $currency }}</small>
                            </td>
                            <td class="simple-money">
                                <input class="form-control form-control-sm js-simple-price" data-field="sales_eur" type="number" min="0" step="0.000001" value="{{ number_format((float) data_get($row, 'sales_eur', 0), 6, '.', '') }}">
                                <small>EUR converted value</small>
                            </td>
                            <td class="simple-progress">
                                <div class="d-flex justify-content-between"><small>Inv.</small><small class="js-simple-invoice-progress">{{ $invoiceProgress }}%</small></div>
                                <div class="progress mb-1"><div class="progress-bar bg-warning js-simple-invoice-bar" style="width:{{ $invoiceProgress }}%"></div></div>
                                <div class="d-flex justify-content-between"><small>Rec.</small><small class="js-simple-receive-progress">{{ $receiveProgress }}%</small></div>
                                <div class="progress"><div class="progress-bar bg-success js-simple-receive-bar" style="width:{{ $receiveProgress }}%"></div></div>
                            </td>
                            <td class="simple-actions text-end">
                                <button class="btn btn-sm btn-outline-secondary js-simple-line-note" type="button" data-action="line-note" title="Line note"><i class="fa-regular fa-note-sticky"></i></button>
                                <button class="btn btn-sm btn-outline-danger js-simple-remove-line" type="button" data-action="remove-line" title="Remove line"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="simple-add-row">
                        <td colspan="10">
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 js-simple-add-product" data-action="add-product">
                                <i class="fa-solid fa-plus me-1"></i>Add another product to this order note
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="simple-footer">
            <div class="simple-rule"><i class="fa-solid fa-shield-halved me-1"></i>Guardrails: received ≤ invoiced ≤ ordered. Quantity removals must retain OMS stock and audit history.</div>
            <div class="small text-muted">One product, one row, one operational flow.</div>
        </div>
    </div>
</div>

<script>
/* Prototype-only client feedback. Persistence is deliberately not implemented in this Blade. */
document.addEventListener('click', function (event) {
    const button = event.target.closest('.js-simple-change');
    if (!button) return;

    const row = button.closest('.js-simple-row');
    const action = button.dataset.action;
    const field = action.replace('increase-', '').replace('decrease-', '');
    const input = row.querySelector('[data-field="' + field + '"]');
    const direction = action.startsWith('increase-') ? 1 : -1;
    const ordered = row.querySelector('[data-field="ordered"]');
    const invoiced = row.querySelector('[data-field="invoiced"]');
    const received = row.querySelector('[data-field="received"]');
    const next = Math.max(Number(input.min || 0), Number(input.value || 0) + direction);

    if ((field === 'invoiced' && next > Number(ordered.value)) || (field === 'received' && next > Number(invoiced.value))) return;
    input.value = next;
    if (field === 'ordered') invoiced.max = next;
    if (field === 'invoiced') received.max = next;

    const invoicePct = Number(ordered.value) ? Math.min(100, Math.round((Number(invoiced.value) / Number(ordered.value)) * 100)) : 0;
    const receivePct = Number(invoiced.value) ? Math.min(100, Math.round((Number(received.value) / Number(invoiced.value)) * 100)) : 0;
    row.querySelector('.js-simple-invoice-progress').textContent = invoicePct + '%';
    row.querySelector('.js-simple-invoice-bar').style.width = invoicePct + '%';
    row.querySelector('.js-simple-receive-progress').textContent = receivePct + '%';
    row.querySelector('.js-simple-receive-bar').style.width = receivePct + '%';
});
</script>
@endsection
