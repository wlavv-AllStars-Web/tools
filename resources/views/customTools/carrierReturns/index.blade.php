@extends('layouts.app')
@section('content')

    @include("customTools.carrierReturns.includes.add")

    <div class="navbar navbar-light customPanel categorList" style="display: inline-table;">
        <div id="buttonReturnActive"    style="width: 50%; float: left;text-align: center;background-color: dodgerblue;  color: #333; padding: 10px; border: 1px solid #999; font-weight: bolder; cursor: pointer;" onclick="select_active_issues()">OPEN ISSUES</div>
        <div id="buttonReturnArchived"  style="width: 50%; float: left;text-align: center;background-color: transparent; color: #333; padding: 10px; border: 1px solid #999; font-weight: bolder; cursor: pointer;" onclick="select_archived_issues()">ARCHIVED</div>
    </div>
    
    @include("customTools.carrierReturns.includes.list", ['carrierReturn' => $carrierReturnActive, 'listType' => 'carrierReturnActive' ])
    @include("customTools.carrierReturns.includes.list", ['carrierReturn' => $carrierReturnArchived, 'listType' => 'carrierReturnArchived' ])

    @include("customTools.carrierReturns.includes.js")
    @include("customTools.carrierReturns.includes.css")

@endsection