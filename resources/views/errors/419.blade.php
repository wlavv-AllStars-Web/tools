<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sessão expirada</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: #f4f6f8; color: #212529; font-family: Arial, sans-serif; }
        .card { width: min(100%, 520px); padding: 32px; border: 1px solid #d9dee5; border-radius: 8px; background: #fff; box-shadow: 0 12px 30px rgba(15, 23, 42, .08); text-align: center; }
        .icon { display: inline-grid; place-items: center; width: 58px; height: 58px; margin-bottom: 16px; border-radius: 50%; background: #fff3cd; color: #997404; font-size: 28px; }
        h1 { margin: 0 0 12px; font-size: 26px; }
        p { margin: 0 0 24px; color: #667085; line-height: 1.55; }
        button { border: 0; border-radius: 5px; padding: 11px 18px; background: #0d6efd; color: #fff; font-size: 15px; font-weight: 700; cursor: pointer; }
        button:hover { background: #0b5ed7; }
    </style>
</head>
<body>
    <main class="card">
        <div class="icon">!</div>
        <h1>Sessão expirada</h1>
        <p>O pedido já não corresponde à sessão atual. Limpa a sessão e os cookies do WebTools para iniciar um login novo.</p>
        <form method="GET" action="{{ route('session.reset') }}" id="resetSessionForm">
            <button type="submit">Limpar sessão e voltar ao login</button>
        </form>
    </main>
    <script>
        document.getElementById('resetSessionForm').addEventListener('submit', function () {
            [@json(config('session.cookie')), 'laravel_session', 'XSRF-TOKEN'].forEach(function (name) {
                document.cookie = name + '=; Max-Age=0; path=/';
                document.cookie = name + '=; Max-Age=0; path=/; domain=.all-stars-motorsport.com';
            });
        });
    </script>
</body>
</html>