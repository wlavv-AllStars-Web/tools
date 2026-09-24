@extends('layouts.app')
@section('content')
<div class="card mb-3"><div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
 <div><h4 class="mb-1">Hist&oacute;rico de stock: {{ $reference }}</h4><small class="text-muted">Varia&ccedil;&otilde;es registadas no per&iacute;odo selecionado.</small></div>
 <form class="d-flex gap-2 align-items-center">
  <input type="hidden" name="reference" value="{{ $reference }}">
  <label for="days" class="visually-hidden">Per&iacute;odo</label>
  <select id="days" name="days" class="form-select"><option value="7" @selected($days === 7)>7 dias</option><option value="14" @selected($days === 14)>14 dias</option><option value="30" @selected($days === 30)>30 dias</option><option value="60" @selected($days === 60)>60 dias</option></select>
  <button class="btn btn-outline-primary">Atualizar</button>
 </form>
</div></div>
@if($movements->isEmpty())
<div class="alert alert-info mb-0">Sem movimentos registados para esta refer&ecirc;ncia nos &uacute;ltimos {{ $days }} dias.</div>
@else
<div class="card mb-3"><div class="card-header">Quantity e stock arrive</div><div class="card-body"><div style="height: 380px"><canvas id="stock-chart"></canvas></div></div></div>
<div class="card"><div class="card-header">Movimentos</div><div class="table-responsive"><table class="table mb-0">
<thead><tr><th>Data</th><th>Refer&ecirc;ncia</th><th>Origem</th><th>Encomenda</th><th>User</th><th>Quantity</th><th>Stock arrive</th></tr></thead>
<tbody>
@forelse($movements as $movement)
<tr>
 <td>{{ $movement->occurred_at }}</td>
 <td>{{ $movement->reference ?: '-' }}</td>
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
@endif
@if(!$movements->isEmpty())
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
const stockAuditChart = @json($chart);
const pointColors = (values) => values.map((value, index) => {
 if (value === null) return 'transparent';
 const previous = values.slice(0, index).reverse().find(item => item !== null);
 return previous === undefined ? '#6c757d' : (value < previous ? '#dc3545' : '#198754');
});
new Chart(document.getElementById('stock-chart'), {
 type: 'line',
 data: {
  labels: stockAuditChart.labels,
  datasets: [
   { label: 'Quantity', data: stockAuditChart.quantity, borderColor: '#0d6efd', backgroundColor: '#0d6efd', pointBackgroundColor: pointColors(stockAuditChart.quantity), pointRadius: 5, pointHoverRadius: 7, borderWidth: 3, tension: 0, spanGaps: true },
   { label: 'Stock arrive', data: stockAuditChart.stockArrive, borderColor: '#198754', backgroundColor: '#198754', pointBackgroundColor: pointColors(stockAuditChart.stockArrive), pointRadius: 5, pointHoverRadius: 7, borderWidth: 3, tension: 0, spanGaps: true }
  ]
 },
 options: {
  responsive: true, maintainAspectRatio: false, interaction: { mode: 'nearest', intersect: true },
  plugins: { tooltip: { callbacks: {
   title: items => stockAuditChart.labels[items[0].dataIndex],
   label: item => item.dataset.label + ': ' + item.formattedValue,
   afterLabel: item => { const detail = stockAuditChart.details[item.dataIndex]; const change = item.dataset.label === 'Quantity' ? detail.quantity_change : detail.stock_arrive_change; const lines = ['User: ' + detail.user, 'Origem: ' + detail.source]; if (change !== null) lines.push('Variacao: ' + (change > 0 ? '+' : '') + change); if (detail.order_id) lines.push('Encomenda: #' + detail.order_id); if (detail.operation) lines.push('Operacao: ' + detail.operation); return lines; }
  } } },
  scales: { x: { ticks: { autoSkip: true, maxTicksLimit: 10, maxRotation: 0 } }, y: { ticks: { precision: 0 }, grace: '5%' } }
 }
});
</script>
@endif
@endsection