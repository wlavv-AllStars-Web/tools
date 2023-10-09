@extends('layouts.app')
@section('content')
<div>
    <ul class="listUL">
        <li class="rowStyling">
            <div style="display: flex;">
                <div style="width: 60px;float:left;text-align: center;">ID</div> 
                <div style="width: 250px;float:left;text-align: center;">REFERENCE</div> 
                <div style="width: 150px;float:left;text-align: center;">ETA</div>
                <div style="width: 80px;float:left;text-align: right;">TOTAL</div>
                <div style="width: 20px;float:left;"></div>
                <div style="width: 600px;float:left;">COMMENTS</div>
            </div>
        </li>
        @foreach ($openOrders as $suppliers)
            <li class="rowStyling" >
                <div style="display: flex;">
                    <div style="width: 60px;float:left;text-align: center;">
                        <button class="btn btn-default" onclick="$('#order_{{ $suppliers->id_bms_procurement_purchase_order }}').toggle()">
                            <i class="fa fa-chevron-down" style="color: dodgerblue; margin: 0 10px;"></i>
                        </button>
                    </div> 
                    <div style="width: 60px;float:left;text-align: center;">{{ $suppliers->id_bms_procurement_purchase_order }} </div> 
                    <div style="width: 250px;float:left;text-align: center;">{{ $suppliers->reference }} </div> 
                    <div style="width: 150px;float:left;text-align: center;">{{ $suppliers->eta }}</div>
                    <div style="width: 80px;float:left;text-align: right;">{{ number_format($suppliers->total, 2) }} €</div>
                    <div style="width: 20px;float:left;"></div>
                    <div style="width: 600px;float:left;">{{ $suppliers->comments_private }}</div>
                </div>
                <div id="order_{{ $suppliers->id_bms_procurement_purchase_order }}" style="display: none;background-color: #ddd;">
                    <ul class="listUL" style="display: grid;padding: 10px;">
                        <li class="rowStyling">
                            <div style="width: 100px;float:left;text-align: center;">NEW</div> 
                            <div style="width: 150px;float:left;text-align: center;">REFERENCE</div> 
                            <div style="width: 150px;float:left;text-align: center;">QTY ORDERED</div>
                            <div style="width: 150px;float:left;text-align: center;">QTY INVOICED</div>
                            <div style="width: 150px;float:left;text-align: center;">QTY RECEIVED</div>
                            <div style="width: 150px;float:left;text-align: center;">QTY EXPECTED</div>
                            <div style="width: 150px;float:left;text-align: center;">INVOICE DATE</div>
                            <div style="width: 100px;float:left;text-align: center;">PRICE</div>
                        </li>

                    @foreach ($suppliers['rows'] as $row)
                        <li class="rowStyling">
                            <div style="width: 100px;float:left;text-align: center;"><label class="showInfo">@if($row['is_new'] == 1) <span class="text-green">{{ __('messages.yes')}}</span> @else <span class="text-red">{{ __('messages.no')}}</span> @endif</label></div> 
                            <div style="width: 150px;float:left;text-align: center;">{{$row['sku']}}</div> 
                            <div style="width: 150px;float:left;text-align: center;">{{$row['qty_ordered']+0}}</div>
                            <div style="width: 150px;float:left;text-align: center;">{{$row['qty_received']+0}}</div>
                            <div style="width: 150px;float:left;text-align: center;">{{$row['qty_wmfaturado']+0}}</div>
                            <div style="width: 150px;float:left;text-align: center;">{{$row['qty_expected']+0}}</div>
                            <div style="width: 150px;float:left;text-align: center;">
                                @if(strlen($row['date_wmfaturado']) > 0)
                                    {{$row['date_wmfaturado']}}
                                @else
                                    -
                                @endif
                            </div>
                            <div style="width: 100px;float:left;text-align: right;">{{number_format($row['price'], 2)}} €</div>
                        </li>
                    @endforeach

                    </ul>
                </div>
            </li>
        @endforeach
    </ul>
</div>
@endsection