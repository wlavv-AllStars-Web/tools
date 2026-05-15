@extends('layouts.app')
@section('content')
    <style>
        .card{background:#fff;border-radius:5px;padding:20px;box-shadow:0 4px 18px rgba(0,0,0,.08);margin-bottom:20px}
        h1,h2,h3{margin-top:0}
        textarea{min-width:95%;max-width:100%;min-height:110px;border:1px solid #d7dde5;border-radius:10px;padding:12px;font-family:monospace;font-size:14px}
        button{background:#0d6efd;color:#fff;border:0;padding:12px 18px;border-radius:10px;cursor:pointer}
        button:hover{opacity:.94}
        .muted{color:#666}
        table{width:100%;border-collapse:collapse}
        th,td{border:1px solid #e5e7eb;padding:10px;vertical-align:top;text-align:left}
        th{background:#eef2f6;position:sticky;top:0;z-index:1}
        .same td{background:#eefaf0}
        .similar td{background:#fff8e6}
        .different td{background:#fff1f1}
        .left_only td{background:#eef6ff}
        .right_only td{background:#f7f0ff}
        .row-hidden{display:none}
        .pill{display:inline-block;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:bold;margin-right:8px;margin-bottom:8px;border:1px solid transparent;cursor:pointer;user-select:none}
        .pill.active{outline:2px solid rgba(0,0,0,.12);transform:translateY(-1px)}
        .pill-same{background:#dff3e4;color:#146c2e}
        .pill-similar{background:#fff1cc;color:#9a6700}
        .pill-different{background:#ffd9d9;color:#a61b1b}
        .pill-left{background:#dcecff;color:#0b57a4}
        .pill-right{background:#eadcff;color:#6b21a8}
        .pill-all{background:#eef2f6;color:#344054}
        .mono{font-family:monospace}
        .table-wrap{overflow:auto;max-height:74vh;border:1px solid #e5e7eb;border-radius:12px}
        .lang-badge{display:inline-flex;align-items:center;gap:10px;font-size:50px;font-weight:700}
        .collapse-card{border:1px solid #e5e7eb;border-radius:14px;background:#fff;overflow:hidden}
        .collapse-card summary{list-style:none;cursor:pointer;padding:10px;display:flex;justify-content:space-between;align-items:center;gap:12px;background:#fff}
        .collapse-card summary::-webkit-details-marker{display:none}
        .collapse-card[open] summary{border-bottom:1px solid #e5e7eb}
        .collapse-content{padding:20px 10px}
        .summary-right{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end}
        .url-cell a{word-break:break-all}
        .url-label{display:block;font-size:11px;color:#667085;margin-bottom:4px;text-transform:uppercase;letter-spacing:.04em}
        .small{font-size:12px}
        .field-col{white-space:nowrap;font-weight:bold}
        .top-note{margin-top:10px;font-size:12px;color:#667085}
        @media (max-width:768px){
            body{padding:14px}
            .card{padding:16px}
            .collapse-card summary{padding:14px}
            .collapse-content{padding:14px}
            .lang-badge{font-size:20px}
            .summary-right{justify-content:flex-start}
        }
    </style>

    <div class="wrap" style="margin-top: 10px;">
        <div class="card" style="text-align:center;">
            <h1>Comparador SEO</h1>
            <p class="muted">Formato: <span class="mono">lang|url_old|url_new</span></p>

            <form method="post" action="{{ route('site-seo-compare.compare') }}">
                @csrf
                <textarea name="pairs" style="text-align:center;" placeholder="pt|https://site-antigo.com/pt/pagina-a|https://site-novo.com/pt/pagina-a
en|https://site-antigo.com/en/page-a|https://site-novo.com/en/page-a
es|https://site-antigo.com/es/pagina-a|https://site-novo.com/es/pagina-a">{{ old('pairs', $input ?? '') }}</textarea>
                <br><br>
                <button type="submit">Comparar SEO avançado</button>
            </form>
        </div>

        @foreach($results as $idx => $result)
            @php
                $summary = $result['summary'] ?? [];
                $panelId = 'seo-advanced-panel-' . $idx;
            @endphp

            <details id="{{ $panelId }}" class="collapse-card card" {{ $idx === 0 ? 'open' : '' }}>
                <summary>
                    <div class="lang-badge">
                        <span>{{ $result['flag'] }}</span>
                    </div>

                    <div class="summary-right">
                        <span class="pill pill-all active" data-filter="all">todos</span>
                        <span class="pill pill-same" data-filter="same">iguais: {{ $summary['same'] ?? 0 }}</span>
                        <span class="pill pill-similar" data-filter="similar">parecidos: {{ $summary['similar'] ?? 0 }}</span>
                        <span class="pill pill-different" data-filter="different">diferentes: {{ $summary['different'] ?? 0 }}</span>
                        <span class="pill pill-left" data-filter="left_only">só old: {{ $summary['left_only'] ?? 0 }}</span>
                        <span class="pill pill-right" data-filter="right_only">só new: {{ $summary['right_only'] ?? 0 }}</span>
                    </div>
                </summary>

                <div class="collapse-content">
                    @if(!empty($result['error']))
                        <div style="color:#b42318;font-weight:bold;">{{ $result['error'] }}</div>
                    @else
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width:18%">Campo SEO</th>
                                        <th style="width:38%">Old</th>
                                        <th style="width:6%">%</th>
                                        <th style="width:38%">New</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="compare-row compare-row-url" data-type="url">
                                        <td class="field-col">URL</td>
                                        <td class="url-cell">
                                            <span class="url-label">Old URL</span>
                                            <a href="{{ $result['url_old'] }}" target="_blank">{{ $result['url_old'] }}</a>
                                        </td>
                                        <td class="small" style="text-align:center;">—</td>
                                        <td class="url-cell">
                                            <span class="url-label">New URL</span>
                                            <a href="{{ $result['url_new'] }}" target="_blank">{{ $result['url_new'] }}</a>
                                        </td>
                                    </tr>

                                    @foreach($result['rows'] as $row)
                                        <tr class="compare-row {{ $row['type'] }}" data-type="{{ $row['type'] }}">
                                            <td class="field-col">{{ $row['field'] }}</td>
                                            <td>{!! nl2br(e($row['left'])) !!}</td>
                                            <td class="small" style="text-align:center;">{{ $row['score'] }}</td>
                                            <td>{!! nl2br(e($row['right'])) !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </details>
        @endforeach
    </div>

    <script>
        (function(){
            const detailsList = document.querySelectorAll(".collapse-card");

            detailsList.forEach(function(details){
                const pills = details.querySelectorAll(".pill[data-filter]");

                pills.forEach(function(pill){
                    pill.addEventListener("click", function(ev){
                        ev.preventDefault();
                        ev.stopPropagation();

                        if (!details.open) {
                            details.open = true;
                        }

                        const filter = pill.getAttribute("data-filter");
                        const rows = details.querySelectorAll("tbody .compare-row");

                        pills.forEach(function(p){ p.classList.remove("active"); });
                        pill.classList.add("active");

                        rows.forEach(function(row){
                            if (row.classList.contains("compare-row-url")) {
                                row.classList.remove("row-hidden");
                                return;
                            }

                            const rowType = row.getAttribute("data-type");

                            if (filter === "all" || rowType === filter) {
                                row.classList.remove("row-hidden");
                            } else {
                                row.classList.add("row-hidden");
                            }
                        });
                    });
                });
            });
        })();
    </script>
@endsection
