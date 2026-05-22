@extends('layouts.app')

@section('content')
<div class="navbar navbar-light customPanel">
    <div style="width: 100%; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <h5 style="margin: 0;">{{ $diff['table'] }} #{{ $diff['id'] }}</h5>
        <a class="btn btn-secondary btn-sm" href="{{ route('web.tools.db_migration.table', $diff['table']) }}">Back to records</a>
    </div>

    <table class="table table-bordered customTable" style="width: 100%;">
        <thead>
            <tr style="text-transform: uppercase;">
                <th style="width: 220px;">Column</th>
                <th>New tools</th>
                <th>Old tools</th>
                <th style="width: 120px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($diff['columns'] as $column)
                <tr style="background-color: {{ $column['equal'] ? '#e8f6ec' : '#fdecec' }};">
                    <td><strong>{{ $column['name'] }}</strong></td>
                    <td style="white-space: pre-wrap; word-break: break-word;">{{ $column['new'] === null ? 'NULL' : $column['new'] }}</td>
                    <td style="white-space: pre-wrap; word-break: break-word;">{{ $column['old'] === null ? 'NULL' : $column['old'] }}</td>
                    <td class="text-center">
                        @if($column['equal'])
                            <span class="badge bg-success">Equal</span>
                        @else
                            <span class="badge bg-danger">Different</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
