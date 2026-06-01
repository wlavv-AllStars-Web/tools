@extends('layouts.app')

@section('content')
    @include('customTools.logistics.inventory.partials.styles')

    <div class="inventory-wrap">
        @php
            $inventoryActions = [
                ['url' => route('logistics.tools.inventory.index', ['date' => $date]), 'icon' => 'fa-solid fa-house', 'label' => 'Inicio', 'class' => 'admin'],
                ['url' => route('logistics.tools.inventory.admin.map', ['date' => $date]), 'icon' => 'fa-solid fa-map-location-dot', 'label' => 'Mapa', 'class' => 'map'],
                ['url' => route('logistics.tools.inventory.admin.verification', ['date' => $date]), 'icon' => 'fa-solid fa-check-double', 'label' => 'Verificacao', 'class' => 'verify'],
                ['url' => route('logistics.tools.inventory.admin.report', ['date' => $date]), 'icon' => 'fa-solid fa-file-lines', 'label' => 'Relatorio', 'class' => 'report'],
            ];
            $counters = [
                ['icon' => 'fa-solid fa-border-all', 'label' => 'Celulas', 'value' => $stats['cells'], 'class' => 'kpi-cells'],
                ['icon' => 'fa-solid fa-box-open', 'label' => 'Preparadas', 'value' => $stats['prepared'], 'class' => 'kpi-prepared'],
                ['icon' => 'fa-solid fa-barcode', 'label' => 'Feitas', 'value' => $stats['done'], 'class' => 'kpi-done'],
                ['icon' => 'fa-solid fa-check-double', 'label' => 'Validadas', 'value' => $stats['verified'] ?? 0, 'class' => 'kpi-verified'],
                ['icon' => 'fa-solid fa-list', 'label' => 'Linhas', 'value' => $stats['rows'], 'class' => 'kpi-rows'],
                ['icon' => 'fa-solid fa-triangle-exclamation', 'label' => 'Diferencas', 'value' => $stats['differences'], 'class' => 'kpi-diff'],
            ];
        @endphp
        @include('customTools.logistics.inventory.partials.command-bar', [
            'actions' => $inventoryActions,
            'dateRoute' => route('logistics.tools.inventory.admin.index'),
            'date' => $date,
            'counters' => $counters,
        ])

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif

        <form method="POST" action="{{ route('logistics.tools.inventory.admin.schedule.store') }}" class="inventory-card">
            @csrf
            <div class="inventory-card-header inventory-collapsible-header" data-target="#inventorySchedulePanel">
                <span><i class="fa-solid fa-chevron-down inventory-collapse-icon"></i> Adicionar celulas/colunas</span>
                <button class="btn btn-primary btn-sm" type="submit">Guardar selecao</button>
            </div>
            <div id="inventorySchedulePanel" class="inventory-card-body inventory-collapsible-body" style="display:none;">
                <input type="hidden" name="date" value="{{ $date }}">
                @include('customTools.logistics.inventory.partials.warehouse-map', [
                    'warehouseRows' => $warehouseRows,
                    'selectable' => true,
                    'showLegend' => false,
                    'planSchedules' => $schedules,
                    'date' => $date,
                ])
            </div>
        </form>

        @foreach($schedules as $schedule)
            <form id="inventory-delete-schedule-{{ $schedule->id }}" method="POST" action="{{ route('logistics.tools.inventory.admin.schedule.destroy', $schedule->id) }}" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    </div>

    <script>
        $('.inventory-collapsible-header').on('click', function(event) {
            if ($(event.target).closest('button').length) {
                return;
            }

            const target = $($(this).data('target'));
            target.slideToggle(160);
            $(this).find('.inventory-collapse-icon').toggleClass('open');
        });
    </script>
@endsection
