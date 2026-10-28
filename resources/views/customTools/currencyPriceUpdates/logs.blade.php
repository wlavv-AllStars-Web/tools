@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="navbar navbar-light customPanel" style="margin-bottom: 15px;">
        <form method="get" action="{{ route('purchase.tools.currency_price_updates.logs') }}" class="row" style="align-items: end;">
            <div class="col-md-3 form-group">
                <label for="currency">Currency</label>
                <select id="currency" name="currency" class="form-control"><option value="">All</option>@foreach($currencies as $currency)<option value="{{ $currency->iso_code }}" @selected(request('currency') === $currency->iso_code)>{{ $currency->iso_code }}</option>@endforeach</select>
            </div>
            <div class="col-md-3 form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control"><option value="">All</option><option value="applied" @selected(request('status') === 'applied')>Applied</option><option value="reverted" @selected(request('status') === 'reverted')>Reverted</option><option value="failed" @selected(request('status') === 'failed')>Failed</option></select>
            </div>
            <div class="col-md-2 form-group"><button class="btn btn-primary">Search</button></div>
        </form>
    </div>
    <div class="navbar navbar-light customPanel">
        <div class="table-responsive">
            <table class="table table-hover"><thead><tr><th>Date</th><th>Currency</th><th>Rate</th><th>Lines</th><th>Applied by</th><th>Status</th><th></th></tr></thead>
                <tbody>@forelse($batches as $batch)<tr>
                    <td>{{ optional($batch->created_at)->format('Y-m-d H:i:s') }}</td><td>{{ $batch->currency_iso }}</td><td>P {{ number_format((float) $batch->old_purchase_conversion_rate, 8, '.', '') }} → {{ number_format((float) $batch->new_purchase_conversion_rate, 8, '.', '') }}<br>S {{ number_format((float) $batch->old_sale_conversion_rate, 8, '.', '') }} → {{ number_format((float) $batch->new_sale_conversion_rate, 8, '.', '') }}</td><td>{{ $batch->lines_count }}</td><td>{{ $batch->applied_by_name ?: '-' }}</td><td><strong>{{ $batch->status }}</strong></td><td><a class="btn btn-sm btn-info" href="{{ route('purchase.tools.currency_price_updates.logs.show', $batch) }}">Open log</a></td>
                </tr>@empty<tr><td colspan="7" class="text-center">No currency price updates found.</td></tr>@endforelse</tbody>
            </table>
        </div>
        {{ $batches->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>
@endsection
