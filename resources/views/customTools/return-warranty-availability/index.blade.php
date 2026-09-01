@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="mb-4">
            <h1 class="h3 mb-1">Disponibilizar devolução / garantia</h1>
            <p class="text-muted mb-0">Consulta informativa. A disponibilização manual não altera tracking nem datas de entrega.</p>
        </div>

        @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

        <form method="GET" action="{{ route('web.tools.return_warranty.index') }}" class="card card-body mb-4">
            <label for="order_id" class="form-label fw-bold">ID da encomenda</label>
            <div class="input-group" style="max-width: 460px;">
                <input id="order_id" name="order_id" type="number" min="1" required class="form-control" value="{{ $orderId ?: '' }}" autofocus>
                <button class="btn btn-primary" type="submit">Consultar</button>
            </div>
        </form>

        @if ($orderId && !$order) <div class="alert alert-warning">Não foi encontrada nenhuma encomenda com o ID {{ $orderId }}.</div> @endif

        @if ($order)
            <section class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                    <strong>Encomenda #{{ $order->id_order }} — {{ $order->reference }}</strong>
                    @if ($order->return_warranty_enabled)
                        <span class="badge text-bg-success">Disponibilizada em {{ \Carbon\Carbon::parse($order->return_warranty_enabled_at)->format('d/m/Y H:i') }}</span>
                    @else
                        <span class="badge text-bg-secondary">Ainda não disponibilizada</span>
                    @endif
                </div>
                <div class="card-body row g-3">
                    <div class="col-md-4"><span class="text-muted d-block small">Cliente</span>{{ $order->customer_name ?: '—' }}</div>
                    <div class="col-md-4"><span class="text-muted d-block small">Email</span>{{ $order->email ?: '—' }}</div>
                    <div class="col-md-4"><span class="text-muted d-block small">Estado atual</span>{{ $order->state_name ?: ('ID ' . $order->current_state) }}</div>
                    <div class="col-md-4"><span class="text-muted d-block small">Criada em</span>{{ \Carbon\Carbon::parse($order->date_add)->format('d/m/Y H:i') }}</div>
                </div>
                <div class="card-footer bg-white"><form method="POST" action="{{ route('web.tools.return_warranty.enable') }}" onsubmit="return confirm('Disponibilizar esta encomenda ao cliente para devolução e garantia?');">@csrf <input type="hidden" name="order_id" value="{{ $order->id_order }}"><button class="btn btn-success" type="submit">{{ $order->return_warranty_enabled ? 'Manter disponibilizada' : 'Disponibilizar para return / warranty' }}</button></form></div>
            </section>

            <section class="card mb-4"><div class="card-header"><strong>Produtos da encomenda</strong></div><div class="table-responsive"><table class="table table-sm table-striped mb-0 align-middle"><thead><tr><th>Linha</th><th>Referência</th><th>Produto</th><th>Comprado</th><th>Expedido</th><th>Entrega confirmada</th></tr></thead><tbody>@forelse ($details as $detail)<tr><td>{{ $detail->id_order_detail }}</td><td>{{ $detail->product_reference }}</td><td>{{ $detail->product_name }}</td><td>{{ $detail->product_quantity }}</td><td>{{ $detail->qtd_sent ?? 0 }}</td><td>{{ $detail->delivered ? 'Sim' : 'Não' }}</td></tr>@empty<tr><td colspan="6" class="text-muted">A encomenda não tem linhas de produto.</td></tr>@endforelse</tbody></table></div></section>

            <section class="card mb-4"><div class="card-header"><strong>Datas de expedição</strong></div>@if ($shipments->isNotEmpty())<div class="table-responsive"><table class="table table-sm table-striped mb-0 align-middle"><thead><tr><th>Data de envio</th><th>Ciclo</th><th>Produto</th><th>Quantidade</th><th>Entrega API</th></tr></thead><tbody>@foreach ($shipments as $shipment)<tr><td>{{ $shipment->shipped_date ? \Carbon\Carbon::parse($shipment->shipped_date)->format('d/m/Y H:i') : '—' }}</td><td>{{ $shipment->cycle_number }}</td><td>{{ $shipment->product_reference }} — {{ $shipment->product_name }}</td><td>{{ $shipment->qty_shipped }}</td><td>{{ $shipment->delivered ? 'Confirmada' : 'Não confirmada' }}</td></tr>@endforeach</tbody></table></div>@else <div class="card-body text-muted">Não existem ciclos de expedição registados em <code>custom_order_shipment</code>.</div> @endif</section>

            <section class="card"><div class="card-header"><strong>Trackings</strong></div>@if ($trackings->isNotEmpty())<div class="list-group list-group-flush">@foreach ($trackings as $tracking)<div class="list-group-item d-flex flex-wrap align-items-center justify-content-between gap-2"><div><strong>{{ $tracking->tracking_number }}</strong><span class="text-muted ms-2">{{ $tracking->carrier_name ?: 'Transportadora não identificada' }}</span><span class="text-muted ms-2 small">{{ $tracking->date_add ? \Carbon\Carbon::parse($tracking->date_add)->format('d/m/Y H:i') : '' }}</span></div><a class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener" href="https://tracking.all-stars-motorsport.com/?tracking={{ rawurlencode($tracking->tracking_number) }}">Abrir tracking</a></div>@endforeach</div>@else <div class="card-body text-muted">A encomenda não tem números de tracking registados.</div> @endif</section>
        @endif
    </div>
@endsection
