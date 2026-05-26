@extends('layouts.app')

@section('content')

    @if(session('success'))
        <div class="alert alert-success" style="width: 100%;">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" style="width: 100%;">{{ session('error') }}</div>
    @endif

    <div class="navbar navbar-light customPanel">
        <div class="listUL" style="margin: 0 auto;display: table;">
            <form method="POST" action="{{ route('web.newsletter.send_pending') }}" style="margin: 0; float: left;">
                @csrf
                <button type="submit" class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left; border: 1px solid #ccc; padding: 20px 10px; background: transparent;">
                    <div><i style="font-size: 40px;" class="fa-solid fa-envelope-circle-check"></i></div>
                    <div style="line-height: 18px; padding: 5px 0;">Send 10</div>
                </button>
            </form>
            @foreach($accessList AS $access)
            <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                <a href="{{$access['url']}}">     
                    <div>{!! $access['icon'] !!}</div>
                    <div style="line-height: 18px; padding: 5px 0;">{{ $access['name']}}</div>
                </a>
            </div>
            @endforeach
        </div>
    </div>

    {!! $counters !!}
    
@endsection
