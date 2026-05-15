@extends('layouts.app')
@section('content')

    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <div class="row">
        <div class="col-lg-3">
            <div class="navbar navbar-light customPanel">
                <div style="background-color: #ddd; color: #666; text-align: center; padding: 10px; border: 1px solid #aaa;margin-bottom: 10px;"> DOCUMENT INFORMATION </div>
                <div class="input-group" style="border-radius: 0 5px 5px 0; margin-bottom: 10px;">
                    <div class="input-group-prepend" style="width: 120px; float: left;"> <div class="input-group-text" style="border-radius: 5px 0 0 5px;text-align: right;">{{ __("messages.Name")}}</div> </div>
                    <input id="name" name="name" type="text" class="form-control" value="" required>
                </div>
                <div class="input-group" style="border-radius: 0 5px 5px 0; margin-bottom: 10px;text-align: center">
                    <div class="input-group-prepend" style="width: 120px; float: left;"> <div class="input-group-text" style="border-radius: 5px 0 0 5px;text-align: right;">{{ __("messages.Document Date")}}</div> </div>
                    <input style="text-align: center;" id="document_year" name="year"  placeholder="YY" type="text" class="form-control" value="{{date('Y')}}" required>
                    <input style="text-align: center;" id="document_month" name="month" placeholder="M"  type="text" class="form-control" value="{{date('m')}}" required>
                    <input style="text-align: center;" id="document_day" name="day"   placeholder="D"  type="text" class="form-control" value="{{date('d')}}" required>
                </div>

                @include("customTools.documentsManager.includes.documentType")
                
                @include("customTools.documentsManager.includes.manifestBlock") 

                <div id="categoryBlock" class="input-group" style="border-radius: 0 5px 5px 0; margin-bottom: 10px; display: none;">
                    <div class="input-group-prepend" style="width: 120px; float: left;"> <div class="input-group-text" style="border-radius: 5px 0 0 5px;text-align: right;">{{ __("messages.Category")}}</div> </div>
                    <select id="category" name="category" class="form-control" onchange="checkExtraFields()" style="width: calc( 100% - 120px);">
                        <option value="" selected="selected"> Please select...</option>
                        <option value="supplier">Supplier</option>
                        <option value="services">Services</option>
                        <option value="carriers">Carriers</option>
                        <option value="others">Others</option>
                    </select>
                </div>

                @include("customTools.documentsManager.includes.suppliers", [ 'suppliers' => $suppliers ])
                @include("customTools.documentsManager.includes.services",  [ 'services' => $services ])
                @include("customTools.documentsManager.includes.carriers",  
                    [ 
                        'carriers' => $carriers, 
                        'carriersDocuments' => $carriersDocuments,
                    ]
                )

                @include("customTools.documentsManager.includes.financeBlock") 
                
                <div class="input-group" style="border-radius: 0 5px 5px 0; margin-bottom: 10px;">
                    <div class="input-group-prepend" style="width: 120px; float: left;display: flex;"> <div class="input-group-text" style="border-radius: 5px 0 0 5px;text-align: right;width: 100%;">{{ __("messages.Notes")}}</div> </div>
                    <div class="form-control" style="padding: 0;">
                        <textarea name="notes" id="notes" placeholder="Notes" class="form-control"></textarea>
                    </div>
                </div>

                <div class="input-group" style="border-radius: 0 5px 5px 0;display: grid; ">
                    <div style="margin-bottom: 20px; text-align: center;">
                        @include("customTools.uploads.upload_container", [
                            'title' => '', 
                            'message' => trans('tags.Drop pdf files here or click to upload') . '<br>' . trans('tags.Only pdf files'), 
                            'upload_path' => "documents/",
                            'filename' => "upload.pdf",
                            'upload_accepted_files' => ".pdf",
                            'max_files' => 1,
                            'on_success' => 'executeFunction()'
                        ]) 
                    </div>    
                </div>
            </div>
        </div>
        <div class="col-lg-9">
            <div id="loadFile"></div>
            <div class="navbar navbar-light customPanel" style="margin-top: 10px;display: none;" id="idSaveButton">
                <div class="input-group" style="border-radius: 0 5px 5px 0; margin-bottom: 10px;display: grid;">
                    <button type="button" class="btn btn-success" onclick="saveDocument()" style="width: 250px;"> SAVE </button>
                </div>
            </div>
        </div>
    </div>
  
    <style>
        #upload_area{ display: block !important; margin-top: 0 !important;}
    </style>

    @include("customTools.documentsManager.includes.js")

@endsection
  