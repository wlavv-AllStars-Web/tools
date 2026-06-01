<h2>Relatorio de inventario - {{ $date }}</h2>

<p>
    Linhas: {{ $report['lines']->implode(', ') ?: '-' }}<br>
    Colunas: {{ $report['columns']->implode(', ') ?: '-' }}<br>
    Celulas: {{ $report['cells']->implode(', ') ?: '-' }}<br>
    Inventario por: {{ $report['inventory_users']->implode(', ') ?: '-' }}<br>
    Verificado por: {{ $report['verification_users']->implode(', ') ?: '-' }}
</p>

@if($report['notes']->isNotEmpty())
    <h4>Notas</h4>
    <ul>
        @foreach($report['notes'] as $note)
            <li>{{ $note }}</li>
        @endforeach
    </ul>
@endif

<table border="1" cellpadding="4">
    <thead>
        <tr style="font-weight:bold;background-color:#f0f0f0;">
            <th>Linha</th>
            <th>Coluna</th>
            <th>Celula</th>
            <th>Inventario por</th>
            <th>Verificado por</th>
            <th>Inventario em</th>
            <th>Verificacao em</th>
            <th>Linhas</th>
            <th>Dif.</th>
            <th>Comentarios produtos</th>
            <th>Nota</th>
        </tr>
    </thead>
    <tbody>
        @forelse($report['schedules'] as $schedule)
            <tr>
                <td>{{ $schedule->line }}</td>
                <td>{{ $schedule->column }}</td>
                <td>{{ $schedule->cell }}</td>
                <td>{{ $schedule->inventory_users }}</td>
                <td>{{ $schedule->verification_users }}</td>
                <td>{{ $schedule->inventory_done_at ?: '-' }}</td>
                <td>{{ $schedule->verification_done_at ?: '-' }}</td>
                <td>{{ $schedule->total_rows }}</td>
                <td>{{ $schedule->diff_rows }}</td>
                <td>{{ $schedule->verification_comments ?: '-' }}</td>
                <td>{{ $schedule->date_note ?: '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11">Sem inventario concluido para esta data.</td>
            </tr>
        @endforelse
    </tbody>
</table>
