@extends('layouts.app')
@section('content')

    @include("customTools.productIssues.includes.js")

    <div class="navbar navbar-light customPanel categorList">
        <form id="myform" action="{{ route('productIssues.update') }}" method="POST" class="p-3">
            {{ csrf_field() }}
            <input type="hidden" name="id_issue" id="id_issue" class="form-control" value="{{$issue->id_issue}}">
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label for="reference" class="form-label">REFERENCE</label>
                        <input type="text" name="reference" id="reference" class="form-control" value="{{$issue->reference}}">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">ISSUE</label>
                        <input type="text" name="description" id="description" class="form-control" value="{{$issue->description}}">
                    </div>
                    <div class="mb-3">
                        <label for="car" class="form-label">CAR</label>
                        <input type="text" name="car" id="car" class="form-control" value="{{$issue->car}}">
                    </div>
                    <div style="margin-bottom: 20px;height: 60px;">
                        <div>
                            <label for="reference" class="form-label">ISSUES</label>
                        </div>
                        <div class="form-check" style="width: 33%; float: left;">
                            <input type="checkbox" name="assembly" id="assembly" value="1" class="form-check-input" @if($issue->assembly == 1) checked="checked" @endif>
                            <label class="form-check-label" for="assembly">ASSEMBLY</label>
                        </div>
                        <div class="form-check" style="width: 33%; float: left;">
                            <input type="checkbox" name="compatibility" id="compatibility" value="1" class="form-check-input" @if($issue->compatibility == 1) checked="checked" @endif>
                            <label class="form-check-label" for="compatibility">COMPATIBILITY</label>
                        </div>
                        <div class="form-check" style="width: 33%; float: left;">
                            <input type="checkbox" name="defect" id="defect" value="1" class="form-check-input" @if($issue->defect == 1) checked="checked" @endif>
                            <label class="form-check-label" for="defect">DEFECT</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="id_order">ID ORDER</label>
                        <input type="number" name="id_order" id="id_order" value="{{$issue->id_order}}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="date" class="form-label">DATE</label>
                        <input type="date" name="date" id="date" value="{{$issue->date}}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="shop" class="form-label">SHOP</label>
                        <select name="shop" id="shop" class="form-select">
                            <option @if($issue->store == 'ASM') selected="selected" @endif value="ASM">ASM</option>
                            <option @if($issue->store == 'ASD') selected="selected" @endif value="ASD">ASD</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">STATUS</label>
                        <select name="status" id="status" class="form-select">
                            <option @if($issue->status == 'NO SOLUTION') selected="selected" @endif value="NO SOLUTION">NO SOLUTION</option>
                            <option @if($issue->status == 'SOLVED') selected="selected" @endif value="SOLVED">SOLVED</option>
                            <option @if($issue->status == 'PENDING') selected="selected" @endif value="PENDING">PENDING</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="conclusion" class="form-label">CONCLUSION</label>
                        <input type="text" name="conclusion" id="conclusion" class="form-control" value="{{$issue->conclusion}}">
                    </div>
                    <button type="submit" class="btn btn-success w-100 mt-3">SAVE</button>
                </div>
                <div class="col-lg-6">
                    <div style="text-align: center;margin-bottom: 30px;"> ADD PICTURES </div>
                    <div class="input-group" style="border-radius: 0 5px 5px 0;display: grid; width: 500px; margin: 0 auto;">
                        <div id="upload_area" class="dropzone" style="display: none; margin-top: 20px;">
                            <input id="upload_filename" type="hidden" name="upload_filename" value="image.jpg">
                            <input id="upload_path" type="hidden" name="upload_path" value="productIssues/">
                            <div class="dz-message needsclick"> 
                                <i class="fa-solid fa-download" style="font-size: 80px; color: dodgerblue;"></i>
                                <br><br>
                                Drop file here or click to upload
                            </div>
                            <div id="dzPreviewContainer" style="display: none;"></div>
                        </div>
                    </div>
                </div>
                <script>
                    Dropzone.autoDiscover = false;
                
                    function executeFunction() { }
                
                    function initializeDropzone() {
                        console.log("Inicializando Dropzone...");
                
                        const dropzoneElement = document.querySelector("#upload_area");
                        
                        if (dropzoneElement) {
                            const dropzone = new Dropzone(dropzoneElement, {
                                url: "{{ route('uploads.upload') }}",
                                paramName: "file",
                                maxFilesize: 3, // MB
                                addRemoveLinks: true,
                                headers: {
                                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                                },
                                accept: function (file, done) {
                                    const check_filename = document.getElementById("reference").value;
                            
                                    if (check_filename.length < 1) {

                                        Swal.fire({
                                            icon: "warning",
                                            title: "Reference required",
                                            text: "Please fill the reference field before uploading.",
                                        });
        
                                        done("Reference required"); 
                                    } else {
                                        done();
                                    }
                                },
                                init: function () {
                                    this.on("sending", function (file, xhr, formData) {
                                        const rand = Math.floor(Math.random() * 100);

                                        const uploadPath = document.getElementById("upload_path").value;
                                        const reference = document.getElementById("reference").value;
                                        const id_order = document.getElementById("id_order").value;
                                        const extension = file.name.split('.').pop().toLowerCase();                                    
                                    
                                        const safeReference = sanitizeForPath(reference);
                                        const upload_filename = safeReference + "_rand_" + rand + "." + extension;
                                
                                        formData.append("upload_path", uploadPath + safeReference + "/" + id_order + "/");
                                        formData.append("upload_filename", upload_filename);
                                    });
                            
                                    this.on("success", function (file, response) {
                                        console.log("Upload concluído:", response);
                                        executeFunction();
                                    });
                            
                                    this.on("error", function (file, errorMessage) {
                                        console.error("Erro no upload:", errorMessage);
                                    });
                            
                                    this.on("addedfile", function (file) {
                                        document.getElementById("upload_filename").value = file.name;
                                    });
                                }
                            });

                        }
                    }

                    function sanitizeForPath(str) {
                        return str
                            .normalize("NFD")                   // remove acentos
                            .replace(/[\u0300-\u036f]/g, "")    // remove marcas de acento
                            .replace(/[^a-zA-Z0-9_-]/g, "_")    // substitui tudo o que não for letra, número, underscore ou hífen
                            .replace(/_+/g, "_")                // evita underscores repetidos
                            .replace(/^_+|_+$/g, "");           // remove underscores do início/fim
                    }

                    document.addEventListener("DOMContentLoaded", initializeDropzone);
                </script>
                <style>
                
                .dropzone {
                    background: white;
                    border-radius: 5px;
                    border: 2px dashed rgb(0, 135, 247);
                    width: 500px;
                    margin-left: auto;
                    margin-right: auto;
                }
                
                    
                </style>
            </div>
        </form>
    </div>
@endsection