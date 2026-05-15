@php
    $project = $project ?? null;
@endphp

<div class="">
    <div class="">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Título da alteração / Nome do projeto</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $project->title ?? '') }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Área</label>
                <select name="area" class="form-control" required>
                    <option value="frontoffice" {{ old('area', $project->area ?? '') === 'frontoffice' ? 'selected' : '' }}>Frontoffice</option>
                    <option value="backoffice" {{ old('area', $project->area ?? '') === 'backoffice' ? 'selected' : '' }}>Backoffice</option>
                    <option value="both" {{ old('area', $project->area ?? '') === 'both' ? 'selected' : '' }}>Ambos</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select name="status" class="form-control" required>
                    <option value="planned" {{ old('status', $project->status ?? 'planned') === 'planned' ? 'selected' : '' }}>Planned</option>
                    <option value="in_progress" {{ old('status', $project->status ?? '') === 'in_progress' ? 'selected' : '' }}>In progress</option>
                    <option value="done" {{ old('status', $project->status ?? '') === 'done' ? 'selected' : '' }}>Done</option>
                    <option value="on_hold" {{ old('status', $project->status ?? '') === 'on_hold' ? 'selected' : '' }}>On hold</option>
                    <option value="cancelled" {{ old('status', $project->status ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Quem pediu a alteração</label>
                <input type="text" name="requested_by" class="form-control" value="{{ old('requested_by', $project->requested_by ?? '') }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Data da alteração</label>
                <input type="date" name="change_date" class="form-control" value="{{ old('change_date', isset($project->change_date) ? $project->change_date->format('Y-m-d') : '') }}" required>
            </div>

            <div class="col-md-12">
                <label class="form-label">Descrição</label>
                <textarea name="description" class="form-control" rows="5" required>{{ old('description', $project->description ?? '') }}</textarea>
            </div>

            <div class="col-md-12">
                <label class="form-label">Upload de ficheiros originais para backup</label>
                <input type="file" name="backup_files[]" class="form-control" multiple>
                <div style="font-size:12px;color:#667085;margin-top:6px;">Aceita múltiplos ficheiros. Máximo 50MB por ficheiro.</div>
            </div>
        </div>
    </div>
</div>
