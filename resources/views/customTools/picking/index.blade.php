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

    <div class="navbar navbar-light customPanel" style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
        <button class="btn btn-primary" type="button" onclick="openList('asm')">ASM <span class="badge bg-light text-dark">{{ $orders->asm->counter }}</span></button>
        <button class="btn btn-dark" type="button" onclick="openList('asd')">ASD <span class="badge bg-light text-dark">{{ $orders->asd->counter }}</span></button>
        @if($orders->other->counter > 0)
            <button class="btn btn-secondary" type="button" onclick="openList('other')">Outras <span class="badge bg-light text-dark">{{ $orders->other->counter }}</span></button>
        @endif
    </div>

    @include("customTools.picking.pickingList", [ 'data' => $orders->asm, 'pickingSource' => 'asm', 'defaultOpen' => true ])
    @include("customTools.picking.pickingList", [ 'data' => $orders->asd, 'pickingSource' => 'asd', 'defaultOpen' => false ])
    @if($orders->other->counter > 0)
        @include("customTools.picking.pickingList", [ 'data' => $orders->other, 'pickingSource' => 'other', 'defaultOpen' => false ])
    @endif
    @include("customTools.picking.includes.js")

@endsection
