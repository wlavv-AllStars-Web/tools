@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="navbar navbar-light customPanel" style="display: block; margin-bottom: 15px;">
        <h4>Currency price update log #{{ $batch->id }}</h4>
        <p class="mb-1"><strong>{{ $batch->currency_iso }}</strong> — purchase rate {{ number_format((float) $batch->old_purchase_conversion_rate, 8, '.', '') }} → {{ number_format((float) $batch->new_purchase_conversion_rate, 8, '.', '') }}; sale rate {{ number_format((float) $batch->old_sale_conversion_rate, 8, '.', '') }} → {{ number_format((float) $batch->new_sale_conversion_rate, 8, '.', '') }} — status: <strong>{{ $batch->status }}</strong></p>
        <p class="mb-0 text-muted">Applied by {{ $batch->applied_by_name ?: '-' }} on {{ optional($batch->created_at)->format('Y-m-d H:i:s') }}.</p>
        @if($batch->status === 'reverted')<p class="mb-0 text-muted">Reverted by {{ $batch->reverted_by_name ?: '-' }} on {{ optional($batch->reverted_at)->format('Y-m-d H:i:s') }}.</p>@endif
        <a href="{{ route('purchase.tools.currency_price_updates.logs.csv', $batch) }}" class="btn btn-outline-primary" style="margin-top: 15px;">Download CSV report</a>
        @if($batch->status === 'applied')
            <form method="post" action="{{ route('purchase.tools.currency_price_updates.logs.rollback', $batch) }}" style="margin-top: 15px;" onsubmit="return confirm('Restore all EUR prices saved in this log? Global currency rates will not be changed.');">@csrf<button class="btn btn-warning">Revert this update</button></form>
        @endif
    </div>
    <div class="navbar navbar-light customPanel" style="margin-bottom: 15px;">
        <form method="get" class="row" style="align-items: end;"><div class="col-md-4 form-group"><label for="reference">Reference</label><input id="reference" name="reference" class="form-control" value="{{ request('reference') }}"></div><div class="col-md-2 form-group"><button class="btn btn-primary">Search</button></div></form>
    </div>
    <div class="navbar navbar-light customPanel">
        <div class="table-responsive"><table class="table table-sm table-hover" style="white-space: nowrap;"><thead><tr><th>Brand</th><th>Reference</th><th>Type</th><th>Purchase {{ $batch->currency_iso }}</th><th>Sale {{ $batch->currency_iso }}</th><th>Old purchase €</th><th>New purchase €</th><th>Old sale €</th><th>New sale €</th><th>Result</th></tr></thead><tbody>
            @forelse($lines as $line)<tr @if($line->reason) style="background:#fee2e2;" @endif><td>{{ $line->manufacturer }}</td><td>{{ $line->reference }}</td><td>{{ $line->line_type === 'attribute' ? 'Combination' : 'Product' }}</td><td>{{ $line->purchase_base_currency === null ? '—' : number_format((float) $line->purchase_base_currency, 6, '.', '') }}</td><td>{{ $line->sale_base_currency === null ? '—' : number_format((float) $line->sale_base_currency, 6, '.', '') }}</td><td>{{ number_format((float) $line->old_purchase_eur, 6, '.', '') }}</td><td>{{ number_format((float) $line->new_purchase_eur, 6, '.', '') }}</td><td>{{ number_format((float) $line->old_sale_eur, 6, '.', '') }}</td><td>{{ number_format((float) $line->new_sale_eur, 6, '.', '') }}</td><td>{{ $line->reason ? str_replace('_', ' ', $line->reason) : 'Updated' }}</td></tr>@empty<tr><td colspan="10" class="text-center">No lines found.</td></tr>@endforelse
        </tbody></table></div>
        {{ $lines->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>
@endsection
