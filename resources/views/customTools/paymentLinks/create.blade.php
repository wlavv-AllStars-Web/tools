@extends('layouts.app')

@section('content')
    <div class="navbar navbar-light customPanel">
        <div class="panel panel-default" style="width: 100%;">
            <div class="panel-heading">
                <strong>New payment link request</strong>
            </div>
            <div class="panel-body" style="padding: 15px;">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('sales.tools.payment_links.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="store_code">Store *</label>
                                <select id="store_code" name="store_code" class="form-control" required>
                                    @foreach($stores as $code => $name)
                                        <option value="{{ $code }}" @selected(old('store_code') === $code)>{{ $code }} - {{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="order_id">OrderID *</label>
                                <input id="order_id" name="order_id" class="form-control" type="text" value="{{ old('order_id') }}" required>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="description">Description * <small>(max. 30 chars)</small></label>
                                <input id="description" name="description" class="form-control" type="text" maxlength="30" value="{{ old('description') }}" required>
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <div class="form-group">
                                <label for="amount">Amount *</label>
                                <input id="amount" name="amount" class="form-control" type="number" min="0.01" step="0.01" value="{{ old('amount') }}" required>
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <div class="form-group">
                                <label for="currency">Currency</label>
                                <input id="currency" class="form-control" type="text" value="EUR" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label for="customer_email">Customer email *</label>
                                <input id="customer_email" name="customer_email" class="form-control" type="email" value="{{ old('customer_email') }}" required>
                            </div>
                        </div>

                        <div class="col-lg-4" style="display: flex; align-items: end; gap: 10px;">
                            <button class="btn btn-success" type="submit" style="width: 100%;">Request approval</button>
                            <a class="btn btn-secondary" href="{{ route('sales.tools.payment_links.index') }}" style="width: 100%;">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
