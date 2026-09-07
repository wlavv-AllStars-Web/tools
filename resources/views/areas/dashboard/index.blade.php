@extends('layouts.app')

    @include("areas.dashboard.includes.js")
    @include("areas.dashboard.includes.css")

@section('content')

    <style>#yieldContent { width: 100% !important; margin: 0 !important; display: block !important; }</style>

    <div id="dashboard_content">
        {!! $kpiContent !!}
    </div>

@endsection