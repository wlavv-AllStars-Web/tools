@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>User</th>
                            <th>Módulo</th>
                            <th>Ação</th>
                            <th>Descrição</th>
                            <th>Gravidade</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td>{{ $log->created_at }}</td>
                            <td>{{ $log->user->name ?? 'Sistema' }}</td>
                            <td>{{ $log->module }}</td>
                            <td>{{ $log->action }}</td>
                            <td>{{ $log->description }}</td>
                            <td>
                                <span class="badge bg-{{ 
                                    $log->severity === 'critical' || $log->severity === 'error' ? 'danger' :
                                    ($log->severity === 'warning' ? 'warning' : 'secondary')
                                }}">
                                    {{ strtoupper($log->severity) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('logs.show', $log->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row" style="margin-bottom: 10px;">
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel">
                {{ $logs->withQueryString()->links('vendor.pagination.bootstrap-5') }}
                <div class="text-center" style="margin-top: 10px;">
                    <small class="text-muted">
                        A mostrar {{ $logs->firstItem() }}–{{ $logs->lastItem() }}
                        de {{ $logs->total() }} registos
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
