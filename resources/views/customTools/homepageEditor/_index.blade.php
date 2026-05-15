@extends('layouts.app')
@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<style>
.cms-section{
    margin:0;
    padding:0;
    background:#111;
}

.cms-item{
    border: 1ps solid #111;
    background:#fff;
    padding:0;
    margin:0;
    cursor:pointer;
    transition:all .15s ease;
}

.cms-item:hover{
    //transform:translateY(-2px);
    border: 1px solid red;
    box-shadow:0 8px 20px rgba(0,0,0,0.08);
}

.grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
.grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}

img{width:100%}

.slider-wrapper{
    position:relative;
    overflow:hidden;
    width:100%;
}

.slider-track{
    display:flex;
    transition:transform .35s ease;
    will-change:transform;
}

.slider-item{
    flex:0 0 100%;
}

.slider-nav{
    position:absolute;
    top:50%;
    transform:translateY(-50%);
    background:#fff;
    border:1px solid #e5e7eb;
    width:36px;
    height:36px;
    border-radius:50%;
    cursor:pointer;
    z-index:2;
}
.slider-nav.left{left:8px}
.slider-nav.right{right:8px}
</style>

<script>
const BASE_URL = "{{ url('/marketing/customTools/homepage') }}";
let choicesInstances = {};
let sliderIndex = 0;
</script>

<div class="container-fluid" style="margin-top:20px;">

<!-- HEADER -->
<div>
    <img src="/images/homepage/mock/homepage_header_mock.jpg">
</div>

<!-- SLIDERS -->
<div class="cms-section">
    <div class="slider-wrapper">

        <button class="slider-nav left" onclick="moveSlider(-1)">‹</button>

        <div class="slider-track" id="sliderTrack">
            @foreach($sliders as $item)
            <div class="slider-item cms-item" onclick="openModal({{ $item->id }})">
                <img src="{{ $item->image_en }}">
            </div>
            @endforeach
        </div>

        <button class="slider-nav right" onclick="moveSlider(1)">›</button>

    </div>
</div>

<!-- 50% -->
<div class="cms-section">
    <div class="grid-2">
        @foreach($half as $item)
        <div class="cms-item" onclick="openModal({{ $item->id }})" style="margin: 5px 0;">
            <img src="{{ $item->image_en }}">
        </div>
        @endforeach
    </div>
</div>

<!-- 33% -->
<div class="cms-section">
    <div class="grid-3">
        @foreach($third as $item)
        <div class="cms-item" onclick="openModal({{ $item->id }})" style="margin: 5px 0;">
            <img src="{{ $item->image_en }}">
        </div>
        @endforeach
    </div>
</div>

<!-- VIDEOS -->
<div class="cms-section">
    <div class="grid-3">
        @foreach($videos as $item)
        <div class="cms-item" onclick="openModal({{ $item->id }})" style="margin: 5px 0;">
            <img src="{{ $item->image_en }}">
        </div>
        @endforeach
    </div>
</div>

<!-- FOOTER -->
<div>
    <img src="/images/homepage/mock/homepage_footer_mock.jpg">
</div>

@include('customTools.homepageEditor.modal')

</div>

<script>

/* =======================
   MODAL OPEN
======================= */
function openModal(id){

    resetModal();

    fetch(BASE_URL+'/homepage/slot/'+id)
    .then(r=>r.json())
    .then(d=>{

        document.getElementById('slot_id').value = id;
        document.getElementById('type_selector').value = d.type || '';

        toggleType(d.type || '');

        setVal('manufacturer_select', d.value_id);
        setVal('category_select', d.value_id);
        setVal('compat_select', d.value_id);

        initChoices();

        document.getElementById('cmsModalOverlay').style.display='flex';

    });

}

/* =======================
   RESET MODAL (FIXED)
======================= */
function resetModal(){

    const type = document.getElementById('type_selector');
    if(type) type.value = '';

    ['manufacturer_box','category_box','compat_box','video_box']
    .forEach(i=>{
        const el=document.getElementById(i);
        if(el) el.style.display='none';
    });

    const yt = document.getElementById('youtube_code');
    if(yt) yt.value='';

    ['manufacturer_select','category_select','compat_select']
    .forEach(id=>{
        const el=document.getElementById(id);
        if(el) el.value='';
    });

    ['en','es','fr'].forEach(l=>{
        const f=document.getElementById('file_'+l);
        if(f) f.innerText='Click or drop';
    });

}

/* =======================
   SET VALUE
======================= */
function setVal(id,val){
    const el=document.getElementById(id);
    if(el) el.value = val || '';
}

/* =======================
   TOGGLE TYPE
======================= */
function toggleType(t){

    ['manufacturer_box','category_box','compat_box','video_box']
    .forEach(i=>{
        const el=document.getElementById(i);
        if(el) el.style.display='none';
    });

    const map = {
        manufacturer: 'manufacturer_box',
        category: 'category_box',
        compat: 'compat_box',
        video: 'video_box'
    };

    if(map[t]){
        const el=document.getElementById(map[t]);
        if(el) el.style.display='block';
    }

}

/* =======================
   CLOSE MODAL
======================= */
function closeModal(){
    document.getElementById('cmsModalOverlay').style.display='none';
}

/* =======================
   SAVE
======================= */
function saveBlock(){

    let fd = new FormData();

    fd.append('slot_id', document.getElementById('slot_id').value);
    fd.append('type', document.getElementById('type_selector').value);

    let t = document.getElementById('type_selector').value;

    let v = null;
    if(t==='manufacturer') v=document.getElementById('manufacturer_select').value;
    if(t==='category') v=document.getElementById('category_select').value;
    if(t==='compat') v=document.getElementById('compat_select').value;

    fd.append('value_id', v);
    fd.append('youtube_code', document.getElementById('youtube_code').value || '');

    ['en','es','fr'].forEach(l => {

        const input = document.getElementById('image_' + l);

        if(!input || !input.files || !input.files[0]) return;

        fd.append('image_'+l, input.files[0]);

    });

    fetch(BASE_URL+'/homepage/slot/save',{
        method:'POST',
        headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body:fd
    }).then(()=>location.reload());

}

/* =======================
   CHOICES
======================= */
function initChoices(){

    const selects = [
        'type_selector',
        'manufacturer_select',
        'category_select',
        'compat_select'
    ];

    selects.forEach(id=>{

        const el=document.getElementById(id);
        if(!el) return;

        if(choicesInstances[id]){
            choicesInstances[id].destroy();
            delete choicesInstances[id];
        }

        choicesInstances[id] = new Choices(el,{
            searchEnabled:true,
            shouldSort:false,
            itemSelectText:'',
            allowHTML:false
        });

    });

}

/* =======================
   SLIDER
======================= */
function moveSlider(dir){

    const track = document.getElementById('sliderTrack');
    if(!track) return;

    const items = track.querySelectorAll('.slider-item');
    const total = items.length;

    sliderIndex += dir;

    if(sliderIndex < 0) sliderIndex = 0;
    if(sliderIndex > total - 1) sliderIndex = total - 1;

    track.style.transform = `translateX(-${sliderIndex * 100}%)`;
}

</script>

@endsection