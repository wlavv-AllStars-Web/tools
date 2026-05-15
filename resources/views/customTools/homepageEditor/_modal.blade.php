<div id="cmsModalOverlay" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center">

<div style="width:760px;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 30px 80px rgba(0,0,0,0.25)">
<div style="padding:18px;display:flex;flex-direction:column;gap:12px">

<input type="hidden" id="slot_id">

<label>Type</label>
<select id="type_selector" onchange="toggleType(this.value)">
    <option value="">Select type</option>
    <option value="manufacturer">Manufacturer</option>
    <option value="category">Category</option>
    <option value="compat">Compatibility</option>
    <option value="video">Video</option>
</select>

<div id="manufacturer_box" style="display:none">
<select id="manufacturer_select">
@foreach($manufacturers as $m)
<option value="{{ $m->id }}">{{ $m->name }}</option>
@endforeach
</select>
</div>

<div id="category_box" style="display:none">
<select id="category_select">
@foreach($categories as $c)
<option value="{{ $c->id }}">{{ $c->name }}</option>
@endforeach
</select>
</div>

<div id="compat_box" style="display:none">
<select id="compat_select">
@foreach($compats as $c)
<option value="{{ $c->id }}">{{ $c->name }}</option>
@endforeach
</select>
</div>

<div id="video_box" style="display:none">
<input id="youtube_code" placeholder="YouTube code">
</div>

<hr>

<!-- UPLOAD AREA (SHOPIFY STYLE) -->
<div style="border-top:1px solid #eee;padding-top:12px">

<div style="border-top:1px solid #eee;padding-top:12px">

<div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:10px">
Images
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">

<!-- EN -->
<div class="dropzone" onclick="triggerFile('en')">
    <input type="file" id="image_en" hidden onchange="previewFile(this,'en')">
    <div class="dz-inner">
        <div class="dz-text"><label>🇬🇧</label></div>
        <div class="dz-file" id="file_en">Click or drop</div>
    </div>
</div>

<!-- ES -->
<div class="dropzone" onclick="triggerFile('es')">
    <input type="file" id="image_es" hidden onchange="previewFile(this,'es')">
    <div class="dz-inner">
        <div class="dz-text"><label>🇪🇸</label></div>
        <div class="dz-file" id="file_es">Click or drop</div>
    </div>
</div>

<!-- FR -->
<div class="dropzone" onclick="triggerFile('fr')">
    <input type="file" id="image_fr" hidden onchange="previewFile(this,'fr')">
    <div class="dz-inner">
        <div class="dz-text"><label>🇫🇷</label></div>
        <div class="dz-file" id="file_fr">Click or drop</div>
    </div>
</div>

</div>

</div>

</div>

</div>

<!-- FOOTER -->
<div style="display:flex;justify-content:flex-end;gap:10px;padding:14px 18px;border-top:1px solid #eee;background:#fafafa">

<button onclick="closeModal()" style="padding:8px 14px;border-radius:8px;border:none;background:#e5e7eb">Cancel</button>

<button onclick="saveBlock()" style="padding:8px 14px;border-radius:8px;border:none;background:#2563eb;color:#fff">
Save
</button>

</div>

</div>

<style>
.dropzone{
    border:1px dashed #cbd5e1;
    border-radius:12px;
    background:#f8fafc;
    padding:14px;
    cursor:pointer;
    transition:all .2s ease;
    text-align:center;
    position:relative;
}

.dropzone:hover{
    border-color:#3b82f6;
    background:#eff6ff;
    transform:translateY(-2px);
}

.dz-inner{
    display:flex;
    flex-direction:column;
    gap:6px;
    align-items:center;
    justify-content:center;
}

.dz-icon{
    font-size:18px;
    color:#3b82f6;
}

.dz-text{
    font-size:90px;
    font-weight:600;
    color:#334155;
}

.dz-file{
    font-size:11px;
    color:#94a3b8;
}    
</style>

<script>
    
function triggerFile(lang){
    const el = document.getElementById('image_'+lang);
    if(el) el.click();
}

function previewFile(input, lang){

    if(!input || !input.files || !input.files[0]) return;

    const file = input.files[0];

    const label = document.getElementById('file_'+lang);
    if(label) label.innerText = file.name;

}

</script>
</div>