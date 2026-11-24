@extends('layouts.app')

@include('areas.dashboard.includes.js')

@section('content')
    @include('areas.partials.areaActionPanel', ['title' => 'After Sales'])

    <section class="nt-monitoring-section">
        <div class="nt-monitoring-section__header">
            <h2 class="nt-section-title">Monitoring</h2>
        </div>
        {!! $counters !!}
    </section>
@endsection
