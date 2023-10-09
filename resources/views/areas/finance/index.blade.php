@extends('layouts.app')

@section('content')
    <div class="navbar navbar-light customPanel">
        <ul class="listUL">
            <li class="rowStyling"> <a href="{{route('products.index')}}">      {{ __('messages.Products')}}</a></li>
        {{--<li class="rowStyling"> <a href="{{route('orders.index')}}">        {{ __('messages.Orders')}}</a></li>--}}
            <li class="rowStyling"> <a href="{{route('customers.index')}}">     {{ __('messages.Customers')}}</a></li>
        </ul>
    </div>
@endsection
