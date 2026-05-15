@foreach ($lastInsertedIds as $table => $data)
    @if( ( isset( $data['last_new_id'] ) ) && ( $data['last_new_id'] != '' ) )
        @if( ( (int)$data['last_old_id']  - (int)$data['last_new_id'] ) > 0)
            @if( isset( $data['insert_queries'] ) )
                @foreach ($data['insert_queries'] as $query)
                    <pre>{{ $query }}</pre>
                @endforeach
            @endif
        @endif
    @endif
@endforeach

<!--
<table border="1">
    <tr>
        <th>Tabela</th>
        <th>Coluna ID</th>
        <th>Último ID (Antiga)</th>
        <th>Último ID (Nova)</th>
        <th>Queries de Insert</th>
    </tr>
    @foreach ($lastInsertedIds as $table => $data)
    
        @if( ( isset( $data['last_new_id'] ) ) && ( $data['last_new_id'] != '' ) )
            @if( ( (int)$data['last_old_id']  - (int)$data['last_new_id'] ) > 0)
                <tr>
                    <td>{{ $table }}</td>
                    <td>{{ $data['id_column'] ?? 'Erro' }}</td>
                    <td>{{ $data['last_old_id'] }}</td>
                    <td>{{ $data['last_new_id'] }}</td>
                    <td>
                        @if( isset( $data['insert_queries'] ) )
                            @foreach ($data['insert_queries'] as $query)
                                <pre>{{ $query }}</pre>
                            @endforeach
                        @endif
                    </td>
                </tr>
            @endif
        @endif
    @endforeach
</table>
-->