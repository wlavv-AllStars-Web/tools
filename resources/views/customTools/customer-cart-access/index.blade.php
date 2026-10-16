@extends('layouts.app')

@section('content')
    <div class="container py-4" style="max-width: 780px;">
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <section class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('web.tools.customer_cart_access.lookup') }}">
            @csrf
            <label for="email" class="form-label fw-bold">Email do cliente</label>
            <div class="input-group">
                <input id="email" name="email" type="email" required autofocus class="form-control" value="{{ old('email', $email) }}" placeholder="cliente@example.com">
                <button class="btn btn-primary" type="submit">Create cart</button>
            </div>
                </form>

        @if ($stores->isNotEmpty())
            <div class="border-top mt-4 pt-4">
                <p class="mb-3"><strong>Selecione a loja</strong></p>
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
        @endif

        @if ($result)
            <div class="border-top mt-4 pt-4">
                    <dl class="row mb-3">
                        <dt class="col-sm-4">Cliente</dt><dd class="col-sm-8">{{ $result['customer_name'] }} (#{{ $result['customer_id'] }})</dd>
                        <dt class="col-sm-4">Loja</dt><dd class="col-sm-8">{{ $result['store'] }}</dd>
                        <dt class="col-sm-4">Carrinho</dt><dd class="col-sm-8">#{{ $result['cart_id'] }} — {{ $result['reused'] ? 'existente reutilizado' : 'novo carrinho vazio' }}</dd>
                    </dl>
                    <label for="customer-cart-access-url" class="form-label fw-bold">Link de acesso</label>
                    <input id="customer-cart-access-url" type="text" class="form-control mb-3" value="{{ $result['url'] }}" readonly>
                    <a class="btn btn-success" href="{{ $result['url'] }}" target="_blank" rel="noopener">Abrir conta do cliente</a>
            </div>
        @endif
            </div>
        </section>
    </div>
@endsection
