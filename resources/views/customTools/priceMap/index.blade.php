@extends('layouts.app')
@section('content')

    @include("customTools.priceMap.includes.js")
    
    <div class="navbar navbar-light customPanel" style="display: flex;">
        <select onchange="getPriceMapOf(this.value)" class="form-select" style="max-width: 300px;">
            <option value="">Seleciona uma marca</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->id_manufacturer }}">{{ $brand->name }}</option>
            @endforeach
        </select> 
        <div style="float: right; width: 150px;">
            <a style="display:none;" href="#" class="btn btn-success" id="priceMapDownload">DOWNLOAD CSV</a>
        </div>
    </div>

    <div class="navbar navbar-light customPanel" style="display: none;" id="priceMapContainer">
        <table class="table table-bordered" style="text-align: center;padding-top: 20px;">
            <tr id="brandContainer"></tr>
        </table>
    </div>
@endsection
