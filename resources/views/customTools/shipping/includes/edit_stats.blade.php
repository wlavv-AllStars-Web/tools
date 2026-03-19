
<form action="{{route('shipping.store')}}" method="post">
    {!! csrf_field() !!}
    <table style="width: 100%;">
        <tr>
            <td></td>
            <td>
                <h2>STATS</h2>
            </td>
        </tr>
        <tr>
            <td class="left_column">INCOTERM</td>
            <td class="right_column">
                <select name="incoterm" style="width: 100%;">
                    <option value=""    @if($shipment->incoterm == "") selected="selected" @endif>SELECT INCOTERM</option>
                    <option value="CPT" @if($shipment->incoterm == "CPT") selected="selected" @endif>CPT</option>
                    <option value="DAP" @if($shipment->incoterm == "DAP") selected="selected" @endif>DAP</option>
                    <option value="DDP" @if($shipment->incoterm == "DDP") selected="selected" @endif>DDP</option>
                    <option value="EXW" @if($shipment->incoterm == "EXW") selected="selected" @endif>EXW</option>
                    <option value="FCA" @if($shipment->incoterm == "FCA") selected="selected" @endif>FCA</option>
                    <option value="FOB" @if($shipment->incoterm == "FOB") selected="selected" @endif>FOB</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="left_column">ROUTE</td>
            <td class="right_column">
                <select name="route" style="width: 100%;">
                    <option value=""        @if($shipment->route == "")         selected="selected" @endif>SELECT ROUTE</option>
                    <option value="LAND"    @if($shipment->route == "LAND")     selected="selected" @endif>LAND</option>
                    <option value="AIR"     @if($shipment->route == "AIR" )     selected="selected" @endif>AIR</option>
                    <option value="SEA_LCL" @if($shipment->route == "SEA_LCL" ) selected="selected" @endif>SEA LCL</option>
                    <option value="SEA_FCL" @if($shipment->route == "SEA_FCL" ) selected="selected" @endif>SEA FCL</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="left_column">FREIGHT</td>
            <td class="right_column"><input placeholder="ExVAT" style="width: 100%;" type="number" step="any" value="{{$shipment->freight}}" name="freight"></td>
        </tr>
        <tr>
            <td class="left_column">CUSTOMS CLEAR</td>
            <td class="right_column"><input placeholder="ExVAT" style="width: 100%;" type="number" step="any" value="{{$shipment->customs_clear}}" name="customs_clear"></td>
        </tr>
        <tr>
            <td class="left_column">IMPORT DUTIES</td>
            <td class="right_column"><input placeholder="ExVAT" style="width: 100%;" type="number" step="any" value="{{$shipment->import_duties}}" name="import_duties"></td>
        </tr>
        @if(count($delay_dates) == 0)
            <tr>
                <td class="left_column">ETA</td>
                <td class="right_column" onclick="addNewDelayDate({{$shipment->id}})"> <span style="color: dodgerblue; cursor: pointer;"> SET ETA </span> </td>
            </tr>
        @else
            @foreach($delay_dates AS $key => $delay)
                <tr>
                    <td class="left_column"> @if($key == 0) ETA @else {{$key}}ª DELAYED ETA @endif</td>
                    <td class="right_column">
                        <input style="width: 100%;" type="date" value="{{$delay->date}}" name="eta[]" readonly>
                    </td>
                </tr>
            @endforeach
            <tr>
                <td class="left_column"></td>
                <td class="right_column" onclick="addNewDelayDate({{$shipment->id}})"> <span style="color: dodgerblue; cursor: pointer;"> SET NEW DELAYED ETA </span> </td>
            </tr>
        @endif
        <tr>
            <td class="left_column" colspan="2"> <div style="width: 100%; height: 20px;"></div> </td>
        </tr>
        <tr>
            <td class="left_column">DELIVERY DATE</td>
            <td class="right_column"><input style="width: 100%;" type="date" value="{{$shipment->delivery_date}}" name="delivery_date"></td>
        </tr>
        <tr>
            <td class="left_column">COMMENTS</td>
            <td class="right_column">
                <textarea name="comments" rows="4" style="width: 100%;" value="{{$shipment->comments}}">{{$shipment->comments}}</textarea>
            </td>
        </tr>
    </table>
</form>

<script>
    
    function addNewDelayDate( id_shipping ){

        Swal.fire({
            title: 'New delay ETA',
            html: '<input type="date" id="dataInput" class="swal2-input">',
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'OK',
            preConfirm: () => {
                let dataSelecionada = document.getElementById('dataInput').value;
                if (!dataSelecionada) {
                    Swal.showValidationMessage('Por favor, selecione uma data!');
                }
                return dataSelecionada;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('NEW DELAY DATE', `You saved: <span style="font-weight: bolder"> ${result.value} </span> as new delay date!`, 'success');

                $.ajax({
                    type: 'POST',
                    url: "{{route('shipping.addDelay')}}",
                    dataType: "json",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_shipping: id_shipping,
                        newEta: result.value
                    },
                    success: function(response) {
                        Swal.fire("Success!", "Your data has been saved.", "success");
                    }
                });
                
            }
        });
    
    }
</script>