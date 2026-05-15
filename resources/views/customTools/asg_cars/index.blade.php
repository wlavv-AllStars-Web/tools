@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 asg-page">

    @include('customTools.asg_cars.partials.styles')

    @if(session('success'))
        <div class="alert alert-success asg-alert">{{ session('success') }}</div>
    @endif

    <div class="asg-header mb-4">
        <div>
            <h2 class="mb-1">ASG Cars</h2>
            <div class="text-muted">Gestão da galeria de veículos e produtos associados.</div>
        </div>

        <a href="{{ route('asg_cars.create') }}" class="btn btn-success">
            + Novo veículo
        </a>
    </div>

    <form method="GET" class="asg-card mb-4">
        <div class="asg-card-title">Filtros</div>

        <div class="row g-3 align-items-end">
            <div class="col-lg-6">
                <label class="form-label">Pesquisa</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Pesquisar por nome do veículo">
            </div>

            <div class="col-lg-2">
                <label class="form-label">ID Shop</label>
                <input type="number" name="id_shop" class="form-control" value="{{ request('id_shop') }}">
            </div>

            <div class="col-lg-2">
                <label class="form-label">Estado</label>
                <select name="display" class="form-select">
                    <option value="">Todos</option>
                    <option value="1" @selected(request('display') === '1')>Visível</option>
                    <option value="0" @selected(request('display') === '0')>Oculto</option>
                </select>
            </div>

            <div class="col-lg-2 d-flex gap-2">
                <button class="btn btn-primary flex-fill">Filtrar</button>
                <a href="{{ route('asg_cars.index') }}" class="btn btn-outline-secondary">Limpar</a>
            </div>
        </div>
    </form>

    <div class="asg-grid">
        @forelse($cars as $car)
            <div class="asg-car-card">
                <div class="asg-car-media">
                    @php
                        $cover = $car->cover_desktop ?: ($car->images_array[0] ?? null);
                    @endphp

                    @if($cover)
                        <img src="{{ asset($cover) }}" alt="{{ $car->name }}">
                    @else
                        <div class="asg-car-placeholder">Sem imagem</div>
                    @endif

                    <div class="asg-car-status">
                        @if((int)$car->display === 1)
                            <span class="badge bg-success">Visível</span>
                        @else
                            <span class="badge bg-secondary">Oculto</span>
                        @endif
                    </div>
                </div>

                <div class="asg-car-body">
                    <div class="asg-car-title">{{ $car->name }}</div>
                    <div class="asg-car-subtitle">{{ $car->car_name_galleries ?: 'Sem nome de galeria' }}</div>

                    <div class="asg-car-meta">
                        <span>ID {{ $car->id_asg_car }}</span>
                        <span>Shop {{ $car->id_shop }}</span>
                        <span>Pos. {{ $car->position }}</span>
                        <span>{{ $car->products_count }} produtos</span>
                    </div>

                    <div class="asg-car-actions">
                        <a href="{{ route('asg_cars.edit', $car->id_asg_car) }}" class="btn btn-sm btn-outline-primary w-100">
                            Editar
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="asg-empty">
                Não existem veículos para apresentar.
            </div>
        @endforelse
    </div>

    @if(method_exists($cars, 'links'))
        <div class="mt-4">
            {{ $cars->links() }}
        </div>
    @endif

</div>
@endsection
