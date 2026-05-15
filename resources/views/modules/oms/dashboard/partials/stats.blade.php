<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div class="d-flex flex-wrap gap-2">
        <div class="oms-kpi"><strong>{{ $stats['order_notes'] ?? 0 }}</strong> Open Order Notes</div>
        <div class="oms-kpi"><strong>{{ $stats['billed'] ?? 0 }}</strong> Billed Notes</div>
        <div class="oms-kpi"><strong>{{ $stats['open_receptions'] ?? 0 }}</strong> Open Receptions</div>
        <div class="oms-kpi"><strong>{{ $stats['receptions_history'] ?? 0 }}</strong> Receptions History</div>
    </div>
</div>
