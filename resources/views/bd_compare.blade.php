<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparação de Bases de Dados</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .container { display: flex; gap: 20px; }
        .column { width: 50%; }
        .collapse { cursor: pointer; background-color: #f4f4f4; padding: 10px; border: 1px solid #ddd; margin-top: 10px; }
        .content { display: none; padding: 10px; border: 1px solid #ddd; }
    </style>
    <script>
        function toggleCollapse(id) {
            var content = document.getElementById(id);
            content.style.display = content.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <div style="width: 800px; margin: 0 auto;text-align: center;">
        <div style="text-align: center;">
            <h1>Comparação de Bases de Dados</h1>
        </div>
        
        <h2>Tabelas Exclusivas</h2>
        <div class="container">
            <div class="column">
                <h3>Apenas na Base Antiga</h3>
                <ul style="text-align: left;">
                    @foreach($tableDifferences['only_in_old'] as $table)
                        <li style>{{ $table }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="column">
                <h3>Apenas na Base Nova</h3>
                <ul style="text-align: left;">
                    @foreach($tableDifferences['only_in_new'] as $table)
                        <li>{{ $table }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        
        <h2>Comparação de Colunas</h2>
        @foreach($columnDifferences as $table => $differences)
        
            @if( ( count($differences['only_in_old']) + ( count($differences['only_in_new']) > 0 ) + count($differences['modified']) ) > 0)
                <div class="collapse" onclick="toggleCollapse('table-{{ $table }}')">{{ $table }}</div>
                <div class="content" id="table-{{ $table }}">
        
                    @if( ( count($differences['only_in_old']) > 0 ) || ( count($differences['only_in_new']) > 0 ) )
                    
                    <div class="container">
                        <div class="column">
                            <h4>Colunas Apenas na Base Antiga</h4>
                            <ul style="text-align: left;padding-left: 0;">
                                @foreach($differences['only_in_old'] as $column)
                                    <li style="list-style: none;text-align: center;padding-left: 0;">{{ $column }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="column">
                            <h4>Colunas Apenas na Base Nova</h4>
                            <ul style="text-align: left;padding-left: 0;">
                                @foreach($differences['only_in_new'] as $column)
                                    <li style="list-style: none;text-align: center;padding-left: 0;">{{ $column }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                    @if(count($differences['modified']) > 0)
                        <h4>Colunas Modificadas</h4>
                        <table style="text-align: center;">
                            <tr>
                                <th style="text-align: center;">Coluna</th>
                                <th style="text-align: center;">Tipo na Base Antiga</th>
                                <th style="text-align: center;">Tipo na Base Nova</th>
                            </tr>
                            @foreach($differences['modified'] as $column => $types)
                                <tr>
                                    <td style="text-align: center;">{{ $column }}</td>
                                    <td style="text-align: center;">{{ $types['old'] }}</td>
                                    <td style="text-align: center;">{{ $types['new'] }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @endif
                </div>
            @endif
            
        @endforeach
    </div>
</body>
</html>