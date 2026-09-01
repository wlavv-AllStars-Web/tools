@extends('layouts.app')

@section('content')
    <div class="container-fluid py-3 px-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('web.tools.return_warranty.index') }}" class="card mb-3">
            <div class="card-body p-3">
                <div class="row align-items-end g-2">
                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="order_id" class="form-label fw-bold mb-1">ID da encomenda</label>
                        <div class="input-group">
                            <input id="order_id" name="order_id" type="number" min="1" required class="form-control" value="{{ $orderId ?: '' }}" autofocus>
                            <button class="btn btn-primary" type="submit">Consultar</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        @if ($orderId && !$order)
            <div class="alert alert-warning">Nao foi encontrada nenhuma encomenda com o ID {{ $orderId }}.</div>
        @endif

        @if ($order)
            <section class="card mb-3">
                <div class="card-body p-3">
                    <div class="row align-items-center g-3">
                        <div class="col-12 col-xl-3">
                            <div class="fw-bold fs-5">Encomenda #{{ $order->id_order }}</div>
                            <div class="text-muted">{{ $order->reference }}</div>
                        </div>
                        <div class="col-6 col-md-3 col-xl-2">
                            <div class="text-muted small">Cliente</div>
                            <div>{{ $order->customer_name ?: '-' }}</div>
                        </div>
                        <div class="col-6 col-md-3 col-xl-3">
                            <div class="text-muted small">Email</div>
                            <div class="text-break">{{ $order->email ?: '-' }}</div>
                        </div>
                        <div class="col-6 col-md-3 col-xl-2">
                            <div class="text-muted small">Estado atual</div>
                            <div>{{ $order->state_name ?: ('ID ' . $order->current_state) }}</div>
                        </div>
                        <div class="col-6 col-md-3 col-xl-2">
                            <div class="text-muted small">Criada em</div>
                            <div>{{ \Carbon\Carbon::parse($order->date_add)->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="col-12 d-flex flex-wrap align-items-center gap-2 pt-1 border-top">
                            @if ($order->return_warranty_enabled)
                                <span class="badge text-bg-success">Disponibilizada em {{ \Carbon\Carbon::parse($order->return_warranty_enabled_at)->format('d/m/Y H:i') }}</span>
                            @else
                                <span class="badge text-bg-secondary">Ainda nao disponibilizada</span>
                            @endif
                            <form method="POST" action="{{ route('web.tools.return_warranty.enable') }}" onsubmit="return confirm('Disponibilizar esta encomenda ao cliente para devolucao e garantia?');">
                                @csrf
                                <input type="hidden" name="order_id" value="{{ $order->id_order }}">
                                <button class="btn btn-success btn-sm" type="submit">{{ $order->return_warranty_enabled ? 'Manter disponibilizada' : 'Disponibilizar para return / warranty' }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card mb-3">
                <div class="card-header"><strong>Produtos da encomenda</strong></div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0 align-middle">
                        <thead><tr><th>Linha</th><th>Referencia</th><th>Produto</th><th class="text-center">Comprado</th><th class="text-center">Expedido</th><th class="text-center">Entrega confirmada</th></tr></thead>
                        <tbody>
                            @forelse ($details as $detail)
                                <tr><td>{{ $detail->id_order_detail }}</td><td>{{ $detail->product_reference }}</td><td>{{ $detail->product_name }}</td><td class="text-center">{{ $detail->product_quantity }}</td><td class="text-center">{{ $detail->qtd_sent ?? 0 }}</td><td class="text-center">{{ $detail->delivered ? 'Sim' : 'Nao' }}</td></tr>
                            @empty
                                <tr><td colspan="6" class="text-muted">A encomenda nao tem linhas de produto.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="card mb-3">
                <div class="card-header"><strong>Datas de expedicao</strong></div>
                @if ($shipments->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0 align-middle">
                            <thead><tr><th>Data de expedicao</th><th>Fonte</th><th>Transportadora</th><th>Tracking</th></tr></thead>
                            <tbody>
                                @foreach ($shipments as $shipment)
                                    <tr>
                                        <td>{{ $shipment->shipped_date ? \Carbon\Carbon::parse($shipment->shipped_date)->format('d/m/Y H:i') : '-' }}</td>
                                        <td>{{ $shipment->source === 'order_carrier' ? 'Order carrier' : 'Historico: Shipped' }}</td>
                                        <td>{{ $shipment->carrier_name ?: '-' }}</td>
                                        <td>{{ $shipment->tracking_number ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="card-body text-muted">Nao foi encontrada nenhuma data de expedicao no order carrier nem no historico Shipped.</div>
                @endif
            </section>

            <section class="card">
                <div class="card-header"><strong>Trackings</strong></div>
                @if ($trackings->isNotEmpty())
                    <div class="list-group list-group-flush">
                        @foreach ($trackings as $tracking)
                            <div class="list-group-item d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div><strong>{{ $tracking->tracking_number }}</strong><span class="text-muted ms-2">{{ $tracking->carrier_name ?: 'Transportadora nao identificada' }}</span><span class="text-muted ms-2 small">{{ $tracking->date_add ? \Carbon\Carbon::parse($tracking->date_add)->format('d/m/Y H:i') : '' }}</span></div>
                                <a class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener" href="https://tracking.all-stars-motorsport.com/?tracking={{ rawurlencode($tracking->tracking_number) }}">Abrir tracking</a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="card-body text-muted">A encomenda nao tem numeros de tracking registados.</div>
                @endif
            </section>
        @endif
    </div>
@endsection
