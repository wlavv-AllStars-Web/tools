@extends('layouts.app')

@section('content')

<div class="customPanel">
    <form class="row g-2 mb-3 align-items-end" method="get" style="float: left;width: 1200px;">

        <div class="col-md-2">
            <input type="number" name="year" class="form-control" value="{{ $year }}" min="2000" max="2100">
        </div>

        @if(auth()->user()->role === 'admin')
            <div class="col-md-3">
                <select name="team" class="form-select">
                    <option value="">-- All teams --</option>
                    @foreach($teams as $t)
                        <option value="{{ $t->id }}" @selected((string)$teamId === (string)$t->id)>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100">View</button>
        </div>

    </form>


    <a style="float: right;width:100px;" class="btn btn-outline-secondary" href="{{ route('admin.tools.tasks.admin.index') }}">
        Back
    </a>
</div>

@php
    $done = (int)($stats['done'] ?? 0);
    $fail = (int)($stats['fail'] ?? 0);
    $delayed = (int)($stats['delayed'] ?? 0);
    $points = (int)($stats['points'] ?? 0);
    $productivity = $stats['productivity'] ?? null;

    $byStatus = $stats['by_status'] ?? [];
    $totalStatus = array_sum($byStatus);

    $computedOther = 0;
    foreach($byStatus as $k => $v) {
        if (!in_array($k, ['done','fail','delayed'], true)) $computedOther += (int)$v;
    }

    $statusUi = [
        'new'     => ['label'=>'NEW',     'class'=>'secondary', 'color'=>'#adb5bd'],
        'pending' => ['label'=>'PENDING', 'class'=>'warning',   'color'=>'#ffc107'],
        'delayed' => ['label'=>'DELAYED', 'class'=>'warning',   'color'=>'#fd7e14'],
        'done'    => ['label'=>'DONE',    'class'=>'success',   'color'=>'#198754'],
        'fail'    => ['label'=>'FAIL',    'class'=>'danger',    'color'=>'#dc3545'],
        'ok'      => ['label'=>'OK',      'class'=>'info',      'color'=>'#0dcaf0'],
        'extra'   => ['label'=>'EXTRA',   'class'=>'secondary', 'color'=>'#6c757d'],
        'hold'    => ['label'=>'HOLD',    'class'=>'secondary', 'color'=>'#6c757d'],
    ];

    $hasTeam = !empty($teamId);
@endphp

<div class="row g-3">

    {{-- SUMMARY --}}
    <div class="col-md-4">
        <div class="customPanel" style="height: 420px;">
            <div class="card p-3">
                <h5 class="mb-3">Summary ({{ $year }})</h5>
                <div><strong>Done:</strong> {{ $done }}</div>
                <div><strong>Fail:</strong> {{ $fail }}</div>
                <div><strong>Delayed:</strong> {{ $delayed }}</div>
                <div><strong>Others:</strong> {{ $computedOther }}</div>
                <hr>
                <div><strong>Points:</strong> {{ $points }}</div>
                <div><strong>Productivity:</strong> {{ $productivity === null ? '-' : $productivity.'%' }}</div>
                <div class="mt-2"><strong>Annual average:</strong> {{ $avg === null ? '-' : $avg.'%' }}</div>
            </div>
        </div>
    </div>

    {{-- COUNTERS BY STATUS --}}
    <div class="col-md-4">
        <div class="customPanel" style="height: 420px;">
            <h5 class="mb-3">Counters by status ({{ $year }})</h5>

            @if(!empty($byStatus))
                <div class="d-flex flex-column gap-2">
                    @foreach($statusUi as $key => $ui)
                        @php
                            $count = (int)($byStatus[$key] ?? 0);
                            $pct = $totalStatus > 0 ? round($count / $totalStatus * 100, 1) : 0;
                        @endphp

                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-{{ $ui['class'] }}" style="min-width:90px;">
                                {{ $ui['label'] }}
                            </span>

                            <div class="progress flex-grow-1" style="height: 10px;">
                                <div class="progress-bar bg-{{ $ui['class'] }}"
                                     role="progressbar"
                                     style="width: {{ $pct }}%;"
                                     aria-valuenow="{{ $pct }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                </div>
                            </div>

                            <div class="text-end" style="min-width:110px;">
                                <strong>{{ $count }}</strong>
                                <span class="text-muted">({{ $pct }}%)</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-muted">No data to display.</div>
            @endif
        </div>
    </div>

    {{-- PIE: STATUS DISTRIBUTION --}}
    <div class="col-md-4">
        <div class="customPanel" style="height: 420px;">
            <div class="p-3 mt-3 text-center">
                <h5 class="mb-3">Status distribution ({{ $year }})</h5>
                <canvas id="statusPieYear" style="max-height:320px;"></canvas>
            </div>
        </div>
    </div>

    {{-- OPTIONAL: PRODUCTIVITY BY MONTH (line/bar) --}}
    <div class="col-md-12" style="margin-top: 0;">
        <div class="customPanel text-center" style="height: 420px;">
            <h5 class="mb-3">Productivity by month ({{ $year }})</h5>
            @if(empty($monthly))
                <div class="text-muted">No data.</div>
            @else
                <canvas id="prodByMonth" style="height:350px; margin: 0 auto;"></canvas>
            @endif
        </div>
    </div>

    {{-- USERS CARDS --}}
    <div class="col-md-12" style="margin-top: 0;">
        <div class="customPanel">
            <h5 class="mb-3">Tasks assigned per user ({{ $year }})</h5>

            @if(empty($teamId))
                <div class="text-muted">Select a department to view users.</div>
            @elseif(empty($userStats))
                <div class="text-muted">No data.</div>
            @else
                <div class="row g-2">
                    @foreach($userStats as $name => $s)
                        @php
                            if ($name === 'Unassigned') continue;

                            $total = (int)($s['total'] ?? 0);
                            $doneU  = (int)($s['done'] ?? 0);
                            $failU  = (int)($s['fail'] ?? 0);
                            $delU   = (int)($s['delayed'] ?? 0);
                            $otherU = (int)($s['other'] ?? 0);

                            $prodU = $total > 0 ? round($doneU / $total * 100, 2) : null;
                            $pctDoneTotal = $total > 0 ? round($doneU / $total * 100, 2) : 0;
                        @endphp

                        <div class="col-md-4">
                            <div class="card p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="fw-bold">{{ $name }}</div>
                                </div>

                                <div class="mt-2 d-flex flex-wrap gap-2">
                                    <span class="badge bg-dark">Assigned: {{ $total }}</span>
                                    <span class="badge bg-success">DONE: {{ $doneU }}</span>
                                    <span class="badge bg-danger">FAIL: {{ $failU }}</span>
                                    <span class="badge bg-warning text-dark">DELAYED: {{ $delU }}</span>
                                    <span class="badge bg-secondary">OTHERS: {{ $otherU }}</span>
                                </div>

                                <div class="mt-2 text-muted small">
                                    Productivity (done / total):
                                    <strong>{{ $prodU === null ? '—' : number_format($prodU, 2).'%' }}</strong>
                                </div>

                                <div class="mt-2">
                                    <div class="small text-muted">DONE / Assigned</div>
                                    <div class="progress" style="height:10px;">
                                        <div class="progress-bar bg-success" style="width: {{ $pctDoneTotal }}%"></div>
                                    </div>
                                    <div class="small text-muted mt-1">{{ number_format($pctDoneTotal, 2) }}%</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- DONE SHARE PIE --}}
    <div class="col-md-4" style="margin-top: 0;">
        <div class="customPanel" style="height: 420px;">
            <div class="p-3 mt-3 text-center">
                <h5 class="mb-3">Percentage of DONE tasks by user ({{ $year }})</h5>
                @if(!$hasTeam)
                    <div class="text-muted">Select a department to view user distribution.</div>
                @elseif(empty($doneShareByUser))
                    <div class="text-muted">No DONE tasks in this period.</div>
                @else
                    <canvas id="doneByUserPieYear" style="max-height:320px;"></canvas>
                @endif
            </div>
        </div>
    </div>

    {{-- STACKED BAR: MIX BY USER --}}
    <div class="col-md-8" style="margin-top: 0;">
        <div class="customPanel text-center" style="height: 420px;">
            <h5 class="mb-3">Breakdown per user (DONE / FAIL / DELAYED / OTHERS) — {{ $year }}</h5>
            @if(!$hasTeam)
                <div class="text-muted">Select a department to view user details.</div>
            @elseif(empty($userStats))
                <div class="text-muted">No data.</div>
            @else
                <canvas id="userMixStackedYear" style="height:350px; margin: 0 auto;"></canvas>
            @endif
        </div>
    </div>

    {{-- TABLE (existing) --}}
    <div class="col-md-12" style="margin-top: 0;">
        <div class="customPanel">
            <h5 class="mb-3">Monthly table ({{ $year }})</h5>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Done</th>
                                <th>Fail</th>
                                <th>Delayed</th>
                                <th>Productivity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthly as $row)
                                <tr>
                                    <td>{{ $row['month'] }}</td>
                                    <td>{{ $row['done'] }}</td>
                                    <td>{{ $row['fail'] }}</td>
                                    <td>{{ $row['delayed'] }}</td>
                                    <td>{{ $row['productivity'] === null ? '-' : $row['productivity'].'%' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

{{-- PIE: status distribution (YEAR) --}}
<script>
(() => {
    const byStatus = @json($byStatus);
    const statusUi = @json($statusUi);

    const entries = Object.entries(byStatus || {})
        .filter(([_, v]) => Number(v) > 0);

    const canvas = document.getElementById('statusPieYear');
    if (!canvas) return;

    if (!entries.length) {
        const ctx2 = canvas.getContext('2d');
        ctx2.font = '14px sans-serif';
        ctx2.fillText('No data', 10, 30);
        return;
    }

    const labels = entries.map(([k]) => String(k).toUpperCase());
    const values = entries.map(([_, v]) => Number(v));
    const colors = entries.map(([k]) => (statusUi[k] && statusUi[k].color) ? statusUi[k].color : '#999');
    const total = values.reduce((a,b) => a + b, 0);

    Chart.register(ChartDataLabels);

    new Chart(canvas, {
        type: 'pie',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true } },
                datalabels: {
                    formatter: (value) => {
                        const pct = total ? value / total * 100 : 0;
                        return pct >= 2 ? pct.toFixed(0) + '%' : '';
                    },
                    color: '#fff',
                    font: { weight: 'bold', size: 12 }
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const v = ctx.raw || 0;
                            const pct = total ? (v / total * 100) : 0;
                            return `${ctx.label}: ${v} (${pct.toFixed(1)}%)`;
                        }
                    }
                }
            }
        }
    });
})();
</script>

{{-- BAR: productivity by month --}}
<script>
(() => {
    const monthly = @json($monthly ?? []);
    const canvas = document.getElementById('prodByMonth');
    if (!canvas) return;
    if (!Array.isArray(monthly) || !monthly.length) return;

    // labels: 01..12 ou nomes se preferires
    const labels = monthly.map(r => r.month);

    // valores (null mantém meses sem dados vazios)
    const values = monthly.map(r =>
        (r.productivity === null || r.productivity === undefined)
            ? null
            : Number(r.productivity)
    );

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Productivity (%)',
                data: values,
                borderWidth: 1,
                borderRadius: 6,
                maxBarThickness: 40
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const v = ctx.raw;
                            return v === null
                                ? 'Productivity: -'
                                : `Productivity: ${Number(v).toFixed(2)}%`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: 100,
                    ticks: {
                        callback: (v) => v + '%'
                    }
                }
            }
        }
    });
})();
</script>


{{-- DOUGHNUT: done share by user (YEAR) --}}
<script>
(() => {
    const hasTeam = @json($hasTeam);
    const doneShareByUser = @json($doneShareByUser ?? []);

    if (!hasTeam) return;

    const entries = Object.entries(doneShareByUser || {});
    if (!entries.length) return;

    const labels = entries.map(([name]) => name);
    const values = entries.map(([_, v]) => Number(v));
    const total = values.reduce((a,b) => a + b, 0);

    const canvas = document.getElementById('doneByUserPieYear');
    if (!canvas) return;

    Chart.register(ChartDataLabels);

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true } },
                datalabels: {
                    formatter: (value) => {
                        const pct = total ? value / total * 100 : 0;
                        return pct >= 3 ? pct.toFixed(0) + '%' : '';
                    },
                    color: '#fff',
                    font: { weight: 'bold', size: 12 }
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const v = ctx.raw || 0;
                            const pct = total ? (v / total * 100) : 0;
                            return `${ctx.label}: ${v} DONE (${pct.toFixed(1)}%)`;
                        }
                    }
                }
            }
        }
    });
})();
</script>

{{-- STACKED BAR: mix by user (100%) (YEAR) --}}
<script>
(() => {
    const hasTeam = @json($hasTeam);
    const userStats = @json($userStats ?? []);

    if (!hasTeam) return;

    const entries = Object.entries(userStats || {})
        .filter(([name]) => name !== 'Unassigned');

    if (!entries.length) return;

    const labels = entries.map(([name]) => name);

    const donePct = entries.map(([_, s]) => s.total ? +(s.done / s.total * 100).toFixed(2) : 0);
    const failPct = entries.map(([_, s]) => s.total ? +(s.fail / s.total * 100).toFixed(2) : 0);
    const delayedPct = entries.map(([_, s]) => s.total ? +(s.delayed / s.total * 100).toFixed(2) : 0);
    const otherPct = entries.map(([_, s]) => s.total ? +(s.other / s.total * 100).toFixed(2) : 0);

    const canvas = document.getElementById('userMixStackedYear');
    if (!canvas) return;

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'DONE',    data: donePct,    backgroundColor: '#198754' },
                { label: 'FAIL',    data: failPct,    backgroundColor: '#dc3545' },
                { label: 'DELAYED', data: delayedPct, backgroundColor: '#fd7e14' },
                { label: 'OTHERS',  data: otherPct,   backgroundColor: '#adb5bd' },
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.dataset.label}: ${Number(ctx.raw).toFixed(2)}%`
                    }
                }
            },
            scales: {
                x: { stacked: true },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    max: 100,
                    ticks: { callback: (v) => v + '%' }
                }
            }
        }
    });
})();
</script>

@endsection
