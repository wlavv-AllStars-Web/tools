<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">CREATE NEW COMPAT RELATION</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-2" style="text-align: right;"><label for="type">TYPE</label></div>
                    <div class="col-lg-10">                        
                        <select id="type" name="type" onchange="loadParent($(this))" style="width: 100%;">
                            <option value="">Select type</option>
                            <option value="0">Brand</option>
                            <option value="1">Model</option>
                            <option value="2">Type</option>
                            <option value="3">Version</option>
                        </select>
                    </div>
                    <div class="col-lg-12 selectParent"><div style="height: 20px;width: 100%;"></div></div>
                    <div class="col-lg-2  selectParent" style="text-align: right;"><label for="id_parent">PARENT</label></div>
                    <div class="col-lg-10 selectParent">                        
                        <div id="parentOptionContainer"></div>
                    </div>
                    <div class="col-lg-12"><div style="height: 20px;width: 100%;"></div></div>
                    <div class="col-lg-2" style="text-align: right;"><label for="name">NAME</label></div>
                    <div class="col-lg-10">                        
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"> <img src="https://www.all-stars-motorsport.com/img/l/1.jpg" style="width: 40px;padding: 7px 2px;border: 1px solid #ccc; background: #eee;border-radius: 5px 0 0 5px;"> </div>
                            <input type="text" class="form-control" placeholder="" id="name_en" name="name_en" aria-label="name_en" aria-describedby="basic-addon1" onkeyup="$('#name_es').attr('value', $(this).val());$('#name_fr').attr('value', $(this).val());">
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"> <img src="https://www.all-stars-motorsport.com/img/l/4.jpg" style="width: 40px;padding: 7px 2px;border: 1px solid #ccc; background: #eee;border-radius: 5px 0 0 5px;"> </div>
                            <input type="text" class="form-control" placeholder="" id="name_es" name="name_es" aria-label="name_es" aria-describedby="basic-addon1">
                        </div>

                        <div class="input-group mb-3">
                            <div class="input-group-prepend"> <img src="https://www.all-stars-motorsport.com/img/l/5.jpg" style="width: 40px;padding: 7px 2px;border: 1px solid #ccc; background: #eee;border-radius: 5px 0 0 5px;"> </div>
                            <input type="text" class="form-control" placeholder="" id="name_fr" name="name_fr" aria-label="name_fr" aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col-lg-12 dropzoneImage" style="display: none">
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
                <button type="button" class="btn btn-primary" onclick="saveRelationship()">Save</button>
            </div>
        </div>
    </div>
</div>