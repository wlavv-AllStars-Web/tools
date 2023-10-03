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
            <li class="rowStyling">
                <div style="display: flex;">
                    <div style="width: 60px;float:left;text-align: center;">{{ $suppliers->id_bms_procurement_purchase_order }} </div> 
                    <div style="width: 250px;float:left;text-align: center;">{{ $suppliers->reference }} </div> 
                    <div style="width: 150px;float:left;text-align: center;">{{ $suppliers->eta }}</div>
                    <div style="width: 80px;float:left;text-align: right;">{{ number_format($suppliers->total, 2) }} €</div>
                    <div style="width: 20px;float:left;"></div>
                    <div style="width: 600px;float:left;">{{ $suppliers->comments_private }}</div>
                </div>
            </li>
        @endforeach
    </ul>
</div>
@endsection