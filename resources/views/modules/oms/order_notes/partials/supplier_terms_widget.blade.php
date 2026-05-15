@php
    $summary = $supplierTermsService->buildProgressSummary((int) $supplierId, (float) $orderAmount);
    $currentLevel = $summary['current_level'];
    $nextLevel = $summary['next_level'];
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-header">
        <div class="small text-muted">Supplier terms</div>
        <div class="fw-bold">Commercial thresholds</div>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <div class="small text-muted">Current order total</div>
            <div class="fs-5 fw-bold">{{ number_format((float) $summary['amount'], 2, ',', '.') }}</div>
        </div>

        <div class="mb-3">
            <div class="small text-muted">Current level</div>
            @if($currentLevel)
                <div class="fw-semibold">{{ $supplierTermsService->buildLabel($currentLevel) }}</div>
                <div class="small text-muted">
                    {{ number_format((float) $currentLevel->min_amount, 2, ',', '.') }}
                    —
                    {{ $currentLevel->max_amount !== null ? number_format((float) $currentLevel->max_amount, 2, ',', '.') : '∞' }}
                </div>
            @else
                <div class="text-muted">No level matched yet.</div>
            @endif
        </div>

        <div>
            <div class="small text-muted">Next level</div>
            @if($nextLevel)
                <div class="fw-semibold">{{ $supplierTermsService->buildLabel($nextLevel) }}</div>
                <div class="small text-muted">
                    Missing: {{ number_format((float) $summary['missing_to_next'], 2, ',', '.') }}
                </div>
            @else
                <div class="text-success fw-semibold">Highest level reached.</div>
            @endif
        </div>
    </div>
</div>
