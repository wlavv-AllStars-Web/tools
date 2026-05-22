@extends('layouts.app')

@section('content')
    <div class="navbar navbar-light customPanel">
        <div style="width: 100%; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h5 style="margin: 0;">{{ ($mode ?? 'pending') === 'archive' ? 'Archived payment links' : "Payment link request's" }}</h5>
            <div style="display: flex; gap: 8px;">
                <a class="btn {{ ($mode ?? 'pending') === 'pending' ? 'btn-primary' : 'btn-secondary' }}" href="{{ route('finance.tools.payment_links.index') }}">Pending</a>
                <a class="btn {{ ($mode ?? 'pending') === 'archive' ? 'btn-primary' : 'btn-secondary' }}" href="{{ route('finance.tools.payment_links.archive') }}">Archived</a>
            </div>
        </div>

        @if(session('success')) <div class="alert alert-success" style="width: 100%;">{{ session('success') }}</div> @endif

        <table class="table table-bordered customTable text-center" style="width: 100%;">
            <thead>
                <tr style="text-transform: uppercase;">
                    <th>ID</th>
                    <th>OrderID</th>
                    <th>Client email</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Requested by</th>
                    <th>Requested at</th>
                    <th>Store</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $requestItem)
                    <tr>
                        <td>{{ $requestItem->id }}</td>
                        <td>{{ $requestItem->order_id }}</td>
                        <td>{{ $requestItem->customer_email }}</td>
                        <td>{{ number_format((float) $requestItem->amount, 2, '.', ' ') }} EUR</td>
                        <td>
                            @if($requestItem->isSent())
                                <span class="badge bg-secondary">Sent</span>
                            @elseif($requestItem->isApproved())
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </td>
                        <td>{{ optional($requestItem->requester)->name ?? '#' . $requestItem->requested_by }}</td>
                        <td>{{ optional($requestItem->requested_at)->format('Y-m-d H:i') }}</td>
                        <td>
                            <strong style="color: {{ $requestItem->storeColor() }};">{{ $requestItem->storeName() }}</strong>
                        </td>
                        <td>
                            <a class="btn btn-primary btn-sm" href="{{ route('finance.tools.payment_links.show', $requestItem) }}">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">No payment link requests.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
