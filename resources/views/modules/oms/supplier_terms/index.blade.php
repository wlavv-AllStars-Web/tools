@extends('layouts.app')

@section('content')
<div class="container-fluid py-3 oms-premium">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <div class="small text-muted">OMS / Supplier Terms</div>
                <div class="fw-bold">Commercial levels for supplier #{{ $supplierId }}</div>
            </div>
            <a href="{{ route('suppliersMap.index') }}" class="btn btn-outline-primary btn-sm">Back</a>
        </div>

        <div class="card-body">
            <form method="post" action="{{ route('erp.oms.supplier_terms.store', $supplierId) }}" class="row g-2 mb-4">
                @csrf
                <div class="col-md-2">
                    <label class="form-label">Label</label>
                    <input type="text" name="label" class="form-control" placeholder="Optional label">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Min amount</label>
                    <input type="number" step="0.01" name="min_amount" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Max amount</label>
                    <input type="number" step="0.01" name="max_amount" class="form-control" placeholder="Open ended">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Discount %</label>
                    <input type="number" step="0.01" name="discount_percent" class="form-control" value="0" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sort</label>
                    <input type="number" name="sort_order" class="form-control" value="0" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check me-3">
                        <input class="form-check-input" type="checkbox" name="free_shipping" value="1" id="freeShippingNew">
                        <label class="form-check-label" for="freeShippingNew">Free shipping</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeNew" checked>
                        <label class="form-check-label" for="activeNew">Active</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="2" class="form-control"></textarea>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary btn-sm">Add level</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Label</th>
                            <th>Range</th>
                            <th>Discount</th>
                            <th>Free shipping</th>
                            <th>Summary</th>
                            <th>Sort</th>
                            <th>Active</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($levels as $level)
                            <tr>
                                <td>{{ $level->label ?: '—' }}</td>
                                <td>
                                    {{ number_format((float) $level->min_amount, 2, ',', '.') }}
                                    —
                                    {{ $level->max_amount !== null ? number_format((float) $level->max_amount, 2, ',', '.') : '∞' }}
                                </td>
                                <td>{{ number_format((float) $level->discount_percent, 2, ',', '.') }}%</td>
                                <td>{{ $level->free_shipping ? 'Yes' : 'No' }}</td>
                                <td>{{ $supplierTermsService->buildLabel($level) }}</td>
                                <td>{{ $level->sort_order }}</td>
                                <td>{{ $level->is_active ? 'Yes' : 'No' }}</td>
                                <td class="text-end">
                                    <form method="post" action="{{ route('erp.oms.supplier_terms.destroy', $level) }}" onsubmit="return confirm('Remove this level?')" class="d-inline">
                                        @csrf
                                        @method('delete')
                                        <button class="btn btn-outline-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No commercial levels configured yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
