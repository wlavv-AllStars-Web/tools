<div class="navbar navbar-light customPanel" id="carrierUploadContainer">
    <div class="input-group" style="border-radius: 0 5px 5px 0;display: grid; ">
        <div style="margin-bottom: 20px; text-align: center;">
            <input type="hidden" value="{{$carrier}}" id="selectedCarrier">
            @include("customTools.uploads.upload_container", [
                'title' => '', 
                'message' => trans('tags.Drop csv files here or click to upload') . '<br>' . trans('tags.Only csv files'), 
                'upload_path' => "carriers/" . $carrier . '/',
                'filename' => $carrier . ".csv",
                'upload_accepted_files' => ".xlsx, .xls, .csv",
                'max_files' => 1,
                'on_success' => 'executeFunction()'
            ]) 
        </div>    
    </div>
</div>

<script> 
    Dropzone.autoDiscover = false; 
</script>