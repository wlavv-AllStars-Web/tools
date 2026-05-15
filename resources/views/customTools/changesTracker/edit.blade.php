@extends('layouts.app')

@section('content')
<div class="navbar navbar-light customPanel">
    <h1 style="margin-bottom:20px;">Editar alteração #{{ $project->id }}</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Existem erros no formulário:</strong>
            <ul style="margin:8px 0 0 18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('customTools.changesTracker.update', $project->id) }}" enctype="multipart/form-data">
        @csrf
        @include('customTools.changesTracker.partials.form', ['project' => $project])

        <div style="display:flex;gap:10px; float: right;">
            <button class="btn btn-primary">Atualizar alteração</button>
        </div>
    </form>
</div>

<div class="navbar navbar-light customPanel">
    <details class="backups-collapse">
        <summary class="backups-summary">
            <div>
                <strong style="float: left;">Ficheiros de backup</strong>
                <div class="small text-muted" style="float: left;margin-left: 10px;"> {{ $project->files->count() }} ficheiro(s) associado(s) </div>
            </div>
            <span class="backups-chevron">▼</span>
        </summary>
        <div class="card-body">
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


<style>
    .backups-collapse{
        display:block;
    }

    .backups-summary{
        list-style:none;
        cursor:pointer;
        padding:16px 0px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        user-select:none;
    }

    .backups-summary::-webkit-details-marker{
        display:none;
    }

    .backups-chevron{
        font-size:16px;
        color:#667085;
        transition:transform .2s ease;
    }

    .backups-collapse[open] .backups-chevron{
        transform:rotate(180deg);
    }

    .backup-grid{
        display:grid;
        grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));
        gap:12px;
    }

    .backup-mini-card{
        border:1px solid #e4e7ec;
        border-radius:10px;
        background:#fff;
        padding:12px;
        display:flex;
        flex-direction:column;
        justify-content:space-between;
        min-height:120px;
    }

    .backup-mini-title{
        font-size:13px;
        font-weight:600;
        color:#101828;
        line-height:1.3;
        margin-bottom:8px;
        word-break:break-word;

        display:-webkit-box;
        -webkit-line-clamp:3;
        -webkit-box-orient:vertical;
        overflow:hidden;
    }

    .backup-mini-meta{
        font-size:11px;
        color:#667085;
        line-height:1.4;
        margin-bottom:10px;
    }

    .backup-mini-actions{
        margin-top:auto;
    }

    .backup-mini-actions .btn{
        width:100%;
    }
</style>
@endsection
