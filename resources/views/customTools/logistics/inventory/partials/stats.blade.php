<div class="inventory-kpis">
    <div class="inventory-kpi"><span>Celulas</span><strong>{{ $stats['cells'] }}</strong></div>
    <div class="inventory-kpi"><span>Preparadas</span><strong>{{ $stats['prepared'] }}</strong></div>
    <div class="inventory-kpi"><span>Feitas</span><strong>{{ $stats['done'] }}</strong></div>
    <div class="inventory-kpi"><span>Validadas</span><strong>{{ $stats['verified'] ?? 0 }}</strong></div>
    <div class="inventory-kpi"><span>Linhas</span><strong>{{ $stats['rows'] }}</strong></div>
    <div class="inventory-kpi"><span>Diferencas</span><strong>{{ $stats['differences'] }}</strong></div>
</div>
