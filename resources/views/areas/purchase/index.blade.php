@extends('layouts.app')

@section('content')

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
            <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                <a href="{{ route('suppliersIssues.index', ['type' => 1]) }}" title="SUPPLIER">   
                    <div><i class="fa-solid fa-boxes-packing" style="font-size: 40px;"></i></div>
                    <div style="line-height: 1.2;">Supplier's Issues</div>
                </a>
            </div>
        </div>
    </div>
    

    
    {!! $counters !!}    
@endsection
