@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Moloni · VIES VAT tracking</h3>
        <a class="btn btn-outline-secondary" href="{{ route('web.tools.moloni_vat.index') }}">Todos</a>
    </div>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

    <form method="GET" action="{{ route('web.tools.moloni_vat.index') }}" class="card card-body mb-3">
        <div class="row align-items-end">
            <div class="col-sm-4 col-md-3">
                <label class="form-label" for="status">Estado</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    @foreach($statuses as $key=>$label)<option value="{{ $key }}" @selected($selectedStatus===$key)>{{ $label }}</option>@endforeach
                </select>
            </div>
            <div class="col-sm-2"><button class="btn btn-primary" type="submit">Filtrar</button></div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Estado</th><th>VAT</th><th>Última tentativa</th><th>Válido até</th><th>Encomendas</th><th>Detalhe</th><th></th></tr></thead>
                <tbody>
                @forelse($validations as $validation)
                    @php($color=['valid'=>'success','invalid'=>'danger','missing_vat'=>'danger','manual_review'=>'danger','pending'=>'warning','processing'=>'warning','retry_scheduled'=>'warning'][$validation->status]??'secondary')
                    <tr>
                        <td><span class="badge text-bg-{{ $color }}">{{ $statuses[$validation->status]??$validation->status }}</span></td>
                        @php($vatForDisplay=\App\Models\modules\moloni_vat_validation\MoloniVatValidation::normalizeVatNumber($validation->vat_number,$validation->country_iso))
                        <td><strong>{{ $validation->country_iso }}{{ $vatForDisplay }}</strong><small class="d-block text-muted">tentativas: {{ $validation->attempts }}</small></td>
                        <td>{{ $validation->last_attempt_at?->format('d/m/Y H:i')??'—' }}</td>
                        <td>{{ $validation->valid_until?->format('d/m/Y H:i')??'—' }}</td>
                        <td>
                            @foreach($validation->orders as $link)
                                @if($link->prestashop_url)<a class="d-block" target="_blank" href="{{ $link->prestashop_url }}">#{{ $link->id_order }} {{ $link->order_reference }}</a>@else<span class="d-block">#{{ $link->id_order }} {{ $link->order_reference }}</span>@endif
                            @endforeach
                            @if($validation->orders_count>count($validation->orders))<small class="text-muted">+{{ $validation->orders_count-count($validation->orders) }} encomenda(s)</small>@endif
                        </td>
                        <td><small>{{ $validation->last_error?:'—' }}</small></td>
                        <td>@if($validation->status!=='valid')<form method="POST" action="{{ route('web.tools.moloni_vat.retry',$validation) }}">@csrf<button class="btn btn-sm btn-outline-primary" type="submit">Tentar agora</button></form>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Sem registos.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($validations->hasPages())<div class="card-body">{{ $validations->links() }}</div>@endif
    </div>
</div>
@endsection
