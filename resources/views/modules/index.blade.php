@extends('layouts.app')

@section('content')
    <div class="navbar navbar-light customPanel">
        <ul class="listUL">
            @foreach ($modules as $module)
                <li class="rowStyling">
                    <a href="{{$module['url']}}" title="{{$module['name']}}"><div style="display: flex;">{{ $module['name'] }}</div></a>
                </li>
            @endforeach
        </ul>
    </div>
@endsection