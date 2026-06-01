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
                <input id="cellInput" class="form-control inventory-mobile-input" name="cell" value="{{ $selectedCell }}" placeholder="A-01-01" autofocus autocomplete="off">
                <button class="btn btn-primary w-100 mt-3" type="submit">Preparar</button>
            </div>
        </form>

        <div class="inventory-card">
            <div class="inventory-card-header">Preparacao pendente</div>
            <div class="inventory-card-body">
                @if($schedules->isEmpty())
                    <div class="alert alert-success mb-0">Sem celulas pendentes de preparacao.</div>
                @else
                    <div class="inventory-housing-groups">
                        @foreach($scheduleGroups as $group)
                            <details class="inventory-housing-group" {{ $selectedCell && $group->schedules->pluck('cell')->contains($selectedCell) ? 'open' : '' }}>
                                <summary>
                                    <strong>{{ $group->housing }}</strong>
                                    <span>{{ $group->count }} celulas</span>
                                </summary>
                                <div class="inventory-housing-cells">
                                    @foreach($group->schedules as $schedule)
                                        <a class="inventory-cell {{ $selectedCell === $schedule->cell ? 'active' : '' }}" href="{{ route('logistics.tools.inventory.prepare', ['date' => $date, 'cell' => $schedule->cell]) }}">
                                            <span class="code">{{ $schedule->cell }}</span>
                                            <span class="meta">Por preparar</span>
                                            <span class="inventory-status todo">Selecionar</span>
                                        </a>
                                    @endforeach
                                </div>
                            </details>
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
