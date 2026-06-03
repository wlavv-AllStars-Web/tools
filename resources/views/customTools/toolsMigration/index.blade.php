@extends('layouts.app')

@section('content')
<div class="navbar navbar-light customPanel">
    <div style="width: 100%; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <h5 style="margin: 0;">Migration tool</h5>
        <div style="display: flex; gap: 8px; align-items: center;">
            <span class="badge bg-secondary">{{ count($tables) }} importable tables</span>
            <form method="POST" action="{{ route('web.tools.db_migration.import_all') }}" style="margin: 0;" onsubmit="return confirm('Clear and import all allowed common tables from OLD tools into NEW tools? Protected tables will be ignored.');">
                @csrf
                <button class="btn btn-success btn-sm" type="submit">Run migration import</button>
            </form>
        </div>
    </div>

    @if(session('error')) <div class="alert alert-danger" style="width: 100%;">{{ session('error') }}</div> @endif
    @if(session('success')) <div class="alert alert-success" style="width: 100%;">{{ session('success') }}</div> @endif

    @if(collect($tables)->where('can_import', true)->isEmpty())
        <div class="alert alert-warning" style="width: 100%;">
            No common tables are currently importable. Check protected tables in <code>config/tools_migration.php</code> and required column differences.
        </div>
    @endif

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
                <th>Table</th>
                <th>New rows estimate</th>
                <th>Old rows estimate</th>
                <th>Common columns</th>
                <th>Import mode</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tables as $table)
                <tr>
                    <td>{{ $table['name'] }}</td>
                    <td>{{ $table['new_rows'] ?? '-' }}</td>
                    <td>{{ $table['old_rows'] ?? '-' }}</td>
                    <td>{{ $table['import']['common_columns'] }}</td>
                    <td>
                        <span class="badge bg-success">{{ $table['import']['label'] }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        No tables found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
