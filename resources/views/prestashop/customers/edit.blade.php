@extends('layouts.app')
@section('content')
    <form name="customersForm" id="customersForm" method="POST" action="{{route('customers.update', $supplier->id_supplier)}}">
        {{ csrf_field() }}
        @method('PUT')

        
        <div class="navbar navbar-light customPanel">
            <div class="row">
                <div class="col-lg-2 text-right"><label class="showInfo">{{ __('messages.id')}}: </label></div>
                <div class="col-lg-10"><label class="showInfo">{{$supplier->id_supplier}}</label></div>
            </div>
            <div class="row">
                <div class="col-lg-2 text-right"><label class="showInfo">{{ __('messages.name')}}: </label></div>
                <div class="col-lg-10"><input class="customInput" type="text" name="name" value="{{$supplier->name}}"></div>
            </div>
            <div class="row">
                <div class="col-lg-2 text-right"><label class="showInfo">{{ __('messages.creation_date')}}:</label> </div>
                <div class="col-lg-10"><label class="showInfo">{{$supplier->date_add}}</label></div>
            </div>
            <div class="row">
                <div class="col-lg-2 text-right"><label class="showInfo">{{ __('messages.update_date')}}: </label></div>
                <div class="col-lg-10"><label class="showInfo">{{$supplier->date_upd}}</label></div>
            </div>
            <div class="row">
                <div class="col-lg-2 text-right"><label class="showInfo">{{ __('messages.active')}}: </label></div>
                <div class="col-lg-10"><input type="checkbox" name="active" @if($supplier->active == 1) checked @else @endif value="1"></div>
            </div>
        </div>

        @foreach($supplier->lang AS $lang)
        <div class="navbar navbar-light customPanel">
            <div class="row">
                <div class="col-lg-2 text-right"><label class="showInfo">{{ __('messages.lang')}}: </label></div>
                <div class="col-lg-10"><img src="/images/lang/{{$lang->id_lang}}.jpg" style="width: 30px"></div>
            </div>
            <div class="row">
                <div class="col-lg-2 text-right"><label class="showInfo">{{ __('messages.description')}}: </label></div>
                <div class="col-lg-10"><input class="customInput" type="text" name="description[{{$lang->id_lang}}]" value="{{$lang->description}}"></div>
            </div>
            <div class="row">
                <div class="col-lg-2 text-right"><label class="showInfo">{{ __('messages.meta_title')}}: </label></div>
                <div class="col-lg-10"><input class="customInput" type="text" name="meta_title[{{$lang->id_lang}}]" value="{{$lang->meta_title}}"></div>
            </div>
            <div class="row">
                <div class="col-lg-2 text-right"><label class="showInfo">{{ __('messages.meta_keywords')}}: </label></div>
                <div class="col-lg-10"><input class="customInput" type="text" name="meta_keywords[{{$lang->id_lang}}]" value="{{$lang->meta_keywords}}"></div>
            </div>
            <div class="row">
                <div class="col-lg-2 text-right"><label class="showInfo">{{ __('messages.meta_description')}}: </label></div>
                <div class="col-lg-10"><input class="customInput" type="text" name="meta_description[{{$lang->id_lang}}]" value="{{$lang->meta_description}}"></div>
            </div>
        </div>
        @endforeach

    </form>
@endsection