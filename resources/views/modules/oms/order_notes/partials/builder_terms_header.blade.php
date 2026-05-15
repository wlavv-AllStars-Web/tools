<div class="oms-terms-inline align-items-center justify-content-between flex-wrap gap-3">

    <div>
        <span class="text-muted">Total</span>
        <strong>{{ $formatMoney($orderAmount) }}</strong>
    </div>

    <div>
        <span class="text-muted">Current</span>

        @if($currentLevel)
            <strong>{{ $currentLevelLabel }}</strong>
        @else
            <span class="text-muted">—</span>
        @endif
    </div>

    <div>
        <span class="text-muted">Next</span>

        @if($nextLevel)
            <strong>{{ $nextLevelLabel }}</strong>
            <span class="text-warning">
                +{{ $formatMoney($missingToNext) }}
            </span>
        @else
            <span class="text-success">
                <i class="fa-solid fa-check"></i>
                Max
            </span>
        @endif
    </div>

</div>