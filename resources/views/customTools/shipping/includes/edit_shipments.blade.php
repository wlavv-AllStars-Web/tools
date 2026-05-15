<table style="width: 100%;">
    <tr>
        <td></td>
        <td>
            <h2>SHIPMENTS</h2>
        </td>
    </tr>
    <tr>
        <td class="left_column">SUPPLIER</td>
        <td class="right_column">
            <select name="supplier" style="width: 100%;">
                <option value="0">Select supplier</option>
                @foreach($suppliers AS $id_supplier => $supplier)
                    <option value="{{$id_supplier}}" @if($shipment->supplier == $id_supplier) selected="selected" @endif>{{$supplier}}</option>
                @endforeach
            </select>
        </td>
    </tr>
    <tr>
        <td class="left_column">READY DATE</td>
        <td class="right_column"><input style="width: 100%;" type="date" value="{{$shipment->ready_date}}" name="ready_date"></td>
    </tr>
    <tr>
        <td class="left_column">INVOICE NUMBER</td>
        <td class="right_column"><input placeholder="Invoice number" style="width: 100%;" value="{{$shipment->invoice_number}}" type="text" name="invoice_number"></td>
    </tr>
    <tr>
        <td class="left_column">INVOICE VALUE</td>
        <td class="right_column"><input placeholder="€uros" style="width: 100%;" step="any" value="{{$shipment->invoice}}" type="number" name="invoice"></td>
    </tr>
    <tr>
        <td class="left_column">CARRIER</td>
        <td class="right_column">
            <select name="carrier" style="width: 100%;">
                <option value="0">Select carrier</option>
                @foreach($carriers AS $id_carrier => $carrier)
                    <option value="{{$id_carrier}}" @if($shipment->carrier == $id_carrier) selected="selected" @endif>{{$carrier}}</option>
                @endforeach
            </select>
        </td>
    </tr>
    <tr>
        <td class="left_column">SHIPPING QUOTE</td>
        <td class="right_column"><input placeholder="ExVAT" style="width: 100%;" step="any" type="number" value="{{$shipment->shipping_quote}}" name="shipping_quote"></td>
    </tr>
    <tr>
        <td class="left_column">VALIDATION DATE</td>
        <td class="right_column"><input style="width: 100%;" type="date" value="{{$shipment->validation_date}}" name="validation_date"></td>
    </tr>
    <tr>
        <td class="left_column">PACKING LIST</td>
        <td class="right_column">
            <select name="packing_list" style="width: 100%;">
                <option value="0" @if($shipment->packing_list == 0) selected="selected" @endif>NO</option>
                <option value="1" @if($shipment->packing_list == 1) selected="selected" @endif>YES</option>
                <option value="2" @if($shipment->packing_list == 2) selected="selected" @endif>N/D</option>
            </select>
        </td>
    </tr>
    <tr>
        <td class="left_column">CUSTOMS DOCS</td>
        <td class="right_column">
            <select name="customs_docs" style="width: 100%;">
                <option value="0" @if($shipment->customs_docs == 0) selected="selected" @endif>NO</option>
                <option value="1" @if($shipment->customs_docs == 1) selected="selected" @endif>YES</option>
                <option value="2" @if($shipment->customs_docs == 2) selected="selected" @endif>N/D</option>
            </select>
        </td>
    </tr>
    <tr>
        <td class="left_column">PICKING DATE</td>
        <td class="right_column"><input style="width: 100%;" type="date" value="{{$shipment->picking_date}}" name="picking_date"></td>
    </tr>
    <tr>
        <td class="left_column">TRACKING</td>
        <td class="right_column"><input style="width: 100%;" type="text" value="{{$shipment->tracking}}" name="tracking"></td>
    </tr>
    <tr>
        <td class="left_column">STATUS</td>
        <td class="right_column">
            <select id="statusSelector" name="status" style="width: 100%;">
                <option value="1" @if($shipment->status == 1) selected="selected" @endif>WAITING</option>
                <option value="2" @if($shipment->status == 2) selected="selected" @endif>IN TRANSIT</option>
                <option value="3" @if($shipment->status == 3) selected="selected" @endif>RECEIVED</option>
                <option value="4" @if($shipment->status == 4) selected="selected" @endif>CANCELLED</option>
            </select>
        </td>
    </tr>
</table>