@extends('layouts.app')
@section('content')

    <form action="{{route('shipping.store')}}" method="post">
        <div class="row">
            {!! csrf_field() !!}
            <div class="col-lg-4">
                <div class="navbar navbar-light customPanel">
                    @include("customTools.shipping.includes.shipments", ['suppliers' => $suppliers, 'carriers' => $carriers])
                </div>
            </div>
            <div class="col-lg-8">
                <div class="navbar navbar-light customPanel">
                    @include("customTools.shipping.includes.packaging")
                </div>
            </div>
        </div>
    </form>
    
    @include("customTools.shipping.includes.css")
    @include("customTools.shipping.includes.js")
@endsection