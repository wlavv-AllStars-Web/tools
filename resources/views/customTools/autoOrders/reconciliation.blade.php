@extends('layouts.app')
@section('content')
<div class="container-fluid"><div class="row">
  <aside class="col-md-3"><div class="list-group">@foreach($manufacturers as $manufacturer)<button type="button" class="list-group-item list-group-item-action js-brand" data-id="{{ $manufacturer->id_manufacturer }}">{{ $manufacturer->name }}</button>@endforeach</div></aside>
  <main class="col-md-9"><h3>Auto Orders — missing recommendations</h3><p class="text-muted">Uses shared stock, stock arriving and draft quantities.</p><button id="add-to-draft" class="btn btn-success mb-3 d-none" type="button">Add to To order</button><div id="reconciliation-results">Select a brand.</div></main>
</div></div>
@endsection
@push('scripts')<script>
let selectedManufacturer = null;
const addButton = document.querySelector('#add-to-draft');
document.querySelectorAll('.js-brand').forEach(button => button.addEventListener('click', async () => {
 selectedManufacturer = button.dataset.id; addButton.classList.add('d-none');
 document.querySelector('#reconciliation-results').innerHTML = 'Loading…';
 const response = await fetch('{{ url('customTools/autoOrders/reconciliation') }}/' + button.dataset.id, {headers:{'X-Requested-With':'XMLHttpRequest'}});
 document.querySelector('#reconciliation-results').innerHTML = response.ok ? (await response.json()).html : 'Unable to load reconciliation.';
 if (response.ok) addButton.classList.remove('d-none');
}));
addButton.addEventListener('click', async () => {
 if (!selectedManufacturer || !confirm('Add the currently missing quantities to To order?')) return;
 addButton.disabled = true;
 const response = await fetch('{{ url('customTools/autoOrders/reconciliation') }}/' + selectedManufacturer + '/add', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','X-Requested-With':'XMLHttpRequest'}});
 const data = await response.json(); addButton.disabled = false;
 if (response.ok) { alert(data.added + ' references added to To order.'); document.querySelector('.js-brand[data-id="' + selectedManufacturer + '"]').click(); }
 else alert('Unable to add the references.');
});
</script>@endpush
