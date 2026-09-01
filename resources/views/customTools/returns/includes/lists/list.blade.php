<table id="ordersTable" class="display" style="width:100%;text-align: center;">
    <thead>
        <tr style="text-align: center;">
            <th></th>
            <th>RETURN</th>
            <th>ORDER</th>
            <th>STATUS</th>
            <th>REFERENCE</th>
            <th>CUSTOMER</th>
            <th>REFUND METHOD</th>
        </tr>
    </thead>
    <tbody>
        @foreach($returns AS $return)
            @php
                $orderAdminUrl = \App\Services\Prestashop\PrestashopAdminLinkService::dashboardOrderAdminUrl((int) $return->id_order, 'ASM');
                $customer = $return->customer;
                $order = $return->order;
                $status = $return->status;
                $statusName = data_get($status, 'lang.name', '-');
                $statusColor = data_get($status, 'color', '#6c757d');
                $customerAdminUrl = $customer
                    ? \App\Services\Prestashop\PrestashopAdminLinkService::dashboardCustomerAdminUrl((int) $customer->id_customer, 'ASM')
                    : null;
            @endphp
            <tr>
                <td style="text-align: center;">
                    @if(optional($order)->id_lang == 2) <img style="border: 1px solid #ccc;border-radius: 8px;" src="/images/flags/en.png" alt="English" width="25" height="20"> @endif
                    @if(optional($order)->id_lang == 4) <img style="border: 1px solid #ccc;border-radius: 8px;" src="/images/flags/es.png" alt="Spanish" width="25" height="20"> @endif
                    @if(optional($order)->id_lang == 5) <img style="border: 1px solid #ccc;border-radius: 8px;" src="/images/flags/fr.png" alt="French" width="25" height="20"> @endif
                </td>
                <td>{{date('d-m-Y', strtotime($return->date_add))}}</td>
                <td style="cursor: pointer;" @if($orderAdminUrl) onclick="window.open(@json($orderAdminUrl), '_blank')" @endif>{{$return->id_order}}</td>
                <td>
                    <button class="open-ajax-modal" style="display: none;" data-route="{{ route('returns.getModal', ['id' => $return->id_order_return]) }}">{{$statusName}}</button>
                    @if( ( $return->state != 3 ) && ( $return->state != 8 ) && ( $return->state != 12 ) && ( $return->state != 13 ) )
                        <form id="returnStatusForm" class="formSubmit-{{$return->id_order_return}}" method="POST" action="{{ route('returns.changeStatus') }}">
                            @csrf
                            <input type="hidden" name="id_order_return" value="{{$return->id_order_return}}" name="id_order_return">
                            <input type="hidden" name="return_type" value="return" name="return_type">
                            <select id="returnStatusSelect" name="returnStatusSelect" class="form-select return-status-select" data-id="{{ $return->id_order_return }}" style="text-align: center;color: #FFF;background-color: {{$statusColor}};">
                                <option value="11" {{ $return->state=='11' ? 'selected' : '' }}>Return – awaiting delivery</option>
                                <option value="14" {{ $return->state=='14' ? 'selected' : '' }}>Return – package received</option>
                                <option value="12" {{ $return->state=='12' ? 'selected' : '' }}>Return – not approved</option>
                                <option value="13" {{ $return->state=='13' ? 'selected' : '' }}>Return – approved</option>
                            </select>                
                        </form>
                    @else
                        <div style="text-align: center;color: #FFF;background-color: {{$statusColor}}; padding: 10px;border-radius: 5px;width: 100%;">
                            @if( $return->state=='12') Return – not approved @endif
                            @if( $return->state=='13') Return – approved @endif
                        </div>
                    @endif
                </td>
                <td>
                    @foreach($return->details AS $detail)
                        <div style="height: 28px;"> 
                            <span style="margin-right: 5px; background-color: dodgerblue; color: #FFF; width: 25px; height: 25px; border-radius: 25px;text-align: center;display: inline-block;"> {{$detail->product_quantity}} </span>
                            <span>@if(isset($detail->orderDetail)){{$detail->orderDetail->product_reference}}@else ??? @endif</span>
                        </div>
                    @endforeach
                </td>
                <td style="cursor: pointer;" @if($customerAdminUrl) onclick="window.open(@json($customerAdminUrl), '_blank')" @endif>{{ $customer ? trim($customer->firstname . ' ' . $customer->lastname) : '-' }}</td>
                <td style="text-transform: uppercase;">{{$detail->refund_method}}</td>
            </tr>
        @endforeach
    </tbody>
</table>
