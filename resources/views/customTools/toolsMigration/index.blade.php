@extends('layouts.app')

@section('content')
<style>
    .migration-row-new-only td {
        background-color: dodgerblue !important;
        color: #fff;
    }

    .migration-row-old-only td {
        background-color: red !important;
        color: #fff;
    }

    .migration-row-new-only td a,
    .migration-row-old-only td a {
        color: #fff;
    }
</style>

<div class="navbar navbar-light customPanel">
    <div style="width: 100%; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <h5 style="margin: 0;">Migration tool</h5>
        <span class="badge bg-secondary">Compare only</span>
    </div>

    @if(session('error')) <div class="alert alert-danger" style="width: 100%;">{{ session('error') }}</div> @endif
    @if(session('success')) <div class="alert alert-success" style="width: 100%;">{{ session('success') }}</div> @endif

    <div style="width: 100%; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-bottom: 12px;">
        <div style="border: 1px solid #ddd; border-radius: 5px; padding: 10px;">
            <strong>New tools</strong>
            <div>{{ $connections['new']['database'] ?? '-' }}</div>
            @if($connections['new']['ok'])
                <span class="badge bg-success">Connected</span>
            @else
                <span class="badge bg-danger">Connection error</span>
            @endif
        </div>
        <div style="border: 1px solid #ddd; border-radius: 5px; padding: 10px;">
            <strong>Old tools</strong>
            <div>{{ $connections['old']['database'] ?? '-' }}</div>
            @if($connections['old']['ok'])
                <span class="badge bg-success">Connected</span>
            @else
                <span class="badge bg-danger">Connection error</span>
                <div style="margin-top: 5px; color: #777;">Check OLD_TOOLS_DB_* env values.</div>
            @endif
        </div>
    </div>

    <table class="table table-bordered customTable text-center" style="width: 100%;">
        <thead>
            <tr style="text-transform: uppercase;">
                <th>New tools</th>
                <th>Rows estimate</th>
                <th>Old tools</th>
                <th>Rows estimate</th>
                <th>Status</th>
                <th>Structure</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tables as $table)
                <tr class="@if($table['status'] === 'new_only') migration-row-new-only @elseif($table['status'] === 'old_only') migration-row-old-only @endif">
                    <td>{{ $table['new'] ?? 'Not found' }}</td>
                    <td style="text-align: left;">
                        <div style="display: flex; gap: 6px; justify-content: flex-start; align-items: center;">
                            @if($table['new'])
                                <form method="POST" action="{{ route('web.tools.db_migration.clear', $table['name']) }}" style="margin: 0;" onsubmit="return confirm('Clear the NEW table {{ $table['name'] }}? This will delete all records from the new table.');">
                                    @csrf
                                    <button class="btn btn-warning btn-sm" type="submit">Clear</button>
                                </form>
                            @endif
                            <span>{{ $table['new_rows'] ?? '-' }}</span>
                        </div>
                    </td>
                    <td>{{ $table['old'] ?? 'Not found' }}</td>
                    <td>{{ $table['old_rows'] ?? '-' }}</td>
                    <td>
                        @if($table['status'] === 'matched')
                            <span class="badge bg-success">Matched</span>
                        @elseif($table['status'] === 'new_only')
                            <span class="badge" style="background-color: dodgerblue; color: white;">New only</span>
                        @else
                            <span class="badge bg-danger">Old only</span>
                        @endif
                    </td>
                    <td>
                        @if($table['structure']['same'])
                            <span class="badge bg-success">Same</span>
                        @elseif($table['status'] === 'matched')
                            <span class="badge bg-danger" title="{{ implode(' | ', $table['structure']['differences']) }}">
                                {{ $table['structure']['label'] }}
                            </span>
                            @if(!empty($table['structure']['differences']))
                                <div style="font-size: 11px; color: #777; margin-top: 3px;">
                                    {{ implode(' | ', $table['structure']['differences']) }}
                                </div>
                            @endif
                        @else
                            <span class="badge bg-secondary">Not comparable</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px; justify-content: center;">
                            @if($table['can_verify'])
                                <a class="btn btn-primary btn-sm" href="{{ route('web.tools.db_migration.table', $table['name']) }}">Verify</a>
                            @endif
                            @if($table['can_replace'])
                                <form method="POST" action="{{ route('web.tools.db_migration.sync', $table['name']) }}" style="margin: 0;" onsubmit="return confirm('Replace the NEW table {{ $table['name'] }} with the OLD table? This will delete all records from the new table and then copy all records from the old table.');">
                                    @csrf
                                    <button class="btn btn-danger btn-sm" type="submit">Replace</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        No tables found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
