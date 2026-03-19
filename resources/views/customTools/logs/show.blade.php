@extends('layouts.app')
@section('content')
    <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="navbar navbar-light customPanel">
                <h1>Detalhe do Log</h1>
                <div>
                    <p><strong>User:</strong> {{ $log->user->name ?? 'Sistema' }}</p>
                    <p><strong>Módulo:</strong> {{ $log->module }}</p>
                    <p><strong>Ação:</strong> {{ $log->action }}</p>
                    <p><strong>Rota:</strong> {{ $log->route }}</p>
                    <p><strong>Método:</strong> {{ $log->method }}</p>
                    <p><strong>IP:</strong> {{ $log->ip_address }}</p>
                    <p><strong>User Agent:</strong> {{ $log->user_agent }}</p>
                    <p><strong>Gravidade:</strong> {{ $log->severity }}</p>
                    <p><strong>Descrição:</strong> {{ $log->description }}</p>
                    <p><strong>Data:</strong> {{ $log->created_at }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
