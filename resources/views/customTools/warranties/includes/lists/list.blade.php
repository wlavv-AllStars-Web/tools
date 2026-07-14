<table id="ordersTable" class="display" style="width:100%;">
    <thead>
        <tr>
            <th></th>
            <th>ORDER</th>
            <th>STATUS</th>
            <th>REFERENCE</th>
            <th>BRAND</th>
            <th>MODEL</th>
            <th>CHASSIS</th>
            <th>CUSTOMER</th>
            <th>DESCRIPTION</th>
            <th>MEDIA</th>
        </tr>
    </thead>
    <tbody>
        @foreach($warranties AS $warranty)
            @php
                $order = $warranty->order;
                $customer = $warranty->customer;
                $status = $warranty->status;
                $statusLang = optional($status)->lang;
            @endphp
            @foreach($warranty->details AS $detail)
                @php
                    $orderDetail = $detail->orderDetail;
                @endphp
                <tr>
                    <td style="text-align: center;">
                        @if(optional($order)->id_lang == 1) <img style="border: 1px solid #ccc;border-radius: 8px;" src="/images/flags/en.png" alt="English" width="25" height="20"> @endif
                        @if(optional($order)->id_lang == 4) <img style="border: 1px solid #ccc;border-radius: 8px;" src="/images/flags/es.png" alt="Spanish" width="25" height="20"> @endif
                        @if(optional($order)->id_lang == 5) <img style="border: 1px solid #ccc;border-radius: 8px;" src="/images/flags/fr.png" alt="French"  width="25" height="20"> @endif
                    </td>
                    <td>@if($detail->new_files == 1) <div style="background-color: red; width: 15px; height: 15px; border-radius: 10px;float: left; margin: 5px;"></div> @endif {{$warranty->id_order}}</td>
                    <td>
                        <button class="open-ajax-modal" style="display: none;" data-route="{{ route('warranties.getModal', ['id' => $warranty->id_order_return]) }}">{{ optional($statusLang)->name ?? 'Unknown status' }}</button>
                        @if( ( $warranty->state != 4 ) && ( $warranty->state != 5 ) )
                            <form id="formWarrantyStatusSelect" class="formSubmit-{{$warranty->id_order_return}}" method="POST" action="{{ route('warranties.changeStatus') }}">
                                @csrf
                                <input type="hidden" name="id_order_return" value="{{$warranty->id_order_return}}" name="id_order_return">
                                <input type="hidden" name="return_type" value="warranty" name="return_type">
                                <select id="warrantyStatusSelect" name="warrantyStatusSelect" class="form-select return-status-select" data-id="{{ $warranty->id_order_return }}" style="text-align: center;color: #FFF;background-color: {{ optional($status)->color ?? '#777' }};">
                                    <option disabled value="1" {{ $warranty->state=='1' ? 'selected' : '' }} style="color:#000;">Warranty - Request Registered</option>
                                    <option disabled value="2" {{ $warranty->state=='2' ? 'selected' : '' }} style="color:#000;">Warranty - Request Being Processed</option>
                                    <option value="3" {{ $warranty->state=='3' ? 'selected' : '' }}>Warranty - Request for Additional Information</option>
                                    <option value="4" {{ $warranty->state=='4' ? 'selected' : '' }}>Warranty - Approved</option>
                                    <option value="5" {{ $warranty->state=='5' ? 'selected' : '' }}>Warranty - Not Approved</option>
                                </select>
                            </form>
                        @else
                            <div style="text-align: center;color: #FFF;background-color: {{ optional($status)->color ?? '#777' }}; padding: 10px;border-radius: 5px;width: 100%;">
                                @if( $warranty->state=='4') Warranty - Approved @endif
                                @if( $warranty->state=='5') Warranty - Not Approved @endif
                            </div>
                        @endif
                    </td>
                    <td><div style="float: left; margin-right: 5px; background-color: dodgerblue; color: #FFF; width: 25px; height: 25px; border-radius: 25px;text-align: center;"> {{$detail->product_quantity}} </div> <span>{{ optional($orderDetail)->product_reference ?? $detail->request_reference ?? '' }}</span></td>
                    <td>{{$detail->brand}}</td>
                    <td>{{$detail->model}}</td>
                    <td>{{$detail->chassis}}</td>
                    <td>{{ trim((optional($customer)->firstname ?? '') . ' ' . (optional($customer)->lastname ?? '')) }}</td>
                    <td> {{ str_replace(['\\r', '\\n'], ' ', $detail->problem_description) }} </td>
                    <td> <button class="btn btn-primary open-ajax-modal" data-route="{{ route('warranties.getModal', ['id' => $detail->id_order_detail]) }}"> <i class="fa-solid fa-magnifying-glass"></i> </button> </td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>