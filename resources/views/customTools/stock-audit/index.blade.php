@extends('layouts.app')
@section('content')
<div class="card mb-3"><div class="card-body d-flex justify-content-between align-items-center">
 <div><h4 class="mb-1">Stock audit</h4><small class="text-muted">Snapshots de quantity e stock_arrive, de 6 em 6 horas.</small></div>
 <form method="POST" action="{{ route('web.tools.stock_audit.snapshot') }}">@csrf<button class="btn btn-primary">Criar backup agora</button></form>
</div></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card mb-3"><div class="card-body"><form class="row g-2">
 <div class="col-md-5"><input name="reference" value="{{ $filters['reference'] ?? '' }}" class="form-control" placeholder="Refer&ecirc;ncia"></div>
 <div class="col-md-2"><input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control" aria-label="Data inicial"></div>
 <div class="col-md-2"><input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control" aria-label="Data final"></div>
 <div class="col-md-3"><button class="btn btn-outline-primary w-100">Filtrar</button></div>
</form></div></div>
<div class="card"><div class="card-header">Movimentos</div><div class="table-responsive"><table class="table mb-0">
<thead><tr><th>Data</th><th>Refer&ecirc;ncia</th><th>Origem</th><th>User</th><th>Quantity</th><th>Stock arrive</th></tr></thead>
<tbody>
@forelse($movements as $movement)
<tr>
 <td>{{ $movement->occurred_at }}</td>
 <td>{{ $movement->reference }}</td>
 <td>{{ $movement->source }}</td>
 <td>{{ $movement->user_name ?: 'Sistema' }}</td>
 <td>@if($movement->quantity_before !== null){{ $movement->quantity_before }} &rarr; {{ $movement->quantity_after }}@else-@endif</td>
 <td>@if($movement->stock_arrive_before !== null){{ $movement->stock_arrive_before }} &rarr; {{ $movement->stock_arrive_after }}@else-@endif</td>
</tr>
@empty
<tr><td colspan="6" class="text-center text-muted">Sem movimentos registados.</td></tr>
@endforelse
</tbody></table></div>
@if(method_exists($movements,'links'))<div class="card-body">{{ $movements->links() }}</div>@endif
</div>
@endsection