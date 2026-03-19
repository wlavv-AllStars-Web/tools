@extends('layouts.app')
@section('content')

    @include('customTools.erp.includes.css')
    @include('customTools.erp.includes.js')

    <div class="row">
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel">
                <div style="margin-bottom: 10px; display: flex;">
                    <div id="button_openOrders"   style="width: 33%; float: left;text-align: center; @if( $list == 1 ) background-color: dodgerblue;  color: #fff; @else background-color: transparent; color: #333; @endif padding: 10px; border: 1px solid #999; font-weight: bolder; cursor: pointer;" onclick="openOrders()"   > OPEN ORDERS</div>
                    <div id="button_closedOrders" style="width: 34%; float: left;text-align: center; @if( $list == 2 ) background-color: dodgerblue;  color: #fff; @else background-color: transparent; color: #333; @endif padding: 10px; border: 1px solid #999; font-weight: bolder; cursor: pointer;" onclick="closedOrders()"> CLOSED ORDERS</div>
                    <div id="button_suppliers"    style="width: 33%; float: left;text-align: center; @if( $list == 3 ) background-color: dodgerblue;  color: #fff; @else background-color: transparent; color: #333; @endif padding: 10px; border: 1px solid #999; font-weight: bolder; cursor: pointer;" onclick="suppliers()"  > SUPPLIERS</div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div style="width: 100%; height: 5px;"></div>
        </div>
        @include("customTools.erp.includes.lists." . $list)
    </div>
    
@endsection