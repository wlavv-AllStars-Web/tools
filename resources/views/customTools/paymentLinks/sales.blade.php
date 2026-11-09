@extends('layouts.app')

@section('content')
    <div class="navbar navbar-light customPanel">
        <div style="width: 100%; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h5 style="margin: 0;">{{ ($mode ?? 'active') === 'archive' ? 'Archived payment links' : 'Payment link' }}</h5>
            <div style="display: flex; gap: 8px;">
                <a class="btn {{ ($mode ?? 'active') === 'active' ? 'btn-primary' : 'btn-secondary' }}" href="{{ route('sales.tools.payment_links.index') }}">Active</a>
                <a class="btn {{ ($mode ?? 'active') === 'archive' ? 'btn-primary' : 'btn-secondary' }}" href="{{ route('sales.tools.payment_links.archive') }}">Archived</a>
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
                    <th>Actions</th>
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
                            @elseif($requestItem->isArchived())
                                <span class="badge bg-dark">Archived</span>
                            @elseif($requestItem->isApproved())
                            <span class="badge bg-success">Ready to send</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending send</span>
                            @endif
                        </td>
                        <td>
                            <strong style="color: {{ $requestItem->storeColor() }};">{{ $requestItem->storeName() }}</strong>
                        </td>
                        <td>
                            @if(($mode ?? 'active') === 'active')
                                <div style="display: flex; justify-content: center; gap: 6px;">
                                    @if(!$requestItem->email_sent_at || in_array(auth()->user()?->role, ['admin', 'manager'], true))
                                        <form method="POST" action="{{ route('sales.tools.payment_links.send_email', $requestItem) }}" style="margin: 0;">
                                        @csrf
                                        <button class="btn btn-primary btn-sm" type="submit">
                                            <i class="fa-solid fa-envelope"></i>
                                            {{ $requestItem->email_sent_at ? 'Send new email' : 'Create and send link' }}
                                        </button>
                                    </form>
                                    @else
                                        <button class="btn btn-secondary btn-sm" type="button" disabled>
                                            <i class="fa-solid fa-lock"></i> Admin/manager only
                                        </button>
                                    @endif
                                    <form method="POST" action="{{ route('sales.tools.payment_links.archive_request', $requestItem) }}" style="margin: 0;" onsubmit="return confirm('Archive this payment link without sending an email?')">
                                        @csrf
                                        <button class="btn btn-outline-secondary btn-sm" type="submit">
                                            <i class="fa-solid fa-box-archive"></i> Archive
                                        </button>
                                    </form>
                                </div>
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
