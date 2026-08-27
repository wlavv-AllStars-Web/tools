@extends('layouts.app')
@section('content')
    
    <style>
        .card-body{ padding: 0; }
        .table{ margin-bottom: 0; border-radius: 0 0 5px 5px; }
    </style>
    
    <div class="card" style="margin-top: 10px;">
        <div class="card-header" style="text-align: center;"> SEARCHING FOR: <span style="font-weight: bolder;color: dodgerblue;">{{$tag}}</span></div>
    </div>
    
    <div class="card" style="margin-top: 10px;">
        <div class="card-header" onclick="$('#tableERPOpenOrders').toggle();" >@if(count($erpOpenOrders) > 0) <span style="color: darkgreen;">( {{count($erpOpenOrders)}} )</span> @else <span style="color: red;">( 0 )</span> @endif ERP OPEN ORDERS</div>
        <div class="card-body" @if ( isset($erpOpenOrders) && ( count($erpOpenOrders) > 0) ) style="display: block;" @else style="display: none;" @endif>
            @if ( isset($erpOpenOrders) && ( count($erpOpenOrders) > 0) )
                <table id="tableERPOpenOrders" class="table table-striped" style="text-align: center;display:none;" >
                    <tr>
                        <td style="width: 10%">PO ID</td>
                        <td style="width: 30%">ORDER</td>
                        <td style="width: 10%">SUPPLIER</td>
                        <td style="width: 20%">SKU</td>
                        <td style="width: 10%">ORD.</td>
                        <td style="width: 10%">INV.</td>
                        <td style="width: 10%">REC.</td>
                    </tr>
                    @foreach( $erpOpenOrders AS $order)
                        @if($order->status_id != 7)
                            <tr onclick="window.open('{{ route('erp.oms.dashboard', ['center_tab' => 'open_receptions', 'document_type' => 'billed_order', 'document_id' => $order->po_id, 'supplier_id' => $order->supplier_id]) }}', '_blank')" style="cursor: pointer;">
                                <td>{{$order->po_id}}</td>
                                <td>{{$order->reference}}</td>
                                <td>{{$order->supplier_name}}</td>
                                <td>{{$order->sku}}</td>
                                <td>@if ($order->qty_ordered == $order->qty_wmfaturado && $order->qty_wmfaturado == $order->qty_received) <div class="alert alert-success" role="alert" style="padding: 0; margin: 0;"> @else <div> @endif {{$order->qty_ordered + 0}}    </div></td>
                                <td>@if ($order->qty_ordered == $order->qty_wmfaturado && $order->qty_wmfaturado == $order->qty_received) <div class="alert alert-success" role="alert" style="padding: 0; margin: 0;"> @else <div> @endif {{$order->qty_wmfaturado + 0}} </div></td>
                                <td>@if ($order->qty_ordered == $order->qty_wmfaturado && $order->qty_wmfaturado == $order->qty_received) <div class="alert alert-success" role="alert" style="padding: 0; margin: 0;"> @else <div> @endif {{$order->qty_received + 0}}   </div></td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            @else
                <p class="card-text" style="text-align: center;">NO ORDERS FOUND</p>
            @endif
        </div>
    </div>
    
    <div class="card" style="margin-top: 10px;">
        <div class="card-header" onclick="$('#tableERPOStockEntry').toggle();" >@if(count($receivedProducts) > 0) <span style="color: darkgreen;">( {{count($receivedProducts)}} )</span> @else <span style="color: red;">( 0 )</span> @endif ERP STOCK ENTRY</div>
        <div class="card-body" @if ( isset($receivedProducts) && ( count($receivedProducts) > 0) ) style="display: block;" @else style="display: none;" @endif>
            @if ( isset($receivedProducts) && ( count($receivedProducts) > 0) )
                <table id="tableERPOStockEntry" class="table table-striped" style="text-align: center;display:none;" >
                    <tr>
                        <td style="width: 10%">PO ID</td>
                        <td style="width: 15%">ID PRODUCT</td>
                        <td style="width: 15%">ID ATTRIBUTE</td>
                        <td style="width: 30%">SKU</td>
                        <td style="width: 10%">QUANTITY</td>
                        <td style="width: 20%">DATE</td>
                    </tr>
                    @foreach( $receivedProducts AS $receivedProduct)
                        <tr onclick="window.open('{{ route('erp.oms.dashboard', ['center_tab' => 'receptions_history', 'document_type' => 'billed_order', 'document_id' => $receivedProduct->po_id]) }}', '_blank')" style="cursor: pointer;">
                            <td>{{$receivedProduct->po_id}}</td>
                            <td>{{$receivedProduct->product_id}}</td>
                            <td>@if( $receivedProduct->product_attribute_id == 0 ) - @else {{$receivedProduct->product_attribute_id}} @endif</td>
                            <td>{{$receivedProduct->sku}}</td>
                            <td>{{$receivedProduct->qty}}</td>
                            <td>{{$receivedProduct->date_add}}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p class="card-text" style="text-align: center;">NO ORDERS FOUND</p>
            @endif
        </div>
    </div>
    
    
    <div class="card" style="margin-top: 10px;">
        <div class="card-header" onclick="$('#tableOrders').toggle();" >@if(count($orders) > 0) <span style="color: darkgreen;">( {{count($orders)}} )</span> @else <span style="color: red;">( 0 )</span> @endif ORDERS</div>
        <div class="card-body" @if ( isset($orders) && ( count($orders) > 0) ) style="display: block;" @else style="display: none;" @endif>
            @if ( isset($orders) && ( count($orders) > 0) )
                <table id="tableOrders" class="table table-striped" style="text-align: center;display:none;" >
                    <tr>
                        <td style="width: 25%">ID ORDER</td>
                        <td style="width: 25%">REFERENCE</td>
                        <td style="width: 25%">TRACKING</td>
                        <td style="width: 25%">STATE</td>
                    </tr>
                    @foreach( $orders AS $order)
                        <tr onclick="window.open('{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/admin77500/index.php?controller=AdminOrders&id_order={{$order->id_order}}&vieworder&token={{Config::get('token')->AdminOrders}}', '_blank')" style="cursor: pointer;">
                            <td>{{$order->id_order}}</td>
                            <td>{{$order->reference}}</td>
                            <td>{{$order->tracking_number}}</td>
                            <td><span style="color: #FFF; font-weight: bolder;background-color: {!!$order->color!!};padding: 5px 10px; text-transform: uppercase;border-radius: 5px;"> {{$order->name}} </span></td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p class="card-text" style="text-align: center;">NO ORDERS FOUND</p>
            @endif
        </div>
    </div>
    
    <div class="card" style="margin-top: 10px;">
        <div class="card-header" onclick="$('#tableTrackingOrders').toggle();">@if(count($tracking) > 0) <span style="color: darkgreen;">( {{count($tracking)}} )</span> @else <span style="color: red;">( 0 )</span> @endif ORDERS - TRACKING NUMBER</div>
        <div class="card-body" @if ( isset($tracking) && ( count($tracking) > 0) ) style="display: block;" @else style="display: none;" @endif>
            @if ( isset($tracking) && ( count($tracking) > 0) )
                <table class="table table-striped" style="text-align: center;display:none;" id="tableTrackingOrders">
                    <tr>
                        <td style="width: 25%">ID ORDER</td>
                        <td style="width: 25%">REFERENCE</td>
                        <td style="width: 25%">TRACKING</td>
                        <td style="width: 25%">STATE</td>
                    </tr>
                    @foreach( $tracking AS $tracking_row)
                        <tr onclick="window.open('{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/admin77500/index.php?controller=AdminOrders&id_order={{$tracking_row->id_order}}&vieworder&token={{Config::get('token')->AdminOrders}}', '_blank')" style="cursor: pointer;">
                            <td>{{$tracking_row->id_order}}</td>
                            <td>{{$tracking_row->reference}}</td>
                            <td>{{$tracking_row->tracking_number}}</td>
                            <td><span style="color: #FFF; font-weight: bolder;background-color: {!!$tracking_row->color!!};padding: 5px 10px; text-transform: uppercase;border-radius: 5px;"> {{$tracking_row->name}} </span></td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p class="card-text" style="text-align: center;">NO ORDERS FOUND</p>
            @endif
        </div>
    </div>

    <div class="card" style="margin-top: 10px;">
        <div class="card-header" onclick="$('#tableCatalogueDetails').toggle();">
            @if($catalogueRows->count() > 0) <span style="color: darkgreen;">( {{ $catalogueRows->count() }} )</span> @else <span style="color: red;">( 0 )</span> @endif PRODUCT DETAILS
        </div>
        <div class="card-body">
            @if($catalogueRows->isNotEmpty())
                <table id="tableCatalogueDetails" class="table table-striped" style="text-align: center; display:none;">
                    <tr><td>ID PRODUCT (ATTRIBUTE)</td><td>REFERENCE</td><td>EAN13</td><td>LOCATION</td><td>STOCK</td><td>STOCK ARRIVE</td><td>ACTIONS</td></tr>
                    @foreach($catalogueRows as $catalogueRow)
                        <tr>
                            <td>{{ $catalogueRow->id_product }}@if($catalogueRow->id_product_attribute) ({{ $catalogueRow->id_product_attribute }}) @endif</td>
                            <td>{{ $catalogueRow->reference }}</td><td>{{ $catalogueRow->ean13 }}</td><td>{{ $catalogueRow->location }}</td><td>{{ $catalogueRow->stock + 0 }}</td><td>{{ $catalogueRow->stock_arrive + 0 }}</td>
                            <td style="white-space: nowrap;">
                                <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ route('barcode.printProductBarcode', ['id_product' => $catalogueRow->id_product, 'id_product_attribute' => $catalogueRow->id_product_attribute, 'repeat' => 1]) }}"><i class="fa-solid fa-barcode"></i> EAN</a>
                                <a class="btn btn-sm btn-outline-secondary" target="_blank" href="{{ route('barcode.printProductStandString', ['id_product' => $catalogueRow->id_product, 'id_product_attribute' => $catalogueRow->id_product_attribute]) }}"><i class="fa-solid fa-warehouse"></i> Estante</a>
                            </td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p class="card-text" style="text-align: center;">NO PRODUCT DETAILS FOUND</p>
            @endif
        </div>
    </div>

    <div class="card" style="margin-top: 10px;">
        <div class="card-header" onclick="$('#tableProducts').toggle();">@if(count($products) > 0) <span style="color: darkgreen;">( {{count($products)}} )</span> @else <span style="color: red;">( 0 )</span> @endif PRODUCTS </div>
        <div class="card-body" @if ( isset($products) && ( count($products) > 0) ) style="display: block;" @else style="display: none;" @endif>
            @if ( isset($products) && ( count($products) > 0) )
                <table class="table table-striped" style="text-align: center;display:none;" id="tableProducts">
                    <tr>
                        <td style="width: 5%">ACTIVE</td>
                        <td style="width: 20%">ID PRODUCT</td>
                        <td style="width: 25%">REFERENCE</td>
                        <td style="width: 25%">EAN13</td>
                        <td style="width: 25%">HOUSING</td>
                    </tr>
                    @foreach( $products AS $product)
                        <tr onclick="window.open('{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/admin77500/index.php?controller=AdminProducts&id_product={{$product->id_product}}&updateproduct&token={{Config::get('token')->AdminProducts}}', '_blank')" style="cursor: pointer;">
                            <td>@if($product->active == 1) <i style="color: green" class="fa-solid fa-check"></i> @else <i style="color: red;" class="fa-solid fa-xmark"></i> @endif</td>
                            <td>{{$product->id_product}}</td>
                            <td>{{$product->reference}}</td>
                            <td>{{$product->ean13}}</td>
                            <td>{{$product->housing}}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p class="card-text" style="text-align: center;">NO PRODUCTS FOUND</p>
            @endif
        </div>
    </div>
    
    <div class="card" style="margin-top: 10px;">
        <div class="card-header" onclick="$('#tableAttributes').toggle();">@if(count($product_attributes) > 0) <span style="color: darkgreen;">( {{count($product_attributes)}} )</span> @else <span style="color: red;">( 0 )</span> @endif PRODUCT ATTRIBUTES </div>
        <div class="card-body" @if ( isset($product_attributes) && ( count($product_attributes) > 0) ) style="display: block;" @else style="display: none;" @endif>
            @if ( isset($product_attributes) && ( count($product_attributes) > 0) )
                <table id="tableAttributes" class="table table-striped" style="text-align: center;display:none;">
                    <tr>
                        <td style="width: 5%">ACTIVE</td>
                        <td style="width: 15%">ID PRODUCT</td>
                        <td style="width: 15%">ID ATTRIBUTE</td>
                        <td style="width: 25%">REFERENCE</td>
                        <td style="width: 20%">EAN13</td>
                        <td style="width: 20%">HOUSING</td>
                    </tr>
                    @foreach( $product_attributes AS $product_attribute)
                        <tr onclick="window.open('{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/admin77500/index.php?controller=AdminProducts&id_product={{$product_attribute->id_product}}&updateproduct&token={{Config::get('token')->AdminProducts}}', '_blank')" style="cursor: pointer;">
                            <td>@if($product_attribute->active == 1) <i style="color: green" class="fa-solid fa-check"></i> @else <i style="color: red;" class="fa-solid fa-xmark"></i> @endif</td>
                            <td>{{$product_attribute->id_product}}</td>
                            <td>{{$product_attribute->id_product_attribute}}</td>
                            <td>{{$product_attribute->ref}}</td>
                            <td>{{$product_attribute->ean13_attr}}</td>
                            <td>{{$product_attribute->location_attr}}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p class="card-text" style="text-align: center;">NO PRODUCT ATTRIBUTES FOUND</p>
            @endif
        </div>
    </div>

    <div class="card" style="margin-top: 10px;">
        <div class="card-header" onclick="$('#tableShipments').toggle();">@if(count($shipments) > 0) <span style="color: darkgreen;">( {{count($shipments)}} )</span> @else <span style="color: red;">( 0 )</span> @endif SHIPMENTS </div>
        <div class="card-body" @if ( isset($shipments) && ( count($shipments) > 0) ) style="display: block;" @else style="display: none;" @endif>
            @if ( isset($shipments) && ( count($shipments) > 0) )
                <table id="tableShipments" class="table table-striped" style="text-align: center;display:none;">
                    <tr>
                        <td style="width: 25%">STATUS</td>
                        <td style="width: 25%">INVOICE NUMBER</td>
                        <td style="width: 25%">TRACKING</td>
                        <td style="width: 25%">COMMENTS</td>
                    </tr>
                @foreach( $shipments AS $shipment)
                    <tr onclick="window.open('{{route('shipping.index')}}', '_blank')" style="cursor: pointer;">
                        <td>
                            
                            @if($shipment->status == 1)
                                <span style="color: #FFF; font-weight: bolder;background-color: dodgerblue;padding: 5px 10px; text-transform: uppercase;border-radius: 5px;"> WAITING </span>
                            @elseif($shipment->status == 2)
                                <span style="color: #FFF; font-weight: bolder;background-color: orange;padding: 5px 10px; text-transform: uppercase;border-radius: 5px;"> IN TRANSIT </span>
                            @elseif($shipment->status == 3)
                                <span style="color: #FFF; font-weight: bolder;background-color: darkgreen;padding: 5px 10px; text-transform: uppercase;border-radius: 5px;"> RECEIVED </span>
                            @else
                                <span style="color: #FFF; font-weight: bolder;background-color: red;padding: 5px 10px; text-transform: uppercase;border-radius: 5px;"> CANCELLED </span>
                            @endif
                            
                        </td>
                        <td>{{$shipment->invoice}}</td>
                        <td>{{$shipment->tracking}}</td>
                        <td>{{$shipment->comments}}</td>
                    </tr>
                @endforeach
                </table>
            @else
                <p class="card-text" style="text-align: center;">NO SHIPMENTS FOUND</p>
            @endif
        </div>
    </div>
    
    {{--
    <div class="card" style="margin-top: 10px;">
        <div class="card-header" onclick="$('#tableSuppliersIssues').toggle();">@if(count($supplier_issues) > 0) <span style="color: darkgreen;">( {{count($supplier_issues)}} )</span> @else <span style="color: red;">( 0 )</span> @endif SUPPLIER ISSUES </div>
        <div class="card-body" @if ( isset($supplier_issues) && ( count($supplier_issues) > 0) ) style="display: block;" @else style="display: none;" @endif>
            @if ( isset($supplier_issues) && ( count($supplier_issues) > 0) )
                <table id="tableSuppliersIssues" class="table table-striped" style="text-align: center;display:none;">
                    <tr>
                        <td style="width: 20%">STATUS</td>
                        <td style="width: 15%">REFERENCE</td>
                        <td style="width: 30%">DESCRIPTION</td>
                        <td style="width: 35%">INFO</td>
                    </tr>
                @foreach( $supplier_issues AS $supplier_issue)
                    <tr onclick="window.open('{{route('suppliersIssues.index')}}', '_blank')" style="cursor: pointer;">
                        <td>
                            @if($supplier_issue->status == 'CLOSED ISSUE')
                                <span style="color: #FFF; font-weight: bolder;background-color: red;padding: 5px 10px; text-transform: uppercase;border-radius: 5px;"> CLOSED </span>
                            @elseif($supplier_issue->status == 'IN PROGRESS')
                                <span style="color: #FFF; font-weight: bolder;background-color: dodgerblue;padding: 5px 10px; text-transform: uppercase;border-radius: 5px;"> IN PROGRESS </span>
                            @elseif($supplier_issue->status == 'APPROVED')
                                <span style="color: #FFF; font-weight: bolder;background-color: darkgreen;padding: 5px 10px; text-transform: uppercase;border-radius: 5px;"> APPROVED </span>
                            @else
                                <span style="color: #000; font-weight: bolder;background-color: pink;padding: 5px 10px; text-transform: uppercase;border-radius: 5px;"> FROM WARRANTY </span>
                            @endif
                        </td>
                        <td>{{$supplier_issue->reference}}</td>
                        <td>{{$supplier_issue->description}}</td>
                        <td>{{$supplier_issue->info}}</td>
                    </tr>
                @endforeach
                </table>
            @else
                <p class="card-text" style="text-align: center;">NO SUPPLIERS ISSUES FOUND</p>
            @endif
        </div>
    </div>

    <div class="card" style="margin-top: 10px;">
        <div class="card-header" onclick="$('#tableSuppliersDeliveryIssues').toggle();">@if(count($supplier_delivery_issues) > 0) <span style="color: darkgreen;">( {{count($supplier_delivery_issues)}} )</span> @else <span style="color: red;">( 0 )</span> @endif SUPPLIERS DELIVERY ISSUES </div>
        <div class="card-body" @if ( isset($supplier_delivery_issues) && ( count($supplier_delivery_issues) > 0) ) style="display: block;" @else style="display: none;" @endif>
            @if ( isset($supplier_delivery_issues) && ( count($supplier_delivery_issues) > 0) )
                <table id="tableSuppliersDeliveryIssues" class="table table-striped" style="text-align: center;display:none;">
                    <tr>
                        <td style="width: 15%">STATUS</td>
                        <td style="width: 15%">ORDER ID</td>
                        <td style="width: 20%">REFERENCE</td>
                        <td style="width: 25%">PRODUCT</td>
                        <td style="width: 35%">COMMENT</td>
                    </tr>
                @foreach( $supplier_delivery_issues AS $supplier_delivery_issue)
                    <tr onclick="window.open('{{route('suppliersIssues.index')}}', '_blank')" style="cursor: pointer;">
                        <td>
                            @if($supplier_delivery_issue->closed == 1)
                                <span style="color: #FFF; font-weight: bolder;background-color: darkgreen;padding: 5px 10px; text-transform: uppercase;border-radius: 5px;"> CLOSED </span>
                            @else
                                <span style="color: #FFF; font-weight: bolder;background-color: red;padding: 5px 10px; text-transform: uppercase;border-radius: 5px;"> OPEN </span>
                            @endif
                        </td>
                        <td>{{$supplier_delivery_issue->po_id}}</td>
                        <td>{{$supplier_delivery_issue->po_reference}}</td>
                        <td>{{$supplier_delivery_issue->reference}}</td>
                        <td>{{$supplier_delivery_issue->comment}}</td>
                    </tr>
                @endforeach
                </table>
            @else
                <p class="card-text" style="text-align: center;">NO SHIPMENTS FOUND</p>
            @endif
        </div>
    --}}
    </div>
    
    <div class="card" style="margin-top: 10px;">
        <div class="card-header" onclick="$('#tableSuppliersWarrantyIssues').toggle();">@if(count($supplier_warranty_issues) > 0) <span style="color: darkgreen;">( {{count($supplier_warranty_issues)}} )</span> @else <span style="color: red;">( 0 )</span> @endif SUPPLIER WARRANTY ISSUES </div>
        <div class="card-body" @if ( isset($supplier_warranty_issues) && ( count($supplier_warranty_issues) > 0) ) style="display: block;" @else style="display: none;" @endif>
            @if ( isset($supplier_warranty_issues) && ( count($supplier_warranty_issues) > 0) )
                <table id="tableSuppliersWarrantyIssues" class="table table-striped" style="text-align: center;display:none;">
                    <tr>
                        <td style="width: 15%">STATUS</td>
                        <td style="width: 10%">ID ORDER</td>
                        <td style="width: 20%">REFERENCE</td>
                        <td style="width: 25%">DESCRIPTION</td>
                        <td style="width: 30%">ACTION</td>
                    </tr>
                @foreach( $supplier_warranty_issues AS $supplier_warranty_issue)
                    <tr onclick="window.open('{{route('suppliersIssues.index')}}', '_blank')" style="cursor: pointer;">
                        <td>
                            @if($supplier_warranty_issue->closed == 1)
                                <span style="color: #FFF; font-weight: bolder;background-color: darkgreen;padding: 5px 10px; text-transform: uppercase;border-radius: 5px;"> CLOSED </span>
                            @else
                                <span style="color: #FFF; font-weight: bolder;background-color: red;padding: 5px 10px; text-transform: uppercase;border-radius: 5px;"> OPEN </span>
                            @endif
                        </td>
                        <td>{{$supplier_warranty_issue->id_order}}</td>
                        <td>{{$supplier_warranty_issue->reference}}</td>
                        <td>{{$supplier_warranty_issue->description}}</td>
                        <td>{{$supplier_warranty_issue->action}}</td>
                    </tr>
                @endforeach
                </table>
            @else
                <p class="card-text" style="text-align: center;">NO SUPPLIERS WARRANTY ISSUES FOUND</p>
            @endif
        </div>
    </div>


    <div class="card" style="margin-top: 10px;">
        <div class="card-header" onclick="$('#tableCarrierIssues').toggle();">@if(count($carrierIssues) > 0) <span style="color: darkgreen;">( {{count($carrierIssues)}} )</span> @else <span style="color: red;">( 0 )</span> @endif CARRIER ISSUES </div>
        <div class="card-body" @if ( isset($carrierIssues) && ( count($carrierIssues) > 0) ) style="display: block;" @else style="display: none;" @endif>
            @if ( isset($carrierIssues) && ( count($carrierIssues) > 0) )
                <table id="tableCarrierIssues" class="table table-striped" style="text-align: center;display:none;">
                    <tr>
                        <td style="width: 15%">STATUS</td>
                        <td style="width: 10%">ID ORDER</td>
                        <td style="width: 20%">TRACKING</td>
                        <td style="width: 20%">DESCRIPTION</td>
                        <td style="width: 20%">ISSUE</td>
                        <td style="width: 15%">CARRIER</td>
                    </tr>
                @foreach( $carrierIssues AS $carrierIssue)
                    <tr onclick="window.open('{{route('carrierIssues.index')}}', '_blank')" style="cursor: pointer;">
                        <td>
                            @if($carrierIssue->archived == 0)
                                <span style="color: #FFF; font-weight: bolder;background-color: darkgreen;padding: 5px 10px; text-transform: uppercase;border-radius: 5px;"> ACTIVE </span>
                            @elseif($carrierIssue->archived == 2)
                                <span style="color: #FFF; font-weight: bolder;background-color: orange;padding: 5px 10px; text-transform: uppercase;border-radius: 5px;"> RETENTITON </span>
                            @else
                                <span style="color: #FFF; font-weight: bolder;background-color: red;padding: 5px 10px; text-transform: uppercase;border-radius: 5px;"> ARCHIVED </span>
                            @endif
                        </td>
                        <td>{{$carrierIssue->id_order}}</td>
                        <td>{{$carrierIssue->tracking}}</td>
                        <td>{{$carrierIssue->description}}</td>
                        <td>{{$carrierIssue->issue}}</td>
                        <td>{{$carrierIssue->carrier}}</td>
                    </tr>
                @endforeach
                </table>
            @else
                <p class="card-text" style="text-align: center;">NO CARRIER ISSUES FOUND</p>
            @endif
        </div>
    </div>

    <div class="card" style="margin-top: 10px;">
        <div class="card-header" onclick="$('#tableDocuments').toggle();">@if(count($documents_manager) > 0) <span style="color: darkgreen;">( {{count($documents_manager)}} )</span> @else <span style="color: red;">( 0 )</span> @endif DOCUMENTS </div>
        <div class="card-body" @if ( isset($documents_manager) && ( count($documents_manager) > 0) ) style="display: block;" @else style="display: none;" @endif>
            @if ( isset($documents_manager) && ( count($documents_manager) > 0) )
                <table id="tableDocuments" class="table table-striped" style="text-align: center;display:none;">
                    <tr>
                        <td style="width: 20%">NAME</td>
                        <td style="width: 20%">DOC. NUMBER</td>
                        <td style="width: 10%">CATEGORY</td>
                        <td style="width: 20%">ELEMENT</td>
                        <td style="width: 30%">NOTES</td>
                    </tr>
                @foreach( $documents_manager AS $document)
                    <tr onclick="$('#documentHolder_{{$document->id_document}}').toggle()">
                        <td>{{$document->name}}</td>
                        <td>{{$document->document_number}}</td>
                        <td style="text-transform: uppercase;">{{$document->category}}</td>
                        <td>{{$document->element}}</td>
                        <td>{{$document->notes}}</td>
                    </tr>
                    <tr style="display: none;" id="documentHolder_{{$document->id_document}}">
                        <td colspan="6">
                            @if( ( strlen( $document->element) > 0 ) && ( $document->element != 'others' ))
                                <embed src="{{ config('allstars.services.webtools.base_url') }}/uploads/documents/{{$document->category}}/{{str_replace('.', '/', str_replace(' ', '', $document->element))}}/{{str_replace('/', '|', $document->document)}}?t={{rand()}}" width="750px" height="750px" type="application/pdf" style="margin: 0 auto;">
                            @elseif( ( $document->category == 'manifest' ) && ( strlen( $document->element) > 0 ) && ( $document->element == 'others' ))
                                <embed src="{{ config('allstars.services.webtools.base_url') }}/uploads/documents/manifest/manifest{{$document->document}}?t={{rand()}}" width="750px" height="750px" type="application/pdf" style="margin: 0 auto;">
                            @else
                                <embed src="{{ config('allstars.services.webtools.base_url') }}/uploads/documents/{{str_replace('.', '/', $document->category)}}/{{$document->document}}?t={{rand()}}" width="750px" height="750px" type="application/pdf" style="margin: 0 auto;">
                            @endif
                        </td>
                    </tr>
                @endforeach
                </table>
            @else
                <p class="card-text" style="text-align: center;">NO DOCUMENTS FOUND</p>
            @endif
        </div>
    </div>

@endsection
