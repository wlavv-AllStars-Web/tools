@extends('layouts.app')

    @include("customTools.picking.includes.css")

@section('content')

    @include("customTools.picking.pickingHeader")

    <div class="navbar navbar-light customPanel" id="orderSupportContainer">
        <h3> Control variables </h3>
        <br>
        <label>OPEN ORDER</label>
        <input readonly type="text" name="openOrder" id="openOrder" value="" style="width: 100%;">
        <br><br>
        <label>TAG TO SEARCH FOR</label>
        <input readonly type="text" name="barcodeToFind" id="barcodeToFind" value="" style="width: 100%;">
        <br><br>
        <label>PICKING CONTAINER BARCODE</label>
        <input readonly type="text" name="pickingContainer" id="pickingContainer" value="" style="width: 100%;">
    </div>

    @include("customTools.picking.pickingList", [ 'data' => $orders->preparation,   'pickingSource' => 'preparation'    ])
    @include("customTools.picking.includes.js")

@endsection