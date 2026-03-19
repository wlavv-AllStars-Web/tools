<div id="housingInfo" style="font-size: 16px;">
    @if( isset($product->id_product) )
    
        <input type="hidden" value="{{$product->id_product}}" name="id_product" id="id_product">                     
        <input type="hidden" value="@if(isset($product->id_product_attribute)) {{$product->id_product_attribute}} @else 0 @endif" name="id_product_attribute" id="id_product_attribute"> 
        
        @if( ( strlen($product->housing) < 1 ) && ( strlen($product->location) < 1 ) )
            <div class="navbar navbar-light customPanel" style="width: 100%;">
                <div style="text-align: left">
                    <table style="width: 100%;">
                        <tr>
                            <td colspan="2"> 
                                <div style="text-align: center;display: grid;">
                                    <div style="text-align: center; text-transform: uppercase; ">NEW PRODUCT LOCATION?</div>
                                    <div style="margin-top: 20px;">
                                        <div style="float: left; width: 100px; text-align: right;padding: 5px 10px;">PRODUCT</div>
                                        <input value="{{$barcode}}" type="text" name="newBarcode" id="newBarcode" style="float: left; width: calc( 100% - 100px ); border-radius: 5px; border: 1px solid #ccc;">
                                    </div>
                                    <div style="margin: 10px 0;">
                                        <div style="float: left; width: 100px; text-align: right;padding: 5px 10px;">STAND</div>
                                        <input type="text" name="newStandBarcode" id="newStandBarcode" style="float: left; width: calc( 100% - 100px ); border-radius: 5px; border: 1px solid #ccc;">                
                                    </div>
                                    <div style="margin-top: 10px;">
                                        <button class="btn btn-primary" style="width: 100%;" onclick="saveNewLocation()">SAVE</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <script>
                document.getElementById("newStandBarcode").focus(); 
            </script>
        @endif
                        
        <div class="navbar navbar-light customPanel" style="width: 100%;">
            <div style="text-align: left">
                <table style="width: 100%;">
                    <tr>
                        <td style="text-align: right; padding-right: 5px;"><b>REFERENCE: </b></td>
                        <td style="padding-left: 5px;">{{$product->reference}}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right; padding-right: 5px;"><b>EAN-13: </b></td>
                        <td style="padding-left: 5px;">@if( strlen($product->ean13) > 0) {{$product->ean13}} @else <span style="color: red; font-weight: bolder;">NOT DEFINED YET!</span> @endif</td>
                    </tr>
                    @if( ( strlen($product->housing) > 0 ) || ( strlen($product->location)  > 0 ))
                    <tr>
                        <td style="text-align: right; padding-right: 5px;"><b>HOUSING: </b></td>
                        <td style="padding-left: 5px;"> 
                            @if( ( strlen($product->housing) > 0 ) )
                                {{$product->housing}}
                                @else
                                {{$product->location}}
                            @endif
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td style="text-align: right; padding-right: 5px;"><b>STOCK: </b></td>
                        <td style="padding-left: 5px;">{{$product->stock->quantity}}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right; padding-right: 5px;"><b>Weight: </b></td>
                        <td style="padding-left: 5px;">
                            @if(isset($product->product) == 1)
                                {{number_format($product->product->weight, 2)}} ( Kg )
                            @else
                                {{number_format($product->weight, 2)}} ( Kg )
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: right; padding-right: 5px;"><b>MEASURES: </b></td>
                        <td style="padding-left: 5px;">
                            @if(isset($product->product) == 1)
                                {{number_format($product->product->width, 2)}} * {{number_format($product->product->height, 2)}} * {{number_format($product->product->depth, 2)}} ( cm )
                            @else
                                {{number_format($product->width, 2)}} * {{number_format($product->height, 2)}} * {{number_format($product->depth, 2)}} ( cm )
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="navbar navbar-light customPanel" style="width: 100%;text-align: center;display: flex;">
            <button class="btn btn-dark" type="button" onclick="$('#editHousing').toggle(); $('#editMeasurements').css('display', 'none')" style="width: 48%;float: left;">EDIT HOUSING</button>
            <button class="btn btn-dark" type="button" onclick="$('#editMeasurements').toggle(); $('#editHousing').css('display', 'none')" style="width: 48%;float: right;">EDIT MEASURES</button>

            <div id="editHousing" style="display: none;padding: 20px 0;width: 100%;">
                <div style="float: left; width: 70px; text-align: right;padding: 7px 10px;">STAND</div>
                <input value="{{$product->housing}}" type="text" name="editedBarcode" id="editedBarcode" style="float: left; width: calc( 100% - 130px ); border-radius: 5px; border: 1px solid #ccc;height: 38px;">                
                <button class="btn btn-primary" type="button" onclick="editLocation()" style="width: 50px;margin-left: 10px;"><i class="fa-regular fa-floppy-disk" style="font-size: 20px;"></i></button>
            </div>
            <div id="editMeasurements" style="display: none;padding: 20px 0;width: 100%;">
                <div style="display: flex;">
                    <div style="float: left; width: 100px; text-align: right;padding: 7px 10px;">WEIGHT</div>
                    <input type="text" value="@if(isset($product->product)) {{number_format($product->product->weight, 2)}} @else {{number_format($product->weight, 2)}} @endif" name="editedWeight" id="editedWeight" style="float: left; width: calc( 100% - 110px ); border-radius: 5px; border: 1px solid #ccc;height: 38px;">                     
                </div>
                <div style="display: flex;">
                    <div style="float: left; width: 100px; text-align: right;padding: 7px 10px;">WIDTH</div>
                    <input type="text" value="@if(isset($product->product)) {{number_format($product->product->width, 2)}}  @else {{number_format($product->width, 2)}}  @endif" name="editedWidth" id="editedWidth" style="float: left; width: calc( 100% - 110px ); border-radius: 5px; border: 1px solid #ccc;height: 38px;">                     
                </div>
                <div style="display: flex;">
                    <div style="float: left; width: 100px; text-align: right;padding: 7px 10px;">HEIGHT</div>
                    <input type="text" value="@if(isset($product->product)) {{number_format($product->product->height, 2)}} @else {{number_format($product->height, 2)}} @endif" name="editedHeight" id="editedHeight" style="float: left; width: calc( 100% - 110px ); border-radius: 5px; border: 1px solid #ccc;height: 38px;">                     
                </div>
                <div style="display: flex;">
                    <div style="float: left; width: 100px; text-align: right;padding: 7px 10px;">DEPTH</div>
                    <input type="text" value="@if(isset($product->product)) {{number_format($product->product->depth, 2)}}  @else {{number_format($product->depth, 2)}}  @endif" name="editedDepth" id="editedDepth" style="float: left; width: calc( 100% - 110px ); border-radius: 5px; border: 1px solid #ccc;height: 38px;">                     
                </div>
                <button class="btn btn-primary" type="button" onclick="editMeasurements()" style="width: calc( 100% - 20px );margin-top: 20px;margin: 10px 10px 0 10px;">UPDATE MEASUREMENTS</button>
            </div>
        </div>
        
        <div class="navbar navbar-light customPanel" style="width: 100%;">
            <div>
                <table style="width: 100%;text-align: center;">
                    <tr>
                        <td colspan="5" style="text-align: center; padding-bottom: 20px;"><b>QUANTITY BY ORDER STATUS </b></td>
                    </tr>
                    <tr>
                        <td style="width: 100px;"></td>
                        <td style="width: calc( ( 100% - 100px ) / 2);">ASD</td>
                        <td style="width: calc( ( 100% - 100px ) / 2);">ASM</td>
                    </tr>
                    <tr>
                        <td style="color: #048dcd;text-align: right;">PROGRESS</td>
                        <td>{{$order_ASD['progress']}}</td>
                        <td>{{$order_ASM['progress']}}</td>
                    </tr>
                    <tr>
                        <td style="color: #956f55;text-align: right;">PARTIAL</td>
                        <td>{{$order_ASD['partial']}}</td>
                        <td>{{$order_ASM['partial']}}</td>
                    </tr>
                    <tr>
                        <td style="color: #f78e1f;text-align: right;">BACKORDERS</td>
                        <td>{{$order_ASD['backorder']}}</td>
                        <td>{{$order_ASM['backorder']}}</td>
                    </tr>
                    <tr>
                        <td style="color: #EBC5E0;text-align: right;">WARRANTY</td>
                        <td>{{$order_ASD['warranty']}}</td>
                        <td>{{$order_ASM['warranty']}}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="navbar navbar-light customPanel" style="width: 100%;text-align: center;">
            @if(isset($product->id_product_attribute))
            
                @if(isset($product->ean13))
                    <button class="btn btn-dark" type="button" onclick="generateListStandBarcode({{$product->id_product}},{{$product->id_product_attribute}})" style="width: 40%;margin-right: 10px;">SHELF PRINT</button>
                    <button class="btn btn-dark" type="button" onclick="generateListBarcode({{$product->id_product}},{{$product->id_product_attribute}})" style="width: 40%;margin-left: 10px;">EAN PRINT</button>
                @else
                    <div class="alert alert-danger" role="alert" style="text-align: center;margin: 0;"> NO EAN BARCODE SET YET!  </div>
                @endif
            @else
                @if(isset($product->ean13))
                    <button class="btn btn-dark" type="button" onclick="generateListStandBarcode({{$product->id_product}},0)" style="width: 40%;margin-right: 10px;">SHELF PRINT</button>
                    <button class="btn btn-dark" type="button" onclick="generateListBarcode({{$product->id_product}},0)" style="width: 40%;margin-left: 10px;">EAN PRINT</button>
                @else
                    <div class="alert alert-danger" role="alert" style="text-align: center;margin: 0;"> NO EAN BARCODE SET YET!  </div>
                @endif
            @endif
            
            <div id="barcode_container"></div>
        </div>
        
        <script>
    
            function generateListBarcode(id_product, id_product_attribute) {
                window.open('https://webtools.all-stars-motorsport.com/barcode/product/print/'+id_product+'/'+id_product_attribute+'/1', '_blank');
            }
            
            function generateListStandBarcode(id_product, id_product_attribute) {
                window.open('https://webtools.all-stars-motorsport.com/barcode/stand/print/'+id_product+'/'+id_product_attribute, '_blank');
            }
        
        </script>
    @else
        <div class="navbar navbar-light customPanel" style="width: 100%;"> <div class="alert alert-danger" role="alert" style="text-align: center;margin: 0;"> PRODUCT NOT FOUND!  </div> </div>
    @endif
</div>
