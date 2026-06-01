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
                    <div class="inventory-validation-cells">
                        @foreach($schedules as $schedule)
                            <a class="inventory-cell {{ $selectedCell === $schedule->cell ? 'active' : '' }}" href="{{ route('logistics.tools.inventory.admin.verification', ['date' => $date, 'cell' => $schedule->cell]) }}">
                                <span class="code">{{ $schedule->cell }}</span>
                                <span class="meta">{{ $schedule->diff_rows }} diferencas | {{ $schedule->counted_rows }}/{{ $schedule->total_rows }} linhas</span>
                                @if($schedule->verification_done)
                                    <span class="inventory-status done">Validada</span>
                                @else
                                    <form method="POST" action="{{ route('logistics.tools.inventory.admin.verification.verify', $schedule->id) }}" class="mt-2" onclick="event.stopPropagation();">
                                        @csrf
                                        <button class="btn btn-sm btn-success" type="submit" onclick="event.stopPropagation();">Validar</button>
                                    </form>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="inventory-card">
            <div class="inventory-card-header">
                <span>Diferencas e controlo - {{ $selectedCell ?: $date }}</span>
                @if($selectedCell)
                    <a class="btn btn-sm btn-secondary" href="{{ route('logistics.tools.inventory.admin.verification', ['date' => $date]) }}">Ver tudo</a>
                @endif
            </div>
            <div class="inventory-card-body" style="overflow-x:auto;">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Celula</th>
                            <th>Referencia</th>
                            <th class="hide-mobile">Localizacao</th>
                            <th class="number">Stock</th>
                            <th>Produtos vendidos</th>
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
                                $pendingSalesByState = $row->pending_sales_by_state ?? [];
                            @endphp
                            <tr class="{{ $class }}">
                                <td>{{ $row->cell }}</td>
                                <td>
                                    @if($row->reference)
                                        <button class="inventory-copy-reference"
                                                type="button"
                                                data-reference="{{ $row->reference }}"
                                                title="Copiar referencia">
                                            {{ $row->reference }}
                                        </button>
                                    @else
                                        <strong>-</strong>
                                    @endif
                                </td>
                                <td class="hide-mobile">{{ $row->location ?: '-' }}</td>
                                <td class="number">{{ $row->current_quantity }}</td>
                                <td class="inventory-sales-states">
                                    <div class="inventory-sales-state-grid">
                                        @if(($pendingSalesByState['payment_accepted'] ?? 0) > 0)
                                            <span class="inventory-sales-state state-paid" title="Pagamento aceite">
                                                <strong>{{ $pendingSalesByState['payment_accepted'] }}</strong>
                                            </span>
                                        @endif
                                        @if(($pendingSalesByState['preparation'] ?? 0) > 0)
                                            <span class="inventory-sales-state state-preparation" title="Preparacao">
                                                <strong>{{ $pendingSalesByState['preparation'] }}</strong>
                                            </span>
                                        @endif
                                        @if(($pendingSalesByState['backorders'] ?? 0) > 0)
                                            <span class="inventory-sales-state state-backorder" title="Backorders">
                                                <strong>{{ $pendingSalesByState['backorders'] }}</strong>
                                            </span>
                                        @endif
                                        @if(($pendingSalesByState['waiting_info'] ?? 0) > 0)
                                            <span class="inventory-sales-state state-info" title="Waiting info">
                                                <strong>{{ $pendingSalesByState['waiting_info'] }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="number">{{ $hasCount ? $row->counted_quantity : '-' }}</td>
                                <td class="number">{{ $hasCount ? $diff : '-' }}</td>
                                <td style="min-width:260px;">
                                    <input class="form-control inventory-comment-input"
                                           type="text"
                                           value="{{ $row->verification_comment }}"
                                           placeholder="Comentario"
                                           data-url="{{ route('logistics.tools.inventory.admin.verification.comment', $row->id) }}">
                                </td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('logistics.tools.inventory.admin.verification.recount', $row->id) }}" onsubmit="return confirm('Enviar apenas este produto para recontagem?');">
                                        @csrf
                                        <button class="btn btn-sm btn-warning" type="submit">Recontar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center">Sem dados para verificar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $('.inventory-comment-input').on('focus', function() {
            $(this).data('original-value', $(this).val());
        });

        $('.inventory-comment-input').on('blur', function() {
            const input = $(this);
            const value = input.val();

            if (value === input.data('original-value')) {
                return;
            }

            $.post(input.data('url'), {
                _token: '{{ csrf_token() }}',
                verification_comment: value
            }).done(function() {
                input.data('original-value', value);
                input.removeClass('is-invalid').addClass('is-valid');
                setTimeout(function(){ input.removeClass('is-valid'); }, 900);
            }).fail(function() {
                input.addClass('is-invalid');
            });
        });

        $('.inventory-copy-reference').on('click', function() {
            const button = $(this);
            const reference = button.data('reference').toString();

            function markCopied() {
                button.addClass('copied');
                setTimeout(function(){ button.removeClass('copied'); }, 700);
            }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(reference).then(markCopied);
                return;
            }

            const input = $('<input>');
            $('body').append(input);
            input.val(reference).select();
            document.execCommand('copy');
            input.remove();
            markCopied();
        });
    </script>
@endsection
