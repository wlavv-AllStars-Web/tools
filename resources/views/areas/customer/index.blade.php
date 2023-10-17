@extends('layouts.app')

@section('content')

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

@endsection
