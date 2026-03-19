@extends('layouts.app')
@section('content')

    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <div class="row">
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel" style="margin-top: 10px;" id="carrierSelection">
                <span style="display: table;margin: 10px auto;">
                   @foreach($carriers AS $carrier)
                        <div style="float: left; width: 100px; text-align: center; border: 1px solid #ccc;background-color: #eee; margin: 10px;border-radius: 5px;" onclick="openUpload('{{$carrier['name']}}')">
                            <img src="{{$carrier['logo']}}" style="width: 100px;">
                        </div>
                   @endforeach                    
                </span>
            </div>
        </div>
        
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel" style="margin-top: 10px;display: none;" id="carrierUploadContainer"> </div> 
        </div>
        
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel" style="margin-top: 10px;display: none;" id="verificationContainer"> </div>
        </div>
    </div>
  
    <style>
        #upload_area{ display: block !important; margin-top: 0 !important;}
        .oversized{ background-color: #f8d7da; }
        tr.oversized > td{ background-color: #f8d7da; }
    </style>



    <script>
        function executeFunction(){
            
            setTimeout(function () {            
                
                let carrier = $('#selectedCarrier').val();

                $.ajax({
                    type: 'POST',
                    url: "{{ route('carrierIssues.verification.carrierVerify') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        carrier: carrier 
                    },
                    success: function(response) {
                        $('#verificationContainer').replaceWith(response);
                        $('#carrierUploadContainer').css('display', 'none');
                        $('#carrierSelection').css('display', 'none');
                        carrierSelection
                    }       
                });

            }, 1000);
            
        }   
        
        function openUpload(carrier){
            
            $.ajax({
                type: 'POST',
                url: "{{ route('carrierIssues.verification.upload') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    carrier: carrier 
                },
                success: function(response) {
                    $('#carrierUploadContainer').replaceWith(response);
                    initializeDropzone();
                }       
            });
        
        }
    
        function initializeDropzone() {
            
            const dropzoneElement = document.querySelector(".dropzone");
            
            if (dropzoneElement) {
                const dropzone = new Dropzone(dropzoneElement, {
                    url: "{{route('uploads.upload')}}",
                    paramName: "file",
                    maxFilesize: 3,
                    success: function (file, response) { console.log("Upload concluído:", response); },
                    error: function (file, errorMessage) { console.error("Erro no upload:", errorMessage); }
                });
            }
        }
    </script>


@endsection
  