@extends('layouts.app')
@section('content')
<div>
    <ul class="listUL" style="width: 100%;">
        @foreach ($manufacturers as $manufacturer)
            <li class="rowStyling" style="float: left;width: 10%;text-align: center;">
                <a href="{{route('manufacturers.show', $manufacturer->id_manufacturer )}}" title="{{ $manufacturer->name }}">
                    <div>
                        <img src="https://www.all-stars-motorsport.com/img/m/{{ $manufacturer->id_manufacturer }}-large_default.jpg" style="width: 150px;"> 
                        <div>{{ $manufacturer->name }}</div>
                    </div>
                </a>
            </li>
        @endforeach
    </ul>
</div>
@endsection