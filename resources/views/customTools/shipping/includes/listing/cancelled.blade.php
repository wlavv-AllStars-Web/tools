<div id="container_CANCELLED" class="containers">
    <table class="table table-striped text-center" style="width: 100;">
        <tr>
            <td>ID</td>
            <td>SUPPLIER</td>
            <td>CARRIER</td>
            <td>PICKING TIME</td>
            <td>PICKING</td>
            <td>TRACKING</td>
            <td>INCOTERM</td>
            <td>ROUTE</td>
            <td>RATIO %</td>
            <td>PACKING LIST</td>
            <td>CUSTOMS DOCS</td>
            <td> <button onclick="downloadData(4)" class="btn btn-primary"> <i class="fa-solid fa-download" style="color: #fff;font-size: 18px; font-weight: bolder;"></i> </button> </td>
        </tr>
        @if(count( $shipments ) > 0)
        @foreach($shipments AS $shipment)
            <tr onclick="$('.shipments_details').css('display', 'none'); $('#shipment_{{$shipment->id}}').css('display', 'contents')">
                <td>{{$shipment->id}}</td>
                <td>{{$shipment->supplier_info->name}}</td>
                <td>{{$shipment->carrier_name}}</td>
                <td>{{ Carbon\Carbon::parse($shipment->ready_date)->diffInDays($shipment->picking_date) }}</td>
                <td>{{$shipment->picking_date}}</td>
                <td>{{$shipment->tracking}}</td>
                <td class="incoterm_{{$shipment->incoterm}}">{{$shipment->incoterm}}</td>
                <td class="route_{{$shipment->route}}">{{str_replace('_', ' ', $shipment->route)}}</td>
                <td style="background-color: {{$shipment->color}}; color: #FFF;">
                    {{number_format($shipment->racio, 2, ',', ' ')}}
                </td>
                <td>
                    @if($shipment->packing_list == 1)
                        <i class="fa-solid fa-check" style="color: green;font-size: 18px; font-weight: bolder;"></i>
                    @elseif($shipment->packing_list == 0)
                        <i class="fa-regular fa-circle-xmark" style="color: red;font-size: 18px; font-weight: bolder;"></i>
                    @else
                        <i class="fa-solid fa-question" style="color: dodgerblue;font-size: 18px; font-weight: bolder;"></i>
                    @endif
                </td>
                <td>
                    @if($shipment->customs_docs == 1)
                        <i class="fa-solid fa-check" style="color: green;font-size: 18px; font-weight: bolder;"></i>
                    @elseif($shipment->customs_docs == 0)
                        <i class="fa-regular fa-circle-xmark" style="color: red;font-size: 18px; font-weight: bolder;"></i>
                    @else
                        <i class="fa-solid fa-question" style="color: dodgerblue;font-size: 18px; font-weight: bolder;"></i>
                    @endif
                </td>
                <td> <a href="{{route('shipping.edit', ['id' => $shipment->id])}}"> <i class="fa-solid fa-pencil" style="color: orange;font-size: 18px; font-weight: bolder;"></i> </a> </td>
            </tr>
            @include("customTools.shipping.show",    ['shipments' => $shipments_in_transit, 'suppliers' => $suppliers, 'carriers' => $carriers])
        @endforeach
        @else
            <tr>
                <td colspan="14"> NO DATA TO DISPLAY!</td>
            </tr>
        @endif
    </table>
</div>