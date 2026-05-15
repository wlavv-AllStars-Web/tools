@extends('layouts.app')

@section('content')
    <div class="navbar navbar-light customPanel">
        @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        <div style="display: block;">
            <div style="width: calc( 100% - 170px); float: left;">
                <form method="get" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:12px;">
                    <input type="text" name="search" class="form-control" placeholder="Pesquisar título, descrição, pedido por..." value="{{ $filters['search'] ?? '' }}">
                    <select name="area" class="form-control">
                        <option value="">Todos</option>
                        <option value="frontoffice" {{ ($filters['area'] ?? '') === 'frontoffice' ? 'selected' : '' }}>Frontoffice</option>
                        <option value="backoffice" {{ ($filters['area'] ?? '') === 'backoffice' ? 'selected' : '' }}>Backoffice</option>
                        <option value="both" {{ ($filters['area'] ?? '') === 'both' ? 'selected' : '' }}>Ambos</option>
                    </select>
                    <select name="status" class="form-control">
                        <option value="">Estado</option>
                        <option value="planned" {{ ($filters['status'] ?? '') === 'planned' ? 'selected' : '' }}>Planned</option>
                        <option value="in_progress" {{ ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' }}>In progress</option>
                        <option value="done" {{ ($filters['status'] ?? '') === 'done' ? 'selected' : '' }}>Done</option>
                        <option value="on_hold" {{ ($filters['status'] ?? '') === 'on_hold' ? 'selected' : '' }}>On hold</option>
                        <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <button class="btn btn-outline-primary">Filtrar</button>
                </form>
            </div>
            <div style="width: 150px; float: right;text-align: right;"> <a href="{{ route('customTools.changesTracker.create') }}" class="btn btn-success">Nova</a> </div>
        </div>
    </div>
    <div class="navbar navbar-light customPanel">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Projeto</th>
                            <th>Área</th>
                            <th>Pedido por</th>
                            <th>Data alteração</th>
                            <th>Estado</th>
                            <th>Backups</th>
                            <th>Erros</th>
                            <th style="width:160px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            <tr>
                                <td>{{ $project->id }}</td>
                                <td>
                                    <strong>{{ $project->title }}</strong>
                                    <div style="font-size:12px;color:#667085;">{{ \Illuminate\Support\Str::limit($project->description, 120) }}</div>
                                </td>
                                <td>{{ ucfirst($project->area) }}</td>
                                <td>{{ $project->requested_by }}</td>
                                <td>{{ optional($project->change_date)->format('Y-m-d') }}</td>
                                <td>{{ $project->status }}</td>
                                <td>{{ $project->files_count }}</td>
                                <td>{{ $project->errors_count }}</td>
                                <td>
                                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('customTools.changesTracker.show', $project->id) }}">Ver</a>
                                        <a class="btn btn-sm btn-outline-warning" href="{{ route('customTools.changesTracker.edit', $project->id) }}">Editar</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr> <td colspan="9" class="text-center">Sem alterações registadas.</td> </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    
        <div class="mt-3"> {{ $projects->links() }} </div>
    </div>
@endsection
