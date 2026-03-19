<div id="container_RECEIVED" class="containers">
    <table class="table table-striped text-center" style="width: 100;">
        <tr>
            <td>ID</td>
            <td>SUPPLIER</td>
            <td>CARRIER</td>
            <td>VALIDATED</td>
            <td>READY</td>
            <td>PICKING</td>
            <td>DELIVERY</td>
            <td>PROCESS TIME</td>
            <td>TRANSIT TIME</td>
            <td>PENDING TIME</td>
            <td>RATIO %</td>
            <td> <button onclick="downloadData(3)" class="btn btn-primary"> <i class="fa-solid fa-download" style="color: #fff;font-size: 18px; font-weight: bolder;"></i> </button> </td>
        </tr>
        <tr>
            <td><input class="searchInput" style="width: 75px;text-align: center;"  type="text" placeholder="ID"                onKeyUp="searchFor('id', $(this))"></td>
            <td><input class="searchInput" style="width: 120px;text-align: center;" type="text" placeholder="SUPPLIER"          onKeyUp="searchFor('supplier', $(this))"></td>
            <td><input class="searchInput" style="width: 120px;text-align: center;" type="text" placeholder="CARRIER"           onKeyUp="searchFor('carrier', $(this))"></td>
            <td><input class="searchInput" style="width: 110px;text-align: center;" type="text" placeholder="VALIDATION DATE"   onKeyUp="searchFor('validation_date', $(this))"></td>
            <td><input class="searchInput" style="width: 110px;text-align: center;" type="text" placeholder="READY DATE"        onKeyUp="searchFor('ready_date', $(this))"></td>
            <td><input class="searchInput" style="width: 110px;text-align: center;" type="text" placeholder="PICKING DATE"      onKeyUp="searchFor('picking_date', $(this))"></td>
            <td><input class="searchInput" style="width: 110px;text-align: center;" type="text" placeholder="DELIVERY DATE"     onKeyUp="searchFor('delivery_date', $(this))"></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        @if(count( $shipments ) > 0)
            @foreach($shipments AS $shipment)
                <tr class="list_shipments_rows" onclick="$('.shipments_details').css('display', 'none'); $('#shipment_{{$shipment->id}}').css('display', 'contents')" 
                    search_id="{{$shipment->id}}" 
                    search_supplier="{{$shipment->supplier_info->name}}" 
                    search_carrier="{{$shipment->carrier_name}}"
                    search_validation_date="{{$shipment->validation_date}}"
                    search_ready_date="{{$shipment->ready_date}}"
                    search_picking_date="{{$shipment->picking_date}}"
                    search_delivery_date="{{$shipment->delivery_date}}"
                    >
                    <td>{{$shipment->id}}</td>
                    <td>{{$shipment->supplier_info->name}}</td>
                    <td>{{$shipment->carrier_name}}</td>
                    <td>{{$shipment->validation_date}}</td>
                    <td>{{$shipment->ready_date}}</td>
                    <td>{{$shipment->picking_date}}</td>
                    <td>{{$shipment->delivery_date}}</td>
                    <td>{{ Carbon\Carbon::parse($shipment->delivery_date)->diffInDays($shipment->validation_date) }}</td>
                    <td>{{ Carbon\Carbon::parse($shipment->delivery_date)->diffInDays($shipment->picking_date) }}</td>
                    <td>{{ Carbon\Carbon::parse($shipment->ready_date)->diffInDays($shipment->delivery_date) }}</td>
                    <td style="background-color: {{$shipment->color}}; color: #FFF;"> {{number_format($shipment->racio, 2, ',', ' ')}} </td>
                    <td> <a href="{{route('shipping.edit', ['id' => $shipment->id])}}"> <i class="fa-solid fa-pencil" style="color: orange;font-size: 18px; font-weight: bolder;"></i> </a> </td>
                </tr>
                @include("customTools.shipping.show",    ['shipments' => $shipments_received, 'suppliers' => $suppliers, 'carriers' => $carriers])
            @endforeach
        @else
            <tr>
                <td colspan="14"> NO DATA TO DISPLAY!</td>
            </tr>
        @endif
    </table>
</div>