@extends('layouts.app')

    @include("areas.dashboard.includes.js")
    @include("areas.dashboard.includes.css")

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel">
                <div class="listUL" style="margin: 0 auto;display: flex;">
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="#" onclick="getDailyStats()">     
                            <div><i style="font-size: 40px;" class="fa-solid fa-chart-simple"></i></div>
                            <div>DAILY</div>
                        </a>
                    </div>
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;margin-left: 20px;"> 
                        @if(auth()->user()->id == 2 || auth()->user()->id == 94 || auth()->user()->id == 43 )
                            <a href="{{ route('stats.adminKpi') }}">     
                        @else
                            <a href="{{ route('stats.kpi') }}">     
                        @endif
                            <div><i style="font-size: 40px;" class="fa-solid fa-chart-pie"></i></div>
                            <div>KPI</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12" id="daily_stats"> </div>
    </div>

@endsection