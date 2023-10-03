@extends('layouts.app')

@section('content')
<div>
    <ul class="listUL">
        @foreach ($suppliersWithOpenOrders as $suppliers)
            <li class="rowStyling">
                <a href="{{route('stockEntry.show', $suppliers->supplier_id )}}" title="{{ $suppliers->supplier->name }}"><div style="display: flex;"><div style="width: 40px;float:left;">{{ $suppliers->supplier_id }} - </div> <div style="width: calc( 100% - 40px );float:left;">{{ $suppliers->supplier->name }}</div></div></a>
            </li>
        @endforeach
    </ul>
</div>
@endsection