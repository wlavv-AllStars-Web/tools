@extends('layouts.app')
@section('content')
<div class="customPanel" style="text-align: center;">
    <div style="display: none;">
        <h1>{{ __('tags.Please select a store') }}</h1>
    </div>
    <div style="background: none; padding:20px;display: flex;text-align: center; border-bottom: 2px solid #ccc; display: none;">
        @foreach ($stores as $k => $store)
            <div @if(!$store->disabled) class="rowStyling" onclick="changeStore($(this), '{{$k}}')" @endif style="margin: 0 auto; width: 23%; border: 1px solid #ccc;border-radius: 5px; @if($store->disabled) background-color: #888 @else cursor: pointer; background-color: #ddd; @endif">
                <div style="margin: auto auto; height: 100%; display: inline-grid;">
                    <img src="{{ $store->logo }}" style="width: 200px;"> 
                    <div>{!! $store->name !!}</div>
                </div>
            </div>
        @endforeach
    </div>
    
    <div id="step_2" style="border-bottom: 2px solid #ccc;padding: 30px 0;">
        <div> <h3>{{ __('tags.select a manufacturer') }}</h3> </div>
        <select id="selected_manufacturer" name="selected_manufacturer" onchange="$('#step_3').css('display', 'block')" style="width: 300px; font-size: 20px; text-align: center;">
            <option value="">{{ __('tags.select a brand') }}</option>
            @foreach ($manufacturers as $manufacturer)
                <option value="{{$manufacturer->id_manufacturer}}">{{$manufacturer->name}}</option>
            @endforeach
        </select>
    </div>
    
    <div id="step_3" style="display: none; margin-top: 60px;background-color: #eee;border: 1px solid #ccc;text-align: center;border-radius: 5px 5px 0 0;">
        <div style="display: inline-block;margin: 0 auto;">
            <div style="width: 150px;float: left;text-align: center;padding-right: 10px;padding-top: 20px;" onclick="changeUploadLogo('xlsx')">
                <h5>{{ __('tags.catalogue') }}</h5>
                <img id="xlsx_logo" src="/images/icons/xlsx.png">
            </div>
            <div style="width: 150px;float: left;text-align: center;padding-right: 10px;padding-top: 20px;" onclick="changeUploadLogo('csv')">
                <h5>{{ __('tags.csv') }}</h5>
                <img id="csv_logo" src="/images/icons/csv.png">
            </div>
            <div style="width: 150px;float: left;text-align: center;padding-right: 10px;padding-top: 20px;" onclick="changeUploadLogo('zip')">
                <h5>{{ __('tags.images') }}</h5>
                <img id="zip_logo" src="/images/icons/zip.png">
            </div>
            <div style="width: 150px;float: left;text-align: center;padding-right: 10px;padding-top: 20px;" onclick="changeUploadLogo('jpg')">
                <h5>{{ __('tags.logos') }}</h5>
                <img id="jpg_logo" src="/images/icons/jpg.png">
            </div>
        </div>
    </div>
    <div style="width: 100%;border: 1px solid #ccc;border-radius: 0 0 5px 5px;">
        <div id="upload_csv" class="upload_container">
            @include("customTools.uploads.upload_container", [
                'title' => '', 
                'message' => trans('tags.drag to upload csv') . '<br>' . trans('tags.Only CSV files'),  
                'upload_path' => "manufacturer/temp/",
                'filename' => "csv.csv",
                'upload_accepted_files' => ".csv",
                'max_files' => 1,
                'on_success' => '$("#step_4").css("display", "block")',
                'uploadElementID' => 'upload_csv'
            ])
        </div>
        <div id="upload_xlsx" class="upload_container">
            @include("customTools.uploads.upload_container", [
                'title' => '', 
                'message' => trans('tags.drag to upload catalogue') . '<br>' . trans('tags.Only XLSX files'),  
                'upload_path' => "manufacturer/temp/",
                'filename' => "catalogue.xlsx",
                'upload_accepted_files' => ".xlsx",
                'max_files' => 1,
                'on_success' => '$("#step_4").css("display", "block")',
                'uploadElementID' => 'upload_xlsx'
            ])
        </div>
        <div id="upload_zip" class="upload_container">
            @include("customTools.uploads.upload_container", [
                'title' => '', 
                'message' => trans('tags.drag to upload images') . '<br>' . trans('tags.Only ZIP files'),  
                'upload_path' => "manufacturer/temp/",
                'filename' => "images.zip",
                'upload_accepted_files' => ".zip",
                'max_files' => 1,
                'on_success' => '$("#step_4").css("display", "block")',
                'uploadElementID' => 'upload_zip'
            ])
        </div>
        <div id="upload_jpg" class="upload_container">
            @include("customTools.uploads.upload_container", [
                'title' => '', 
                'message' => trans('tags.drag to upload logos') . '<br>' . trans('tags.Only ZIP files'),  
                'upload_path' => "manufacturer/temp/",
                'filename' => "logos.zip",
                'upload_accepted_files' => ".zip",
                'max_files' => 1,
                'on_success' => '$("#step_4").css("display", "block")',
                'uploadElementID' => 'upload_logos'
            ])
        </div>
    
        <div id="step_4" style="display: none; margin: 30px 0;">
            <span class="btn btn-success" onclick="sendData()" style="margin-bottom: 20px;">{{ __('tags.upload data') }}</span>
        </div>
    </div>
    <input type="hidden" id="selected_store" value="ASD">        
    <input type="hidden" id="selected_resource_type" value="">        

</div>

<script>
    
    function changeStore(element, store){
        
        $('#selected_store').prop('value', store); 
        $('.rowStyling').css('background', 'none'); 
        element.css('background', '#ddd'); 
        $('#step_2').css('display', 'block');
        $('#step_3').css('display', 'none');
        $('#step_4').css('display', 'none');
        $('#upload_area').hide('slow');

        $('#csv_logo').prop('src', '/images/icons/csv.png');
        $('#xlsx_logo').prop('src', '/images/icons/xlsx.png');
        $('#zip_logo').prop('src', '/images/icons/zip.png');
        $('#jpg_logo').prop('src', '/images/icons/jpg.png');
        
        $('#selected_resource_type').prop('value', '');

    }
    
    function changeUploadLogo(name){
        
        $('#csv_logo').prop('src', '/images/icons/csv.png');
        $('#xlsx_logo').prop('src', '/images/icons/xlsx.png');
        $('#zip_logo').prop('src', '/images/icons/zip.png');
        $('#jpg_logo').prop('src', '/images/icons/jpg.png');
        
        $('#' + name + '_logo').prop('src', '/images/icons/' + name + '_updated.png');

        $('.upload_container').hide();
        $('#upload_' + name).show();
        $('div#upload_' + name + ' > div#upload_area').show();

        
        $('#selected_resource_type').val(name);
        

    }
    
    function executeFunction(){

        $('#step_4').css('display', 'block');
        
    }

    function sendData(){
        
        let store = $('#selected_store').val();
        let manufacturer = $('#selected_manufacturer').val();
        let ressourceType = $('#selected_resource_type').val();

	    $.ajax({
            type: 'POST',
            async: true,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ route('manufacturers.ressourcesPost') }}",
            data: {
                action: 'uploadResources',
                store: store,
                manufacturer: manufacturer,
                ressourceType: ressourceType
            },
            success: function(data) {
                Swal.fire('{{ __("messages.Resources saved at webtoools storage!")}}', '', 'success')
            }
        });
        
    }
</script>
@endsection