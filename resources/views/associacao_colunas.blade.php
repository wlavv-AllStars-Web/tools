<!DOCTYPE html>
<html lang="pt">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
    </head>
    <body>
        <div style="width: 600px; margin: 0 auto;text-align: center;">
            <div>
                <h2>DATA ASSOCIATION AND COPY</h2>
            </div>
    
            <div>
                <form action="#" method="POST">
                    @csrf
                    <h3>COLUMNS MAPPING</h3>
                    <table style="text-align: center;">
                        <tr>
                            <td style="text-align: center;">CURRENT</td>
                            <td style="text-align: center;">NEW</td>
                            <td style="text-align: center;">TABLE</td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">{{ $oldDatabaseName }}</td>
                            <td style="text-align: center;">{{ $newDatabaseName }}</td>
                            <td style="text-align: center;">{{$table}}</td>
                        </tr>
                    </table>

                    <table class="table">
                        <thead>
                            <tr>
                                <th style="text-align: center;">OLD COLUMN</th>
                                <th style="text-align: center;">NEW COLUMN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($oldColumns as $column)
                                <tr>
                                    <td style="text-align: right;">{{ $column[0] }}</td>
                                    <td>
                                        <select name="column_mapping[{{ $column[0] }}]">
                                            <option value="">-- Escolha --</option>
                                            @foreach($newColumns as $newColumn)
                                                <option value="{{ $newColumn[0] }}">{{ $newColumn[0] }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary">Copiar Dados</button>
                </form>
            </div>
        </div>
    </body>
</html>