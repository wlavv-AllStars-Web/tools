@extends('layouts.app')

    @include("areas.marketing.includes.js")

@section('content')

    @if(session('success'))
        <div class="alert alert-success" style="width: 100%;">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" style="width: 100%;">{{ session('error') }}</div>
    @endif

    @if(count($accessList) > 0 || $imageReviewManufacturers->isNotEmpty() || $asdMissingImageBrands->isNotEmpty())
    <div class="navbar navbar-light customPanel">
        <h2 class="nt-section-title">Quick actions</h2>
        <div class="listUL" style="margin: 0 auto;display: table;">
            @foreach($accessList AS $access)
            <div class="rowStyling" style="height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                <a href="{{$access['url']}}">     
                    <div>{!! $access['icon'] !!}</div>
                    <div>{{ $access['name']}}</div>
                </a>
            </div>
            @endforeach
            @include('areas.marketing.includes.image-review-panel')
            @include('areas.marketing.includes.asd-missing-photos-panel')
        </div>
    </div>
    @endif
    
    <section class="nt-monitoring-section">
        <div class="nt-monitoring-section__header"><h2 class="nt-section-title">Monitoring</h2></div>
        {!! $counters !!}
    </section>

@endsection
