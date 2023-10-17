@extends('layouts.app')
@section('content')
    <div class="navbar navbar-light customPanel">
        <div class="row">
            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.id')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{{$supplier->id_supplier}}</label></div>
        </div>
        <div class="row">
            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.name')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{{$supplier->name}}</label></div>
        </div>
        <div class="row">
            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.creation_date')}}:</label> </div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{{$supplier->date_add}}</label></div>
        </div>
        <div class="row">
            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.update_date')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{{$supplier->date_upd}}</label></div>
        </div>
        <div class="row">
            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.active')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">@if($supplier->active == 1) <span class="text-green">{{ __('messages.yes')}}</span> @else <span class="text-red">{{ __('messages.no')}}</span> @endif</label></div>
        </div>
    </div>

    @foreach($supplier->lang AS $lang)
    <div class="navbar navbar-light customPanel">
        <div class="row">
            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.lang')}}: </label></div>
            <div class="col-lg-10 dataContainer"><img src="/images/lang/{{$lang->id_lang}}.jpg" style="width: 30px"></div>
        </div>
        <div class="row">
            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.description')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{{$lang->description}}</label></div>
        </div>
        <div class="row">
            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.meta_title')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{{$lang->meta_title}}</label></div>
        </div>
        <div class="row">
            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.meta_keywords')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{{$lang->meta_keywords}}</label></div>
        </div>
        <div class="row">
            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.meta_description')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{{$lang->meta_description}}</label></div>
        </div>
    </div>
    @endforeach
@endsection