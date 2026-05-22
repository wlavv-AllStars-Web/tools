@extends('layouts.app')

@section('content')
    <div class="navbar navbar-light customPanel">
        <div style="width: 100%; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h5 style="margin: 0;">{{ ($mode ?? 'active') === 'sent' ? 'Sent payment links' : 'Payment link' }}</h5>
            <div style="display: flex; gap: 8px;">
                <a class="btn {{ ($mode ?? 'active') === 'active' ? 'btn-primary' : 'btn-secondary' }}" href="{{ route('sales.tools.payment_links.index') }}">Active</a>
                <a class="btn {{ ($mode ?? 'active') === 'sent' ? 'btn-primary' : 'btn-secondary' }}" href="{{ route('sales.tools.payment_links.sent') }}">Sent</a>
                <a class="btn btn-success" href="{{ route('sales.tools.payment_links.create') }}">
                    <i class="fa-solid fa-plus"></i> New request
                </a>
            </div>
        </div>

        @if(session('success')) <div class="alert alert-success" style="width: 100%;">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="alert alert-danger" style="width: 100%;">{{ session('error') }}</div> @endif

        <table class="table table-bordered customTable text-center" style="width: 100%;">
            <thead>
                <tr style="text-transform: uppercase;">
                    <th>Client email</th>
                    <th>Voucher value</th>
                    <th>OrderID</th>
                    <th>Status</th>
                    <th>Store</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $requestItem)
                    <tr>
                        <td>{{ $requestItem->customer_email }}</td>
                        <td>{{ number_format((float) $requestItem->amount, 2, '.', ' ') }} EUR</td>
                        <td>{{ $requestItem->order_id }}</td>
                        <td>
                            @if($requestItem->isSent())
                                <span class="badge bg-secondary">Sent</span>
                            @elseif($requestItem->isApproved())
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending finance</span>
                            @endif
                        </td>
                        <td>
                            <strong style="color: {{ $requestItem->storeColor() }};">{{ $requestItem->storeName() }}</strong>
                        </td>
                        <td>
                            @if($requestItem->isApproved())
                                @if(!$requestItem->email_sent_at || in_array(auth()->user()?->role, ['admin', 'manager'], true))
                                    <form method="POST" action="{{ route('sales.tools.payment_links.send_email', $requestItem) }}" style="margin: 0;">
                                        @csrf
                                        <button class="btn btn-primary btn-sm" type="submit">
                                            <i class="fa-solid fa-envelope"></i>
                                            {{ $requestItem->email_sent_at ? 'Send new email' : 'Send email' }}
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-secondary btn-sm" type="button" disabled>
                                        <i class="fa-solid fa-lock"></i> Admin/manager only
                                    </button>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No payment link requests.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
