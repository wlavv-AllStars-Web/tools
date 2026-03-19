@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-1">Task #{{ $task->id }} — {{ $task->title }}</h3>
        <div class="text-muted">Equipa: {{ $task->team?->name }} | Atribuído: {{ $task->assignedUser?->name ?? '-' }}</div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('tasks.admin.edit', $task->id) }}">Editar</a>
        <a class="btn btn-outline-secondary" href="{{ route('tasks.admin.index') }}">Voltar</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card p-3">
            <h5>Detalhes</h5>
            <p class="mb-1"><strong>Status:</strong> {{ $task->status_user }} / {{ $task->status_manager }} / {{ $task->status_admin }}</p>
            <p class="mb-1"><strong>Tempo permitido:</strong> {{ $task->time_allowed ?? '-' }} min</p>
            <hr>
            <p class="mb-0">{{ $task->description }}</p>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-3">
            <h5>Observações</h5>
            <p class="mb-1"><strong>User:</strong> {!! nl2br(e($task->observations_user)) !!}</p>
            <p class="mb-1"><strong>Manager:</strong> {!! nl2br(e($task->observations_manager)) !!}</p>
            <p class="mb-0"><strong>Admin:</strong> {!! nl2br(e($task->observations_admin)) !!}</p>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-3">
            <h5>Ficheiros</h5>
            @if($task->files->count() === 0)
                <div class="text-muted">Sem ficheiros.</div>
            @else
                <ul class="mb-0">
                    @foreach($task->files as $f)
                        <li>
                            {{ $f->filename }} ({{ round($f->size/1024, 1) }} KB)
                            — <a href="{{ route('tasks.files.download', $f->id) }}">download</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-3">
            <h5>Histórico</h5>
            @if($task->logs->count() === 0)
                <div class="text-muted">Sem logs.</div>
            @else
                <ul class="mb-0">
                    @foreach($task->logs as $log)
                        <li>
                            <strong>{{ $log->created_at }}</strong> — {{ $log->user?->name ?? 'System' }}:
                            {{ $log->comment ?? 'status change' }}
                            <span class="text-muted"> (U {{ $log->old_status_user }}→{{ $log->new_status_user }},
                            M {{ $log->old_status_manager }}→{{ $log->new_status_manager }},
                            A {{ $log->old_status_admin }}→{{ $log->new_status_admin }})</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection
