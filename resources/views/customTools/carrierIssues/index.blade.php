@extends('layouts.app')
@section('content')

    @include("customTools.carrierIssues.includes.add")

    <div class="navbar navbar-light customPanel categorList">
        <div id="buttonIssuesActive"    style="width: 33%; float: left;text-align: center;background-color: dodgerblue;  color: #333; padding: 10px; border: 1px solid #999; font-weight: bolder; cursor: pointer;" onclick="select_active_issues()">ACTIVE</div>
        <div id="buttonIssuesRetention" style="width: 34%; float: left;text-align: center;background-color: transparent; color: #333; padding: 10px; border: 1px solid #999; font-weight: bolder; cursor: pointer;" onclick="select_retention_issues()">RETENTION</div>
        <div id="buttonIssuesArchived"  style="width: 33%; float: left;text-align: center;background-color: transparent; color: #333; padding: 10px; border: 1px solid #999; font-weight: bolder; cursor: pointer;" onclick="select_archived_issues()">ARCHIVED</div>
    </div>
    
    @include("customTools.carrierIssues.includes.list", ['carrierIssues' => $carrierIssuesActive, 'listType' => 'carrierIssuesActive' ])
    @include("customTools.carrierIssues.includes.list", ['carrierIssues' => $carrierIssuesRetention, 'listType' => 'carrierIssuesRetention' ])
    @include("customTools.carrierIssues.includes.list", ['carrierIssues' => $carrierIssuesArchived, 'listType' => 'carrierIssuesArchived' ])

    @include("customTools.carrierIssues.includes.js")
    @include("customTools.carrierIssues.includes.css")

    <style> #upload_area{ display: block !important; margin-top: 0 !important;} </style>
    
@endsection