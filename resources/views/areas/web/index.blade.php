@extends('layouts.app')

@section('content')

    @if(session('success'))
        <div class="alert alert-success" style="width: 100%;">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" style="width: 100%;">{{ session('error') }}</div>
    @endif

    <div class="navbar navbar-light customPanel">
        <h2 class="nt-section-title">Quick actions</h2>
        <div class="listUL" style="margin: 0 auto;display: table;">
            @foreach($accessList AS $access)
            <div class="rowStyling" style="height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                <a href="{{$access['url']}}">     
                    <div>{!! $access['icon'] !!}</div>
                    <div style="line-height: 18px; padding: 5px 0;">{{ $access['name']}}</div>
                </a>
            </div>
            @endforeach
        </div>
    </div>

    <section class="nt-monitoring-section">
        <div class="nt-monitoring-section__header"><h2 class="nt-section-title">Monitoring</h2></div>
        {!! $counters !!}
    </section>
    
@endsection
