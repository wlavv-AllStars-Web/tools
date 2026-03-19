@extends('layouts.app')
@section('content')

    @if( count( $documents ) > 0)
        <div class="row">
            <div class="col-lg-4">
                <div class="navbar navbar-light customPanel">
                    @include("customTools.documentsManager.listSearch", ['documents' => $documents, 'searchName' => ( isset($searchName) ) ? $searchName : '', 'searchNumber' => ( isset($searchNumber) ) ? $searchName : '', 'searchCategory' => ( isset($searchCategory) ) ? $searchCategory : '', 'searchDate' => ( isset($searchDate) ) ? $searchDate : '' ])
                </div>            
            </div>
            <div class="col-lg-8">
                <div id="loadFile">
                    <div class="navbar navbar-light customPanel" style="text-align: left;">
                        <div class="alert alert-warning" role="alert"> PLEASE SELECT A DOCUMENT</div>
                    </div> 
                </div>            
            </div>
        </div>
    @else
        <div class="navbar navbar-light customPanel" style="text-align: left;">
            <div id="loadFile">
                <div style="margin: 20px; border-radius: 5px; width: 260px;float: right;">
                    <div style="border-right: 1px solid #999;background-color: dodgerblue; width: 250px; text-align: center; padding: 10px; border-radius: 5px 5px 0 0; text-transform: uppercase;color: #fff;border: 1px solid #999;"> SEARCH </div>
                    <div style="width: 250px; background-color: #fff;border: 1px solid #999;border-radius: 0 0 5px 5px;text-align: center;display: flex;">
                        <input type="text" name="search_document" id="search_document" style="width: 100%;font-size: 18px;border: none;padding: 2px;height: 42px; border-radius: 0 0 0 5px;" onkeypress="handle(event)">
                        <button onclick="searchDocuments()" class="btn btn-primary" style="border-radius: 0 0 5px 0;"> <i class="fa-solid fa-magnifying-glass"></i> </button>
                    </div>
                </div>
            </div>

            <div class="searchList"> </div>
            
            <div style="display: block; width: calc( 100% - 300px);padding: 20px;">
                <div class="alert alert-warning" role="alert" style="margin-bottom: 0;"> NO FILES AVAILABLE</div>
            </div>
        </div>
    @endif
    
    @include("customTools.documentsManager.includes.js")

@endsection