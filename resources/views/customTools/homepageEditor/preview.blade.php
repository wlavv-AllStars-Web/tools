@extends('layouts.app')

@section('content')
<style>
    #yieldContent{ width:100% !important; margin:0 !important; }
    .hp-preview{ padding:16px; background:#f8fafc; }
    .hp-preview-toolbar{ display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:14px; padding:14px; border:1px solid rgba(148,163,184,.28); border-radius:5px; background:#fff; box-shadow:0 10px 28px rgba(15,23,42,.06); }
    .hp-title{ font-size:18px; font-weight:800; margin:0; color:#0f172a; }
    .hp-subtitle{ font-size:12px; color:#64748b; margin-top:2px; }
    .hp-actions{ display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end; }
    .hp-btn{ display:inline-flex; align-items:center; gap:7px; border-radius:5px; border:1px solid rgba(148,163,184,.35); background:#fff; color:#334155; padding:8px 12px; font-size:13px; font-weight:600; text-decoration:none; cursor:pointer; }
    .hp-btn:hover{ text-decoration:none; color:#0f172a; }
    .hp-btn-success{ border-color:#86efac; color:#166534; background:#f0fdf4; }
    .hp-shell{ border:1px solid rgba(148,163,184,.28); border-radius:5px; background:#111827; overflow:hidden; box-shadow:0 16px 34px rgba(15,23,42,.08); }
    .hp-front-frame{ max-width:1480px; margin:0 auto; background:#111; }
    .hp-mobile-frame{ width:390px; max-width:100%; margin:0 auto; background:#111; min-height:600px; }
    .hp-mock img,.hp-item img{ display:block; width:100%; height:auto; }
    .hp-section{ background:#111; }
    .hp-grid-2{ display:grid; grid-template-columns:repeat(2,1fr); gap:12px; padding:5px 0; }
    .hp-grid-3{ display:grid; grid-template-columns:repeat(3,1fr); gap:12px; padding:5px 0; }
    .hp-mobile-grid{ display:grid; grid-template-columns:1fr; gap:10px; padding:10px; }
    .hp-video-placeholder{ position:relative; background:#000; color:#fff; display:flex; align-items:center; justify-content:center; min-height:120px; }
    .hp-video-placeholder img{ opacity:.78; }
    .hp-video-placeholder span{ position:absolute; inset:auto; width:54px; height:54px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:rgba(239,68,68,.9); color:#fff; font-size:22px; }
    @media(max-width:768px){ .hp-preview-toolbar{ align-items:flex-start; flex-direction:column; } .hp-grid-2,.hp-grid-3{ grid-template-columns:1fr; } }
</style>

@php
    $isMobile = ($mode ?? 'desktop') === 'mobile';
    $group = $isMobile ? ($data['mobile'] ?? []) : null;
@endphp

<div class="hp-preview">
    <div class="hp-preview-toolbar">
        <div>
            <h1 class="hp-title">Preview Homepage</h1>
            <div class="hp-subtitle">{{ $isMobile ? 'Mobile' : 'Desktop' }} · idioma {{ strtoupper($lang ?? 'en') }} · dados temporários</div>
        </div>
        <div class="hp-actions">
            <a class="hp-btn" href="{{ route('marketing.homepage.index', ['mode' => $mode]) }}"><i class="fa-solid fa-angle-left"></i> Voltar</a>
            <a class="hp-btn" href="{{ route('marketing.homepage.preview', ['mode' => $mode, 'lang' => 'en']) }}">EN</a>
            <a class="hp-btn" href="{{ route('marketing.homepage.preview', ['mode' => $mode, 'lang' => 'es']) }}">ES</a>
            <a class="hp-btn" href="{{ route('marketing.homepage.preview', ['mode' => $mode, 'lang' => 'fr']) }}">FR</a>
            <form method="POST" action="{{ route('marketing.homepage.publish', ['mode' => $mode]) }}" style="margin:0" onsubmit="return confirm('Publicar a versão temporária para online?')">
                @csrf
                <button class="hp-btn hp-btn-success"><i class="fa-solid fa-upload"></i> Publicar</button>
            </form>
        </div>
    </div>

    <div class="hp-shell">
        <div class="{{ $isMobile ? 'hp-mobile-frame' : 'hp-front-frame' }}">
            <div class="hp-mock"><img src="/images/homepage/mock/homepage_header_mock.jpg" alt="Header"></div>

            @if($isMobile)
                <div class="hp-section hp-mobile-grid">
                    @foreach($data['mobile'] ?? [] as $item)
                        <div class="hp-item"><img src="{{ $item['image'] }}" alt="Mobile {{ $item['slot_id'] }}"></div>
                    @endforeach
                </div>
            @else
                <div class="hp-section">
                    @foreach($data['homepage'] ?? [] as $item)
                        <div class="hp-item"><img src="{{ $item['image'] }}" alt="Banner {{ $item['slot_id'] }}"></div>
                    @endforeach
                </div>

                <div class="hp-section hp-grid-2">
                    @foreach($data['half'] ?? [] as $item)
                        <div class="hp-item"><img src="{{ $item['image'] }}" alt="50% {{ $item['slot_id'] }}"></div>
                    @endforeach
                </div>

                <div class="hp-section hp-grid-3">
                    @foreach($data['third'] ?? [] as $item)
                        <div class="hp-item"><img src="{{ $item['image'] }}" alt="33% {{ $item['slot_id'] }}"></div>
                    @endforeach
                </div>

                <div class="hp-section hp-grid-3">
                    @foreach($data['videos'] ?? [] as $item)
                        <div class="hp-video-placeholder">
                            <img src="{{ $item['image'] }}" alt="Video {{ $item['slot_id'] }}">
                            <span><i class="fa-solid fa-play"></i></span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="hp-mock"><img src="/images/homepage/mock/homepage_footer_mock.jpg" alt="Footer"></div>
        </div>
    </div>
</div>
@endsection
