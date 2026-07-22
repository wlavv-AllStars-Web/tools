@extends('layouts.app')

@section('content')
<div class="container-fluid py-3 oms-premium">
    <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
        <div>
            <div class="oms-soft-label">OMS / Billed Orders</div>
            <h1 class="h3 mb-1 oms-title">Billed Orders</h1>
            <div class="text-muted">Documentos operacionais resultantes da faturação.</div>
        </div>
        <a href="{{ route('erp.oms.dashboard', ['left_tab' => 'billed_orders']) }}" class="btn btn-outline-secondary oms-btn-icon">
            <i class="fa-solid fa-table-columns"></i> Dashboard
        </a>
    </div>

    <div class="oms-card">
        <div class="oms-card-header d-flex justify-content-between align-items-center">
            <div>
                <div class="oms-soft-label">Listing</div>
                <div class="fw-bold">Billed Orders List</div>
            </div>
            <i class="fa-solid fa-file-invoice text-primary"></i>
        </div>
        <div class="oms-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><i class="fa-solid fa-hashtag me-1"></i>ID</th>
                            <th><i class="fa-solid fa-file-lines me-1"></i>Reference</th>
                            <th><i class="fa-solid fa-link me-1"></i>Order Note</th>
                            <th><i class="fa-solid fa-receipt me-1"></i>Invoice</th>
                            <th><i class="fa-solid fa-circle-info me-1"></i>Status</th>
                            <th><i class="fa-solid fa-calendar-days me-1"></i>Created</th>
                            <th class="text-end"><i class="fa-solid fa-gears me-1"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($billedOrders as $doc)
                            <tr>
                                <td>{{ $doc->id }}</td>
                                <td class="fw-semibold">{{ $doc->reference }}</td>
                                <td>{{ optional($doc->orderNote)->reference ?? '-' }}</td>
                                <td>{{ optional($doc->invoice)->invoice_reference ?? '-' }}</td>
                                <td><span class="badge text-bg-light border">{{ $doc->status }}</span></td>
                                <td>{{ optional($doc->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('erp.oms.billed_orders.show', $doc) }}" class="btn btn-outline-primary"><i class="fa-solid fa-eye"></i></a>
                                        <a href="{{ route('erp.oms.receptions.history', $doc) }}" class="btn btn-outline-secondary"><i class="fa-solid fa-box-open"></i></a>
                                        <a href="{{ route('erp.oms.billed_orders.export.xlsx', $doc) }}" class="btn btn-outline-secondary"><i class="fa-solid fa-file-excel"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="oms-empty">No billed orders found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $billedOrders->links() }}
    </div>
</div>
@endsection
