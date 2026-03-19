@extends('layouts.app')

    @include("areas.dashboard.includes.js")
    @include("areas.dashboard.includes.css")

@section('content')

    <div class="row">
        <div class="col-lg-3">
            <div class="navbar navbar-light customPanel">
                <div class="panel panel-default" style="display: flow-root">
                    <div class="panel-body sales_history" style="min-height: 100px;">
                        <div class="row">
                            <div class="col-lg-12"><h5 style="margin-top:7px;text-align: center;">{{ __('tags.SALES REPORT')}}</h5></div>
                            <div class="col-lg-12">
                                <div style="width: 40%;float: left;">
                                    <select name="brand_sales_history" id="brand_sales_history" style="width: calc( 100% - 10px ); font-size: 22px;">
                                        <option value="0">{{ __('tags.BRAND')}}</option>
                                        @foreach($manufacturers AS $manufacturer)
                                            <option value="{{$manufacturer->id_manufacturer}}">{{$manufacturer->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="alert alert-danger" style="width: calc( 100% - 10px );display: none;text-align: center; margin-top: 5px;padding: 5px;" id="alert_sales_history_brand"></span>
                                </div>
                                <div style="width: 40%;float: left;">
                                    <input id="date_sales_history" name="date_sales_history" type="text" value="" placeholder="2024-01-01" style="width: calc( 100% - 10px ); font-size: 19px;">
                                    <span class="alert alert-danger" style="width: calc( 100% - 10px );display: none;text-align: center; margin-top: 5px;padding: 5px;" id="alert_sales_history_date">{{ __('messages.Please fill the date')}}</span>
                                </div>
                                <div style="width: 20%;float: left;">
                                    <span onclick="getSalesHistory()" class="btn btn-dark" style="width: 100%"> {{ __('tags.FIND')}} </span>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div id="sales_history"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="navbar navbar-light customPanel">
                <div class="listUL" style="margin: 0 auto;display: flex;">
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="#" onclick="getDailyStats()">     
                            <div><i style="font-size: 40px;" class="fa-solid fa-chart-simple"></i></div>
                            <div>DAILY</div>
                        </a>
                    </div>
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;margin-left: 20px;"> 
                        <a href="{{ route('dashboard.kpi') }}">     
                            <div><i style="font-size: 40px;" class="fa-solid fa-chart-pie"></i></div>
                            <div>KPI</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12" id="daily_stats"> </div>
        
    </div>
</div>

    </div>
</div>

<div class="row">
    @foreach( $counters AS $counter)
        @include("areas.dashboard.includes.counters", $counter)
    @endforeach
</div>

<div class="row">
    @foreach( $panels AS $panel)
        @include("areas.dashboard.includes.panels", $panel)
    @endforeach
</div>

@endsection