@extends('layouts.app')
@section('content')

    <div class="navbar navbar-light customPanel" style="width: 100%;">
        <div class="card">
            <input type="text" name="scan" id="scan" style="width: 100%; border-radius: 5px; border: 1px solid #ccc; text-align: center;">
        </div>
    </div>

    <div id="housingInfo">
        <div class="navbar navbar-light customPanel" style="width: 100%;">
            <div class="alert alert-warning" role="alert" style="text-align: center;margin: 0;"> PLEASE SCAN A BARCODE </div>
        </div>
    </div>
    
    <script>
        document.getElementById("scan").focus();
        
        
        /** ON STOP TYPING **/
        var typingTimer;                
        var doneTypingInterval = 500;
        var input = $('#scan');
        
        input.on('keyup', function () {
          clearTimeout(typingTimer);
          typingTimer = setTimeout(doneTyping, doneTypingInterval);
        });
        
        input.on('keydown', function () {
          clearTimeout(typingTimer);
        });
        
        function doneTyping () {
            let barcode = input.val();
            if(barcode.length) requestData();
        }
        
        function requestData(){
            
            $.ajax({
                type: 'POST',
                url: "{{ route('housing.requestData') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    barcode: input.val()
                },
                success: function(response) {
                    $("#housingInfo").replaceWith(response);
                }       
            });
        }
        
        function saveNewLocation(){
            
            $.ajax({
                type: 'POST',
                url: "{{ route('housing.saveData') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    barcode: $('#newBarcode').val(),
                    stand:   $('#newStandBarcode').val()
                },
                success: function(response) {
                    location.reload();
                }       
            });
        }
        
        function editLocation(){
            
            $.ajax({
                type: 'POST',
                url: "{{ route('housing.editLocation') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    barcode: $('#scan').val(),
                    stand:   $('#editedBarcode').val(),
                    id_product: $('#id_product').val(),
                    id_product_attribute: $('#id_product_attribute').val()
                },
                success: function(response) {
                    location.reload();
                }       
            });
        }
        
        function editMeasurements(){
            
            $.ajax({
                type: 'POST',
                url: "{{ route('housing.editMeasures') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    barcode: $('#scan').val(),
                    weight:  $('#editedWeight').val(),
                    width:   $('#editedWidth').val(),
                    depth:   $('#editedDepth').val(),
                    height:  $('#editedHeight').val(),
                    id_product: $('#id_product').val(),
                    id_product_attribute: $('#id_product_attribute').val()
                },
                success: function(response) {
                    location.reload();
                }       
            });
        }
        
    </script>

@endsection