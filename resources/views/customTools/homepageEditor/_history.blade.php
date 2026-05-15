@extends('layouts.app')

@section('content')

<h2>Histórico Homepage</h2>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Data</th>
            <th>Notas</th>
            <th>Ações</th>
        </tr>
    </thead>

    <tbody>
        @foreach($logs as $log)
            <tr>
                <td>{{ $log->id }}</td>
                <td>{{ $log->published_at }}</td>
                <td>{{ $log->notes }}</td>
                <td>
                    <a href="{{ route('marketing.homepage.restore', $log->id) }}">
                        Restaurar
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection