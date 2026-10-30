@extends('layouts.app')

@section('content')
@php($iso = $currency->iso_code)
<style>
    .currency-price-update-summary .card {
        border: 1px solid #cbd5e1;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .08);
    }

    .currency-price-update-summary strong {
        color: #0f172a;
        font-size: 1.05rem;
    }

    .currency-price-update-tabs {
        gap: 5px;
        border-bottom: 0;
    }

    .currency-price-update-tabs .nav-link {
        min-height: 66px;
        padding: .55rem .6rem;
        border: 1px solid #cbd5e1 !important;
        border-radius: 5px 5px 0 0 !important;
        background: #f8fafc !important;
        color: #1d4ed8 !important;
        font-weight: 700;
    }

    .currency-price-update-tab-title,
    .currency-price-update-tab-alerts {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .currency-price-update-tab-alerts {
        min-height: 20px;
        margin-top: 3px;
    }

    .currency-price-update-tabs .nav-link:hover {
        background: #e0f2fe !important;
    }

    .currency-price-update-tabs .nav-link.active {
        border-color: #0879df !important;
        background: #0879df !important;
        color: #fff !important;
    }

    .currency-price-update-tabs .badge {
        background: #e2e8f0 !important;
        color: #334155 !important;
    }

    .currency-price-update-tabs .nav-link.active .badge {
        background: rgba(255, 255, 255, .28) !important;
        color: #fff !important;
    }

    .currency-price-update-tabs .currency-price-update-tab-warning {
        background: #fecaca !important;
        color: #991b1b !important;
    }

    .currency-price-update-tabs .nav-link.active .currency-price-update-tab-warning {
        background: #fff !important;
        color: #991b1b !important;
    }

    .currency-price-update-table th:not(:first-child),
    .currency-price-update-table td:not(:first-child) {
        text-align: center !important;
        vertical-align: middle;
    }
</style>
<div class="container-fluid">
    <div class="navbar navbar-light customPanel currency-price-update-summary" style="display: block; margin-bottom: 15px;">
        <div class="row">
            <div class="col-lg col-md-4 mb-2">
                <div class="card h-100"><div class="card-body" style="padding: 12px 15px;">
                    <small class="text-muted d-block">Purchase price</small>
                    <strong>{{ number_format((float) $currency->purchase_conversion_rate, 2, '.', '') }} → {{ number_format((float) $newPurchaseRate, 2, '.', '') }}</strong>
                </div></div>
            </div>
            <div class="col-lg col-md-4 mb-2">
                <div class="card h-100"><div class="card-body" style="padding: 12px 15px;">
                    <small class="text-muted d-block">Sale price</small>
                    <strong>{{ number_format((float) $currency->sale_conversion_rate, 2, '.', '') }} → {{ number_format((float) $newSaleRate, 2, '.', '') }}</strong>
                </div></div>
            </div>
            <div class="col-lg col-md-4 mb-2">
                <div class="card h-100"><div class="card-body" style="padding: 12px 15px;">
                    <small class="text-muted d-block">Brands</small>
                    <strong>{{ $summary['brands'] }}</strong>
                </div></div>
            </div>
            <div class="col-lg col-md-4 mb-2">
                <div class="card h-100"><div class="card-body" style="padding: 12px 15px;">
                    <small class="text-muted d-block">Purchase updates</small>
                    <strong>{{ $summary['purchase_updates'] }}</strong>
                </div></div>
            </div>
            <div class="col-lg col-md-4 mb-2">
                <div class="card h-100"><div class="card-body" style="padding: 12px 15px;">
                    <small class="text-muted d-block">Sale updates</small>
                    <strong>{{ $summary['sale_updates'] }}</strong>
                </div></div>
            </div>
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
        <ul class="nav nav-tabs currency-price-update-tabs" role="tablist" style="display: flex; width: 100%; flex-wrap: nowrap;">
            @foreach($brands as $brand)
                <li class="nav-item" style="flex: 1 1 0; min-width: 0;">
                    @php($brandLines = $linesByBrand[$brand->manufacturer_id])
                    @php($missingPurchaseCount = $brandLines->where('missing_purchase_base_currency', true)->count())
                    @php($missingSaleCount = $brandLines->where('missing_sale_base_currency', true)->count())
                    <a class="nav-link @if($loop->first) active @endif" data-toggle="tab" data-bs-toggle="tab" data-target="#brand-{{ $brand->manufacturer_id }}" href="#brand-{{ $brand->manufacturer_id }}" role="tab" style="display: block; text-align: center;">
                        <span class="currency-price-update-tab-title">{{ $brand->manufacturer }} <span class="badge" style="background: #e2e8f0; color: #334155;">{{ $brand->products_count }}</span></span>
                        <span class="currency-price-update-tab-alerts">
                            @if($missingPurchaseCount > 0)<span class="badge currency-price-update-tab-warning" title="{{ $missingPurchaseCount }} product or attribute reference(s) without a purchase base price">P {{ $missingPurchaseCount }}</span>@endif
                            @if($missingSaleCount > 0)<span class="badge currency-price-update-tab-warning" title="{{ $missingSaleCount }} product or attribute reference(s) without a sale base price">S {{ $missingSaleCount }}</span>@endif
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="tab-content currency-price-update-content" style="border: 1px solid #dee2e6; border-top: 0; padding: 15px; background: #fff;">
            @foreach($brands as $brand)
                <div class="tab-pane fade @if($loop->first) show active @endif" id="brand-{{ $brand->manufacturer_id }}" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover currency-price-update-table" style="white-space: nowrap;">
                            <thead><tr>
                                <th>Reference</th><th class="text-center">EOF</th><th class="text-center">ACTIVE</th><th class="text-center">STOCK</th><th class="text-center">Type</th><th class="text-center">Purchase<br>{{ $iso }}</th><th class="text-center">Sale<br>{{ $iso }}</th>
                                <th class="text-center">Current<br>purchase €</th><th class="text-center">New<br>purchase €</th><th class="text-center">Current<br>sale €</th><th class="text-center">New<br>sale €</th><th class="text-center">Change<br>(€)</th><th>Status</th>
                            </tr></thead>
                            <tbody>
                            @foreach($linesByBrand[$brand->manufacturer_id] as $line)
                                @php($purchaseDelta = $line['new_purchase_eur'] - $line['old_purchase_eur'])
                                @php($saleDelta = $line['new_sale_eur'] - $line['old_sale_eur'])
                                <tr>
                                    <td @if($line['missing_purchase_base_currency'] || $line['missing_sale_base_currency']) style="background: #fee2e2;" @endif>
                                        @if($line['missing_purchase_base_currency'])
                                            <span class="badge" style="background: #fecaca; border: 1px solid #f87171; color: #991b1b;" title="Missing purchase base price"><i class="fa fa-exclamation-triangle"></i> P</span>
                                        @endif
                                        @if($line['missing_sale_base_currency'])
                                            <span class="badge" style="background: #fecaca; border: 1px solid #f87171; color: #991b1b;" title="Missing sale base price"><i class="fa fa-exclamation-triangle"></i> S</span>
                                        @endif
                                        {{ $line['reference'] }}
                                    </td>
                                    <td>
                                        @if($line['is_eof'])
                                            <span class="badge" style="background: #fecaca; color: #991b1b;">YES</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($line['is_active'])
                                            <span class="badge" style="background: #dcfce7; color: #166534;">YES</span>
                                        @else
                                            <span class="badge" style="background: #e2e8f0; color: #475569;">NO</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($line['stock_quantity'], 0, '.', '') }}</td>
                                    <td>
                                        @if($line['is_pack'])
                                            <span class="badge" style="background: #bae6fd; color: #0c4a6e;">Pack</span>
                                        @elseif($line['line_type'] === 'attribute')
                                            <span class="badge" style="background: #e2e8f0; color: #334155;">A</span>
                                        @else
                                            <span class="badge" style="background: #bfdbfe; color: #1e3a8a;">P</span>
                                        @endif
                                    </td>
                                    <td>{{ $line['purchase_base_currency'] === null ? '—' : number_format($line['purchase_base_currency'], 2, '.', '') }}</td>
                                    <td>{{ $line['sale_base_currency'] === null ? '—' : number_format($line['sale_base_currency'], 2, '.', '') }}</td>
                                    <td>{{ number_format($line['old_purchase_eur'], 2, '.', '') }}</td>
                                    <td>{{ number_format($line['new_purchase_eur'], 2, '.', '') }}</td>
                                    <td>{{ number_format($line['old_sale_eur'], 2, '.', '') }}</td>
                                    <td>{{ number_format($line['new_sale_eur'], 2, '.', '') }}</td>
                                    <td class="text-center" style="font-weight: 700;">
                                        @if($line['purchase_updated'])
                                            <span style="color: {{ $purchaseDelta > 0 ? '#dc3545' : ($purchaseDelta < 0 ? '#198754' : '#6c757d') }}; white-space: nowrap;">
                                                <i class="fa fa-arrow-{{ $purchaseDelta > 0 ? 'up' : ($purchaseDelta < 0 ? 'down' : 'right') }}"></i>
                                                P {{ $purchaseDelta >= 0 ? '+' : '' }}{{ number_format($purchaseDelta, 2, '.', '') }}
                                            </span>
                                        @endif
                                        @if($line['sale_updated'])
                                            <span style="color: {{ $saleDelta > 0 ? '#dc3545' : ($saleDelta < 0 ? '#198754' : '#6c757d') }}; white-space: nowrap; margin-left: 8px;">
                                                <i class="fa fa-arrow-{{ $saleDelta > 0 ? 'up' : ($saleDelta < 0 ? 'down' : 'right') }}"></i>
                                                S {{ $saleDelta >= 0 ? '+' : '' }}{{ number_format($saleDelta, 2, '.', '') }}
                                            </span>
                                        @endif
                                        @if(! $line['purchase_updated'] && ! $line['sale_updated'])
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $line['reason'] ? str_replace('_', ' ', $line['reason']) : 'Will update' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        <form method="post" action="{{ route('purchase.tools.currency_price_updates.preview.csv') }}" style="margin-top: 20px;">
            @csrf
            <input type="hidden" name="currency_id" value="{{ $currency->id_currency }}">
            <input type="hidden" name="new_purchase_rate" value="{{ $newPurchaseRate }}">
            <input type="hidden" name="new_sale_rate" value="{{ $newSaleRate }}">
            @foreach($brands as $brand)
                @if($brand->update_purchase)<input type="hidden" name="brand_updates[{{ $brand->manufacturer_id }}][purchase]" value="1">@endif
                @if($brand->update_sale)<input type="hidden" name="brand_updates[{{ $brand->manufacturer_id }}][sale]" value="1">@endif
            @endforeach
            <button type="submit" class="btn btn-info"><i class="fa fa-download"></i> Download preview CSV</button>
        </form>

        @if(! $applyEnabled)
            <div class="alert alert-warning" style="margin-top: 20px;">
                <strong>Preview-only mode:</strong> price updates are disabled. You can review the calculation, but no EUR prices can be changed yet.
            </div>
        @else
        <form method="post" action="{{ route('purchase.tools.currency_price_updates.apply') }}" style="margin-top: 20px;" onsubmit="return confirm('Apply all displayed EUR price changes using these temporary purchase and sale rates? The global currency rates will not be changed. A full log will be created and can be reverted.');">
            @csrf
            <input type="hidden" name="currency_id" value="{{ $currency->id_currency }}">
            <input type="hidden" name="current_purchase_rate" value="{{ $currency->purchase_conversion_rate }}">
            <input type="hidden" name="current_sale_rate" value="{{ $currency->sale_conversion_rate }}">
            <input type="hidden" name="new_purchase_rate" value="{{ $newPurchaseRate }}">
            <input type="hidden" name="new_sale_rate" value="{{ $newSaleRate }}">
            @foreach($brands as $brand)
                @if($brand->update_purchase)<input type="hidden" name="brand_updates[{{ $brand->manufacturer_id }}][purchase]" value="1">@endif
                @if($brand->update_sale)<input type="hidden" name="brand_updates[{{ $brand->manufacturer_id }}][sale]" value="1">@endif
            @endforeach
            <button type="submit" class="btn btn-success">Confirm and update prices</button>
            <a href="{{ route('purchase.tools.currency_price_updates.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
        @endif
    @endif
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.currency-price-update-tabs [data-target]').forEach(function (tab) {
        tab.addEventListener('click', function (event) {
            event.preventDefault();

            var target = document.querySelector(tab.dataset.target);
            if (!target) return;

            document.querySelectorAll('.currency-price-update-tabs .nav-link').forEach(function (item) {
                item.classList.remove('active');
            });
            document.querySelectorAll('.currency-price-update-content > .tab-pane').forEach(function (pane) {
                pane.classList.remove('show', 'active');
            });

            tab.classList.add('active');
            target.classList.add('show', 'active');
        });
    });
});
</script>
@endsection
