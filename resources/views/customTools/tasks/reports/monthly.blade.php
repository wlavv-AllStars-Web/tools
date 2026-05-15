@extends('layouts.app')

@section('content')

<div class="customPanel">
    <form class="row g-2 mb-3 align-items-end" method="get" style="float: left;width: 1200px;">

        <div class="col-md-2">
            <select name="year" class="form-select">
                @for($y = now()->year - 3; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" @selected((int)substr($month,0,4) === $y)>
                        {{ $y }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="col-md-2">
            <select name="month_num" class="form-select">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}"
                        @selected((int)substr($month,5,2) === $m)>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
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
            <button class="btn btn-outline-secondary w-100">
                View
            </button>
        </div>

    </form>

    <a style="float: right;width:100px;" class="btn btn-outline-secondary" href="{{ route('admin.tools.tasks.admin.index') }}"> Back </a>
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
    <div class="col-md-4">
        <div class="customPanel" style="height: 400px;">
            <div class="card p-3">
                <h5 class="mb-3">Summary</h5>
                <div><strong>Done:</strong> {{ $done }}</div>
                <div><strong>Fail:</strong> {{ $fail }}</div>
                <div><strong>Delayed:</strong> {{ $delayed }}</div>
                <div><strong>Others:</strong> {{ $computedOther }}</div>
                <hr>
                <div><strong>Points:</strong> {{ $points }}</div>
                <div><strong>Productivity:</strong> {{ $productivity === null ? '-' : $productivity.'%' }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="customPanel" style="height: 400px;">
            <h5 class="mb-3">Counters by status</h5>

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

    <div class="col-md-4">
        <div class="customPanel" style="height: 400px;">
            <div class="p-3 mt-3 text-center">
                <h5 class="mb-3">Status distribution</h5>
                <canvas id="statusPie"  style="max-height:300px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-12" style="margin-top: 0;">
        <div class="customPanel">
            <h5 class="mb-3">Tasks assigned per user</h5>
        
            @if(empty($teamId))
                <div class="text-muted">Select a department to view users.</div>
            @elseif(empty($userStats))
                <div class="text-muted">No data.</div>
            @else
                <div class="row g-2">
                    @foreach($userStats as $name => $s)
                        @php
                            // optional: hide Unassigned if you don't want it
                            if ($name === 'Unassigned') continue;
        
                            $total = (int)($s['total'] ?? 0);
                            $done  = (int)($s['done'] ?? 0);
                            $fail  = (int)($s['fail'] ?? 0);
                            $del   = (int)($s['delayed'] ?? 0);
                            $other = (int)($s['other'] ?? 0);
        
                            $den = $done + $fail + $del;
                            $prod = $den > 0 ? round($done / $total * 100, 2) : null;
                        @endphp
        
                        <div class="col-md-4">
                            <div class="card p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="fw-bold">{{ $name }}</div>
                                </div>
        
                                <div class="mt-2 d-flex flex-wrap gap-2">
                                    <span class="badge bg-dark">  Assigned: {{ $total }} </span>
                                    <span class="badge bg-success">DONE: {{ $done }}</span>
                                    <span class="badge bg-danger">FAIL: {{ $fail }}</span>
                                    <span class="badge bg-warning text-dark">DELAYED: {{ $del }}</span>
                                    <span class="badge bg-secondary">OTHERS: {{ $other }}</span>
                                </div>
        
                                <div class="mt-2 text-muted small">
                                    Productivity ( done / total ):
                                    <strong>{{ $prod === null ? '—' : number_format($prod, 2).'% ' }}</strong>
                                </div>
        
                                @php
                                    $pctDoneTotal = $total > 0 ? round($done / $total * 100, 2) : 0;
                                @endphp
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

    <div class="col-md-4" style="margin-top: 0;">
        <div class="customPanel" style="height: 400px;">
            <div class="p-3 mt-3 text-center">
                <h5 class="mb-3">Percentage of DONE tasks by user</h5>
                @if(!$hasTeam)
                    <div class="text-muted">Select a department to view user distribution.</div>
                @elseif(empty($doneShareByUser))
                    <div class="text-muted">No DONE tasks in this period.</div>
                @else
                    <canvas id="doneByUserPie" style="max-height:300px;"></canvas>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8" style="margin-top: 0;">
        <div class="customPanel text-center" style="height: 400px;">
            <h5 class="mb-3">Breakdown per user (DONE / FAIL / DELAYED / OTHERS)</h5>
            @if(!$hasTeam)
                <div class="text-muted">Select a department to view user details.</div>
            @elseif(empty($userStats))
                <div class="text-muted">No data.</div>
            @else
                <canvas id="userMixStacked" style="height:350px; margin: 0 auto;"></canvas>
            @endif
        </div>
    </div>
    <div class="col-md-12" style="width: 100%; height: 20px;"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

{{-- PIE: status distribution --}}
<script>
(() => {
    const byStatus = @json($byStatus);
    const statusUi = @json($statusUi);

    const entries = Object.entries(byStatus || {})
        .filter(([_, v]) => Number(v) > 0);

    const canvas = document.getElementById('statusPie');
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

{{-- DOUGHNUT: done share by user --}}
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

    const canvas = document.getElementById('doneByUserPie');
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

{{-- STACKED BAR: mix by user (100%) --}}
<script>
(() => {
    const hasTeam = @json($hasTeam);
    const userStats = @json($userStats ?? []);

    if (!hasTeam) return;

    const entries = Object.entries(userStats || {});
    if (!entries.length) return;

    const labels = entries.map(([name]) => name);

    const donePct = entries.map(([_, s]) => s.total ? (s.done / s.total * 100).toFixed(2) : 0);
    const failPct = entries.map(([_, s]) => s.total ? (s.fail / s.total * 100).toFixed(2) : 0);
    const delayedPct = entries.map(([_, s]) => s.total ? (s.delayed / s.total * 100).toFixed(2) : 0);
    const otherPct = entries.map(([_, s]) => s.total ? (s.other / s.total * 100).toFixed(2) : 0);

    const canvas = document.getElementById('userMixStacked');
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
