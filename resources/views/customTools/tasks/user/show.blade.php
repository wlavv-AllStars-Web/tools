@extends('layouts.app')

@section('content')
<div class="customPanel">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">Task #{{ $task->id }} — {{ $task->title }}</h3>
            <div class="text-muted">
                Team: {{ $task->team?->name }} | Status: {{ $task->status_user }}
            </div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('admin.tools.tasks.user.index') }}">
            Back
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">

        <div class="customPanel">
            <h5>Status</h5>
            <form method="post" action="{{ route('admin.tools.tasks.user.status', $task->id) }}" class="d-flex gap-2">
                @csrf
                <select class="form-select" name="status_user" required>
                    @foreach(\App\Models\modules\tasks\task::STATUS_USER as $s)
                        <option value="{{ $s }}" @selected($task->status_user == $s)}>
                            {{ $s }}
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-primary">
                    Save
                </button>
            </form>
        </div>

        <div class="customPanel">
            <h5>Comment / Notes</h5>

            <form method="post" action="{{ route('admin.tools.tasks.user.comment', $task->id) }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label">
                        Notes (user)
                    </label>
                    <textarea
                        class="form-control"
                        name="observations_user"
                        rows="2"
                    >{{ old('observations_user', $task->observations_user) }}</textarea>
                </div>
                <button class="btn btn-primary">
                    Save
                </button>
            </form>

            <hr>

            <p class="mb-1">
                <strong>Manager notes:</strong>
                {!! nl2br(e($task->observations_manager)) !!}
            </p>

            @if(strlen($task->observations_admin) > 0)
                <p class="mb-0 text-muted">
                    <strong>Admin notes:</strong> (not visible)
                </p>
            @endif
        </div>

    </div>

    <div class="col-md-6">

        <div class="customPanel">
            <h5>File upload</h5>

            <div style="display: flow-root">
                <form
                    method="post"
                    enctype="multipart/form-data"
                    action="{{ route('admin.tools.tasks.user.upload', $task->id) }}"
                >
                    @csrf

                    {{-- MULTI FILE INPUT --}}
                    <input
                        class="form-control mb-2"
                        type="file"
                        name="files[]"
                        multiple
                        required
                        style="width: 80%; float: left;"
                    >

                    <button class="btn btn-primary" style="width: 18%; float: right;">
                        Upload
                    </button>
                </form>
            </div>

            <div class="form-text">
                Max. 5MB per file. Any file type. (You can select multiple files)
            </div>

            <hr>

            <h5>Files</h5>

            @if($task->files->count() === 0)
                <div class="text-muted">
                    No files.
                </div>
            @else
                <ul class="mb-0" style="list-style-type: none; padding-left: 0;">
                    @foreach($task->files as $f)
                        <li>
                            <a href="{{ route('admin.tools.tasks.files.download', $f->id) }}">
                                download
                            </a>
                            – {{ $f->filename }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>

    {{--
    <div class="col-md-6">
        <div class="customPanel">
            <h5>History</h5>
            @if($logs->count() === 0)
                <div class="text-muted">No logs.</div>
            @else
                <ul class="mb-0">
                    @foreach($logs as $log)
                        <li>
                            <strong>{{ $log->created_at }}</strong>
                            — {{ $log->user?->name ?? 'System' }}:
                            {{ $log->comment ?? 'status change' }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
    --}}
</div>

    <style>
        .badge{ padding: 7px; text-transform}
    </style>
@endsection
