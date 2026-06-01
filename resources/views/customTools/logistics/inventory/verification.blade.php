@extends('layouts.app')

@section('content')
    @include('customTools.logistics.inventory.partials.styles')

    <div class="inventory-wrap">
        @php
            $inventoryActions = [
                ['url' => route('logistics.tools.inventory.index', ['date' => $date]), 'icon' => 'fa-solid fa-house', 'label' => 'Inicio', 'class' => 'admin'],
                ['url' => route('logistics.tools.inventory.admin.index', ['date' => $date]), 'icon' => 'fa-solid fa-sliders', 'label' => 'Gestao', 'class' => 'admin'],
                ['url' => route('logistics.tools.inventory.admin.map', ['date' => $date]), 'icon' => 'fa-solid fa-map-location-dot', 'label' => 'Mapa', 'class' => 'map'],
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
            'dateRoute' => route('logistics.tools.inventory.admin.verification'),
            'date' => $date,
            'counters' => $counters,
        ])

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif

        <div class="inventory-card">
            <div class="inventory-card-header">Celulas para validacao - {{ $date }}</div>
            <div class="inventory-card-body">
                @if($schedules->isEmpty())
                    <div class="alert alert-warning mb-0">Sem celulas inventariadas para validar.</div>
                @else
                    <div class="inventory-grid">
                        @foreach($schedules as $schedule)
                            <div class="inventory-cell">
                                <span class="code">{{ $schedule->cell }}</span>
                                <span class="meta">{{ $schedule->diff_rows }} diferencas | {{ $schedule->counted_rows }}/{{ $schedule->total_rows }} linhas</span>
                                @if($schedule->verification_done)
                                    <span class="inventory-status done">Validada</span>
                                @else
                                    <form method="POST" action="{{ route('logistics.tools.inventory.admin.verification.verify', $schedule->id) }}" class="mt-2">
                                        @csrf
                                        <button class="btn btn-sm btn-success" type="submit">Validar</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="inventory-card">
            <div class="inventory-card-header">Diferencas e controlo - {{ $date }}</div>
            <div class="inventory-card-body" style="overflow-x:auto;">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Celula</th>
                            <th>Referencia</th>
                            <th class="hide-mobile">Localizacao</th>
                            <th class="number">Stock</th>
                            <th class="number">Contagem</th>
                            <th class="number">Dif.</th>
                            <th>Comentario</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            @php
                                $hasCount = $row->counted_quantity !== null;
                                $diff = $hasCount ? ((int) $row->counted_quantity - (int) $row->current_quantity) : null;
                                $class = !$hasCount ? '' : ($diff === 0 ? 'inventory-row-ok' : 'inventory-row-diff');
                            @endphp
                            <tr class="{{ $class }}">
                                <td>{{ $row->cell }}</td>
                                <td><strong>{{ $row->reference ?: '-' }}</strong></td>
                                <td class="hide-mobile">{{ $row->location ?: '-' }}</td>
                                <td class="number">{{ $row->current_quantity }}</td>
                                <td class="number">{{ $hasCount ? $row->counted_quantity : '-' }}</td>
                                <td class="number">{{ $hasCount ? $diff : '-' }}</td>
                                <td style="min-width:260px;">
                                    <form method="POST" action="{{ route('logistics.tools.inventory.admin.verification.comment', $row->id) }}">
                                        @csrf
                                        <textarea class="form-control" name="verification_comment" rows="2" placeholder="Comentario">{{ $row->verification_comment }}</textarea>
                                        <button class="btn btn-sm btn-outline-primary mt-1" type="submit">Guardar</button>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('logistics.tools.inventory.admin.verification.recount', $row->id) }}" onsubmit="return confirm('Enviar apenas este produto para recontagem?');">
                                        @csrf
                                        <button class="btn btn-sm btn-warning" type="submit">Recontar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center">Sem dados para verificar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
