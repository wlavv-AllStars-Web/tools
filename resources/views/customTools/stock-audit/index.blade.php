@extends('layouts.app')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card mb-3"><div class="card-body"><form class="row g-2">
 <div class="col-md-4"><input name="reference" value="{{ $filters['reference'] ?? '' }}" class="form-control" placeholder="Refer&ecirc;ncia"></div>
 <div class="col-md-2"><input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control" aria-label="Data inicial"></div>
 <div class="col-md-2"><input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control" aria-label="Data final"></div>
 <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filtrar</button></div>
 @if(auth()->id() === 43)<div class="col-md-2"><button type="submit" form="stock-audit-snapshot-form" class="btn btn-primary w-100">Criar backup agora</button></div>@endif
</form>
@if(auth()->id() === 43)<form id="stock-audit-snapshot-form" method="POST" action="{{ route('web.tools.stock_audit.snapshot') }}">@csrf</form>@endif
</div></div>
<div class="card"><div class="card-header">Movimentos</div><div class="table-responsive"><table class="table mb-0">
<thead><tr><th>Data</th><th>Refer&ecirc;ncia</th><th>Origem</th><th>Encomenda</th><th>User</th><th>Quantity</th><th>Stock arrive</th></tr></thead>
<tbody>
@forelse($movements as $movement)
<tr>
 <td>{{ $movement->occurred_at }}</td>
 <td>@if($movement->reference)<a href="{{ route('web.tools.stock_audit.history', ['reference' => $movement->reference]) }}">{{ $movement->reference }}</a>@else-@endif</td>
 <td>{{ $movement->source }}</td>
 <td>
  @php($meta = is_string($movement->meta) ? (json_decode($movement->meta, true) ?: []) : (array) $movement->meta)
  @php($beforeState = (array) ($meta['order_state_before'] ?? []))
  @php($afterState = (array) ($meta['order_state_after'] ?? []))
  @php($beforeId = (int) ($beforeState['id'] ?? 0))
  @php($afterId = (int) ($afterState['id'] ?? 0))
  @php($beforeColor = $stateColors[$beforeId] ?? '#6c757d')
  @php($afterColor = $stateColors[$afterId] ?? '#6c757d')
  @if($movement->id_order)
   #{{ $movement->id_order }}
   @if($beforeId > 0 && $afterId > 0)
    <small class="d-block mt-1"><span class="badge" style="background-color: {{ $beforeColor }}; color: #fff">{{ $beforeState['name'] ?? ('Estado #' . $beforeId) }}</span> &rarr; <span class="badge" style="background-color: {{ $afterColor }}; color: #fff">{{ $afterState['name'] ?? ('Estado #' . $afterId) }}</span></small>
   @else
    <small class="text-muted d-block">-</small>
   @endif
  @else
   -
  @endif
 </td>
 <td>{{ $movement->user_name ?: 'Sistema' }}</td>
 <td>@if($movement->quantity_before !== null){{ $movement->quantity_before }} &rarr; {{ $movement->quantity_after }}@else-@endif</td>
 <td>@if($movement->stock_arrive_before !== null){{ $movement->stock_arrive_before }} &rarr; {{ $movement->stock_arrive_after }}@else-@endif</td>
</tr>
@empty
<tr><td colspan="7" class="text-center text-muted">Sem movimentos registados.</td></tr>
@endforelse
</tbody></table></div>
@if(method_exists($movements,'links'))<div class="card-body">{{ $movements->links() }}</div>@endif
</div>
@endsection