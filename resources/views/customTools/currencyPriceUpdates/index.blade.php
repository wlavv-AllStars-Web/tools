@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="navbar navbar-light customPanel currency-price-update-start" style="max-width: 760px; margin: 20px auto; display: block;">
        <style>
            .currency-price-update-start h4,
            .currency-price-update-start p { display: none; }
        </style>
        <h4 style="margin-bottom: 18px;">Currency price update</h4>
        <p class="text-muted">Step 1 of 3 — select the supplier-currency used by the brands whose EUR prices will be reviewed.</p>

        <form method="get" action="{{ route('purchase.tools.currency_price_updates.confirm') }}">
            <div class="form-group" style="display: flex; gap: 12px; margin: 0; align-items: center;">
                <label for="currency_id" class="sr-only">Currency</label>
                <select id="currency_id" name="currency_id" class="form-control" required style="flex: 1;">
                    <option value="">Select a currency</option>
                    @foreach($currencies as $currency)
                        <option value="{{ $currency->id_currency }}">{{ $currency->iso_code }} — {{ $currency->name }} (purchase: {{ number_format((float) $currency->purchase_conversion_rate, 8, '.', '') }}, sale: {{ number_format((float) $currency->sale_conversion_rate, 8, '.', '') }})</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">Continue</button>
            </div>
        </form>
    </div>
</div>
@endsection
