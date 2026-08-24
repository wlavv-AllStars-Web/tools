@extends('layouts.app')

    @include("areas.dashboard.includes.js")
    @include("areas.dashboard.includes.css")

@section('content')

    {!! $kpiContent !!}

    <div class="row">
        <div class="col-lg-12" id="daily_stats"></div>
    </div>
@endsection