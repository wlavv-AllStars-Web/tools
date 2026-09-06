@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="panel panel-default customPanel" style="display: flow-root; margin: 0 0 20px; border: 1px solid #ddd; border-radius: 4px;">
            <div class="panel-body" style="display: flow-root;">
                <form method="GET" action="{{ route('web.tools.auto_backorder.index') }}" style="float: left; margin: 0;">
                    <label for="audit-date" class="me-2">Data</label>
                    <input id="audit-date" name="date" type="date" class="form-control d-inline-block" style="width: auto;" value="{{ $selectedDate }}">
                    <button type="submit" class="btn btn-primary ms-2">Consultar</button>
                </form>

                @if ($canRunManually)
                    <form method="POST" action="{{ route('web.tools.auto_backorder.run') }}" style="float: right; margin: 0;" onsubmit="return confirm('Executar a verifica&ccedil;&atilde;o agora? N&atilde;o ser&atilde;o alterados estados de encomenda.');">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning">Executar verifica&ccedil;&atilde;o agora</button>
                    </form>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <div class="panel panel-default customPanel" style="display: flow-root; margin: 0 0 20px; border: 1px solid #ddd; border-radius: 4px;">
            <div class="panel-body" style="padding: 15px;">
                <div class="alert alert-info" style="margin: 0;">
                    Modo tempor&aacute;rio: auditoria apenas. N&atilde;o s&atilde;o alterados estados nem hist&oacute;rico das encomendas.
                </div>
            </div>
        </div>

        <div class="panel panel-default customPanel" style="display: flow-root; margin: 0 0 20px; border: 1px solid #ddd; border-radius: 4px;">
            <div class="panel-body" style="padding: 0; overflow-x: auto;">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Data/hora</th>
                            <th>Encomenda</th>
                            <th>Produtos n&atilde;o picados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($audits as $audit)
                            <tr style="{{ $audit->prestashop_url ? 'cursor: pointer;' : '' }}" @if ($audit->prestashop_url) onclick="window.open('{{ $audit->prestashop_url }}', '_blank');" @endif>
                                <td>{{ $audit->detected_at?->format('d/m/Y H:i:s') }}</td>
                                <td>
                                    <strong>#{{ $audit->id_order }}</strong>
                                    @if ($audit->order_reference)
                                        <span class="text-muted"> | </span>{{ $audit->order_reference }}
                                    @endif
                                </td>
                                <td>{{ collect($audit->unpicked_products)->pluck('reference')->filter()->implode(' | ') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">N&atilde;o existem altera&ccedil;&otilde;es registadas nesta data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
