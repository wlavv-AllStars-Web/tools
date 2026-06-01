@extends('layouts.app')

@section('content')
    @include('customTools.logistics.inventory.partials.styles')

    <div class="inventory-wrap">
        @php
            $inventoryActions = [
                ['url' => route('logistics.tools.inventory.index', ['date' => $date]), 'icon' => 'fa-solid fa-house', 'label' => 'Inicio', 'class' => 'admin'],
                ['url' => route('logistics.tools.inventory.admin.index', ['date' => $date]), 'icon' => 'fa-solid fa-sliders', 'label' => 'Gestao', 'class' => 'admin'],
                ['url' => route('logistics.tools.inventory.admin.verification', ['date' => $date]), 'icon' => 'fa-solid fa-check-double', 'label' => 'Verificacao', 'class' => 'verify'],
                ['url' => route('logistics.tools.inventory.admin.report', ['date' => $date]), 'icon' => 'fa-solid fa-file-lines', 'label' => 'Relatorio', 'class' => 'report'],
            ];
            $counters = [
                ['icon' => 'fa-solid fa-layer-group', 'label' => 'Linhas', 'value' => count($warehouseRows), 'class' => 'kpi-cells'],
                ['icon' => 'fa-solid fa-table-columns', 'label' => 'Colunas', 'value' => collect($warehouseRows)->sum('columns_count'), 'class' => 'kpi-prepared'],
                ['icon' => 'fa-solid fa-border-all', 'label' => 'Celulas', 'value' => collect($warehouseRows)->sum('cells_count'), 'class' => 'kpi-done'],
                ['icon' => 'fa-solid fa-wand-magic-sparkles', 'label' => 'Origem', 'value' => 'Auto', 'class' => 'kpi-verified'],
            ];
        @endphp
        @include('customTools.logistics.inventory.partials.command-bar', [
            'actions' => $inventoryActions,
            'dateRoute' => route('logistics.tools.inventory.admin.map'),
            'date' => $date,
            'counters' => $counters,
        ])

        @include('customTools.logistics.inventory.partials.warehouse-map', ['warehouseRows' => $warehouseRows, 'selectable' => false])
    </div>
@endsection
