@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Auto backorder pós-shipping</h1>
                <p class="text-muted mb-0">Auditoria: não altera o estado nem o histórico da encomenda.</p>
            </div>
            <form method="GET" action="{{ route('web.tools.auto_backorder.index') }}" class="d-flex gap-2 align-items-end">
                <div>
                    <label for="audit-date" class="form-label mb-1">Data</label>
                    <input id="audit-date" name="date" type="date" class="form-control" value="{{ $selectedDate }}">
                </div>
                <button type="submit" class="btn btn-primary">Consultar</button>
            </form>
            @if ($canRunManually)
                <form method="POST" action="{{ route('web.tools.auto_backorder.run') }}" onsubmit="return confirm('Executar a verifica&ccedil;&atilde;o agora? N&atilde;o ser&atilde;o alterados estados de encomenda.');">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning">
                        Executar verifica&ccedil;&atilde;o agora
                    </button>
                </form>
            @endif
        </div>
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif


        <div class="alert alert-warning">
            Registos encontrados: <strong>{{ $audits->count() }}</strong>. O processo ignora referências técnicas configuradas.
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Data/hora</th>
                            <th>Encomenda</th>
                            <th>Motivo</th>
                            <th>Produtos não picados</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($audits as $audit)
                            <tr>
                                <td>{{ $audit->detected_at?->format('d/m/Y H:i:s') }}</td>
                                <td>
                                    <strong>#{{ $audit->id_order }}</strong>
                                    @if ($audit->order_reference)
                                        <span class="text-muted d-block small">{{ $audit->order_reference }}</span>
                                    @endif
                                </td>
                                <td>{{ $audit->reason }}</td>
                                <td>
                                    <ul class="mb-0 ps-3">
                                        @foreach ($audit->unpicked_products as $product)
                                            <li>
                                                <code>{{ $product['reference'] ?: 'sem referência' }}</code>
                                                @if ($product['name']) — {{ $product['name'] }} @endif
                                                <span class="text-muted">(qtd. {{ $product['quantity'] }})</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">Auditoria</span>
                                    <span class="d-block small text-muted">{{ $audit->original_state }} → {{ $audit->target_state }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Não existem alterações registadas nesta data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
