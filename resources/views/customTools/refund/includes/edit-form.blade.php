<div class="navbar navbar-light" id="editFormContainer" style="margin: 10px 15px;">
    <form id="refundFormEdit" action="{{ route($updateRoute ?? 'refund.updateRefund') }}" method="POST" class="row g-3">
        @csrf
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <div class="card" style="padding: 0">
            <div class="card-header">Order Details <span style="color: red;float: right;"> ( SALES ) </span></div>
            <div class="card-body row g-3" style="margin: 0; padding-top: 0;">
                <input type="hidden" name="id" value="{{ $refund->id }}">
                <div class="col-md-2">
                    <label class="form-label">Order ID</label>
                    <input type="text" id="order_id" name="order_id" class="form-control" required value="{{ $refund->id_order }}" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Purchase Date</label>
                    <input type="hidden" name="lang" id="lang" class="form-control" value="{{ $refund->lang }}">
                    <input type="date" name="purchase_date" class="form-control" value="{{ \Carbon\Carbon::parse($refund->purchase_date)->format('Y-m-d') }}" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" class="form-control" value="{{ $refund->country }}" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Email</label>
                    <input type="text" id="client_email" class="form-control" readonly value="{{ $refund->email }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Name</label>
                    <input type="text" id="client_name" class="form-control" readonly value="{{ $refund->firstname }} {{ $refund->lastname }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Order total</label>
                    <input type="text" id="order_total" class="form-control" readonly value="{{ $refund->total_paid }}">
                </div>
            </div>
        </div>
        <div class="card" style="padding: 0">
            <div class="card-header">Return / Cancellation <span style="color: red;float: right;"> ( SALES ) </span></div>
            <div class="card-body row g-3" style="margin: 0; padding-top: 0;">
                <div class="col-md-2">
                    <label class="form-label">Order Modified or Cancelled</label>
                    <select name="order_modification" class="form-select">
                        <option value="1" {{ $refund->order_modification == 1 ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ $refund->order_modification == 0 ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Return File OK?</label>
                    <select name="return_file_ok" class="form-select">
                        <option value="1" {{ $refund->return_file_ok == 1 ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ $refund->return_file_ok == 0 ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Amount to Refund (€)</label>
                    <input type="text" name="amount_to_refund" class="form-control" value="{{ $refund->amount_to_refund }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Product Reference(s)</label>
                    <input type="text" name="product_reference" class="form-control" value="{{ $refund->product_reference }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Refund Reason</label>
                    <input type="text" name="refund_reason" class="form-control" value="{{ $refund->refund_reason }}">
                </div>
            </div>
        </div>
        <div class="card" style="padding: 0">
            <div class="card-header">Refund Information <span style="color: dodgerblue;float: right;"> ( ACCOUNTING ) </span></div>
            <div class="card-body row g-3" style="margin: 0; padding-top: 0;">
                <div class="col-md-2">
                    <label class="form-label">Refund Method</label>
                    <select name="refund_payment_method" class="form-select">
                        @foreach (['None', 'Ingenico', 'Paypal', 'Alma', 'Bank Transfer', 'Voucher', 'Other'] as $method)
                            <option value="{{ $method }}" {{ $refund->refund_payment_method == $method ? 'selected' : '' }}>{{ $method }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Amount Refunded (€)</label>
                    <input type="text" name="amount_refunded" class="form-control" value="{{ $refund->amount_refunded }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Refund Date</label>
                    <input type="date" name="refund_date" class="form-control" value="@if(isset($refund->refund_date)){{ \Carbon\Carbon::parse($refund->refund_date)->format('Y-m-d') }}@else @endif">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Credit Note (Moloni)</label>
                    <input type="text" name="credit_note" class="form-control" value="{{ $refund->credit_note }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Refund Status</label>
                    <select name="refund_status" class="form-select">
                        @foreach (['Pending', 'Refunded', 'Partially Refunded', 'Rejected', 'N/A'] as $status)
                            <option value="{{ $status }}" {{ $refund->refund_status == $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Order Status</label>
                    <input type="text" name="none" class="form-control" value="{{ $refund->name }}" readonly>
                </div>
            </div>
        </div>
        <div class="card" style="padding: 0">
            <div class="card-header">New Order <span style="color: red;float: right;"> ( SALES ) </span></div>
            <div class="card-body row g-3" style="margin: 0; padding-top: 0;">
                <div class="col-md-2">
                    <label class="form-label">New Order ID</label>
                    <input type="text" name="new_order_id" class="form-control" value="{{ $refund->new_order_id }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">New Order Value (€)</label>
                    <input type="text" name="new_order_amount" class="form-control" value="{{ $refund->new_order_amount }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">ETA</label>
                    <input type="text" name="eta" class="form-control" value="{{ $refund->eta }}">
                </div>
            </div>
        </div>

        <div class="navbar navbar-light customPanel">
            <div class="col-12">
                <button type="submit" class="btn btn-success">Submit</button>
            </div>
        </div>
    </form>
</div>

</div>
