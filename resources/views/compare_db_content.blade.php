<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQLs Gerados - Inserções</title>
    <style>
        pre { background-color: #f4f4f4; padding: 10px; font-family: monospace; white-space: pre-wrap; word-wrap: break-word; }
    </style>
</head>
<body>
    <h1>SQLs Gerados - Inserções de Dados</h1>

    @if(count($differences['insert_queries']) > 0)
        <h3>SQLs de INSERT para registros ausentes na base nova:</h3>
        <pre>{{ implode("\n", $differences['insert_queries']) }}</pre>
    @else
        <p>Nenhuma diferença encontrada para inserção.</p>
    @endif

</body>
</html>
