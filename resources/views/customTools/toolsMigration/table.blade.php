@extends('layouts.app')

@section('content')
<div class="navbar navbar-light customPanel">
    <div style="width: 100%; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <h5 style="margin: 0;">{{ $details['table'] }}</h5>
        <div style="display: flex; gap: 8px;">
            @if($details['can_replace'])
                <form method="POST" action="{{ route('web.tools.db_migration.sync', $details['table']) }}" style="margin: 0;" onsubmit="return confirm('Replace the NEW table with the OLD table? This will delete all records from the new table and then copy all records from the old table.');">
                    @csrf
                    <button class="btn btn-danger btn-sm" type="submit">Replace new with old</button>
                </form>
            @endif
            <a class="btn btn-secondary btn-sm" href="{{ route('web.tools.db_migration.index') }}">Back to tables</a>
        </div>
    </div>

    @if(session('error')) <div class="alert alert-danger" style="width: 100%;">{{ session('error') }}</div> @endif
    @if(session('success')) <div class="alert alert-success" style="width: 100%;">{{ session('success') }}</div> @endif

    <div style="width: 100%; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-bottom: 12px;">
        <div style="border: 1px solid #ddd; border-radius: 5px; padding: 10px;">
            <strong>New tools</strong>
            <div>{{ $details['new_exists'] ? $details['new_count'] . ' records' : 'Not found' }}</div>
            <div>PK: {{ $details['new_primary_key'] ?? '-' }}</div>
        </div>
        <div style="border: 1px solid #ddd; border-radius: 5px; padding: 10px;">
            <strong>Old tools</strong>
            <div>{{ $details['old_exists'] ? $details['old_count'] . ' records' : 'Not found' }}</div>
            <div>PK: {{ $details['old_primary_key'] ?? '-' }}</div>
        </div>
    </div>

    @if(!$details['has_comparable_key'])
        <div class="alert alert-warning" style="width: 100%;">
            This table does not have a single primary key. Record comparison is not available yet.
        </div>
    @else
        <div class="alert alert-info" style="width: 100%;">
            Showing the first {{ $details['limit'] }} keys ordered by {{ $details['primary_key'] }}.
        </div>

        <table class="table table-bordered customTable text-center" style="width: 100%;">
            <thead>
                <tr style="text-transform: uppercase;">
                    <th>New tools</th>
                    <th>Old tools</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($details['rows'] as $row)
                    <tr>
                        <td>
                            @if($row['new'])
                                Record {{ $details['primary_key'] }} {{ $row['id'] }}
                            @else
                                <span style="color: #c00;">Not found</span>
                            @endif
                        </td>
                        <td>
                            @if($row['old'])
                                Record {{ $details['primary_key'] }} {{ $row['id'] }}
                            @else
                                <span style="color: #c00;">Not found</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px; justify-content: center;">
                                <a class="btn btn-primary btn-sm" href="{{ route('web.tools.db_migration.row', [$details['table'], $row['id']]) }}">Expand</a>
                                @if($details['can_replace'] && $row['old'])
                                    <form method="POST" action="{{ route('web.tools.db_migration.sync_row', [$details['table'], $row['id']]) }}" style="margin: 0;" onsubmit="return confirm('Replace this record in the NEW database with the OLD database version?');">
                                        @csrf
                                        <button class="btn btn-danger btn-sm" type="submit">Replace</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif
</div>
@endsection
