@extends('layouts.app')

@section('content')
    @include('customTools.logistics.inventory.partials.styles')

    <div class="inventory-wrap">
        @php
            $inventoryActions = [];

            if ($isManager) {
                $inventoryActions[] = ['url' => route('logistics.tools.inventory.admin.index', ['date' => $date]), 'icon' => 'fa-solid fa-sliders', 'label' => 'Gestao', 'class' => 'admin'];
                $inventoryActions[] = ['url' => route('logistics.tools.inventory.admin.map', ['date' => $date]), 'icon' => 'fa-solid fa-map-location-dot', 'label' => 'Mapa', 'class' => 'map'];
                $inventoryActions[] = ['url' => route('logistics.tools.inventory.admin.verification', ['date' => $date]), 'icon' => 'fa-solid fa-check-double', 'label' => 'Verificacao', 'class' => 'verify'];
                $inventoryActions[] = ['url' => route('logistics.tools.inventory.admin.report', ['date' => $date]), 'icon' => 'fa-solid fa-file-lines', 'label' => 'Relatorio', 'class' => 'report'];
            } else {
                $inventoryActions[] = ['url' => route('logistics.tools.inventory.prepare', ['date' => $date]), 'icon' => 'fa-solid fa-box-open', 'label' => 'Preparacao', 'class' => 'primary'];
            }

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
            'dateRoute' => route('logistics.tools.inventory.index'),
            'date' => $date,
            'counters' => $counters,
        ])

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif

        @if($schedules->isEmpty())
            <div class="alert alert-warning mb-0">Sem celulas planeadas para esta data.</div>
        @else
            <div class="inventory-workflow-columns {{ $isManager ? '' : 'two-columns' }}">
                <div class="inventory-card">
                    <div class="inventory-card-header">Preparacao pendente</div>
                    <div class="inventory-card-body">
                        @if($preparationSchedules->isEmpty())
                            <div class="alert alert-success mb-0">Sem celulas pendentes de preparacao.</div>
                        @else
                            <div class="inventory-housing-groups">
                                @foreach($preparationGroups as $group)
                                    <details class="inventory-housing-group">
                                        <summary>
                                            <strong>{{ $group->housing }}</strong>
                                            <span>{{ $group->count }} celulas</span>
                                        </summary>
                                        <div class="inventory-housing-cells">
                                            @foreach($group->schedules as $schedule)
                                                <a class="inventory-cell" href="{{ route('logistics.tools.inventory.prepare', ['date' => $date, 'cell' => $schedule->cell]) }}">
                                                    <span class="code">{{ $schedule->cell }}</span>
                                                    <span class="meta">Aguarda preparacao</span>
                                                    <span class="inventory-status todo">Preparar</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </details>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="inventory-card">
                    <div class="inventory-card-header">Pendente de inventario</div>
                    <div class="inventory-card-body">
                        @if($inventorySchedules->isEmpty())
                            <div class="alert alert-success mb-0">Sem celulas preparadas para inventario.</div>
                        @else
                            <div class="inventory-housing-groups">
                                @foreach($inventoryGroups as $group)
                                    <details class="inventory-housing-group">
                                        <summary>
                                            <strong>{{ $group->housing }}</strong>
                                            <span>{{ $group->counted_rows }}/{{ $group->total_rows }} linhas</span>
                                        </summary>
                                        <div class="inventory-housing-cells">
                                            @foreach($group->schedules as $schedule)
                                                <a class="inventory-cell" href="{{ route('logistics.tools.inventory.count', $schedule->id) }}">
                                                    <span class="code">{{ $schedule->cell }}</span>
                                                    <span class="meta">{{ $schedule->counted_rows }}/{{ $schedule->total_rows }} linhas</span>
                                                    <span class="inventory-status {{ $schedule->counted_rows > 0 ? 'progress' : 'verify' }}">{{ $schedule->counted_rows > 0 ? 'Em curso' : 'Inventariar' }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </details>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                @if($isManager)
                    <div class="inventory-card">
                        <div class="inventory-card-header">Pendente de validacao</div>
                        <div class="inventory-card-body">
                            @if($validationSchedules->isEmpty())
                                <div class="alert alert-success mb-0">Sem celulas pendentes de validacao.</div>
                            @else
                                <div class="inventory-housing-groups">
                                    @foreach($validationGroups as $group)
                                        <details class="inventory-housing-group">
                                            <summary>
                                                <strong>{{ $group->housing }}</strong>
                                                <span>{{ $group->diff_rows }} diferencas</span>
                                            </summary>
                                            <div class="inventory-housing-cells">
                                                @foreach($group->schedules as $schedule)
                                                    <a class="inventory-cell" href="{{ route('logistics.tools.inventory.admin.verification', ['date' => $date, 'cell' => $schedule->cell]) }}">
                                                        <span class="code">{{ $schedule->cell }}</span>
                                                        <span class="meta">{{ $schedule->diff_rows }} diferencas</span>
                                                        <span class="inventory-status verify">Validar</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </details>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
