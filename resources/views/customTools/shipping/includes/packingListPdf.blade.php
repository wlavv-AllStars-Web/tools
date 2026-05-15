<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; }
        th { font-weight: bold; }
    </style>
</head>
<body>
    <h3>Packing List</h3>
    <table>
        <thead>
            <tr>
                <th>Referência</th>
                <th>HS Code</th>
                <th>Peso</th>
                <th>Comprimento</th>
                <th>Largura</th>
                <th>Altura</th>
                <th>Quantidade</th>
            </tr>
        </thead>
        <tbody>
        @foreach($items as $it)
            <tr>
                <td>{{ $it['referencia'] ?? '' }}</td>
                <td>{{ $it['hs_code'] ?? '' }}</td>
                <td>{{ $it['weight'] ?? '' }}</td>
                <td>{{ $it['comprimento'] ?? '' }}</td>
                <td>{{ $it['largura'] ?? '' }}</td>
                <td>{{ $it['altura'] ?? '' }}</td>
                <td>{{ $it['quantidade'] ?? '' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
