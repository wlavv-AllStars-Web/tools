<style>
    .form-label { margin-bottom: 0; }
    .media-grid .col-4 { padding: 4px; }
    input, select, textarea{ text-align: center; }
</style>

<div class="container-fluid text-center">
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label fw-semibold">ORDER</label>
            <div class="d-flex gap-2">
                <input type="text" class="form-control" value="{{ $return->id_order }}" readonly>
                <input type="text" class="form-control" value="{{ $return->order->reference }}" readonly id="orderReference">
            </div>
        </div>
        <div class="col-md-8">
            <label class="form-label fw-semibold">CUSTOMER</label>
            <div class="d-flex gap-2">
                <input style="width: 80px;" type="text" class="form-control" value="{{ $return->customer->id_customer }}" readonly>
                <input style="width: 190px;" type="text" class="form-control" value="{{ $return->customer->firstname }} {{ $return->customer->lastname }}" readonly>
                <input type="text" class="form-control" value="{{ $return->customer->email }}" readonly>
            </div>
        </div>

        <div class="col-md-12">
            <div style="height: 2px; width: 100%; margin: 10px 0;background-color: #bbb;"></div>
        </div>
        
        <div class="col-md-1">
            <label class="form-label fw-semibold">QTY</label>
            <input type="text" class="form-control" value="{{ $detail->product_quantity }}" readonly>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Reference</label>
            <input type="text" class="form-control" value="{{ $detail->orderDetail->product->reference }}" readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Product Name</label>
            <input type="text" class="form-control" value="{{ $detail->orderDetail->product_name }}" readonly>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">Refund method</label>
            @if($detail->refund_method=='refund')<input type="text" class="form-control" value="Refund" readonly>@endif
            @if($detail->refund_method=='voucher')<input type="text" class="form-control" value="Voucher" readonly>@endif
        </div>
    </div>
</div>

<div class="container-fluid text-center">
    @if( ( $return->state != 3 ) && ( $return->state != 8 ) && ( $return->state != 12 ) && ( $return->state != 13 ) )
    <form id="returnStatusForm" method="POST" action="{{ route('returns.changeStatus') }}">
        @csrf
        
        <input type="hidden" name="id_order_return" value="{{$return->id_order_return}}" name="id_order_return">
        <input type="hidden" name="return_type" value="return" name="return_type">
        <div class="row">
            <div class="col-md-12">
                <div style="height: 2px; width: 100%; margin: 10px 0;background-color: #bbb;"></div>
            </div>
            <div class="col-md-3">
                <div style="margin: 7px 0;">
                    <label class="form-label fw-semibold">Update status</label>
                    <select id="returnStatusSelect" name="returnStatusSelect" class="form-select return-status-select" data-id="{{ $return->id }}">
                        <option value="">Selecione o estado</option>
                        <option value="10" {{ $return->state=='10' ? 'selected' : '' }}>Return – request registered</option>
                        <option value="11" {{ $return->state=='11' ? 'selected' : '' }}>Return – awaiting delivery</option>
                        <option value="14" {{ $return->state=='14' ? 'selected' : '' }}>Return – package received</option>
                        <option value="12" {{ $return->state=='12' ? 'selected' : '' }}>Return – not approved</option>
                        <option value="13" {{ $return->state=='13' ? 'selected' : '' }}>Return – approved</option>
                    </select>                
                </div>
            </div>
            <div id="extra-fields" class="col-md-3" style="display: contents;"></div>
        </div>
    </form>
    @endif
</div>
