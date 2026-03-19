<div id="container_IN_TRANSIT" class="containers">
    <table class="table table-striped text-center" style="width: 100;">
        <tr>
            <td>ID</td>
            <td>SUPPLIER</td>
            <td>CARRIER</td>
            <td>PICKING TIME</td>
            <td>PICKING</td>
            <td>ETA</td>
            <td>TRACKING</td>
            <td>INCOTERM</td>
            <td>ROUTE</td>
            <td>RATIO %</td>
            <td>PACKING LIST</td>
            <td>CUSTOMS DOCS</td>
            <td> <button onclick="downloadData(2)" class="btn btn-primary"> <i class="fa-solid fa-download" style="color: #fff;font-size: 18px; font-weight: bolder;"></i> </button> </td>
        </tr>
        <tr>
            <td><input class="searchInput" style="width: 75px;text-align: center;"  type="text" placeholder="ID"            onKeyUp="searchFor('id', $(this))"></td>
            <td><input class="searchInput" style="width: 120px;text-align: center;" type="text" placeholder="SUPPLIER"      onKeyUp="searchFor('supplier', $(this))"></td>
            <td><input class="searchInput" style="width: 120px;text-align: center;" type="text" placeholder="CARRIER"       onKeyUp="searchFor('carrier', $(this))"></td>
            <td></td>
            <td><input class="searchInput" style="width: 110px;text-align: center;" type="text" placeholder="PICKING DATE"  onKeyUp="searchFor('picking_date', $(this))"></td>
            <td><input class="searchInput" style="width: 110px;text-align: center;" type="text" placeholder="ETA"           onKeyUp="searchFor('eta', $(this))"></td>
            <td><input class="searchInput" style="width: 120px;text-align: center;" type="text" placeholder="TRACKING"      onKeyUp="searchFor('tracking', $(this))"></td>
            <td><input class="searchInput" style="width: 85px;text-align: center;"  type="text" placeholder="INCOTERM"      onKeyUp="searchFor('incoterm', $(this))"></td>
            <td><input class="searchInput" style="width: 85px;text-align: center;"  type="text" placeholder="ROUTE"         onKeyUp="searchFor('route', $(this))"></td>
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
                    search_picking_date="{{$shipment->picking_date}}"
                    search_eta="
                        @if(count($shipment->delays) > 0)
                            @if(count($shipment->delays) == 1)
                            {{$shipment->delays[0]->date}}
                            @else
                                {{$shipment->delays[0]->date}}
                            @endif
                        @else
                            NO ETA
                        @endif
                    "
                    search_tracking="{{$shipment->tracking}}"
                    search_incoterm="{{$shipment->incoterm}}"
                    search_route="{{$shipment->route}}"
                >
                    <td>{{$shipment->id}}</td>
                    <td>{{$shipment->supplier_info->name}}</td>
                    <td>{{$shipment->carrier_name}}</td>
                    <td>{{ Carbon\Carbon::parse($shipment->ready_date)->diffInDays($shipment->picking_date) }}</td>
                    <td>{{$shipment->picking_date}}</td>
                    <td>
                        @if(count($shipment->delays) > 0)
                            @if(count($shipment->delays) == 1)
                            {{$shipment->delays[0]->date}}
                            @else
                                <span style="color: red; font-weight: bolder;">{{$shipment->delays[0]->date}}</span>
                            @endif
                        @else
                            <span style="color: dodgerblue;">NO ETA</span>
                        @endif
                    </td>
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
                            <span style="color: dodgerblue;font-weight: bolder;">N/D</span>
                        @endif
                    </td>
                    <td>
                        @if($shipment->customs_docs == 1)
                            <i class="fa-solid fa-check" style="color: green;font-size: 18px; font-weight: bolder;"></i>
                        @elseif($shipment->customs_docs == 0)
                            <i class="fa-regular fa-circle-xmark" style="color: red;font-size: 18px; font-weight: bolder;"></i>
                        @else
                            <span style="color: dodgerblue;font-weight: bolder;">N/D</span>
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