<div onclick="openOrder({{$row->id_order}}, $(this))" class="orderHeader" style="border: 1px solid #999; padding: 5px;margin: 3px 0; border-radius: 5px;background-color: #FFF;">
    <span>#{{$row->id_order}} - {{$row->carrier}}</span>
    <span style="border-radius: 30px; border: 1px solid #999; float: right; min-width: 25px;text-align: center;padding: 0 3px;">{{count($row->order)}}</span>
</div>
<div class="order_container" id="order_{{$row->id_order}}" style="display: none; background-color: #eee; border: 1px solid #bbb;border-radius: 5px;padding: 10px 2px;">
    <table style="width: 100%;">
        @foreach($row->order AS $key => $product)

            @if( $key == 0)
                <tr id="tr_pickingContainer_{{$product->id_order}}" @if($product->barcode == '') style="display: none;" @endif>
                    <td colspan="3" style="text-align: center;border-bottom: 1px solid #ccc;"><b>PICKING CONTAINER: </b><span style="color: dodgerblue;" id="pickingContainer_{{$product->id_order}}"> {{$product->barcode}}</span></td>
                </tr>

                @php $orderNote = (string) optional($row->order_main)->note; @endphp
                @if( strlen($orderNote) > 0)
                    <tr>
                        <td colspan="3" style="text-align: center;border-bottom: 1px solid #ccc;"><b>ORDER NOTE: </b><span style="color: red;">{{$orderNote}}</span></td>
                    </tr>
                @endif
                
            @endif
            
            <tr class="product_{{$product->id_order}}_{{$product->product_barcode}} {{--product_{{$product->id_order}}_{{str_replace(' ', '',$product->reference)}}--}}  @if( strlen( $product->component_1) > 0) blurData @endif">
                <td style="width: 55px;">
                    <div style="text-align: center;border-right: 1px solid #bbb; padding-right: 5px;">
                        <span class="productScannedQuantity_{{$product->id_order}}_{{$product->product_barcode}} {{--productScannedQuantity_{{$product->id_order}}_{{$product->reference}}--}}">{{$product->quantity_picked}}</span> / <span style="color: dodgerblue;"> {{$product->quantity}}</span>
                    </div>
                </td>
                <td style="text-align: left;padding-left: 5px;">
                    @if( strlen($product->housing) < 6) <div title="Housing errado ou em falta" style="margin: 3px;width: 15px; height: 15px; border-radius: 15px;background-color: #f5c6cb; border: 1px solid #721c24;float: left;"> </div> @endif
                    @if( !empty($product->is_new) ) <div title="Enviar para marketing: menos de 5 fotos" style="margin: 3px;width: 15px; height: 15px; border-radius: 15px;background-color: #000; border: 1px solid #000;float: left;"> </div> @endif
                    {{$product->reference}}
                </td>
                <td style="width: 90px;">
                    <div style="border-left: 1px solid #bbb;text-align: right;float: right;padding-left: 5px;"> {{$product->housing}} </div>
                </td>
            </tr>  
            
            @if( ( strlen( $product->component_1) > 0 ) || ( strlen( $product->component_2) > 0 ) )
                <tr style="height: 30px;" class="optionsToConfirm_{{$product->id_order}}_{{$product->product_barcode}} {{--optionsToConfirm_{{$product->id_order}}_{{str_replace(' ', '',$product->reference)}}--}}">
                    <td colspan="3" style="background-color: #FFF;">
                        <div style="margin: 10px;">
                            <b style="color: red;">PLEASE CONFIRM:</b>
                        </div>
                    </td>
                </tr>             
            @endif
            
            @if( ( strlen( $product->component_1) > 0 ) || (strlen( $product->component_2)) )
                <tr style="height: 30px;" class="optionsToConfirm_{{$product->id_order}}_{{$product->product_barcode}} {{--optionsToConfirm_{{$product->id_order}}_{{str_replace(' ', '',$product->reference)}}--}}">
                    <td colspan="3" style="background-color: #FFF;">
                        <table style="width: calc( 100% - 140px ); margin: 0px 30px 10px 100px;">
                        @if( strlen( $product->component_1) > 0)
                            <tr style="height: 30px;font-weight: bolder;">
                                <td style="width: 50px;text-align: right;padding-right: 10px;">{{$product->quantity_component_1}} </td>
                                <td style="text-align: left;padding-left: 10px;border-left: 1px solid #ccc;">{{$product->component_1}}</td>
                            </tr>
                        @endif
                        @if( strlen( $product->component_2) > 0)
                            <tr style="height: 30px;font-weight: bolder;">
                                <td style="width: 50px;text-align: right;padding-right: 10px;">{{$product->quantity_component_2}} </td>
                                <td style="text-align: left;padding-left: 10px;border-left: 1px solid #ccc;">{{$product->component_2}}</td>
                            </tr>
                        @endif
                        </table>
                    </td>
                </tr> 
                <tr class="optionsToConfirm_{{$product->id_order}}_{{$product->product_barcode}} {{--optionsToConfirm_{{$product->id_order}}_{{str_replace(' ', '',$product->reference)}}--}}">
                    <td colspan="3" style="background-color: #FFF;">
                        <button class="btn btn-dark" style="margin: 10px;width: calc( 100% - 20px );" onclick="validateComponents('{{$product->id_order}}', '{{$product->product_barcode}}')"> CONFIRM </button>
                    </td>
                </tr>
            @endif
            
            <tr id="hidden_product_{{$product->id_order}}_{{$product->product_barcode}}" class="productSupportContainer">
                <td colspan="3">
                    <div class="navbar navbar-light customPanel" style="width: 100%;padding: 20px;">
                        <label>PICKING CONTAINER</label>
                        <input style="width: 100%;" readonly value="{{$product->barcode}}"              type="text" name="pickingBarcode_{{$product->id_order}}"   id="pickingBarcode_{{$product->id_order}}">
                        <br><br>
                        <label>BARCODE</label>
                        <input style="width: 100%;" readonly value="{{$product->product_barcode}}"      type="text" name="requestedBarcode_{{$product->id_order}}_{{$product->product_barcode}}"   class="requestedBarcode_{{$product->id_order}}_{{$product->product_barcode}} requestedBarcode_{{$product->id_order}}_{{str_replace(' ', '', $product->reference)}}">
                        <br><br>
                        <label>REFERENCE</label>
                        <input style="width: 100%;" readonly value="{{str_replace(' ', '', $product->reference)}}"  type="text" name="requestedReference_{{$product->id_order}}_{{$product->product_barcode}}" class="requestedReference_{{$product->id_order}}_{{$product->product_barcode}} requestedReference_{{$product->id_order}}_{{str_replace(' ', '', $product->reference)}}">
                        <br><br>
                        <label>SCANNED</label>
                        <input style="width: 100%;" readonly value="{{$product->quantity_picked}}"      type="text" name="scannedQuantity_{{$product->id_order}}_{{$product->product_barcode}}"    class="scannedQuantity_{{$product->id_order}}_{{$product->product_barcode}} scannedQuantity_{{$product->id_order}}_{{str_replace(' ', '', $product->reference)}}">
                        <br><br>
                        <label>REQUESTED</label>
                        <input style="width: 100%;" readonly value="{{$product->quantity}}"             type="text" name="requestedQuantity_{{$product->id_order}}_{{$product->product_barcode}}"  class="requestedQuantity_{{$product->id_order}}_{{$product->product_barcode}} requestedQuantity_{{$product->id_order}}_{{str_replace(' ', '', $product->reference)}}">                          
                        <br><br>
                        <label>ID PRODUCT</label>
                        <input style="width: 100%;" readonly value="{{$product->id_product}}"           type="text" name="requestedProduct_{{$product->id_order}}_{{$product->product_barcode}}"  class="requestedProduct_{{$product->id_order}}_{{$product->product_barcode}} requestedProduct_{{$product->id_order}}_{{str_replace(' ', '', $product->reference)}}">                          
                        <br><br>
                        <label>ID PRODUCT ATTRIBUTE</label>
                        <input style="width: 100%;" readonly value="{{$product->id_product_attribute}}" type="text" name="requestedAttribute_{{$product->id_order}}_{{$product->product_barcode}}"  class="requestedAttribute_{{$product->id_order}}_{{$product->product_barcode}} requestedAttribute_{{$product->id_order}}_{{str_replace(' ', '', $product->reference)}}">                          
                    </div>
                </td>    
            </tr>
        @endforeach
    </table>
</div>
