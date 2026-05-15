<tr id="shipment_{{$shipment->id}}" class="shipments_details" style="display: none;">
    <td colspan="13">
        <table style="width: 100%;">
            <tr> 
                <td> 
                    <table style="width: 100%;">
                        <tr>
                            <td colspan="2"> <h2>GENERAL INFORMATION</h2> </td>
                        </tr>
                        <tr>
                            <td class="left_column">SUPPLIER :</td>
                            <td class="right_column"> @foreach($suppliers AS $id_supplier => $supplier) @if($shipment->supplier == $id_supplier) {{$supplier}} @endif @endforeach </td>
                        </tr>
                        <tr>
                            <td class="left_column">READY DATE :</td>
                            <td class="right_column">{{$shipment->ready_date}}</td>
                        </tr>
                        @if(strlen($shipment->invoice_number) > 0)
                        <tr>
                            <td class="left_column">INVOICE NUMBER:</td>
                            <td class="right_column">{{$shipment->invoice_number}}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="left_column">INVOICE VALUE:</td>
                            <td class="right_column">{{number_format($shipment->invoice, 2, ',', ' ')}} €</td>
                        </tr>
                        <tr>
                            <td class="left_column">CARRIER :</td>
                            <td class="right_column"> 
                            
                            @if( $shipment->carrier != '')
                                @foreach($carriers AS $id_carrier => $carrier) 
                                    @if($shipment->carrier == $id_carrier) 
                                        {{$carrier}}
                                    @endif 
                                @endforeach 
                            @else
                                <span style="color: red; font-weight: bolder;">UNKNOWN</span>
                            @endif
                            </td>
                        </tr>
                        @if($shipment->shipping_quote > 0)
                        <tr>
                            <td class="left_column">SHIPPING QUOTE :</td>
                            <td class="right_column"> {{number_format($shipment->shipping_quote, 2, ',', ' ')}} €</td>
                        </tr>
                        @endif
                        @if(strlen($shipment->validation_date) > 0)
                        <tr>
                            <td class="left_column">VALIDATION DATE :</td>
                            <td class="right_column"> {{$shipment->validation_date}} </td>
                        </tr>
                        @endif
                        <tr>
                            <td class="left_column">PACKING LIST :</td>
                            <td class="right_column">
                                @if($shipment->packing_list == 0) <div style="color: red; font-weight: bolder;">NO</div>  @endiF
                                @if($shipment->packing_list == 1) <div style="color: darkgreen; font-weight: bolder;">YES</div> @endif
                                @if($shipment->packing_list == 2) <div style="color: dodgerblue; font-weight: bolder;">N/D</div> @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="left_column">CUSTOMS DOCS :</td>
                            <td class="right_column">
                                    @if($shipment->customs_docs == 0) <div style="color: red; font-weight: bolder;">NO</div> @endif
                                    @if($shipment->customs_docs == 1) <div style="color: darkgreen; font-weight: bolder;">YES</div> @endif
                                    @if($shipment->customs_docs == 2) <div style="color: dodgerblue; font-weight: bolder;">N/D</div> @endif
                                </select>
                            </td>
                        </tr>
                        @if(strlen($shipment->picking_date) > 0)
                        <tr>
                            <td class="left_column">PICKING DATE :</td>
                            <td class="right_column">{{$shipment->picking_date}}</td>
                        </tr>
                        @endif
                        @if(count($shipment->delays) > 0)
                        <tr>
                            <td class="left_column">ETA :</td>
                            <td class="right_column">
                                @foreach($shipment->delays AS $key => $delay)
                                    <div @if( ( $key == 0 ) && ( count($shipment->delays) > 1)) style="color: red; font-weight: bolder;" @else @endif>{{$delay->date}}</div>
                                @endforeach
                            </td>
                        </tr>
                        @endif
                        @if(strlen($shipment->delayed_eta) > 0)
                        <tr>
                            <td class="left_column">DELAYED ETA :</td>
                            <td class="right_column">{{$shipment->delayed_eta}}</td>
                        </tr>
                        @endif
                        
                        @if(isset($shipment->supplier_map))
                        <tr>
                            <td colspan="2"> <div style="width: 100%; height: 40px;"></div> </td>
                        </tr>
                        <tr>
                            <td colspan="2"> <h2>SUPPLIER INFO</h2> </td>
                        </tr>
                        <tr>
                            <td class="left_column">ADDRESS : </td>
                            <td class="right_column" onclick="copyToClipboard(this)"> {{$shipment->supplier_map->address}}</td>
                        </tr>
                        <tr>
                            <td class="left_column">COUNTRY : </td>
                            <td class="right_column" onclick="copyToClipboard(this)"> {{$shipment->supplier_map->country}}</td>
                        </tr>
                        <tr>
                            <td class="left_column">CONTACT : </td>
                            <td class="right_column" onclick="copyToClipboard(this)"> {{$shipment->supplier_map->contact}}</td>
                        </tr>
                        <tr>
                            <td class="left_column">PHONE : </td>
                            <td class="right_column" onclick="copyToClipboard(this)"> {{$shipment->supplier_map->phone}}</td>
                        </tr>
                        <tr>
                            <td class="left_column">EMAIL : </td>
                            <td class="right_column"><span style="color: #333;" onclick="copyToClipboard(this)">{!! str_replace(';', '</span> | <span onclick="copyToClipboard(this)">', $shipment->supplier_map->email) !!}</span></td>
                        </tr>
                        <tr>
                            <td class="left_column">CURRENCY : </td>
                            <td class="right_column"> {{$shipment->supplier_map->currency}}</td>
                        </tr>
                        <tr>
                            <td class="left_column">INCOTERM : </td>
                            <td class="right_column"> {{$shipment->supplier_map->incoterm}}</td>
                        </tr>
                        @endif
                    </table>
                </td>
                <td>
                    <table style="width: 100%;">
                        <tr>
                            <td colspan="2"> <h2>SHIPMENTS</h2> </td>
                        </tr> 
                        <tr>
                            <td class="left_column">INCOTERM : </td>
                            <td class="right_column">
                                @if($shipment->incoterm == "") NONE SELECTED @endif
                                @if($shipment->incoterm == "CPT") CPT @endif
                                @if($shipment->incoterm == "DAP") DAP @endif
                                @if($shipment->incoterm == "DDP") DDP @endif
                                @if($shipment->incoterm == "EXW") EXW @endif
                                @if($shipment->incoterm == "FCA") FCA @endif
                                @if($shipment->incoterm == "FOB") FOB @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="left_column">ROUTE : </td>
                            <td class="right_column">
                                @if($shipment->route == "")         NONE SELECTED @endif
                                @if($shipment->route == "LAND")     LAND @endiF
                                @if($shipment->route == "AIR" )     AIR @endif
                                @if($shipment->route == "SEA_LCL" ) SEA LCL @endif
                                @if($shipment->route == "SEA_FCL" ) SEA FCL @endif
                            </td>
                        </tr>
                        @if($shipment->freight > 0)
                        <tr>
                            <td class="left_column">FREIGHT : </td>
                            <td class="right_column">{{number_format($shipment->freight, 2, ',', ' ')}} €</td>
                        </tr>
                        @endif
                        @if($shipment->customs_clear > 0)
                        <tr>
                            <td class="left_column">CUSTOMS CLEAR : </td>
                            <td class="right_column">{{number_format($shipment->customs_clear, 2, ',', ' ')}} €</td>
                        </tr>
                        @endif
                        @if($shipment->import_duties > 0)
                        <tr>
                            <td class="left_column">IMPORT DUTIES : </td>
                            <td class="right_column">{{number_format($shipment->import_duties, 2, ',', ' ')}} €</td>
                        </tr>
                        @endif
                        @if($shipment->delivery_date != '0000-00-00')
                        <tr>
                            <td class="left_column">DELIVERY DATE : </td>
                            <td class="right_column">{{$shipment->delivery_date}}</td>
                        </tr>
                        @endif
                        @if(strlen($shipment->tracking) > 0)
                            <tr>
                                <td class="left_column">TRACKING :</td>
                                <td class="right_column">{{$shipment->tracking}}</td>
                            </tr>
                        @endif
                        @if(strlen($shipment->comments) > 0)
                        <tr>
                            <td class="left_column">COMMENTS : </td>
                            <td class="right_column"> {{$shipment->comments}}</td>
                        </tr>
                        @endif
                        
                    </table>
                </td>
                <td> 
                    <table style="width: 100%;">
                        <tr>
                            <td colspan="6" style="text-align: center;"> <h2>PACKAGES</h2> </td>
                        </tr>
                        <tr>
                            <td colspan="6"><div style="margin: 4px;height: 2px;width: 100%;"></div></td>
                        </tr>

                        @if( count($shipment->packaging) > 0)
                        <tr>
                            <td style="text-align: center;font-weight: bolder;">TYPE</td>
                            <td style="text-align: center;font-weight: bolder;">QUANTITY</td>
                            <td style="text-align: center;font-weight: bolder;">WIDTH<br><span style="font-weight: initial;">( cm )</span></td>
                            <td style="text-align: center;font-weight: bolder;">HEIGHT<br><span style="font-weight: initial;">( cm )</span></td>
                            <td style="text-align: center;font-weight: bolder;">DEPTH<br><span style="font-weight: initial;">( cm )</span></td>
                            <td style="text-align: center;font-weight: bolder;">WEIGHT<br><span style="font-weight: initial;">( kg )</span></td>
                        </tr>                        
                            @foreach($shipment->packaging AS $package)
                                <tr>
                                    <td style="text-align: center;">
                                        @if($package->type == '') NONE SELECTED @endif
                                        @if($package->type == 'box') Box @endif
                                        @if($package->type == 'pallet') Pallet @endif
                                        @if($package->type == 'container_20') Container - 20 @endif
                                        @if($package->type == 'container_40') Container - 40 @endif        
                                    </td>
                                    <td style="text-align: center;"> {{$package->quantity}}</td>
                                    <td style="text-align: center;"> {{$package->width}}</td>
                                    <td style="text-align: center;"> {{$package->height}}</td>
                                    <td style="text-align: center;"> {{$package->depth}}</td>
                                    <td style="text-align: center;"> {{$package->weight}}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr> <td colspan="6" style="text-align: center;">NO INFORMATION ABOUT PACKAGES</td> </tr>
                        @endif
                    </table>
                </td>
                <td>
                    <table style="width: 100%;">
                        <tr>
                            <td style="text-align: center;"> <h2>SHIPMENT ANALYSIS</h2> </td>
                            <td style="text-align: center;">
                                <i class="fa-regular fa-circle-xmark" onclick="$('.shipments_details').css('display', 'none')" style="float: right; font-size: 24px; color: red;padding: 10px;"></i>
                            </td>
                        </tr>
                        <tr>
                            <td class="left_column">STATUS :</td>
                            <td class="right_column">
                                @if($shipment->status == 1) <div style="color: dodgerblue; font-weight: bolder;">WAITING</div>  @endiF
                                @if($shipment->status == 2) <div style="color: orange; font-weight: bolder;">IN TRANSIT</div> @endif
                                @if($shipment->status == 3) <div style="color: darkgreen; font-weight: bolder;">RECEIVED</div> @endif
                                @if($shipment->status == 4) <div style="color: red; font-weight: bolder;">CANCELLED</div> @endif
                            </td>
                        </tr>
                        @if( Carbon\Carbon::parse($shipment->ready_date)->diffInDays($shipment->picking_date) > 0)
                        <tr> 
                            <td class="left_column"> PICKING TIME : </td>
                            <td class="right_column"> {{ Carbon\Carbon::parse($shipment->ready_date)->diffInDays($shipment->picking_date) }} days</td>
                        </tr>
                        @endif

                        @if( Carbon\Carbon::parse($shipment->delivery_date)->diffInDays($shipment->validation_date) > 0)
                        <tr> 
                            <td class="left_column"> PROCESS TIME : </td>
                            <td class="right_column"> {{ Carbon\Carbon::parse($shipment->delivery_date)->diffInDays($shipment->validation_date) }} days</td>
                        </tr>
                        @endif
                        @if( Carbon\Carbon::parse($shipment->delivery_date)->diffInDays($shipment->picking_date) > 0)
                        <tr> 
                            <td class="left_column"> TRANSIT TIME : </td>
                            <td class="right_column"> {{ Carbon\Carbon::parse($shipment->delivery_date)->diffInDays($shipment->picking_date) }} days</td>
                        </tr>
                        @endif
                        @if( Carbon\Carbon::parse($shipment->ready_date)->diffInDays($shipment->delivery_date) > 0)
                        <tr> 
                            <td class="left_column"> PENDING TIME : </td>
                            <td class="right_column"> {{ Carbon\Carbon::parse($shipment->ready_date)->diffInDays($shipment->delivery_date) }} days</td>
                        </tr>
                        @endif
                        <tr> 
                            <td class="left_column"> CUSTOMS RACIO : </td>
                            <td class="right_column"> @if($shipment->invoice > 0) {{ number_format( ( ($shipment->import_duties / $shipment->invoice) * 100), 2, ',', ' ') }} % @else - @endif</td>
                        </tr>
                        <tr> 
                            <td class="left_column"> SHIPMENT COST : </td>
                            <td class="right_column"> {{number_format(($shipment->freight + $shipment->handling + $shipment->customs_clear + $shipment->import_duties + $shipment->extras + $shipment->to_door), 2, ',', ' ')}} € </td>
                        </tr>
                        <tr> 
                            <td class="left_column"> SHIPMENT RACIO : </td>
                            <td class="right_column"> {{number_format( $shipment->racio, 2, ',', ' ')}} % </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </td>
    
</tr>