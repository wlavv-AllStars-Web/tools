@extends('layouts.app')

    @include("areas.marketing.includes.js")

@section('content')

    @if(session('success'))
        <div class="alert alert-success" style="width: 100%;">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" style="width: 100%;">{{ session('error') }}</div>
    @endif

    @if($imageReviewManufacturers->isNotEmpty())
    <div class="navbar navbar-light customPanel">
        <div class="panel panel-default" style="display: flow-root;">
            <div class="panel-heading text-center" style="padding: 15px;">
                <form action="{{ route('marketing.product_images.index') }}" method="GET" style="margin: 0;">
                    <label for="studioImageManufacturer" style="display: block; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;">
                        {{ __('messages.product_image_review') }}
                    </label>
                    <select id="studioImageManufacturer" name="manufacturer_id" class="form-control" onchange="if (this.value) this.form.submit()">
                        <option value="">{{ __('messages.product_image_review_select_brand') }}</option>
                        @foreach($imageReviewManufacturers as $manufacturer)
                            <option value="{{ $manufacturer->id_manufacturer }}">{{ $manufacturer->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>
    @endif
    @if(count($accessList) > 0)
    <div class="navbar navbar-light customPanel">
        <div class="listUL" style="margin: 0 auto;display: table;">
            @foreach($accessList AS $access)
            <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                <a href="{{$access['url']}}">     
                    <div>{!! $access['icon'] !!}</div>
                    <div>{{ $access['name']}}</div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    {!! $counters !!}

@endsection
