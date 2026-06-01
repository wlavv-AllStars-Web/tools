@extends('layouts.app')

@section('content')
    @include('customTools.logistics.inventory.partials.styles')

    <div class="inventory-wrap">
        @php
            $inventoryActions = [
                ['url' => route('logistics.tools.inventory.index', ['date' => $date]), 'icon' => 'fa-solid fa-house', 'label' => 'Inicio', 'class' => 'admin'],
                ['url' => route('logistics.tools.inventory.admin.index', ['date' => $date]), 'icon' => 'fa-solid fa-sliders', 'label' => 'Gestao', 'class' => 'admin'],
                ['url' => route('logistics.tools.inventory.admin.report.csv', ['date' => $date]), 'icon' => 'fa-solid fa-file-csv', 'label' => 'CSV', 'class' => 'verify'],
                ['url' => route('logistics.tools.inventory.admin.report.pdf', ['date' => $date]), 'icon' => 'fa-solid fa-file-pdf', 'label' => 'PDF', 'class' => 'report'],
            ];
            $counters = [
                ['icon' => 'fa-solid fa-layer-group', 'label' => 'Linhas', 'value' => $report['lines']->count(), 'class' => 'kpi-cells'],
                ['icon' => 'fa-solid fa-table-columns', 'label' => 'Colunas', 'value' => $report['columns']->count(), 'class' => 'kpi-prepared'],
                ['icon' => 'fa-solid fa-border-all', 'label' => 'Celulas', 'value' => $report['cells']->count(), 'class' => 'kpi-done'],
                ['icon' => 'fa-solid fa-barcode', 'label' => 'Inventario', 'value' => $report['inventory_users']->count(), 'class' => 'kpi-rows'],
                ['icon' => 'fa-solid fa-check-double', 'label' => 'Verificacao', 'value' => $report['verification_users']->count(), 'class' => 'kpi-verified'],
                ['icon' => 'fa-solid fa-note-sticky', 'label' => 'Notas', 'value' => $report['notes']->count(), 'class' => 'kpi-diff'],
            ];
        @endphp
        @include('customTools.logistics.inventory.partials.command-bar', [
            'actions' => $inventoryActions,
            'dateRoute' => route('logistics.tools.inventory.admin.report'),
            'date' => $date,
            'counters' => $counters,
        ])

        @if($report['notes']->isNotEmpty())
            <div class="alert alert-warning">
                @foreach($report['notes'] as $note)
                    <div>{{ $note }}</div>
                @endforeach
            </div>
        @endif

        <div class="inventory-card">
            <div class="inventory-card-header">Detalhe - {{ $date }}</div>
            <div class="inventory-card-body" style="overflow-x:auto;">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Linha</th>
                            <th>Coluna</th>
                            <th>Celula</th>
                            <th>Inventario por</th>
                            <th>Verificado por</th>
                            <th>Inventario em</th>
                            <th>Verificacao em</th>
                            <th class="number">Linhas</th>
                            <th class="number">Dif.</th>
                            <th>Comentarios produtos</th>
                            <th>Nota</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['schedules'] as $schedule)
                            <tr>
                                <td>{{ $schedule->line }}</td>
                                <td>{{ $schedule->column }}</td>
                                <td><strong>{{ $schedule->cell }}</strong></td>
                                <td>{{ $schedule->inventory_users }}</td>
                                <td>{{ $schedule->verification_users }}</td>
                                <td>{{ $schedule->inventory_done_at ?: '-' }}</td>
                                <td>{{ $schedule->verification_done_at ?: '-' }}</td>
                                <td class="number">{{ $schedule->total_rows }}</td>
                                <td class="number">{{ $schedule->diff_rows }}</td>
                                <td>{{ $schedule->verification_comments ?: '-' }}</td>
                                <td>{{ $schedule->date_note ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="text-center">Sem inventario concluido para esta data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
