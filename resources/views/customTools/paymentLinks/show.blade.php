@extends('layouts.app')

@section('content')
    <div class="navbar navbar-light customPanel">
        <div class="panel panel-default" style="width: 100%;">
            <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: center;">
                <strong>Payment link request #{{ $requestItem->id }}</strong>
                @if($requestItem->isApproved())
                    @if($requestItem->isSent())
                        <span class="badge bg-secondary">Sent</span>
                    @else
                        <span class="badge bg-success">Approved</span>
                    @endif
                @else
                    <span class="badge bg-warning text-dark">Pending</span>
                @endif
            </div>

            <div class="panel-body" style="padding: 15px;">
                @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

                <table class="table table-bordered customTable">
                    <tbody>
                        <tr><th style="width: 220px;">Store</th><td>{{ $requestItem->store_code }} - {{ $requestItem->storeName() }}</td></tr>
                        <tr><th>OrderID</th><td>{{ $requestItem->order_id }}</td></tr>
                        <tr><th>Description</th><td>{{ $requestItem->description }}</td></tr>
                        <tr><th>Amount</th><td>{{ number_format((float) $requestItem->amount, 2, '.', ' ') }} EUR</td></tr>
                        <tr><th>Currency</th><td>{{ $requestItem->currency }}</td></tr>
                        <tr><th>Customer email</th><td>{{ $requestItem->customer_email }}</td></tr>
                        <tr><th>Requested by</th><td>{{ optional($requestItem->requester)->name ?? '#' . $requestItem->requested_by }}</td></tr>
                        <tr><th>Requested at</th><td>{{ optional($requestItem->requested_at)->format('Y-m-d H:i') }}</td></tr>
                    </tbody>
                </table>

                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <a class="btn btn-secondary" href="{{ $requestItem->isApproved() ? route('finance.tools.payment_links.archive') : route('finance.tools.payment_links.index') }}">Back</a>
                    @if(!$requestItem->isApproved())
                        <form method="POST" action="{{ route('finance.tools.payment_links.approve', $requestItem) }}" style="margin: 0;">
                            @csrf
                            <button class="btn btn-success" type="submit">
                                <i class="fa-solid fa-check"></i> Approve link
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
