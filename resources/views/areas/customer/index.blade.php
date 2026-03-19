@extends('layouts.app')

@section('content')

<div class="row">
    @foreach( $counters AS $counter)
        @include("areas.dashboard.includes.counters", $counter)
    @endforeach
</div>

@endsection
