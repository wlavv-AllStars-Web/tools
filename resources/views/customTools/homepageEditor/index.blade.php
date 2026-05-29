@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<style>
    #yieldContent{ width:100% !important; margin:0 !important; }
    .homepage-editor{ --hp-border: rgba(148,163,184,.28); --hp-muted:#64748b; --hp-bg:#f8fafc; --hp-card:#ffffff; --hp-blue:#2563eb; padding:16px; }
    .hp-toolbar{ display:flex; justify-content:space-between; gap:12px; align-items:center; margin-bottom:14px; padding:14px; border:1px solid var(--hp-border); border-radius:5px; background:linear-gradient(180deg,#fff,#f8fafc); box-shadow:0 10px 28px rgba(15,23,42,.06); }
    .hp-toolbar-title{ font-size:18px; font-weight:700; margin:0; color:#0f172a; }
    .hp-toolbar-subtitle{ font-size:12px; color:var(--hp-muted); margin-top:2px; }
    .hp-actions{ display:flex; flex-wrap:wrap; gap:8px; align-items:center; justify-content:flex-end; }
    .hp-mode-switch{ display:flex; gap:6px; padding:4px; border:1px solid var(--hp-border); border-radius:5px; background:#fff; }
    .hp-lang-switch{ display:flex; gap:6px; padding:4px; border:1px solid var(--hp-border); border-radius:5px; background:#fff; }
    .hp-live-preview-note{ font-size:12px; color:#475569; padding:8px 10px; border:1px solid var(--hp-border); border-radius:5px; background:#fff; }
    .hp-btn{ display:inline-flex; align-items:center; gap:7px; border-radius:5px; border:1px solid var(--hp-border); background:#fff; color:#334155; padding:8px 12px; font-size:13px; font-weight:600; text-decoration:none; cursor:pointer; transition:.16s ease; }
    .hp-btn:hover{ transform:translateY(-1px); box-shadow:0 8px 20px rgba(15,23,42,.08); color:#0f172a; text-decoration:none; }
    .hp-btn.active{ background:#eff6ff; color:#1d4ed8; border-color:#93c5fd; }
    .hp-btn-success{ border-color:#86efac; color:#166534; background:#f0fdf4; }
    .hp-btn-success:disabled{ opacity:.45; cursor:not-allowed; transform:none; box-shadow:none; }
    .hp-btn-warning{ border-color:#fde68a; color:#92400e; background:#fffbeb; }
    .hp-shell{ border:1px solid var(--hp-border); border-radius:5px; overflow:hidden; background:#111827; box-shadow:0 16px 34px rgba(15,23,42,.08); }
    .hp-front-frame{ max-width:1480px; margin:0 auto; background:#111; }
    .hp-mobile-frame{ width:390px; max-width:100%; margin:0 auto; border-left:1px solid #263244; border-right:1px solid #263244; background:#111; min-height:600px; }
    .hp-mock img,.hp-item img{ display:block; width:100%; height:auto; }
    .hp-section{ margin:0; padding:0; background:#111; }
    .hp-grid-2{ display:grid; grid-template-columns:repeat(2,1fr); gap:12px; padding:5px 0; }
    .hp-grid-3{ display:grid; grid-template-columns:repeat(3,1fr); gap:12px; padding:5px 0; }
    .hp-mobile-grid{ display:grid; grid-template-columns:1fr; gap:10px; padding:10px; }
    .hp-item{ position:relative; border:1px solid transparent; background:#fff; padding:0; margin:0; cursor:pointer; transition:.16s ease; min-height:42px; }
    .hp-item:hover{ border-color:#ef4444; box-shadow:0 12px 24px rgba(239,68,68,.22); z-index:2; }
    .hp-item-badge{ position:absolute; top:8px; left:8px; z-index:3; border-radius:5px; background:rgba(15,23,42,.75); color:#fff; font-size:11px; font-weight:700; padding:4px 7px; backdrop-filter:blur(5px); }
    .hp-empty{ color:#cbd5e1; padding:30px; text-align:center; font-size:13px; border:1px dashed rgba(148,163,184,.35); border-radius:5px; margin:10px; }
    .slider-wrapper{ position:relative; overflow:hidden; width:100%; }
    .slider-track{ display:flex; transition:transform .35s ease; will-change:transform; }
    .slider-item{ flex:0 0 100%; }
    .slider-nav{ position:absolute; top:50%; transform:translateY(-50%); background:#fff; border:1px solid #e5e7eb; width:36px; height:36px; border-radius:50%; cursor:pointer; z-index:4; box-shadow:0 8px 18px rgba(15,23,42,.15); }
    .slider-nav.left{ left:8px; } .slider-nav.right{ right:8px; }
    .hp-alert{ margin-bottom:12px; padding:10px 12px; border-radius:5px; font-size:13px; border:1px solid #bbf7d0; background:#f0fdf4; color:#166534; }
    @media(max-width:768px){ .hp-toolbar{ align-items:flex-start; flex-direction:column; } .hp-actions{ width:100%; justify-content:flex-start; } .hp-grid-2,.hp-grid-3{ grid-template-columns:1fr; } }
</style>

@php
    $isMobile = ($mode ?? 'desktop') === 'mobile';
    $activeLang = $lang ?? 'en';
    $imageColumn = $imageColumn ?? ('image_' . $activeLang);
    $mockBase = $isMobile ? '/homepage/images/homepage/mock/mobile' : '/homepage/images/homepage/mock/desktop';
    $slotImage = function ($item, $fallback = null) use ($imageColumn, $mockBase) {
        $path = trim((string) ($item->{$imageColumn} ?? ''));

        if ($path === '') {
            $path = (string) ($fallback ?: $mockBase . '/placeholder.png');
        }

        $path = preg_replace('#^https?://resources\.allstars-group\.com#', '', $path);
        $path = str_replace('/uploads/homepage/uploads/', '/homepage/uploads/', $path);

        return rtrim((string) config('allstars.services.resources.base_url'), '/') . '/' . ltrim($path, '/');
    };
@endphp

<div class="homepage-editor">
    @if(session('success'))
        <div class="hp-alert"><i class="fa-solid fa-check"></i> {{ session('success') }}</div>
    @endif

    <div class="hp-toolbar">
        <div>
            <h1 class="hp-toolbar-title">Homepage Editor</h1>
        </div>

        <div class="hp-actions">
            <div class="hp-mode-switch">
                <a class="hp-btn {{ !$isMobile ? 'active' : '' }}" href="{{ route('marketing.homepage.index', ['mode' => 'desktop', 'lang' => $activeLang]) }}">
                    <i class="fa-solid fa-desktop"></i> Desktop
                </a>
                <a class="hp-btn {{ $isMobile ? 'active' : '' }}" href="{{ route('marketing.homepage.index', ['mode' => 'mobile', 'lang' => $activeLang]) }}">
                    <i class="fa-solid fa-mobile-screen-button"></i> Mobile
                </a>
            </div>

            <div class="hp-lang-switch">
                @foreach(($supportedLanguages ?? ['en' => 'EN', 'es' => 'ES', 'fr' => 'FR']) as $code => $label)
                    <a class="hp-btn {{ $activeLang === $code ? 'active' : '' }}" href="{{ route('marketing.homepage.index', ['mode' => $mode, 'lang' => $code]) }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <a class="hp-btn" href="{{ route('marketing.homepage.history') }}">
                <i class="fa-solid fa-clock-rotate-left"></i> Histórico
            </a>

            <form method="POST" action="{{ route('marketing.homepage.publish', ['mode' => $mode, 'lang' => $activeLang]) }}" style="margin:0" onsubmit="return confirm('Publicar a versão temporária completa para online? Isto publica Desktop e Mobile em conjunto.')">
                @csrf
                <button type="submit" class="hp-btn hp-btn-success" {{ $hasChanges ? '' : 'disabled' }}>
                    <i class="fa-solid fa-upload"></i>
                    {{ $hasChanges ? 'Publicar Desktop + Mobile' : 'Sem alterações' }}
                </button>
            </form>
        </div>
    </div>

    <div class="hp-shell">
        <div class="{{ $isMobile ? 'hp-mobile-frame' : 'hp-front-frame' }}">
            <div class="hp-mock">
                <img src="{{ $headerImage }}" alt="Header {{ strtoupper($activeLang) }}">
            </div>

            @if($isMobile)
                <div class="hp-section hp-mobile-grid">
                    @forelse($mobile as $item)
                        <div class="hp-item" onclick="openModal({{ $item->id }})">
                            <img src="{{ $slotImage($item, '/images/homepage/mock/mobile/placeholder.png') }}" alt="Mobile slot {{ $item->slot_id }}">
                        </div>
                    @empty
                        <div class="hp-empty">Não existem slots mobile configurados. Cria linhas com <strong>icon_type = 5</strong> na tabela <strong>homepage_asm_temp</strong>.</div>
                    @endforelse
                </div>
            @else
                <div class="hp-section">
                    <div class="slider-wrapper">
                        <button class="slider-nav left" type="button" onclick="moveSlider(-1)">‹</button>
                        <div class="slider-track" id="sliderTrack">
                            @forelse($sliders as $item)
                                <div class="slider-item hp-item" onclick="openModal({{ $item->id }})">
                                    <img src="{{ $slotImage($item, '/images/homepage/mock/desktop/placeholder.png') }}" alt="Banner {{ $item->slot_id }}">
                                </div>
                            @empty
                                <div class="hp-empty">Sem banners.</div>
                            @endforelse
                        </div>
                        <button class="slider-nav right" type="button" onclick="moveSlider(1)">›</button>
                    </div>
                </div>

                <div class="hp-section">
                    <div class="hp-grid-2">
                        @foreach($half as $item)
                            <div class="hp-item" onclick="openModal({{ $item->id }})">
                                <img src="{{ $slotImage($item, '/images/homepage/mock/desktop/placeholder.png') }}" alt="50% slot {{ $item->slot_id }}">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="hp-section">
                    <div class="hp-grid-3">
                        @foreach($third as $item)
                            <div class="hp-item" onclick="openModal({{ $item->id }})">
                                <img src="{{ $slotImage($item, '/images/homepage/mock/desktop/placeholder.png') }}" alt="33% slot {{ $item->slot_id }}">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="hp-section">
                    <div class="hp-grid-3">
                        @foreach($videos as $item)
                            <div class="hp-item" onclick="openModal({{ $item->id }})">
                                <img src="{{ $slotImage($item, '/images/homepage/mock/desktop/placeholder.png') }}" alt="Video slot {{ $item->slot_id }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="hp-mock">
                <img src="{{ $footerImage }}" alt="Footer {{ strtoupper($activeLang) }}">
            </div>
        </div>
    </div>

    @include('customTools.homepageEditor.modal')
</div>

<script>
const BASE_URL = "{{ url('/marketing/customTools/homepage') }}";
const CSRF_TOKEN = "{{ csrf_token() }}";
let choicesInstances = {};
let sliderIndex = 0;

function openModal(id){
    resetModal();

    fetch(BASE_URL + '/ASM/homepage/slot/' + id)
        .then(r => r.json())
        .then(d => {
            if(!d.ok){ alert(d.message || 'Slot não encontrado.'); return; }

            document.getElementById('slot_id').value = d.id;
            document.getElementById('modal_slot_label').innerText = '#' + d.slot_id + ' · icon_type ' + d.icon_type;
            document.getElementById('type_selector').value = d.type || '';
            toggleType(d.type || '');

            setVal('manufacturer_select', d.value_id);
            setVal('category_select', d.value_id);
            setVal('compat_select', d.value_id);
            setVal('youtube_code', d.type === 'video' ? d.value_id : '');

            setImageLabel('en', d.image_en);
            setImageLabel('es', d.image_es);
            setImageLabel('fr', d.image_fr);

            initChoices();
            document.getElementById('cmsModalOverlay').style.display='flex';
        });
}

function resetModal(){
    destroyChoices();
    setVal('type_selector', '');
    ['manufacturer_box','category_box','compat_box','video_box'].forEach(id => {
        const el = document.getElementById(id); if(el) el.style.display = 'none';
    });
    ['manufacturer_select','category_select','compat_select','youtube_code'].forEach(id => setVal(id, ''));
    ['en','es','fr'].forEach(lang => setImageLabel(lang, null));
}

function setVal(id, val){ const el = document.getElementById(id); if(el) el.value = val || ''; }
function setImageLabel(lang, path){
    const label = document.getElementById('file_' + lang);
    if(label) label.innerText = path ? path.split('/').pop() : 'Click or drop';
}

function toggleType(t){
    ['manufacturer_box','category_box','compat_box','video_box'].forEach(id => {
        const el = document.getElementById(id); if(el) el.style.display = 'none';
    });
    const map = { manufacturer:'manufacturer_box', category:'category_box', compat:'compat_box', video:'video_box' };
    if(map[t]) document.getElementById(map[t]).style.display = 'block';
}

function closeModal(){ document.getElementById('cmsModalOverlay').style.display='none'; }

function saveBlock(){
    const type = document.getElementById('type_selector').value;
    if(!type){ alert('Seleciona o tipo de destino.'); return; }

    const fd = new FormData();
    fd.append('slot_id', document.getElementById('slot_id').value);
    fd.append('type', type);

    let value = '';
    if(type === 'manufacturer') value = document.getElementById('manufacturer_select').value;
    if(type === 'category') value = document.getElementById('category_select').value;
    if(type === 'compat') value = document.getElementById('compat_select').value;
    if(type === 'video') value = document.getElementById('youtube_code').value;

    fd.append('value_id', value || '0');
    fd.append('youtube_code', type === 'video' ? (value || '') : '');

    ['en','es','fr'].forEach(lang => {
        const input = document.getElementById('image_' + lang);
        if(input && input.files && input.files[0]) fd.append('image_' + lang, input.files[0]);
    });

    fetch(BASE_URL + '/ASM/homepage/slot/save', { method:'POST', headers:{'X-CSRF-TOKEN': CSRF_TOKEN}, body: fd })
        .then(async r => ({ ok: r.ok, data: await r.json() }))
        .then(({ok, data}) => {
            if(!ok || !data.ok){ console.error(data); alert('Não foi possível guardar. Valida os dados.'); return; }
            location.reload();
        });
}

function destroyChoices(){
    Object.keys(choicesInstances).forEach(id => { choicesInstances[id].destroy(); delete choicesInstances[id]; });
}

function initChoices(){
    ['type_selector','manufacturer_select','category_select','compat_select'].forEach(id => {
        const el = document.getElementById(id); if(!el) return;
        choicesInstances[id] = new Choices(el, { searchEnabled:true, shouldSort:false, itemSelectText:'', allowHTML:false });
    });
}

function triggerFile(lang){ const el = document.getElementById('image_' + lang); if(el) el.click(); }
function previewFile(input, lang){ if(input && input.files && input.files[0]) setImageLabel(lang, input.files[0].name); }

function moveSlider(dir){
    const track = document.getElementById('sliderTrack'); if(!track) return;
    const total = track.querySelectorAll('.slider-item').length;
    sliderIndex += dir;
    if(sliderIndex < 0) sliderIndex = 0;
    if(sliderIndex > total - 1) sliderIndex = total - 1;
    track.style.transform = `translateX(-${sliderIndex * 100}%)`;
}
</script>
@endsection
