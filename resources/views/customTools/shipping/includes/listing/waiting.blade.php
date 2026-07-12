<div id="container_WAITING" class="containers">
    <table class="table table-striped text-center" style="width: 100;">
        <tr>
            <td>ID</td>
            <td>SUPPLIER</td>
            <td>CARRIER</td>
            <td>READY</td>
            <td>PICKING</td>
            <td>PACKAGING</td>
            <td>FREIGHT</td>
            <td>TOTAL COST</td>
            <td>RATIO %</td>
            <td>PACKING LIST</td>
            <td>CUSTOMS DOCS</td>
            <td>ETA</td>
            <td> <button onclick="downloadData(1)" class="btn btn-primary"> <i class="fa-solid fa-download" style="color: #fff;font-size: 18px; font-weight: bolder;"></i> </button> </td>
        </tr>
        @if(count( $shipments ) > 0)
        @foreach($shipments AS $shipment)
            <tr onclick="$('.shipments_details').css('display', 'none'); $('#shipment_{{$shipment->id}}').css('display', 'contents')">
                <td>{{$shipment->id}}</td>
                <td>{{ optional($shipment->supplier_info)->name ?? ('#' . $shipment->supplier) }}</td>
                <td><span @if( $shipment->carrier_name == 'UNKNOWN') style="color: red;font-weight: bolder;" @endif>{{$shipment->carrier_name}}</span></td>
                <td>{{$shipment->ready_date}}</td>
                <td>{{$shipment->picking_date}}</td>
                <td>
                    <i class="fa-solid fa-weight-hanging" style="color: orange;font-size: 18px; font-weight: bolder;" onclick="openModalWithPackaging({{$shipment->id}})"></i>
                    <div style="display: none;" class="packaging_{{$shipment->id}}">
                        <table class="table table-striped text-center" style="width: 100%;text-transform: uppercase;">
                            <tr>
                                <td>QUANTITY</td>
                                <td>TYPE</td>
                                <td>MEASUREMENTS</td>                                
                            </tr>
                            @foreach($shipment->packaging AS $packaging)
                                <tr>
                                    <td>{{$packaging->quantity}}</td>
                                    <td>{{$packaging->type}}</td>
                                    <td>{{$packaging->width}} * {{$packaging->width}} * {{$packaging->height}} | {{$packaging->depth}} kg </td>                                
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </td>
                <td>{{number_format($shipment->freight, 2, ',', ' ')}} €</td>
                <td>{{number_format(($shipment->freight + $shipment->customs_clear + $shipment->import_duties), 2, ',', ' ')}} €</td>
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
                <td> <a href="{{route('shipping.edit', ['id' => $shipment->id])}}"> <i class="fa-solid fa-pencil" style="color: orange;font-size: 18px; font-weight: bolder;"></i> </a> </td>
            </tr>

                @include("customTools.shipping.show",    ['shipments' => $shipments_waiting, 'suppliers' => $suppliers, 'carriers' => $carriers])

        @endforeach
        @else
            <tr>
                <td colspan="14"> NO DATA TO DISPLAY!</td>
            </tr>
        @endif
    </table>
</div>

<style>
    
    table td, table td * {
    vertical-align: top;
}
</style>
