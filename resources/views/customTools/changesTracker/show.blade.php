@extends('layouts.app')

@section('content')
    <div class="navbar navbar-light customPanel">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><strong>Área:</strong>            <span style="margin-left: 5px;">{{ ucfirst($project->area) }}</span></div>
                    <div class="col-md-3"><strong>Estado:</strong>          <span style="margin-left: 5px;">{{ $project->status }}</span></div>
                    <div class="col-md-3"><strong>Pedido por:</strong>      <span style="margin-left: 5px;">{{ $project->requested_by }}</span></div>
                    <div class="col-md-3"><strong>Data:</strong>            <span style="margin-left: 5px;">{{ optional($project->change_date)->format('Y-m-d') }}</span></div>
                    <div class="col-md-12"><strong>Descrição:</strong>      <span style="margin-left: 5px;">{!! nl2br(e($project->description)) !!}</span></div>
                </div>
            </div>
        </div> 
    </div>

    <div class="navbar navbar-light customPanel">
        <div class="card">
            <details class="backups-collapse">
                <summary class="backups-summary">
                    <div>
                        <strong style="float: left;">Ficheiros de backup</strong>
                        <div class="small text-muted" style="float: left;margin-left: 10px;">
                            {{ $project->files->count() }} ficheiro(s) associado(s)
                        </div>
                    </div>
                    <span class="backups-chevron">▼</span>
                </summary>
        
                <div class="card-body" style="border-top:1px solid #e4e7ec;">
                    @if($project->files->count())
                        <div class="backup-grid">
                            @foreach($project->files as $file)
                                <div class="backup-mini-card">
                                    <div class="backup-mini-title" title="{{ $file->original_name }}">
                                        {{ $file->original_name }}
                                    </div>
        
                                    <div class="backup-mini-meta">
                                        <div>{{ $file->mime_type ?: 'Ficheiro' }}</div>
                                        <div>{{ number_format(($file->file_size ?? 0) / 1024, 2) }} KB</div>
                                    </div>
        
                                    <div class="backup-mini-actions">
                                        <a href="{{ route('customTools.changesTracker.files.download', [$project->id, $file->id]) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           target="_self">
                                            Download
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div>Sem backups associados.</div>
                    @endif
                </div>
            </details>
        </div>
    </div>

    <div class="navbar navbar-light customPanel">
        <div class="card">
            <div class="card-header">
                <strong>Erros registados</strong>
            </div>
        
            <div class="card-body">
                @if($project->errors->count())
                    <div class="errors-timeline">
                        <div class="card mb-4">
                            <details class="timeline-card">
                                <summary>
                                    <div class="timeline-summary-left">
                                        <div class="timeline-title" style="float: left;color: orange;">Adicionar erro ao projeto</div>
                                        <div class="timeline-meta" style="float: left;margin-left: 10px;">
                                            <span>( Clique para abrir o formulário )</span>
                                        </div>
                                    </div>
                                </summary>
                        
                                <div class="timeline-content">
                                    <form method="post" action="{{ route('customTools.changesTracker.errors.store', $project->id) }}">
                                        @csrf
                        
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Título do erro</label>
                                                <input type="text" name="title" class="form-control" required>
                                            </div>
                        
                                            <div class="col-md-3">
                                                <label class="form-label">Estado</label>
                                                <select name="status" class="form-control" required>
                                                    <option value="open">Open</option>
                                                    <option value="in_analysis">In analysis</option>
                                                    <option value="resolved">Resolved</option>
                                                    <option value="closed">Closed</option>
                                                </select>
                                            </div>
                        
                                            <div class="col-md-3">
                                                <label class="form-label">Data deteção</label>
                                                <input type="datetime-local" name="detected_at" class="form-control">
                                            </div>
                        
                                            <div class="col-md-12">
                                                <label class="form-label">Descrição do erro</label>
                                                <textarea name="description" class="form-control" rows="4" required></textarea>
                                            </div>
                        
                                            <div class="col-md-12">
                                                <label class="form-label">Resolução encontrada</label>
                                                <textarea name="resolution" class="form-control" rows="4"></textarea>
                                            </div>
                        
                                            <div class="col-md-3">
                                                <label class="form-label">Data resolução</label>
                                                <input type="datetime-local" name="resolved_at" class="form-control">
                                            </div>
                        
                                            <div class="col-md-12">
                                                <button class="btn btn-primary">Registar erro</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </details>
                        </div>
    
                        @foreach($project->errors as $error)
                            <div class="timeline-item">
                                <span class="timeline-dot {{ $error->status }}"></span>
        
                                <details class="timeline-card">
                                    <summary>
                                        <div class="timeline-summary-left">
                                            <div class="timeline-title">{{ $error->title }}</div>
        
                                            <div class="timeline-meta">
                                                <span>
                                                    <strong>Detetado:</strong>
                                                    {{ $error->detected_at ? $error->detected_at->format('Y-m-d H:i') : '—' }}
                                                </span>
        
                                                <span>
                                                    <strong>Resolvido:</strong>
                                                    {{ $error->resolved_at ? $error->resolved_at->format('Y-m-d H:i') : '—' }}
                                                </span>
                                            </div>
                                        </div>
        
                                        <div class="timeline-summary-right">
                                            <span class="status-badge status-{{ $error->status }}">
                                                {{ ucfirst(str_replace('_', ' ', $error->status)) }}
                                            </span>
                                            <span class="timeline-chevron">▼</span>
                                        </div>
                                    </summary>
        
                                    <div class="timeline-content">
                                        <form method="post" action="{{ route('customTools.changesTracker.errors.update', [$project->id, $error->id]) }}">
                                            @csrf
        
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Título</label>
                                                    <input type="text" name="title" class="form-control" value="{{ $error->title }}" required>
                                                </div>
        
                                                <div class="col-md-3">
                                                    <label class="form-label">Estado</label>
                                                    <select name="status" class="form-control" required>
                                                        <option value="open" {{ $error->status === 'open' ? 'selected' : '' }}>Open</option>
                                                        <option value="in_analysis" {{ $error->status === 'in_analysis' ? 'selected' : '' }}>In analysis</option>
                                                        <option value="resolved" {{ $error->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                                        <option value="closed" {{ $error->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                                    </select>
                                                </div>
        
                                                <div class="col-md-3">
                                                    <label class="form-label">Detetado em</label>
                                                    <input type="datetime-local" name="detected_at" class="form-control" value="{{ $error->detected_at ? $error->detected_at->format('Y-m-d\TH:i') : '' }}">
                                                </div>
        
                                                <div class="col-md-12">
                                                    <label class="form-label">Descrição</label>
                                                    <textarea name="description" class="form-control" rows="3" required>{{ $error->description }}</textarea>
                                                </div>
        
                                                <div class="col-md-12">
                                                    <label class="form-label">Resolução</label>
                                                    <textarea name="resolution" class="form-control" rows="3">{{ $error->resolution }}</textarea>
                                                </div>
        
                                                <div class="col-md-3">
                                                    <label class="form-label">Resolvido em</label>
                                                    <input type="datetime-local" name="resolved_at" class="form-control" value="{{ $error->resolved_at ? $error->resolved_at->format('Y-m-d\TH:i') : '' }}">
                                                </div>
        
                                                <div class="col-md-12">
                                                    <button class="btn btn-outline-primary">Atualizar erro</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </details>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div>Sem erros registados para este projeto.</div>
                @endif
            </div>
        </div>
    </div>

<style>
.backups-summary,.timeline-card summary{list-style:none;cursor:pointer;gap:12px;user-select:none}.backups-collapse{display:block}.backups-summary{padding:16px 20px;display:flex;align-items:center;justify-content:space-between}.backups-summary::-webkit-details-marker{display:none}.backups-chevron,.timeline-chevron{font-size:16px;color:#667085;transition:transform .2s}.backups-collapse[open] .backups-chevron,.timeline-card[open] .timeline-chevron{transform:rotate(180deg)}.backup-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px}.backup-mini-card{border:1px solid #e4e7ec;border-radius:10px;background:#fff;padding:12px;display:flex;flex-direction:column;justify-content:space-between;min-height:120px}.backup-mini-title{font-size:13px;font-weight:600;color:#101828;line-height:1.3;margin-bottom:8px;word-break:break-word;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}.backup-mini-meta{font-size:11px;color:#667085;line-height:1.4;margin-bottom:10px}.backup-mini-actions{margin-top:auto}.backup-mini-actions .btn{width:100%}.errors-timeline{position:relative;margin:0;padding:0 0 0 32px}.errors-timeline::before{content:'';position:absolute;left:10px;top:0;bottom:0;width:2px;background:#d0d5dd}.timeline-item{position:relative;margin-bottom:18px}.timeline-dot{position:absolute;left:-28px;top:18px;width:14px;height:14px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 0 2px #d0d5dd;background:#98a2b3;z-index:2}.timeline-dot.open{background:#f79009}.timeline-dot.in_analysis{background:#3b82f6}.timeline-dot.resolved{background:#12b76a}.timeline-dot.closed{background:#667085}.timeline-card{border:1px solid #e4e7ec;background:#fff;overflow:hidden}.timeline-card summary{padding:14px 16px;display:flex;align-items:center;justify-content:space-between;background:#fcfcfd}.timeline-card summary::-webkit-details-marker{display:none}.timeline-card[open] summary{border-bottom:1px solid #e4e7ec;background:#f9fafb}.timeline-summary-left{gap:4px;min-width:0}.timeline-title{font-weight:600;color:#101828;line-height:1.2}.timeline-meta{font-size:12px;color:#667085;display:flex;flex-wrap:wrap;gap:10px}.timeline-summary-right{display:flex;align-items:center;gap:10px;flex-shrink:0}.status-badge{display:inline-flex;align-items:center;justify-content:center;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600;border:1px solid transparent}.status-open{background:#fff4e5;color:#b54708;border-color:#fedf89}.status-in_analysis{background:#eff8ff;color:#175cd3;border-color:#b2ddff}.status-resolved{background:#ecfdf3;color:#027a48;border-color:#abefc6}.status-closed{background:#f2f4f7;color:#344054;border-color:#d0d5dd}.timeline-content{padding:16px}.timeline-section{margin-bottom:16px}.timeline-section:last-child{margin-bottom:0}.timeline-label{display:block;font-size:12px;font-weight:700;color:#475467;margin-bottom:6px;text-transform:uppercase;letter-spacing:.03em}
</style> 
@endsection
