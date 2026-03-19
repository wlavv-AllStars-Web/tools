<div class="navbar navbar-light" id="newRefundContainer" style="margin: 0 10px;display: none;">
    <form id="refundForm" action="{{route('refund.newRefund')}}" method="POST" class="row g-3">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        {{-- ORDER DETAILS --}}
        <div class="card" style="padding: 0">
            <div class="card-header">Order Details <span style="color: red;float: right;"> ( SALES ) </span></div>
            <div class="card-body row g-3" style="margin: 0; padding-top: 0;">
                <div class="col-md-2">
                    <label class="form-label">Order ID</label>
                    <input type="text" id="order_id" name="order_id" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Purchase Date</label>
                    <input type="hidden" name="lang" id="lang" class="form-control">
                    <input type="date" name="purchase_date" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Email</label>
                    <input type="text" id="client_email" class="form-control" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Name</label>
                    <input type="text" id="client_name" class="form-control" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Order total</label>
                    <input type="text" id="order_total" class="form-control" readonly>
                </div>
            </div>
        </div>
        <div class="card" style="padding: 0">
            <div class="card-header">Return / Cancellation <span style="color: red;float: right;"> ( SALES ) </span></div>
            <div class="card-body row g-3" style="margin: 0; padding-top: 0;">
                <div class="col-md-2">
                    <label class="form-label">Order Modified or Cancelled</label>
                    <select name="order_modification" class="form-select">
                        <option value="">None</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Product return?</label>
                    <select name="return_file_ok" class="form-select">
                        <option value="">None</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Amount to Refund (€)</label>
                    <input type="text" name="amount_to_refund" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Product Reference(s)</label>
                    <input type="text" name="product_reference" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Refund Reason</label>
                    <input type="text" name="refund_reason" class="form-control">
                </div>
            </div>
        </div>
        <div class="card" style="padding: 0">
            <div class="card-header">Refund Information <span style="color: dodgerblue;float: right;"> ( ACCOUNTING ) </span></div>
            <div class="card-body row g-3" style="margin: 0; padding-top: 0;">
                <div class="col-md-2">
                    <label class="form-label">Refund Method</label>
                    <select name="refund_payment_method" class="form-select">
                        <option value="None">None</option>
                        <option value="Ingenico">Ingenico</option>
                        <option value="Paypal">Paypal</option>
                        <option value="Alma">Alma</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Amount Refunded (€)</label>
                    <input type="text" name="amount_refunded" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Refund Date</label>
                    <input type="date" name="refund_date" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Credit Note (Moloni)</label>
                    <input type="text" name="moloni_credit_note" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Refund Status</label>
                    <select name="refund_status" class="form-select">
                        <option value="Pending">Pending</option>
                        <option value="Refunded">Refunded</option>
                        <option value="Partially Refunded">Partially Refunded</option>
                        <option value="Rejected">Rejected</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card" style="padding: 0">
            <div class="card-header">New Order <span style="color: red;float: right;"> ( SALES ) </span></div>
            <div class="card-body row g-3" style="margin: 0; padding-top: 0;">
                <div class="col-md-2">
                    <label class="form-label">New Order ID</label>
                    <input type="text" name="new_order_id" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">New Order Value (€)</label>
                    <input type="text" name="new_order_amount" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">ETA</label>
                    <input type="text" name="eta" class="form-control">
                </div>
            </div>
        </div>
        <div class="navbar navbar-light customPanel">
            <div class="col-12">
                <button type="submit" class="btn btn-success" style="float: right;">Submit</button>
            </div>
        </div>
    </form>
</div>