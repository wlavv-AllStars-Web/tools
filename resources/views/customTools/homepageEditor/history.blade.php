@extends('layouts.app')

@section('content')
<style>
    .hp-history{ padding:16px; }
    .hp-card{ border:1px solid rgba(148,163,184,.28); border-radius:5px; background:#fff; box-shadow:0 10px 28px rgba(15,23,42,.06); overflow:hidden; }
    .hp-card-header{ display:flex; justify-content:space-between; align-items:center; gap:12px; padding:14px 16px; background:linear-gradient(180deg,#fff,#f8fafc); border-bottom:1px solid rgba(148,163,184,.24); }
    .hp-title{ margin:0; font-size:18px; font-weight:800; color:#0f172a; }
    .hp-btn{ display:inline-flex; align-items:center; gap:7px; border-radius:5px; border:1px solid rgba(148,163,184,.35); background:#fff; color:#334155; padding:8px 12px; font-size:13px; font-weight:600; text-decoration:none; }
    .hp-btn:hover{ text-decoration:none; color:#0f172a; }
    .hp-table{ width:100%; margin:0; }
    .hp-table th{ font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:#64748b; background:#f8fafc; padding:10px 12px; border-bottom:1px solid rgba(148,163,184,.24); }
    .hp-table td{ padding:10px 12px; border-bottom:1px solid rgba(148,163,184,.18); vertical-align:middle; }
</style>

<div class="hp-history">
    <div class="hp-card">
        <div class="hp-card-header">
            <h1 class="hp-title">Histórico Homepage</h1>
            <a class="hp-btn" href="{{ route('marketing.homepage.index') }}"><i class="fa-solid fa-angle-left"></i> Voltar</a>
        </div>

        <table class="hp-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Publicado em</th>
                    <th>Publicado por</th>
                    <th>Notas</th>
                    <th style="width:130px">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>#{{ $log->id }}</td>
                        <td>{{ $log->published_at }}</td>
                        <td>{{ $log->published_by ?: '-' }}</td>
                        <td>{{ $log->notes ?: '-' }}</td>
                        <td>
                            <a class="hp-btn" href="{{ route('marketing.homepage.restore', $log->id) }}" onclick="return confirm('Restaurar esta publicação para temporário?')">
                                <i class="fa-solid fa-clock-rotate-left"></i> Restaurar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Ainda não existem publicações.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
