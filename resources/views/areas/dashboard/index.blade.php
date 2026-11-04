@extends('layouts.app')

    @include("areas.dashboard.includes.js")
    @include("areas.dashboard.includes.css")

@section('content')

    <div id="dashboard_content">
        {!! $kpiContent !!}
    </div>

@endsection
