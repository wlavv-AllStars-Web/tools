@extends('layouts.app')
@section('content')

    @include("customTools.autoOrders.includes.css")
    @include("customTools.autoOrders.includes.js")

    <div class="navbar navbar-light customPanel" style="margin: 10px 0; width: 100%; cursor: pointer;font-weight: bolder;text-align: center;background-color: #54aaff">CEDRIC</div>

    <div class="navbar navbar-light customPanel" style="width: 100%;">
        <div class="card">
            <div class="card-body">
                <div>
                    @foreach($auto_orders AS $key => $manufacturer)
                        @if(!in_array($manufacturer['name'], [
                                'Acexxon', 'Athena', 'Drakos', 'Eventuri', 'Forge Motorsport',
                                'Goodridge', 'HKS', 'ITG', 'NGK', 'Pipercross', 'Ultra Racing',
                                'Tein', 'Eibach', 'ACL Performance', 'DBA Brakes', 'ZRP', 'Motul', 'Armytrix', 'Whiteline'
                            ])
                        )
                                        
                            <div class="card" style="border-radius: 0;width: 100%" id="row_brand_{{$manufacturer['id_manufacturer']}}">
                                <div class="card-body" style="padding: 0px;">
                                    <h5 class="card-title" style="margin-bottom: 0;height: 32px;">
                                        <div class="auto-order-brand-toggle" data-manufacturer="{{ e($manufacturer['name']) }}" data-id-manufacturer="{{$manufacturer['id_manufacturer']}}" style="width: calc( 100% - 200px ); float: left; cursor: pointer;">
                                            <div id="counter_brand_{{$manufacturer['id_manufacturer']}}" style="padding: 7px;font-size: 16px;color: #FFF;background-color: dodgerblue; width: 50px; height: 32px;text-align: center;float: left;margin-right: 5px;padding: 6px;">{{$manufacturer['counter']}}</div>
                                            <div style="float: left;padding: 5px 0;"> {{$manufacturer['name']}} </div>
                                        </div>
                                        <div style="margin: 0 5px;padding: 5px;float: right;width: 175px;">
                                            <span class="auto-order-clean-brand" data-manufacturer="{{ e($manufacturer['name']) }}" data-id-manufacturer="{{$manufacturer['id_manufacturer']}}" target="_blank" style="cursor: pointer;text-decoration: none; float: right;color: #333; margin-right: 5px;">{{ __("messages.CLEAN")}}</span>
                                            <span style="margin: 0 5px;float: right;">|</span>
                                            <span class="auto-order-export-brand" data-manufacturer="{{ e($manufacturer['name']) }}" style="text-decoration: none; float: right;color: #333;cursor: pointer;" download>{{ __("messages.EXPORT")}}</span>
                                        </div>
                                    </h5>
                                    <div style="border-top: 1px solid #bbb; display: none;" id="table_{{$manufacturer['id_manufacturer']}}"> </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <span type="button" class="btn btn-dark" style="margin-top: 15px; text-align: center; border-radius: 0; width: 100%;" onclick="createAndDownloadZip('full')">{{ __("messages.EXPORT ALL BRANDS")}}</span>
            </div>
        </div>
    </div>

    <div class="navbar navbar-light customPanel" style="margin: 10px 0; width: 100%; cursor: pointer;font-weight: bolder;text-align: center;background-color: #ff5464">PIERRE</div>

    <div class="navbar navbar-light customPanel" style="width: 100%;">
        <div class="card">
            <div class="card-body">
                <div>
                    @foreach($auto_orders AS $key => $manufacturer)
                        @if(in_array($manufacturer['name'], [
                                'Acexxon', 'Athena', 'Drakos', 'Eventuri', 'Forge Motorsport',
                                'Goodridge', 'HKS', 'ITG', 'NGK', 'Pipercross', 'Ultra Racing',
                                'Tein', 'Eibach', 'ACL Performance', 'DBA Brakes', 'ZRP', 'Motul', 'Armytrix', 'Whiteline'
                            ])
                        )
                            <div class="card" style="border-radius: 0;width: 100%" id="row_brand_{{$manufacturer['id_manufacturer']}}">
                                <div class="card-body" style="padding: 0px;">
                                    <h5 class="card-title" style="margin-bottom: 0;height: 32px;">
                                        <div class="auto-order-brand-toggle" data-manufacturer="{{ e($manufacturer['name']) }}" data-id-manufacturer="{{$manufacturer['id_manufacturer']}}" style="width: calc( 100% - 200px ); float: left; cursor: pointer;">
                                            <div id="counter_brand_{{$manufacturer['id_manufacturer']}}" style="padding: 7px;font-size: 16px;color: #FFF;background-color: dodgerblue; width: 50px; height: 32px;text-align: center;float: left;margin-right: 5px;padding: 6px;">{{$manufacturer['counter']}}</div>
                                            <div style="float: left;padding: 5px 0;"> {{$manufacturer['name']}} </div>
                                        </div>
                                        <div style="margin: 0 5px;padding: 5px;float: right;width: 175px;">
                                            <span class="auto-order-clean-brand" data-manufacturer="{{ e($manufacturer['name']) }}" data-id-manufacturer="{{$manufacturer['id_manufacturer']}}" target="_blank" style="cursor: pointer;text-decoration: none; float: right;color: #333; margin-right: 5px;">{{ __("messages.CLEAN")}}</span>
                                            <span style="margin: 0 5px;float: right;">|</span>
                                            <span class="auto-order-export-brand" data-manufacturer="{{ e($manufacturer['name']) }}" style="text-decoration: none; float: right;color: #333;cursor: pointer;" download>{{ __("messages.EXPORT")}}</span>
                                        </div>
                                    </h5>
                                    <div style="border-top: 1px solid #bbb; display: none;" id="table_{{$manufacturer['id_manufacturer']}}"> </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <span type="button" class="btn btn-dark" style="margin-top: 15px; text-align: center; border-radius: 0; width: 100%;" onclick="createAndDownloadZip('full')">{{ __("messages.EXPORT ALL BRANDS")}}</span>
            </div>
        </div>
    </div>
    
@endsection
