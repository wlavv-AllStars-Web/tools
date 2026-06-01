@extends('layouts.app')

@section('content')
    @include('customTools.logistics.inventory.partials.styles')

    <div class="inventory-wrap">
        <div class="inventory-header">
            <h3 class="inventory-title">Preparacao de inventario</h3>
            <div class="inventory-actions">
                <a class="btn btn-secondary" href="{{ route('logistics.tools.inventory.index', ['date' => $date]) }}"><i class="fa-solid fa-chevron-left"></i></a>
            </div>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif

        <form method="POST" action="{{ route('logistics.tools.inventory.prepare.store') }}" class="inventory-card">
            @csrf
            <div class="inventory-card-header">Scan de celula/coluna</div>
            <div class="inventory-card-body">
                <input type="hidden" name="date" value="{{ $date }}">
                <input id="cellInput" class="form-control inventory-mobile-input" name="cell" value="" placeholder="A-01-01" autofocus autocomplete="off">
                <button class="btn btn-primary w-100 mt-3" type="submit">Preparar</button>
            </div>
        </form>

        <div class="inventory-card">
            <div class="inventory-card-header">Preparacao pendente</div>
            <div class="inventory-card-body">
                @if($schedules->isEmpty())
                    <div class="alert alert-success mb-0">Sem celulas pendentes de preparacao.</div>
                @else
                    <div class="inventory-grid">
                        @foreach($schedules as $schedule)
                            <form method="POST" action="{{ route('logistics.tools.inventory.prepare.store') }}" class="inventory-cell">
                                @csrf
                                <input type="hidden" name="date" value="{{ $date }}">
                                <input type="hidden" name="cell" value="{{ $schedule->cell }}">
                                <span class="code">{{ $schedule->cell }}</span>
                                <span class="meta">Por preparar</span>
                                <button class="btn btn-sm btn-primary mt-2" type="submit">Preparar</button>
                            </form>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        $('#cellInput').on('keyup', function(event) {
            if (event.keyCode === 13) {
                $(this).closest('form').submit();
            }
        });
    </script>
@endsection
