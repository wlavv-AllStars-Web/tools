@extends('layouts.app')

@section('content')
    <div class="navbar navbar-light customPanel">
        <ul class="listUL">
            <li class="rowStyling"> <a href="{{route('products.index')}}">      {{ __('messages.Products')}}</a></li>
        </ul>
    </div>
@endsection
