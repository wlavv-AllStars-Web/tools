@extends('layouts.app')
@section('content')
<div class="container-fluid"><div class="row">
  <aside class="col-md-3"><div class="list-group">@foreach($manufacturers as $manufacturer)<button type="button" class="list-group-item list-group-item-action js-brand" data-id="{{ $manufacturer->id_manufacturer }}">{{ $manufacturer->name }}</button>@endforeach</div></aside>
  <main class="col-md-9"><h3>Auto Orders — missing recommendations</h3><p class="text-muted">Read-only reconciliation using shared stock, stock arriving and draft quantities.</p><div id="reconciliation-results">Select a brand.</div></main>
</div></div>
@endsection
@push('scripts')<script>
document.querySelectorAll('.js-brand').forEach(button => button.addEventListener('click', async () => {
 document.querySelector('#reconciliation-results').innerHTML = 'Loading…';
 const response = await fetch('{{ url('customTools/autoOrders/reconciliation') }}/' + button.dataset.id, {headers:{'X-Requested-With':'XMLHttpRequest'}});
 document.querySelector('#reconciliation-results').innerHTML = response.ok ? (await response.json()).html : 'Unable to load reconciliation.';
}));
</script>@endpush
