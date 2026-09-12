@extends('layouts.app')

@section('content')
@php
    $latest = $dashboard['latest'];
    $ratingChange = $dashboard['rating_change'];
    $chartLabels = $chartRecords->map(fn ($record) => $record->recorded_on->format('d/m/Y'))->values();
    $chartReviewCounts = $chartRecords->pluck('review_count')->values();
    $chartRatings = $chartRecords->map(fn ($record) => (float) $record->rating)->values();
    $formatDelta = static fn ($value) => $value === null ? 'N/A' : (($value > 0 ? '+' : '') . $value);
@endphp

<div class="trustpilot-dashboard container-fluid py-3">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="mb-0"><i class="fa-solid fa-star text-warning me-2"></i>Trustpilot</h4>
            <small class="text-muted">Manual review and rating monitoring</small>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#trustpilotRecordModal">
            <i class="fa-solid fa-plus me-1"></i>Add update
        </button>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3"><div class="card tp-card h-100"><div class="card-body"><small>Current rating</small><strong>{{ $latest ? number_format((float) $latest->rating, 1) . ' / 5' : 'N/A' }}</strong></div></div></div>
        <div class="col-6 col-lg-3"><div class="card tp-card h-100"><div class="card-body"><small>Total reviews</small><strong>{{ $latest ? number_format($latest->review_count, 0, '.', ',') : 'N/A' }}</strong></div></div></div>
        <div class="col-6 col-lg-3"><div class="card tp-card h-100"><div class="card-body"><small>New reviews this period</small><strong>{{ $latest && $latest->review_count_period !== null ? '+' . number_format($latest->review_count_period, 0, '.', ',') : 'N/A' }}</strong></div></div></div>
        <div class="col-6 col-lg-3"><div class="card tp-card h-100"><div class="card-body"><small>Rating evolution</small><strong class="{{ $ratingChange !== null && $ratingChange < 0 ? 'text-danger' : 'text-success' }}">{{ $ratingChange === null ? 'N/A' : ($ratingChange == 0.0 ? 'No change' : $formatDelta(number_format($ratingChange, 1))) }}</strong></div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6"><div class="card h-100"><div class="card-header bg-white fw-bold">Total reviews</div><div class="card-body"><canvas id="trustpilotReviewsChart" height="130"></canvas></div></div></div>
        <div class="col-lg-6"><div class="card h-100"><div class="card-header bg-white fw-bold">Rating</div><div class="card-body"><canvas id="trustpilotRatingChart" height="130"></canvas></div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="card h-100"><div class="card-body"><small>Reviews this week</small><div class="fs-4 fw-bold">{{ $formatDelta($dashboard['reviews_this_week']) }}</div></div></div></div>
        <div class="col-md-4"><div class="card h-100"><div class="card-body"><small>Reviews this month</small><div class="fs-4 fw-bold">{{ $formatDelta($dashboard['reviews_this_month']) }}</div></div></div></div>
        <div class="col-md-4"><div class="card h-100"><div class="card-body"><small>Reviews this year</small><div class="fs-4 fw-bold">{{ $formatDelta($dashboard['reviews_this_year']) }}</div></div></div></div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white fw-bold">This week vs previous week</div>
        <div class="card-body d-flex flex-wrap gap-4">
            <div><span class="text-muted d-block small">This week</span><strong>{{ $formatDelta($dashboard['week_comparison']['current']) }}</strong></div>
            <div><span class="text-muted d-block small">Previous week</span><strong>{{ $formatDelta($dashboard['week_comparison']['previous']) }}</strong></div>
            <div><span class="text-muted d-block small">Difference</span><strong>{{ $dashboard['week_comparison']['current'] === null || $dashboard['week_comparison']['previous'] === null ? 'N/A' : $formatDelta($dashboard['week_comparison']['current'] - $dashboard['week_comparison']['previous']) }}</strong></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between gap-2 align-items-center"><strong>History</strong>
            <form class="row g-2" method="GET" action="{{ route('web.tools.trustpilot.index') }}">
                <div class="col-auto"><input class="form-control form-control-sm" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" aria-label="Date from"></div>
                <div class="col-auto"><input class="form-control form-control-sm" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" aria-label="Date to"></div>
                <div class="col-auto"><select class="form-select form-select-sm" name="period_type"><option value="">All periods</option>@foreach(['weekly' => 'Weekly', 'monthly' => 'Monthly', 'manual' => 'Manual'] as $value => $label)<option value="{{ $value }}" @selected(($filters['period_type'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="col-auto"><button class="btn btn-sm btn-outline-primary">Filter</button></div>
            </form>
        </div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Date</th><th class="text-end">Total reviews</th><th class="text-end">New reviews</th><th class="text-end">Rating</th><th class="text-end">Rating change</th><th>Period</th><th>User</th><th>Notes</th>@if($canManage)<th></th>@endif</tr></thead><tbody>
            @forelse($historyRows as $row)
                @php($record = $row['record'])
                <tr><td>{{ $record->recorded_on->format('d/m/Y') }}</td><td class="text-end">{{ number_format($record->review_count, 0, '.', ',') }}</td><td class="text-end">{{ $record->review_count_period === null ? 'N/A' : '+' . number_format($record->review_count_period, 0, '.', ',') }}</td><td class="text-end">{{ number_format((float) $record->rating, 1) }}</td><td class="text-end">{{ $row['rating_change'] === null ? 'N/A' : $formatDelta(number_format($row['rating_change'], 1)) }}</td><td>{{ ucfirst($record->period_type) }}@if($record->period_start && $record->period_end)<small class="d-block text-muted">{{ $record->period_start->format('d/m/Y') }} – {{ $record->period_end->format('d/m/Y') }}</small>@endif</td><td>{{ $creatorNames[$record->created_by] ?? ($record->created_by ? '#' . $record->created_by : '-') }}</td><td class="tp-notes">{{ $record->notes ?: '–' }}</td>@if($canManage)<td class="text-end text-nowrap"><button type="button" class="btn btn-sm btn-outline-primary js-edit-record" data-bs-toggle="modal" data-bs-target="#trustpilotRecordModal" data-id="{{ $record->getKey() }}" data-date="{{ $record->recorded_on->format('Y-m-d') }}" data-review-count="{{ $record->review_count }}" data-rating="{{ $record->rating }}" data-period-type="{{ $record->period_type }}" data-period-start="{{ optional($record->period_start)->format('Y-m-d') }}" data-period-end="{{ optional($record->period_end)->format('Y-m-d') }}" data-notes="{{ $record->notes }}"><i class="fa-solid fa-pen"></i></button><form method="POST" action="{{ route('web.tools.trustpilot.destroy', $record) }}" class="d-inline" onsubmit="return confirm('Soft-delete this Trustpilot record?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button></form></td>@endif</tr>
            @empty
                <tr><td colspan="{{ $canManage ? 9 : 8 }}" class="text-center text-muted py-4">No Trustpilot records found.</td></tr>
            @endforelse
        </tbody></table></div>
        @if($records->hasPages())<div class="card-footer bg-white">{{ $records->links() }}</div>@endif
    </div>
</div>

<div class="modal fade" id="trustpilotRecordModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><form method="POST" action="{{ route('web.tools.trustpilot.store') }}" class="modal-content" id="trustpilotRecordForm">@csrf <input type="hidden" name="_method" id="trustpilotMethod" value="POST"><input type="hidden" name="confirm_lower_review_count" id="confirmLowerReviewCount" value="0"><div class="modal-header"><h5 class="modal-title" id="trustpilotModalTitle">Add update</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row g-3"><div class="col-md-6"><label class="form-label">Date</label><input class="form-control" type="date" name="recorded_on" id="tpRecordedOn" value="{{ old('recorded_on', now()->toDateString()) }}" required></div><div class="col-md-6"><label class="form-label">Period type</label><select class="form-select" name="period_type" id="tpPeriodType" required><option value="weekly">Weekly</option><option value="monthly">Monthly</option><option value="manual" selected>Manual</option></select></div><div class="col-md-6"><label class="form-label">Total reviews</label><input class="form-control" type="number" min="0" step="1" name="review_count" id="tpReviewCount" required></div><div class="col-md-6"><label class="form-label">Rating</label><input class="form-control" type="number" min="0" max="5" step="0.1" name="rating" id="tpRating" required></div><div class="col-md-6"><label class="form-label">Period start</label><input class="form-control" type="date" name="period_start" id="tpPeriodStart"></div><div class="col-md-6"><label class="form-label">Period end</label><input class="form-control" type="date" name="period_end" id="tpPeriodEnd"></div><div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" rows="3" name="notes" id="tpNotes"></textarea></div></div><div class="alert alert-warning small mt-3 mb-0 d-none" id="tpLowerWarning">The total is lower than the previous record. Confirm before saving.</div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Save update</button></div></form></div></div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
(() => {
    const labels = @json($chartLabels);
    const reviewCounts = @json($chartReviewCounts);
    const ratings = @json($chartRatings);
    const latestReviewCount = {{ $latest?->review_count ?? 'null' }};
    const modal = document.getElementById('trustpilotRecordModal');
    const form = document.getElementById('trustpilotRecordForm');
    const actionTemplate = @json(route('web.tools.trustpilot.update', ['trustpilot' => '__id__']));
    const storeAction = @json(route('web.tools.trustpilot.store'));
    const lowerWarning = document.getElementById('tpLowerWarning');
    const resetForCreate = () => { form.action = storeAction; document.getElementById('trustpilotMethod').value = 'POST'; document.getElementById('trustpilotModalTitle').textContent = 'Add update'; document.getElementById('tpRecordedOn').value = '{{ now()->toDateString() }}'; document.getElementById('tpPeriodType').value = 'manual'; document.getElementById('tpReviewCount').value = ''; document.getElementById('tpRating').value = ''; document.getElementById('tpPeriodStart').value = ''; document.getElementById('tpPeriodEnd').value = ''; document.getElementById('tpNotes').value = ''; };
    modal.addEventListener('show.bs.modal', (event) => { const button = event.relatedTarget; if (!button || !button.classList.contains('js-edit-record')) { resetForCreate(); return; } form.action = actionTemplate.replace('__id__', button.dataset.id); document.getElementById('trustpilotMethod').value = 'PUT'; document.getElementById('trustpilotModalTitle').textContent = 'Edit record'; document.getElementById('tpRecordedOn').value = button.dataset.date; document.getElementById('tpPeriodType').value = button.dataset.periodType; document.getElementById('tpReviewCount').value = button.dataset.reviewCount; document.getElementById('tpRating').value = button.dataset.rating; document.getElementById('tpPeriodStart').value = button.dataset.periodStart; document.getElementById('tpPeriodEnd').value = button.dataset.periodEnd; document.getElementById('tpNotes').value = button.dataset.notes; });
    form.addEventListener('submit', (event) => { const count = Number(document.getElementById('tpReviewCount').value); const isLower = latestReviewCount !== null && count < latestReviewCount; document.getElementById('confirmLowerReviewCount').value = isLower ? '1' : '0'; lowerWarning.classList.toggle('d-none', !isLower); if (isLower && !window.confirm('The total is lower than the latest saved record. Do you confirm this value?')) event.preventDefault(); });
    const chartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } };
    new Chart(document.getElementById('trustpilotReviewsChart'), { type: 'line', data: { labels, datasets: [{ data: reviewCounts, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,.12)', fill: true, tension: .25 }] }, options: chartOptions });
    new Chart(document.getElementById('trustpilotRatingChart'), { type: 'line', data: { labels, datasets: [{ data: ratings, borderColor: '#f0ad4e', backgroundColor: 'rgba(240,173,78,.12)', fill: true, tension: .25 }] }, options: { ...chartOptions, scales: { y: { min: 0, max: 5, ticks: { stepSize: .5 } } } } });
})();
</script>
@endpush

<style>
.trustpilot-dashboard .tp-card small{color:#6c757d;display:block;text-transform:uppercase;font-size:.72rem;font-weight:700}.trustpilot-dashboard .tp-card strong{font-size:1.6rem;display:block;margin-top:4px}.trustpilot-dashboard .card{border-color:#e2e8f0}.trustpilot-dashboard .tp-notes{max-width:260px;white-space:normal}.trustpilot-dashboard canvas{max-height:280px}
</style>
@endsection
