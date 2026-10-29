@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="navbar navbar-light customPanel" style="max-width: 1050px; margin: 20px auto; display: block;">
        <h4>Confirm brands for {{ $currency->iso_code }}</h4>
        <p class="text-muted">Step 2 of 3 — the following brands use {{ $currency->iso_code }} as their base currency. No price is changed on this screen.</p>

        <div class="row" style="margin-bottom: 20px;">
            @forelse($brands as $brand)
                <div class="col-md-4" style="margin-bottom: 10px;">
                    <div class="card"><div class="card-body" style="padding: 12px;">
                        <strong>{{ $brand->manufacturer }}</strong><br>
                        <small class="text-muted">{{ $brand->products_count }} products</small>
                        <div class="mt-2">
                            <label class="mr-2 mb-0">
                                <input form="currency-price-preview" type="checkbox" name="brand_updates[{{ $brand->manufacturer_id }}][purchase]" value="1" checked>
                                Purchase
                            </label>
                            <label class="mb-0">
                                <input form="currency-price-preview" type="checkbox" name="brand_updates[{{ $brand->manufacturer_id }}][sale]" value="1" checked>
                                Sale
                            </label>
                        </div>
                    </div></div>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-warning">No brands are configured with this currency.</div></div>
            @endforelse
        </div>

        @if($brands->isNotEmpty())
            <form id="currency-price-preview" method="post" action="{{ route('purchase.tools.currency_price_updates.preview') }}" class="row" style="align-items: end;">
                @csrf
                <input type="hidden" name="currency_id" value="{{ $currency->id_currency }}">
                <div class="col-md-3 form-group">
                    <label>Current purchase rate</label>
                    <input type="text" class="form-control" value="{{ number_format((float) $currency->purchase_conversion_rate, 8, '.', '') }}" readonly>
                </div>
                <div class="col-md-3 form-group">
                    <label for="new_purchase_rate">New purchase rate</label>
                    <input id="new_purchase_rate" name="new_purchase_rate" type="number" class="form-control" step="0.00000001" min="0.00000001" value="{{ old('new_purchase_rate', $currency->iso_code === 'EUR' ? '1.00000000' : number_format((float) $currency->purchase_conversion_rate, 8, '.', '')) }}" @if($currency->iso_code === 'EUR') readonly @endif required>
                    @error('new_purchase_rate')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-3 form-group">
                    <label>Current sale rate ({{ $currency->sale_currency_iso }})</label>
                    <input type="text" class="form-control" value="{{ number_format((float) $currency->sale_conversion_rate, 8, '.', '') }}" readonly>
                </div>
                <div class="col-md-3 form-group">
                    <label for="new_sale_rate">New sale rate</label>
                    <input id="new_sale_rate" name="new_sale_rate" type="number" class="form-control" step="0.00000001" min="0.00000001" value="{{ old('new_sale_rate', $currency->iso_code === 'EUR' ? '1.00000000' : number_format((float) $currency->sale_conversion_rate, 8, '.', '')) }}" @if($currency->iso_code === 'EUR') readonly @endif required>
                    @error('new_sale_rate')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-12 form-group">
                    @error('brand_updates')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                    <button type="submit" class="btn btn-primary">Preview EUR price changes</button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
