@extends('layouts.app')

    @include("areas.finance.includes.js")

@section('content')

    <style> table, th, td { border: 1px solid black; } </style>
    
    <div class="navbar navbar-light customPanel" style="margin-top: 10px;display: table;">
        <div style="text-align: center;margin: 30px;">
            <h3 style="color: black; font-weight: bolder; text-transform: uppercase;">{{ __('tags.Intrasta documents') }}</h3>
        </div>
        <div class="row" id="filesDownload">
            <div class="col-lg-3"></div>
            <div class="col-lg-6">
                <a id="downloadCH" href="#" download="download"></a>
                <a id="downloadEX" href="#" download="download"></a>
                <div style="margin: 0 auto;width: 700px;">
                    <div style="text-align: center;font-size: 28px;float: left;width: 45%;">
                        <span class="btn btn-info" onclick="download_import()" style="width: 100%;"> 
                            <i class="fa-solid fa-download" style="font-size: 28px;color: #FFF;"></i>
                            <br>
                            <span style="font-size: 18px;color: #FFF;">{{ __('tags.INTRA-CH-') }}{{$year}}{{$month}}</span>
                        </span>
                    </div>
                    <div style="width: 10%;float: left;"></div>
                    <div style="text-align: center;font-size: 28px;float: right;width: 45%;">
                        <span class="btn btn-info" onclick="$('#upload_area').show('slow')" style="width: 100%;"> 
                            <i class="fa-solid fa-download" style="font-size: 28px;color: #FFF;"></i>
                            <br>
                            <span style="font-size: 18px;color: #FFF;">{{ __('tags.INTRA-EX-') }}{{$year}}{{$month}}</span>
                            
                            @include("customTools.uploads.upload_container", [
                                'title' => '', 
                                'message' => trans('tags.Drop files here or click to upload') . '<br>' . trans('tags.Only CSV files'), 
                                'upload_path' => "finance/moloni/",
                                'filename' => "moloni.csv",
                                'upload_accepted_files' => ".csv",
                                'max_files' => 1,
                                'on_success' => '$("#download_intra_ex").show("slow")'
                            ])
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3"></div>
        </div>
    </div>
@endsection
