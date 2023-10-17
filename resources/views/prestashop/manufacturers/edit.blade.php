@extends('layouts.app')
@section('content')
    <form name="manufacturersForm" id="manufacturersForm" method="POST" action="{{route('manufacturers.update', $manufacturer->id_manufacturer)}}">
        {{ csrf_field() }}
        @method('PUT')
        <div class="navbar navbar-light customPanel">
            <div class="row">
                <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.id')}}: </label></div>
                <div class="col-lg-10 dataContainer"><label class="showInfo">{{$manufacturer->id_manufacturer}}</label></div>
            </div>
            <div class="row">
                <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.name')}}: </label></div>
                <div class="col-lg-10 dataContainer"><input class="customInput" type="text" name="name" value="{{$manufacturer->name}}"></div>
            </div>
            <div class="row">
                <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.warranty')}}: </label></div>
                <div class="col-lg-10 dataContainer"><input class="customInput" type="text" name="warranty" value="{{$manufacturer->warranty}}"></div>
            </div>
            <div class="row">
                <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.countryCode')}}: </label></div>
                <div class="col-lg-10 dataContainer">
                    <select name="country_code" class="customInput" style="width: 192px;">
                        @foreach($countries AS $country)
                            <option @if($manufacturer->country_code == $country->iso_code) selected="selected" @endif value="{{$country->iso_code}}">{{$country->iso_code}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.currency')}}: </label></div>
                <div class="col-lg-10 dataContainer">
                    <select name="currency" class="customInput" style="width: 192px;">
                        @foreach($currencies AS $currency)
                            <option @if($manufacturer->currency == $currency->iso_code) selected="selected" @endif value="{{$currency->iso_code}}">{{$currency->name}} ( {{$currency->iso_code}} )</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.creation_date')}}:</label> </div>
                <div class="col-lg-10 dataContainer"><label class="showInfo">{{$manufacturer->date_add}}</label></div>
            </div>
            <div class="row">
                <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.update_date')}}: </label></div>
                <div class="col-lg-10 dataContainer"><label class="showInfo">{{$manufacturer->date_upd}}</label></div>
            </div>
            <div class="row">
                <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.active')}}: </label></div>
                <div class="col-lg-10 dataContainer"><label class="showInfo"><input type="checkbox" name="active" @if($manufacturer->active == 1) checked @else @endif value="1"></div>
            </div>
        </div>

        @foreach($manufacturer->lang AS $lang)
        <div class="navbar navbar-light customPanel">
            <div class="row">
                <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.lang')}}: </label></div>
                <div class="col-lg-10 dataContainer"><img src="/images/lang/{{$lang->id_lang}}.jpg" style="width: 30px"></div>
            </div>
            <div class="row">
                <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.description')}}: </label></div>
                <div class="col-lg-10 dataContainer"><input class="customInput" type="text" name="description[{{$lang->id_lang}}]" value="{{$lang->description}}"></div>
            </div>
            <div class="row">
                <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.short_description')}}: </label></div>
                <div class="col-lg-10 dataContainer"><input class="customInput" type="text" name="short_description[{{$lang->id_lang}}]" value="{{$lang->short_description}}"></div>
            </div>
            <div class="row">
                <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.meta_title')}}: </label></div>
                <div class="col-lg-10 dataContainer"><input class="customInput" type="text" name="meta_title[{{$lang->id_lang}}]" value="{{$lang->meta_title}}"></div>
            </div>
            <div class="row">
                <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.meta_keywords')}}: </label></div>
                <div class="col-lg-10 dataContainer"><input class="customInput" type="text" name="meta_keywords[{{$lang->id_lang}}]" value="{{$lang->meta_keywords}}"></div>
            </div>
            <div class="row">
                <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.meta_description')}}: </label></div>
                <div class="col-lg-10 dataContainer"><input class="customInput" type="text" name="meta_description[{{$lang->id_lang}}]" value="{{$lang->meta_description}}"></div>
            </div>
        </div>
        @endforeach
    </form>

@endsection