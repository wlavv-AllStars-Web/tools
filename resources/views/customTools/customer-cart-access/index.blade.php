@extends('layouts.app')

@section('content')
    <div class="container py-4" style="max-width: 780px;">
        <div class="mb-4">
            <h1 class="h3 mb-1">Customer cart access</h1>
            <p class="text-muted mb-0">Cria ou reutiliza um carrinho sem encomenda e gera um acesso temporário à conta do cliente.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="alert alert-warning small">
            Este link autentica a sessão como o cliente. Utilize-o apenas para suporte autorizado e não o partilhe.
        </div>

        <form method="POST" action="{{ route('web.tools.customer_cart_access.lookup') }}" class="card card-body mb-4">
            @csrf
            <label for="email" class="form-label fw-bold">Email do cliente</label>
            <div class="input-group">
                <input id="email" name="email" type="email" required autofocus class="form-control" value="{{ old('email', $email) }}" placeholder="cliente@example.com">
                <button class="btn btn-primary" type="submit">Create cart</button>
            </div>
        </form>

        @if ($stores->isNotEmpty())
            <section class="card mb-4">
                <div class="card-header"><strong>Selecione a loja</strong></div>
                <div class="card-body">
                    <p class="mb-3">O email existe nas duas lojas. Escolhe onde deve ser criado ou reutilizado o carrinho.</p>
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach ($stores as $store)
                            <form method="POST" action="{{ route('web.tools.customer_cart_access.create') }}">
                                @csrf
                                <input type="hidden" name="email" value="{{ $email }}">
                                <input type="hidden" name="store" value="{{ $store->store }}">
                                <button class="btn btn-outline-primary" type="submit">
                                    {{ $store->store }} — {{ trim($store->firstname . ' ' . $store->lastname) }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if ($result)
            <section class="card border-success">
                <div class="card-header bg-success text-white"><strong>Link criado</strong></div>
                <div class="card-body">
                    <dl class="row mb-3">
                        <dt class="col-sm-4">Cliente</dt><dd class="col-sm-8">{{ $result['customer_name'] }} (#{{ $result['customer_id'] }})</dd>
                        <dt class="col-sm-4">Loja</dt><dd class="col-sm-8">{{ $result['store'] }}</dd>
                        <dt class="col-sm-4">Carrinho</dt><dd class="col-sm-8">#{{ $result['cart_id'] }} — {{ $result['reused'] ? 'existente reutilizado' : 'novo carrinho vazio' }}</dd>
                    </dl>
                    <a class="btn btn-success" href="{{ $result['url'] }}" target="_blank" rel="noopener">Abrir conta do cliente</a>
                </div>
            </section>
        @endif
    </div>
@endsection
