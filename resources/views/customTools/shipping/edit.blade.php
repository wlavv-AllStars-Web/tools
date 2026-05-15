@extends('layouts.app')
@section('content')

    <form id="editFormShipment" action="{{route('shipping.update', ['id' => $shipment->id])}}" method="post">
        {!! csrf_field() !!}
        <div class="navbar navbar-light customPanel">
            <div class="row">
                <div class="col-lg-3">
                    @include("customTools.shipping.includes.edit_shipments", ['shipment' => $shipment, 'suppliers' => $suppliers, 'carriers' => $carriers])
                </div>
                <div class="col-lg-3">
                    @include("customTools.shipping.includes.edit_stats", ['shipment' => $shipment, 'suppliers' => $suppliers, 'carriers' => $carriers])
                </div>
                <div class="col-lg-6">
                    @include("customTools.shipping.includes.edit_packaging", ['packages' => $packages])

                    @if(in_array(auth()->id(), [2, 43, 59, 94, 103]))
                        @include("customTools.shipping.includes.edit_erp", ['erp' => $erp])
                    @endif
                </div>
                

                
                <div class="col-lg-12" style="display: flex;">
                   <button class="btn btn-success" type="button" onclick="submitForm()" style="width: 300px;margin: 0 auto;">SAVE</button>
                </div>
            </div>
        </div>
    </form>
    
    <script>
        
        function submitForm(){
            
            status = $('#statusSelector').val();
            
            if(status == 3){
                
                send=0;
                
                ready_date = document.getElementsByName("ready_date")[0].value;
                invoice_number = document.getElementsByName("invoice_number")[0].value;
                invoice    = document.getElementsByName("invoice")[0].value;
                carrier = document.getElementsByName("carrier")[0].value;
                shipping_quote = document.getElementsByName("shipping_quote")[0].value;
                validation_date = document.getElementsByName("validation_date")[0].value;
                picking_date = document.getElementsByName("picking_date")[0].value;
                tracking = document.getElementsByName("tracking")[0].value;
                incoterm = document.getElementsByName("incoterm")[0].value;
                route = document.getElementsByName("route")[0].value;
                freight = document.getElementsByName("freight")[0].value;
                delivery_date = document.getElementsByName("delivery_date")[0].value;
                
                if( (ready_date.length > 8) && ( ready_date != '0000-00-00' ) ) send = send + 1;
                if( invoice_number.length > 1 ) send = send + 1;
                if( (invoice.length > 1) && ( invoice > 0 ) ) send = send + 1;
                if( carrier > 0 ) send = send + 1;
                if( shipping_quote > 0 ) send = send + 1;
                if( (validation_date.length > 8) && ( validation_date != '0000-00-00' ) ) send = send + 1;
                if( (picking_date.length > 8) && ( picking_date != '0000-00-00' ) ) send = send + 1;
                if( tracking.length > 0 ) send = send + 1;
                if( incoterm > 0 ) send = send + 1;
                if( route.length > 0 ) send = send + 1;
                if( freight.length > 0 ) send = send + 1;
                if( (delivery_date.length > 8) && ( delivery_date != '0000-00-00' ) ) send = send + 1;
                
                if(send == 11){
                    $('#editFormShipment').submit();
                }else{
                    Swal.fire('PLEASE FILL ALL FIELDS!', '', 'error');
                }
            }else{
                $('#editFormShipment').submit();
            }
        }
        
    </script>
    @include("customTools.shipping.includes.css")
    @include("customTools.shipping.includes.js")
@endsection