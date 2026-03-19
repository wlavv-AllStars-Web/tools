<div class="{{$listType}} refundListContainer">
    <table class="table table-striped table-bordered text-center">
        <thead>
          <tr>
            <th>DATE</th>
            <th>ORDER ID</th>
            <th>MODIFIED / CANCELED</th>
            <th>RETURN FILE</th>
            <th>AMOUNT TO REFUND</th>
            <th>REFUND REASON</th>
            <th>PRODUCTS</th>
            <th>COUNTRY</th>
            <th>REFUND DATE</th>
            <th>REFUND</th>
            <th>ORDER</th>
            <th>NC</th>
            <th>NEW ORDER</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
            @foreach($refunds as $key => $refund)
            <tr class="refund-row" data-toggle="details-{{$listType}}-{{ $key }}" style="cursor: pointer;">
                <td>{{ \Carbon\Carbon::parse($refund->created_at)->format('Y-m-d') }}</td>
                <td>{{ $refund->id_order }}</td>
                <td>
                    @if($refund->order_modification == 1)
                        <i class="fas fa-check text-success"></i>
                    @else
                        <i class="fas fa-times text-danger"></i>
                    @endif
                </td>
                <td>
                    @if($refund->return_file_ok == 1)
                        <i class="fas fa-check text-success"></i>
                    @else
                        <i class="fas fa-times text-danger"></i>
                    @endif
                </td>
                <td>{{ number_format($refund->amount_to_refund, 2, ',', '.') }} €</td>
                <td>{{ $refund->refund_reason }}</td>
                <td>{{ $refund->product_reference }}</td>
                <td>{{ $refund->country }}</td>
                <td>@if(isset($refund->refund_date)){{ \Carbon\Carbon::parse($refund->refund_date)->format('Y-m-d') }}@else -- @endif</td>
                <td>{{ ucfirst($refund->refund_status) }}</td>
                <td>{{ ucfirst($refund->name) }}</td>
                <td>
                    @if($refund->credit_note != '')
                        <i class="fas fa-check text-success"></i>
                    @else
                        <i class="fas fa-times text-danger"></i>
                    @endif
                </td>
                <td>
                    @if($refund->new_order_id != 0)
                        {{ $refund->new_order_id }}
                    @else
                        --
                    @endif
                </td>
                <td> 
                    <a href="#" class="btn btn-sm btn-warning edit-refund-btn" title="Edit" onclick="editRefund({{$refund->id}})" 
                        data-id="{{ $refund->id }}"
                        data-order_id="{{ $refund->order_id }}"
                        data-purchase_date="{{ $refund->purchase_date }}"
                        data-country="{{ $refund->country }}"
                        data-client_email="{{ $refund->client_email }}"
                        data-client_name="{{ $refund->client_name }}"
                        data-order_total="{{ $refund->order_total }}"
                        data-order_modification="{{ $refund->order_modification }}"
                        data-return_file_ok="{{ $refund->return_file_ok }}"
                        data-product_reference="{{ $refund->product_reference }}"
                        data-refund_reason="{{ $refund->refund_reason }}"
                        data-refund_payment_method="{{ $refund->refund_payment_method }}"
                        data-amount_to_refund="{{ $refund->amount_to_refund }}"
                        data-amount_refunded="{{ $refund->amount_refunded }}"
                        data-refund_date="{{ $refund->refund_date }}"
                        data-credit_note="{{ $refund->credit_note }}"
                        data-refund_status="{{ $refund->refund_status }}"
                        data-order_status="{{ $refund->name }}"
                        data-new_order_id="{{ $refund->new_order_id }}"
                        data-new_order_amount="{{ $refund->new_order_amount }}"
                        data-eta="{{ $refund->eta }}"> 
                        
                        <i class="fas fa-pen" style="color: #FFF;"></i>
                    </a>
                </td>
            </tr>
      <tr class="refund-details-row d-none" id="details-{{$listType}}-{{ $key }}" style="text-align: center;">
            <td colspan="13" class="text-start" style="background-color: #fff">
                {{-- CONTEÚDO DOS DETALHES --}}
                <div class="card mb-3">
                    <div class="card-header">Order Details</div>
                    <div class="card-body row g-3" style="margin: 0px 5px;">
                        <div class="col-md-2"><strong>Order ID:</strong><br> <a href="https://all-stars-motorsport.com/admin77500/index.php?controller=AdminOrders&id_order={{ $refund->id_order }}&token={{Config::get('token')->AdminOrders}}&vieworder" target="_blank">{{ $refund->id_order }}</a></div>
                        <div class="col-md-2"><strong>Purchase Date:</strong><br> {{ \Carbon\Carbon::parse($refund->purchase_date)->format('Y-m-d') }}</div>
                        <div class="col-md-2"><strong>Country:</strong><br> {{ $refund->country }}</div>
                        <div class="col-md-2"><strong>Email:</strong><br> <span id="emailText" onclick="copyEmail()" style="cursor: pointer; color: blue;">{{ $refund->email }}</span></div>
                        <div class="col-md-2"><strong>Name:</strong><br> <a href="https://all-stars-motorsport.com/admin77500/index.php?tab=AdminCustomers&id_customer={{ $refund->id_customer }}&viewcustomer&token={{Config::get('token')->AdminCustomers}}" target="_blank">{{ $refund->firstname }} {{ $refund->lastname }}</a> </div>
                        <div class="col-md-2"><strong>Order Total:</strong><br> {{ number_format($refund->total_paid, 2, ',', '.') }} €</div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-header">Return / Cancellation</div>
                    <div class="card-body row g-2" style="margin: 0px 5px;">
                        <div class="col-md-3">
                            <strong>Modified or Cancelled:</strong><br> 
                            @if($refund->order_modification)
                                <i class="fas fa-check text-success"></i>
                            @else
                                <i class="fas fa-times text-danger"></i>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <strong>Return File OK:</strong><br> 
                            @if($refund->return_file_ok)
                                <i class="fas fa-check text-success"></i>
                            @else
                                <i class="fas fa-times text-danger"></i>
                            @endif
                        </div>
                        <div class="col-md-3"><strong>Product Reference(s):</strong> <br>{{ $refund->product_reference }}</div>
                        <div class="col-md-3"><strong>Refund Reason:</strong> <br>{{ $refund->refund_reason }}</div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-header">Refund Information</div>
                    <div class="card-body row g-3" style="margin: 0px 5px;">
                        <div class="col-md-2"><strong>Refund Method:</strong><br> {{ $refund->refund_payment_method }}</div>
                        <div class="col-md-2"><strong>Amount to Refund:</strong><br> {{ number_format($refund->amount_to_refund, 2, ',', '.') }} €</div>
                        <div class="col-md-2"><strong>Amount Refunded:</strong><br> {{ number_format($refund->amount_refunded, 2, ',', '.') }} €</div>
                        <div class="col-md-2"><strong>Refund Date:</strong><br> {{ \Carbon\Carbon::parse($refund->refund_date)->format('Y-m-d') }}</div>
                        <div class="col-md-2"><strong>Credit Note:</strong><br> {{ $refund->credit_note }}</div>
                        <div class="col-md-1"><strong>Refund:</strong><br> {{ $refund->refund_status }}</div>
                        <div class="col-md-1"><strong>Order:</strong><br> {{ $refund->name }}</div>
                    </div>
                </div>
                @if( $refund->new_order_id > 0)
                <div class="card mb-3">
                    <div class="card-header">New Order</div>
                    <div class="card-body row g-3" style="margin: 0px 5px;">
                        <div class="col-md-4"><strong>New Order ID:</strong><br><a href="https://all-stars-motorsport.com/admin77500/index.php?controller=AdminOrders&id_order={{ $refund->new_order_id }}&token={{Config::get('token')->AdminOrders}}&vieworder" target="_blank">{{ $refund->new_order_id }}</a></div>
                        <div class="col-md-4"><strong>New Order Value:</strong><br> {{ number_format($refund->new_order_amount, 2, ',', '.') }} €</div>
                        <div class="col-md-4"><strong>ETA:</strong><br> {{ $refund->eta }}</div>
                    </div>
                </div>
                @endif
            </td>
        </tr>
        
            @endforeach
        </tbody>
    </table>
</div>
