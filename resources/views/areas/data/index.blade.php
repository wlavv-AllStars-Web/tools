@extends('layouts.app')

@section('content')

    @if(session('success'))
        <div class="alert alert-success" style="width: 100%;">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" style="width: 100%;">{{ session('error') }}</div>
    @endif

    <div class="navbar navbar-light customPanel" style="margin-bottom: 10px;">
        <form method="POST" action="{{ route('data.asd_images.sync') }}" style="margin: 0 auto;">
            @csrf
            <button type="submit" class="btn btn-info">
                <i class="fa-solid fa-rotate me-1"></i>
                Verify ASD images
            </button>
        </form>
    </div>

    @if(count($accessList) > 0)
        <div class="navbar navbar-light customPanel">
            <div class="listUL" style="margin: 0 auto;display: table;">
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
    @endif
    
    {!! $counters !!}
    
@endsection
