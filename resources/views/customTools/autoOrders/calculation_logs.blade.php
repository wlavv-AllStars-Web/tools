@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="navbar navbar-light customPanel" style="margin-bottom: 15px;">
        <form method="get" action="{{ route('autoOrders.calculationLogs') }}" class="row" style="align-items: end;">
            <div class="col-md-3 form-group">
                <label for="reference">Reference</label>
                <input id="reference" type="text" class="form-control" name="reference" value="{{ request('reference') }}" autocomplete="off">
            </div>
            <div class="col-md-2 form-group">
                <label for="supplier">Supplier</label>
                <select id="supplier" class="form-control" name="supplier">
                    <option value="">All</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id_supplier }}" @selected((string) request('supplier') === (string) $supplier->id_supplier)>{{ $supplier->supplier }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 form-group">
                <label for="action">Result</label>
                <select id="action" class="form-control" name="action">
                    <option value="">All</option>
                    @foreach($logActions as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>{{ str_replace('_', ' ', $action) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 form-group">
                <label for="date_from">From</label>
                <input id="date_from" type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2 form-group">
                <label for="date_to">To</label>
                <input id="date_to" type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1 form-group">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Search</button>
            </div>
        </form>
    </div>

    <div class="navbar navbar-light customPanel">
        <div class="table-responsive">
            <table class="table table-sm table-hover" style="white-space: nowrap;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Supplier</th>
                        <th>Sales<br>12 months</th>
                        <th>Physical<br>stock</th>
                        <th>Arrive</th>
                        <th>Target<br>6 months</th>
                        <th>Before</th>
                        <th>Calculated</th>
                        <th>After</th>
                        <th>Result</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr @if($log->action === 'kept_above_calculated') style="background: #fff0d8;" @endif>
                            <td>{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $log->reference }}</td>
                            <td>{{ $log->supplier ?: '-' }}</td>
                            <td>{{ $log->sales_last_12_months }}</td>
                            <td>{{ $log->physical_stock }}</td>
                            <td>{{ $log->stock_arrive }}</td>
                            <td>{{ $log->target_stock }}</td>
                            <td>{{ $log->quantity_before }}</td>
                            <td>{{ $log->quantity_calculated }}</td>
                            <td>{{ $log->quantity_after }}</td>
                            <td><span style="color: dodgerblue; font-weight: 700;">{{ str_replace('_', ' ', $log->action) }}</span></td>
                            <td>{{ $log->reason ? str_replace('_', ' ', $log->reason) : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center">No automatic replenishment calculations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $logs->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>
@endsection
