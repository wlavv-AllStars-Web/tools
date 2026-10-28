@extends('layouts.app')

@section('content')
@php($iso = $currency->iso_code)
<div class="container-fluid">
    <div class="navbar navbar-light customPanel" style="display: block; margin-bottom: 15px;">
        <h4>Price preview — {{ $iso }}</h4>
        <p class="mb-1">Step 3 of 3 — review all affected brands and references before applying the update.</p>
        <div class="row" style="margin-top: 10px;">
            <div class="col-md-3"><strong>Purchase rate:</strong> {{ number_format((float) $currency->purchase_conversion_rate, 8, '.', '') }} → {{ number_format((float) $newPurchaseRate, 8, '.', '') }}</div>
            <div class="col-md-3"><strong>Sale rate:</strong> {{ number_format((float) $currency->sale_conversion_rate, 8, '.', '') }} → {{ number_format((float) $newSaleRate, 8, '.', '') }}</div>
            <div class="col-md-2"><strong>Brands:</strong> {{ $summary['brands'] }}</div>
            <div class="col-md-2"><strong>Purchase updates:</strong> {{ $summary['purchase_updates'] }}</div>
            <div class="col-md-2"><strong>Sale updates:</strong> {{ $summary['sale_updates'] }}</div>
        </div>
        @if($summary['invalid_lines'] > 0)
            <div class="alert alert-danger" style="margin: 15px 0 0;">
                <strong>Attention:</strong> {{ $summary['invalid_lines'] }} reference(s) are invalid for update because the purchase or sale base-currency price is missing. They will not be updated.
            </div>
        @endif
    </div>

    @if($summary['lines'] === 0)
        <div class="alert alert-warning">No products or combinations were found for this currency.</div>
    @else
        <ul class="nav nav-tabs" role="tablist">
            @foreach($brands as $brand)
                <li class="nav-item"><a class="nav-link @if($loop->first) active @endif" data-toggle="tab" href="#brand-{{ $brand->manufacturer_id }}" role="tab">{{ $brand->manufacturer }} <span class="badge badge-secondary">{{ $linesByBrand[$brand->manufacturer_id]->count() }}</span></a></li>
            @endforeach
        </ul>
        <div class="tab-content" style="border: 1px solid #dee2e6; border-top: 0; padding: 15px; background: #fff;">
            @foreach($brands as $brand)
                <div class="tab-pane fade @if($loop->first) show active @endif" id="brand-{{ $brand->manufacturer_id }}" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" style="white-space: nowrap;">
                            <thead><tr>
                                <th>Reference</th><th>Type</th><th>Purchase {{ $iso }}</th><th>Sale {{ $iso }}</th>
                                <th>Current purchase €</th><th>New purchase €</th><th>Current sale €</th><th>New sale €</th><th>EUR change</th><th>Status</th>
                            </tr></thead>
                            <tbody>
                            @foreach($linesByBrand[$brand->manufacturer_id] as $line)
                                @php($purchaseDelta = $line['new_purchase_eur'] - $line['old_purchase_eur'])
                                @php($saleDelta = $line['new_sale_eur'] - $line['old_sale_eur'])
                                <tr @if($line['invalid_for_update']) style="background: #fee2e2;" @endif>
                                    <td>{{ $line['reference'] }}</td>
                                    <td>{{ $line['line_type'] === 'attribute' ? 'Combination' : 'Product' }}</td>
                                    <td>{{ $line['purchase_base_currency'] === null ? '—' : number_format($line['purchase_base_currency'], 6, '.', '') }}</td>
                                    <td>{{ $line['sale_base_currency'] === null ? '—' : number_format($line['sale_base_currency'], 6, '.', '') }}</td>
                                    <td>{{ number_format($line['old_purchase_eur'], 6, '.', '') }}</td>
                                    <td>{{ number_format($line['new_purchase_eur'], 6, '.', '') }}</td>
                                    <td>{{ number_format($line['old_sale_eur'], 6, '.', '') }}</td>
                                    <td>{{ number_format($line['new_sale_eur'], 6, '.', '') }}</td>
                                    <td style="color: {{ ($purchaseDelta + $saleDelta) > 0 ? '#d97706' : (($purchaseDelta + $saleDelta) < 0 ? '#15803d' : '#555') }}; font-weight: 700;">P {{ $purchaseDelta >= 0 ? '+' : '' }}{{ number_format($purchaseDelta, 6, '.', '') }} / S {{ $saleDelta >= 0 ? '+' : '' }}{{ number_format($saleDelta, 6, '.', '') }}</td>
                                    <td>{{ $line['reason'] ? str_replace('_', ' ', $line['reason']) : 'Will update' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        <form method="post" action="{{ route('purchase.tools.currency_price_updates.apply') }}" style="margin-top: 20px;" onsubmit="return confirm('Apply all displayed EUR price changes using these temporary purchase and sale rates? The global currency rates will not be changed. A full log will be created and can be reverted.');">
            @csrf
            <input type="hidden" name="currency_id" value="{{ $currency->id_currency }}">
            <input type="hidden" name="current_purchase_rate" value="{{ $currency->purchase_conversion_rate }}">
            <input type="hidden" name="current_sale_rate" value="{{ $currency->sale_conversion_rate }}">
            <input type="hidden" name="new_purchase_rate" value="{{ $newPurchaseRate }}">
            <input type="hidden" name="new_sale_rate" value="{{ $newSaleRate }}">
            <button type="submit" class="btn btn-success">Confirm and update prices</button>
            <a href="{{ route('purchase.tools.currency_price_updates.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    @endif
</div>
@endsection
