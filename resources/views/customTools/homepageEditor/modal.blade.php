<div id="cmsModalOverlay" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.58);backdrop-filter:blur(7px);z-index:9999;align-items:center;justify-content:center;padding:18px">
    <div class="hp-modal-card">
        <div class="hp-modal-header">
            <div>
                <div class="hp-modal-title">Editar bloco da homepage</div>
                <div id="modal_slot_label" class="hp-modal-subtitle">Slot</div>
            </div>
            <button type="button" class="hp-modal-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="hp-modal-body">
            <input type="hidden" id="slot_id">

            <div class="hp-field">
                <label>Destino</label>
                <select id="type_selector" onchange="toggleType(this.value)">
                    <option value="">Selecionar destino</option>
                    <option value="manufacturer">Manufacturer</option>
                    <option value="category">Category</option>
                    <option value="compat">Compatibility</option>
                    <option value="video">Video</option>
                </select>
            </div>

            <div id="manufacturer_box" class="hp-field" style="display:none">
                <label>Manufacturer</label>
                <select id="manufacturer_select">
                    <option value="">Selecionar manufacturer</option>
                    @foreach($manufacturers as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="category_box" class="hp-field" style="display:none">
                <label>Category</label>
                <select id="category_select">
                    <option value="">Selecionar categoria</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="compat_box" class="hp-field" style="display:none">
                <label>Compatibility</label>
                <select id="compat_select">
                    <option value="">Selecionar compatibilidade</option>
                    @foreach($compats as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="video_box" class="hp-field" style="display:none">
                <label>YouTube code</label>
                <input id="youtube_code" type="text" placeholder="Ex: eMt0L9qXenU">
            </div>

            <div class="hp-upload-title">Imagens por idioma</div>
            <div class="hp-upload-grid">
                <div class="hp-dropzone" onclick="triggerFile('en')">
                    <input type="file" id="image_en" hidden onchange="previewFile(this,'en')" accept="image/*">
                    <div class="hp-flag">🇬🇧</div>
                    <div class="hp-drop-label" id="file_en">Click or drop</div>
                </div>

                <div class="hp-dropzone" onclick="triggerFile('es')">
                    <input type="file" id="image_es" hidden onchange="previewFile(this,'es')" accept="image/*">
                    <div class="hp-flag">🇪🇸</div>
                    <div class="hp-drop-label" id="file_es">Click or drop</div>
                </div>

                <div class="hp-dropzone" onclick="triggerFile('fr')">
                    <input type="file" id="image_fr" hidden onchange="previewFile(this,'fr')" accept="image/*">
                    <div class="hp-flag">🇫🇷</div>
                    <div class="hp-drop-label" id="file_fr">Click or drop</div>
                </div>
            </div>
        </div>

        <div class="hp-modal-footer">
            <button type="button" class="hp-btn" onclick="closeModal()"><i class="fa-solid fa-angle-left"></i> Cancelar</button>
            <button type="button" class="hp-btn hp-btn-success" onclick="saveBlock()"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
        </div>
    </div>
</div>

<style>
    .hp-modal-card{ width:780px; max-width:96vw; max-height:92vh; overflow:auto; background:#fff; border:1px solid rgba(148,163,184,.30); border-radius:5px; box-shadow:0 30px 80px rgba(0,0,0,.25); }
    .hp-modal-header{ display:flex; justify-content:space-between; gap:12px; align-items:center; padding:16px 18px; border-bottom:1px solid rgba(148,163,184,.24); background:linear-gradient(180deg,#fff,#f8fafc); }
    .hp-modal-title{ font-weight:800; font-size:16px; color:#0f172a; }
    .hp-modal-subtitle{ font-size:12px; color:#64748b; margin-top:2px; }
    .hp-modal-close{ width:34px; height:34px; border-radius:5px; border:1px solid rgba(148,163,184,.35); background:#fff; color:#334155; }
    .hp-modal-body{ padding:18px; display:flex; flex-direction:column; gap:14px; }
    .hp-field label{ display:block; font-size:12px; font-weight:800; color:#475569; text-transform:uppercase; letter-spacing:.04em; margin-bottom:7px; }
    .hp-field select,.hp-field input{ width:100%; border:1px solid rgba(148,163,184,.38); border-radius:5px; min-height:40px; padding:8px 10px; background:#fff; }
    .hp-upload-title{ margin-top:4px; padding-top:14px; border-top:1px solid rgba(148,163,184,.24); font-size:12px; font-weight:800; color:#475569; text-transform:uppercase; letter-spacing:.04em; }
    .hp-upload-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
    .hp-dropzone{ border:1px dashed #cbd5e1; border-radius:5px; background:#f8fafc; padding:16px 10px; cursor:pointer; transition:.16s ease; text-align:center; min-height:132px; display:flex; flex-direction:column; align-items:center; justify-content:center; }
    .hp-dropzone:hover{ border-color:#3b82f6; background:#eff6ff; transform:translateY(-1px); }
    .hp-flag{ font-size:48px; line-height:1; margin-bottom:10px; }
    .hp-drop-label{ font-size:11px; color:#64748b; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .hp-modal-footer{ display:flex; justify-content:flex-end; gap:10px; padding:14px 18px; border-top:1px solid rgba(148,163,184,.24); background:#f8fafc; }
    .choices__inner{ border-radius:5px !important; min-height:40px !important; border-color:rgba(148,163,184,.38) !important; background:#fff !important; }
    @media(max-width:640px){ .hp-upload-grid{ grid-template-columns:1fr; } }
</style>
