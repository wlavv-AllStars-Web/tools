@extends('layouts.app')

@section('content')
    <div class="navbar navbar-light customPanel">
        <ul class="listUL">
        {{--<li class="rowStyling"> <a href="{{route('orders.index')}}">        {{ __('messages.Orders')}}</a></li>--}}
        {{--<li class="rowStyling"> <a href="{{route('categories.index')}}">    {{ __('messages.Categories')}}</a></li>--}}
        {{--<li class="rowStyling"> <a href="{{route('customers.index')}}">     {{ __('messages.Customers')}}</a></li>
            <li class="rowStyling"> <a href="{{route('carriers.index')}}">      {{ __('messages.Carriers')}}</a></li>
            <li class="rowStyling"> <a href="{{route('employees.index')}}">     {{ __('messages.employees')}}</a></li>
            <li class="rowStyling"> <a href="{{route('issues.index')}}">        {{ __('messages.issues')}}</a></li>
            <li class="rowStyling"> <a href="{{route('permissions.index')}}">   {{ __('messages.permissions')}}</a></li>
            <li class="rowStyling"> <a href="{{route('profiles.index')}}">      {{ __('messages.profiles')}}</a></li>
            <li class="rowStyling"> <a href="{{route('cms.index')}}">           {{ __('messages.cms')}}</a></li> --}}
        </ul>
    </div>
@endsection