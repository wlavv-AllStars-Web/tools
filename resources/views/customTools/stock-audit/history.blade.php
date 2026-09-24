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
<div class="card mb-3"><div class="card-header">Quantity</div><div class="card-body"><div style="height: 320px"><canvas id="quantity-chart"></canvas></div></div></div>
<div class="card"><div class="card-header">Stock arrive</div><div class="card-body"><div style="height: 320px"><canvas id="stock-arrive-chart"></canvas></div></div></div>
@endif
@if(!$movements->isEmpty())
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
const stockAuditCharts = @json($chart);
const createStockAuditChart = (canvasId, datasetLabel, points, color) => {
 const canvas = document.getElementById(canvasId);
 if (!canvas) return;
 if (!points.length) { canvas.parentElement.innerHTML = '<p class="text-muted mb-0">Sem movimentos deste tipo no per&iacute;odo selecionado.</p>'; return; }
 new Chart(canvas, {
  type: 'line',
  data: { labels: points.map(point => point.label), datasets: [{ label: datasetLabel, data: points.map(point => point.value), borderColor: color, backgroundColor: color, pointBackgroundColor: points.map((point, index) => point.baseline ? '#6c757d' : (index && point.value < points[index - 1].value ? '#dc3545' : '#198754')), pointRadius: 5, pointHoverRadius: 7, borderWidth: 3, stepped: 'before', tension: 0 }] },
  options: {
   responsive: true, maintainAspectRatio: false, interaction: { mode: 'nearest', intersect: true },
   plugins: { legend: { display: false }, tooltip: { callbacks: { title: items => items[0].label, label: item => datasetLabel + ': ' + item.formattedValue, afterLabel: item => { const point = points[item.dataIndex]; if (point.baseline) return 'Estado antes do primeiro movimento no per&iacute;odo'; const details = ['User: ' + point.user, 'Origem: ' + point.source]; if (point.change !== null) details.push('Variacao: ' + (point.change > 0 ? '+' : '') + point.change); if (point.order_id) details.push('Encomenda: #' + point.order_id); if (point.operation) details.push('Operacao: ' + point.operation); return details; } } } },
   scales: { x: { ticks: { autoSkip: true, maxTicksLimit: 10, maxRotation: 0 } }, y: { ticks: { precision: 0 }, grace: '5%' } }
  }
 });
};
createStockAuditChart('quantity-chart', 'Quantity', stockAuditCharts.quantity, '#0d6efd');
createStockAuditChart('stock-arrive-chart', 'Stock arrive', stockAuditCharts.stockArrive, '#198754');
</script>
@endif
@endsection