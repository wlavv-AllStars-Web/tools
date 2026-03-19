@extends('layouts.app')
@section('content')

    <div class="navbar navbar-light customPanel categorList">
        @if( count( $filters ) > 0)
            <div>
                @foreach($filters AS $filter)
                    <div style="margin: 10px; border-radius: 5px; width: 240px;float: left;">
                        <div style="border-right: 1px solid #999;background-color: dodgerblue; width: 240px; text-align: center; padding: 10px; border-radius: 5px 5px 0 0; text-transform: uppercase;color: #fff;border: 1px solid #999;"> {{$filter['category']}} </div>
                        <div>
                            @if( ( count($filter['elements']) > 0 ) && ( $filter['category'] != 'manifest' ) )
                                <select class="selectedElement" name="selectedElement" style="padding: 11px; width: 240px; background-color: #fff;border: 1px solid #999;border-radius: 0 0 5px 5px;text-align: center;">
                                    <option value="" style="text-align: center;">Select an option</option>
                                    @foreach($filter['elements'] AS $element)
                                        <option style="text-align: center;" value="{{route('documentsManager.listDocuments', ['category' => $filter['category'], 'element' => $element])}}">{{str_replace('.', ' > ', $element)}}</option>
                                    @endforeach
                                </select>    
                            @else
                            <div style="text-align: center;padding: 10px;border: 1px solid #999;border-radius: 0 0 5px 5px;width: 240px;">
                                <a class="aLink" href="{{route('documentsManager.listDocuments', ['category' => $filter['category'], 'element' => 'others'])}}" style="text-decoration: none;">Check documents</a>
                            </div>
                            @endif
                        </div>
                    </div>
                @endforeach
                <div style="margin: 10px; border-radius: 5px; width: 240px;float: right;">
                    <div style="border-right: 1px solid #999;background-color: dodgerblue; width: 240px; text-align: center; padding: 10px; border-radius: 5px 5px 0 0; text-transform: uppercase;color: #fff;border: 1px solid #999;"> SEARCH </div>
                    <div style="width: 240px; background-color: #fff;border: 1px solid #999;border-radius: 0 0 5px 5px;text-align: center;display: flex;">
                        <input type="text" name="search_document" id="search_document" style="width: 100%;font-size: 18px;border: none;padding: 2px;height: 42px; border-radius: 0 0 0 5px;" onkeypress="handle(event)">
                        <button onclick="searchDocuments()" class="btn btn-primary" style="border-radius: 0 0 5px 0;"> <i class="fa-solid fa-magnifying-glass"></i> </button>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-warning" role="alert" style="padding: 10px; margin-bottom: 0;"> NO FILES AVAILABLE</div>
        @endif
    </div>

    <div class="searchList"> </div>
    
    @include("customTools.documentsManager.includes.js")
    
    <style>
        a.aLink{ color: #000; }
        a.aLink:hover{ color: dodgerblue; }
    </style>
@endsection