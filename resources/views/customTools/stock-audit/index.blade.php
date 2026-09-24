@extends('layouts.app')
@section('content')
<div class="card mb-3"><div class="card-body d-flex justify-content-between align-items-center">
 <div><h4 class="mb-1">Stock audit</h4><small class="text-muted">Snapshots de quantity e stock_arrive, de 6 em 6 horas.</small></div>
 <form method="POST" action="{{ route('web.tools.stock_audit.snapshot') }}">@csrf<button class="btn btn-primary">Criar backup agora</button></form>
</div></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card mb-3"><div class="card-body"><form class="row g-2">
 <div class="col-md-4"><input name="reference" value="{{ $filters['reference'] ?? '' }}" class="form-control" placeholder="Refer�ncia"></div>
 <div class="col-md-2"><input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="form-control"></div>
 <div class="col-md-2"><input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control"></div>
 <div class="col-md-2"><input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control"></div>
 <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filtrar</button></div>
</form></div></div>
<div class="card"><div class="card-header">Movimentos</div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Data</th><th>Refer�ncia</th><th>Origem</th><th>Quantity</th><th>Stock arrive</th></tr></thead><tbody>
@forelse($movements as $movement)<tr><td>{{ $movement->occurred_at }}</td><td>{{ $movement->reference }}</td><td>{{ $movement->source }}</td><td>{{ $movement->quantity_before }} ? {{ $movement->quantity_after }}</td><td>{{ $movement->stock_arrive_before }} ? {{ $movement->stock_arrive_after }}</td></tr>
@empty<tr><td colspan="5" class="text-center text-muted">Sem movimentos registados.</td></tr>@endforelse
</tbody></table></div>@if(method_exists($movements,'links'))<div class="card-body">{{ $movements->links() }}</div>@endif</div>
@endsection