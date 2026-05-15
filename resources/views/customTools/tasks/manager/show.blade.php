@extends('layouts.app')

@section('content')

@php
    $badgeClass = function ($status) {
        return match ($status) {
            'done', 'ok'         => 'bg-success',
            'pending', 'delayed' => 'bg-warning text-dark',
            'fail'               => 'bg-danger',
            'hold', 'extra'      => 'bg-secondary',
            default              => 'bg-light text-dark',
        };
    };
@endphp

<div class="customPanel">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">Task #{{ $task->id }} — {{ $task->title }}</h3>
            <div class="text-muted"> Team: {{ $task->team?->name }} | Assigned: {{ $task->assignedUser?->name ?? '-' }} </div>
        </div>

        <a class="btn btn-outline-secondary" href="{{ route('admin.tools.tasks.manager.index') }}">
            Back
        </a>
    </div>
</div>

<div class="row g-3">

    {{-- LEFT COLUMN --}}
    <div class="col-md-6">

        <div class="customPanel">
            <h5>Assign user</h5>

            <form method="post" action="{{ route('admin.tools.tasks.manager.assign', $task->id) }}" class="d-flex gap-2">
                @csrf
                <select class="form-select" name="assigned_user_id" required>
                    @foreach($teamUsers as $u)
                        <option value="{{ $u->id }}" @selected($task->assigned_user_id == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary">Assign</button>
            </form>
        </div>

        <div class="customPanel">
            <h5>Comment / Notes</h5>

            <form method="post" action="{{ route('admin.tools.tasks.manager.observations', $task->id) }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label">Notes (manager)</label>
                    <textarea class="form-control" name="observations_manager" rows="4">{{ old('observations_manager', $task->observations_manager) }}</textarea>
                </div>
                <button class="btn btn-primary">Save</button>
            </form>

            <hr>

            <p class="mb-1">
                <strong>User notes:</strong>
                {!! nl2br(e($task->observations_user)) !!}
            </p>

            <p class="mb-0">
                <strong>Admin notes:</strong>
                {!! nl2br(e($task->observations_admin)) !!}
            </p>
        </div>

    </div>

    {{-- RIGHT COLUMN --}}
    <div class="col-md-6">

        <div class="customPanel">
            <h5>Manager status</h5>

            <form method="post" action="{{ route('admin.tools.tasks.manager.status', $task->id) }}" class="d-flex gap-2">
                @csrf
                <select class="form-select" name="status_manager" required>
                    @foreach(\App\Models\modules\tasks\task::STATUS_MANAGER as $s)
                        <option value="{{ $s }}" @selected($task->status_manager == $s)>{{ $s }}</option>
                    @endforeach
                </select>

                <button class="btn btn-primary">Save</button>
            </form>

            <hr class="my-3">

                <table class="table table-sm table-bordered mb-0 align-middle" style="width: 100%;">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="min-width: 90px;">User</th>
                            <th class="text-center" style="min-width: 90px;">Manager</th>
                            <th class="text-center" style="min-width: 90px;">Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">
                                <span class="badge {{ $badgeClass($task->status_user) }}">
                                    {{ $task->status_user ?? '-' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $badgeClass($task->status_manager) }}">
                                    {{ $task->status_manager ?? '-' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $badgeClass($task->status_admin) }}">
                                    {{ $task->status_admin ?? '-' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
        </div>

        <div class="customPanel">
            <h5>Files</h5>

            @if($task->files->count() === 0)
                <div class="text-muted">No files.</div>
            @else
                <ul class="mb-0" style="list-style-type: none; padding-left: 0;">
                    @foreach($task->files as $f)
                        <li class="mb-1">
                            <a href="{{ route('admin.tools.tasks.files.download', $f->id) }}">download</a>
                            — {{ $f->filename }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>

</div>
    
    <style>
        
        .badge{ padding: 7px; text-transform: uppercase;}
    </style>
@endsection
