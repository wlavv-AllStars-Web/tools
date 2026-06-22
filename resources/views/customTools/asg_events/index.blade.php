@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 asg-page">

    @include('customTools.asg_events.partials.styles')

    @if(session('success'))
        <div class="alert alert-success asg-alert">{{ session('success') }}</div>
    @endif

    <div class="asg-header mb-4">
        <div>
            <h2 class="mb-1">ASG Events</h2>
            <div class="text-muted">Gestao da lista de eventos e galerias.</div>
        </div>

        <a href="{{ route('asg_events.create') }}" class="btn btn-success">
            + Novo evento
        </a>
    </div>

    <form method="GET" class="asg-card mb-4">
        <div class="asg-card-title">Filtros</div>

        <div class="row g-3 align-items-end">
            <div class="col-lg-4">
                <label class="form-label">Pesquisa</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Pesquisar por nome do evento">
            </div>

            <div class="col-lg-2">
                <label class="form-label">Tipo</label>
                <select name="gallery_type" class="form-select">
                    <option value="">Todos</option>
                    <option value="internal" @selected(request('gallery_type') === 'internal')>Interna</option>
                    <option value="flickr" @selected(request('gallery_type') === 'flickr')>Flickr</option>
                </select>
            </div>

            <div class="col-lg-2">
                <label class="form-label">ID Shop</label>
                <input type="number" name="id_shop" class="form-control" value="{{ request('id_shop') }}">
            </div>

            <div class="col-lg-2">
                <label class="form-label">Estado</label>
                <select name="display" class="form-select">
                    <option value="">Todos</option>
                    <option value="1" @selected(request('display') === '1')>Visivel</option>
                    <option value="0" @selected(request('display') === '0')>Oculto</option>
                </select>
            </div>

            <div class="col-lg-2 d-flex gap-2">
                <button class="btn btn-primary flex-fill">Filtrar</button>
                <a href="{{ route('asg_events.index') }}" class="btn btn-outline-secondary">Limpar</a>
            </div>
        </div>
    </form>

    <div class="asg-grid">
        @forelse($events as $event)
            <div class="asg-event-card">
                <div class="asg-event-media">
                    @php
                        $cover = $event->cover_desktop ?: $event->cover_mobile ?: ($event->images_array[0] ?? null);
                    @endphp

                    @if($cover)
                        <img src="{{ asset($cover) }}" alt="{{ $event->name }}">
                    @else
                        <div class="asg-event-placeholder">Sem imagem</div>
                    @endif

                    <div class="asg-event-status">
                        <span class="badge {{ $event->gallery_type === 'flickr' ? 'bg-info' : 'bg-dark' }}">
                            {{ $event->gallery_type === 'flickr' ? 'Flickr' : 'Interna' }}
                        </span>
                        @if((int)$event->display === 1)
                            <span class="badge bg-success">Visivel</span>
                        @else
                            <span class="badge bg-secondary">Oculto</span>
                        @endif
                    </div>
                </div>

                <div class="asg-event-body">
                    <div class="asg-event-title">{{ $event->name }}</div>
                    <div class="asg-event-subtitle">{{ $event->event_date ?: 'Sem data' }}</div>

                    <div class="asg-event-meta">
                        <span>ID {{ $event->id_gallery }}</span>
                        <span>Shop {{ $event->id_shop }}</span>
                        <span>Pos. {{ $event->position }}</span>
                        <span>{{ count($event->images_array) }} imagens</span>
                    </div>

                    <div class="asg-event-actions">
                        <a href="{{ route('asg_events.edit', $event->id_gallery) }}" class="btn btn-sm btn-outline-primary w-100">
                            Editar
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="asg-empty">
                Nao existem eventos para apresentar.
            </div>
        @endforelse
    </div>

    @if(method_exists($events, 'links'))
        <div class="mt-4">
            {{ $events->links() }}
        </div>
    @endif

</div>
@endsection
