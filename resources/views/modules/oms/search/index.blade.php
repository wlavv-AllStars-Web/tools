@extends('layouts.app')

@section('content')
@php
    $sections = [
        ['id' => 'order-notes', 'title' => 'Order Notes', 'icon' => 'fa-file-lines', 'rows' => $orderNoteRows],
        ['id' => 'billed-notes', 'title' => 'Billed Notes', 'icon' => 'fa-file-invoice', 'rows' => $billedRows],
        ['id' => 'received-notes', 'title' => 'Received Notes', 'icon' => 'fa-box-open', 'rows' => $receivedRows],
        ['id' => 'stock-history', 'title' => 'Stock History', 'icon' => 'fa-boxes-stacked', 'rows' => $stockHistoryRows],
        ['id' => 'price-history', 'title' => 'Price History', 'icon' => 'fa-chart-line', 'rows' => $priceHistoryRows],
    ];
@endphp
<div class="container-fluid py-3 oms-premium oms-search-results">
    <div class="mb-3">
        <h4 class="mb-1">OMS Search</h4>
        @if($search !== '')
            <div class="text-muted">Results for <strong>{{ $search }}</strong></div>
        @else
            <div class="text-muted">Enter a parent reference, child reference, EAN or product name.</div>
        @endif
    </div>

    <div class="accordion" id="omsSearchAccordion">
        @foreach($sections as $section)
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading-{{ $section['id'] }}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $section['id'] }}" aria-expanded="false">
                        <span class="badge text-bg-primary me-2">{{ $section['rows']->count() }}</span>
                        <i class="fa-solid {{ $section['icon'] }} me-2"></i>
                        <span>{{ $section['title'] }}</span>
                    </button>
                </h2>
                <div id="collapse-{{ $section['id'] }}" class="accordion-collapse collapse" data-bs-parent="#omsSearchAccordion">
                    <div class="accordion-body p-0">
                        @if($section['rows']->isEmpty())
                            <div class="p-4 text-center text-muted">No results found in this section.</div>
                        @elseif($section['id'] === 'order-notes')
                            <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Order Note</th><th>Reference</th><th>Product</th><th class="text-center">Ordered</th><th>Status</th><th>Date</th></tr></thead><tbody>
                            @foreach($section['rows'] as $row)<tr><td><a href="{{ route('erp.oms.order_notes.show', $row->order_note_id) }}">{{ $row->document_reference }}</a></td><td>{{ $row->search_reference }}</td><td>{{ $row->search_product_name }}</td><td class="text-center">{{ $row->qty_ordered }}</td><td>{{ $row->document_status }}</td><td>{{ $row->document_date }}</td></tr>@endforeach
                            </tbody></table></div>
                        @elseif($section['id'] === 'billed-notes')
                            <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Billed Note</th><th>Invoice</th><th>Reference</th><th>Product</th><th class="text-center">Billed</th><th>Date</th></tr></thead><tbody>
                            @foreach($section['rows'] as $row)<tr><td><a href="{{ route('erp.oms.billed_orders.show', $row->billed_order_id) }}">{{ $row->document_reference }}</a></td><td>{{ $row->invoice_reference ?: '-' }}</td><td>{{ $row->search_reference }}</td><td>{{ $row->search_product_name }}</td><td class="text-center">{{ $row->qty_billed }}</td><td>{{ $row->document_date }}</td></tr>@endforeach
                            </tbody></table></div>
                        @elseif($section['id'] === 'received-notes')
                            <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Received Note</th><th>Invoice</th><th>Reference</th><th>Product</th><th class="text-center">Received</th><th>Date</th></tr></thead><tbody>
                            @foreach($section['rows'] as $row)<tr><td><a href="{{ route('erp.oms.receptions.history', $row->billed_order_id) }}">{{ $row->document_reference }}</a></td><td>{{ $row->invoice_reference ?: '-' }}</td><td>{{ $row->search_reference }}</td><td>{{ $row->search_product_name }}</td><td class="text-center">{{ $row->qty_received }}</td><td>{{ $row->document_date }}</td></tr>@endforeach
                            </tbody></table></div>
                        @elseif($section['id'] === 'stock-history')
                            <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Date</th><th>Reference</th><th>Product</th><th>Source</th><th class="text-center">Stock Δ</th><th class="text-center">Arrive Δ</th><th>User</th></tr></thead><tbody>
                            @foreach($section['rows'] as $row)<tr><td>{{ $row->created_at }}</td><td>{{ $row->search_reference }}</td><td>{{ $row->search_product_name }}</td><td>{{ $row->source_type }} #{{ $row->source_id }}</td><td class="text-center">{{ $row->ps_quantity_delta }}</td><td class="text-center">{{ $row->ps_quantity_arrive_delta }}</td><td>{{ $row->user_name_snapshot ?: '-' }}</td></tr>@endforeach
                            </tbody></table></div>
                        @else
                            <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Date</th><th>Reference</th><th>Product</th><th>Currency</th><th class="text-end">Unit price</th><th class="text-end">Price EUR</th><th>User</th></tr></thead><tbody>
                            @foreach($section['rows'] as $row)<tr><td>{{ $row->created_at }}</td><td>{{ $row->search_reference }}</td><td>{{ $row->search_product_name }}</td><td>{{ $row->invoice_currency_iso ?: 'EUR' }}</td><td class="text-end">{{ number_format((float) $row->unit_price_invoice_currency, 2, '.', '') }}</td><td class="text-end">{{ number_format((float) $row->unit_price_eur, 2, '.', '') }}</td><td>{{ $row->user_name_snapshot ?: '-' }}</td></tr>@endforeach
                            </tbody></table></div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
<style>
.oms-search-results{background:#fff;border:1px solid rgba(20,33,61,.10);border-radius:5px;box-shadow:0 8px 24px rgba(15,23,42,.06);padding:16px!important;margin-top:10px}
.oms-search-results .accordion-item,.oms-search-results .accordion-button,.oms-search-results .table{border-radius:5px!important}
.oms-search-results .accordion-item{margin-bottom:10px;border:1px solid rgba(20,33,61,.10);overflow:hidden}
.oms-search-results .accordion-button:not(.collapsed){background:#eff6ff;color:#1d4ed8}
.oms-search-results th{white-space:nowrap;background:#f8fafc}
</style>
@endsection