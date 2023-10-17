@extends('layouts.app')
@section('content')

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