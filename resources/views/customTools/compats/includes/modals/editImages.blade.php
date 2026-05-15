<div class="modal fade" id="myModalUpload" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Upload</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12 dropzoneImageUpdate" style="display: none">
                        @include("customTools.uploads.upload_container", [
                            'title' => '', 
                            'message' => trans('tags.Drop PNG files here or click to upload') . '<br>' . trans('tags.Only PNG files'), 
                            'upload_path' => "compats/",
                            'filename' => "image.png",
                            'upload_accepted_files' => ".png",
                            'max_files' => 1,
                            'on_success' => 'executeFunction()'
                        ]) 
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="updateImages" element="" id_element="" class="btn btn-primary" onclick="uploadUpdatedLogo()">Save</button>
            </div>
        </div>
    </div>
</div>