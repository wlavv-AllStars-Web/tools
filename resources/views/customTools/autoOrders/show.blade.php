@extends('layouts.app')
@section('content')

@include("customTools.autoOrders.includes.css")
@include("customTools.autoOrders.includes.js")

<div class="navbar navbar-light customPanel" style="width: calc( 100% - 25px ); margin: 10px 10px 0px 10px;">
    <div onclick="$('#toogleCreateNewOrder').toggle();">NEW ORDER FROM SCRATCH ?</div>
    <div id="toogleCreateNewOrder" style="display: none;">
        <div id="suppliersList" style="width: 300px;float: left;">
            <select id="selectSuppliersList" onchange="loadProductsOfBrand()">
                @foreach($suppliers_list AS $supplier_detail)
                <option value="{{$supplier_detail->id_supplier}}">{{$supplier_detail->name}}</option>
                @endforeach
            </select>
        </div>
        <div id="productListOfSuppliers" style="width: 300px;float: left;display: none;">
            <select id="selectProductListOfSuppliers" name="selectProductListOfSuppliers" onchange="loadAttributesOfBrand()" style="width: calc(100% - 20px );"> </select>
        </div>
        <div id="attributeListOfSuppliers" style="width: 300px;float: left;display: none;">
            <select id="selectAttributeListOfSuppliers" name="selectAttributeListOfSuppliers" style="width: calc(100% - 20px );"> </select>
        </div>
        <div id="saveNewOrderOfSupplier" style="width: 300px;float: left;display: none;">
            <button class="btn btn-success" onclick="createNewOrder()" style="width: 100px;margin: 0;padding: 1px 0;">ADD</button>
        </div>
    </div>
</div>

<script>

    function loadProductsOfBrand(){
        
        $.ajax({
            url: "{{ route('autoOrders.loadProducts') }}",
            type: "POST",
            data: { id_supplier: $('#selectSuppliersList').val() } ,    
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (data) {
                $('#selectProductListOfSuppliers').replaceWith(data);
                $('#productListOfSuppliers').css('display', 'block');
            }, 
            error: function (error) {
                console.log(`Error ${error}`);
            }
        });
    } 

    function loadAttributesOfBrand(){
        
        $.ajax({
            url: "{{ route('autoOrders.loadAttributes') }}",
            type: "POST",
            data: { id_product: $('#selectProductListOfSuppliers').val() } ,    
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (data) {
                $('#selectAttributeListOfSuppliers').replaceWith(data);
                $('#attributeListOfSuppliers').css('display', 'block');
                $('#saveNewOrderOfSupplier').css('display', 'block');
            }, 
            error: function (error) {
                console.log(`Error ${error}`);
            }
        });
    }   

    function createNewOrder(){
        
        let id_supplier = $('#selectSuppliersList').val();
        let id_product = $('#selectProductListOfSuppliers').val();
        let id_attribute = $('#selectAttributeListOfSuppliers').val();
        
        $.ajax({
            url: "{{ route('autoOrders.saveNewOrderFromScratch') }}",
            type: "POST",
            data: { 
                id_supplier: id_supplier,
                id_product: id_product,
                id_attribute: id_attribute
            } ,    
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (data) {
                location.reload();
            }, 
            error: function (error) {
                console.log(`Error ${error}`);
            }
        });
    }   
    
</script>

@if(count($suppliers) > 0)
    
    <div class="navbar navbar-light customPanel" style="margin: 10px; width: calc( 100% - 25px ); cursor: pointer;font-weight: bolder;text-align: center;background-color: #54aaff">CEDRIC</div>

    <div style="">
        @foreach( $suppliers AS $key => $supplier)
            @if( !in_array($supplier['supplier'], ['Acexxon', 'Athena Industries', 'Eventuri', 'Forge Motorsport', 'Goodridge', 'HKS', 'Induction Technology Group Ltd', 'NGK', 'Pipercross', 'Ultra Racing', 'TEIN', 'Eibach', 'Filtration Control LTD', 'OZ Parts', 'Drakos', 'Motul', 'Armytrix', 'Drakos Engineering', 'Zeder Corp' ]) )
                <div class="navbar navbar-light customPanel" @if( !isset($supplier['only_counters']) ) id="panel_{{$supplier['id_supplier']}}" @else onclick="location.href='{{ route('autoOrders.show', $supplier['id_supplier']) }}'" @endif style="margin: 10px; width: 32%; cursor: pointer;">
                    <div class="card" style="display: block; width: 100%;">
                        <div class="card-body" style="padding: 0px;">
                            <h5 class="card-title" onclick="$('#toggle_{{$loop->iteration}}').toggle()">
                                <div class="item_counter" id="item_counter_{{$supplier['id_supplier']}}">
                                    @if( isset($supplier['only_counters']) ) {{$supplier['rows']}} @else {{count($supplier['rows'])}} @endif
                                </div>
                                <div class="item_brand_name">{{$supplier['supplier']}}</div>
                                <div class="item_order_items"><button type="button" name="createOrder" class="btn btn-dark" id="createOrder" onclick="saveOrder({{$supplier['id_supplier']}})">{{ __("messages.ORDER")}} </button></div>
                            </h5>
                            <div>
    
                                <input type="hidden" id="id_supplier" value="{{$supplier['id_supplier']}}">
                                <input type="hidden" id="supplier" value="{{$supplier['supplier']}}">
                                @if( !isset($supplier['only_counters']) )
                                    <table class="table_order_items" id="toggle_{{$loop->iteration}}" style="display:block">
                                        <tr>
                                            <td class="table_cell td_orders_reference">{{ __("messages.Reference")}}</td>
                                            <td class="table_cell td_orders_others">{{ __("messages.Stock")}}</td>
                                            <td class="table_cell td_orders_others">{{ __("messages.Arrive")}}</td>
                                            <td class="table_cell td_orders_others">{{ __("messages.Sold")}}</td>
                                            <td class="table_cell td_orders_quantity">{{ __("messages.Quantity")}}</td>
                                        </tr>
                                        @foreach($supplier['rows'] AS $product)
                                            <tr>
                                                <td class="table_cell td_orders_reference">
                                                    <span @if($product['warranty'] == 1) style="color: coral;font-weight: bold;" @endif title="{{$product['name']}}">
                                                        {{$product['reference']}} 
                                                        @if($product['notes'] !='') <div style="background-color: red; width: 14px; height: 14px; border-radius: 50px;float: right;margin-left: 5px;display: flex;margin-top: 3px;" title="{{$product['notes']}}"> <i class="fa-solid fa-question" style="color: #FFF; font-size: 9px;padding: 3px 4px;"></i> </div> @endif
                                                        @if($product['warranty'] == 1) <br>( WARRANTY ) @endif
                                                    </span>
                                                    </td>
                                                <td class="table_cell td_orders_others">   <span>{{$product['stock']+0}}</span></td>
                                                <td class="table_cell td_orders_others">   <span>{{$product['arrive']+0}}</span></td>
                                                <td class="table_cell td_orders_others">   <span>{{$product['sold']+0}}</span></td>
                                                <td class="table_cell td_orders_quantity"><input onblur="updateOrder($(this), {{$supplier['id_supplier']}})" reference="{{$product['reference']}}" style="width: 100px;" type="number" autocomplete="off" name="quantity['{{$key}}'][]" value="{{$product['quantity']}}"></td>
                                            </tr>
                                        @endforeach
                                        <tr id="orderNewRow_{{$supplier['id_supplier']}}">
                                            <td colspan="5"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="5"><div class="spacer-20"></div></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="table_cell td_orders_reference"><input type="text" class="search_reference" style="text-align: right;width: 100%;" name="quantity['new_reference'][]" onkeyup="getProductInfo($(this), {{$supplier['id_supplier']}})" value=""></td>
                                            <td class="table_cell td_orders_quantity" ><input type="number" id="newQuantity_{{$supplier['id_supplier']}}" name="quantity['new_quantity'][]" style="width: 100px" value=""></td>
                                        </tr>
                                        <tr>
                                            <td colspan="5"><div class="ajax_search_response" id="ajax_search_response_{{$supplier['id_supplier']}}"></div></td>
                                        </tr>
                                    </table>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="navbar navbar-light customPanel" style="margin: 10px; width: calc( 100% - 25px ); cursor: pointer;font-weight: bolder;text-align: center;background-color: #ff5464">PIERRE</div>

    <div style="">
        @foreach( $suppliers AS $key => $supplier)
            @if( in_array($supplier['supplier'], ['Acexxon', 'Athena Industries', 'Eventuri', 'Forge Motorsport', 'Goodridge', 'HKS', 'Induction Technology Group Ltd', 'NGK', 'Pipercross', 'Ultra Racing', 'TEIN', 'Eibach', 'Filtration Control LTD', 'OZ Parts', 'Drakos', 'Motul', 'Armytrix', 'Drakos Engineering', 'Zeder Corp' ]) )
                <div class="navbar navbar-light customPanel" @if( !isset($supplier['only_counters']) ) id="panel_{{$supplier['id_supplier']}}" @else onclick="location.href='{{ route('autoOrders.show', $supplier['id_supplier']) }}'" @endif style="margin: 10px; width: 32%; cursor: pointer;">
                    <div class="card" style="display: block; width: 100%;">
                        <div class="card-body" style="padding: 0px;">
                            <h5 class="card-title" onclick="$('#toggle_{{$loop->iteration}}').toggle()">
                                <div class="item_counter" id="item_counter_{{$supplier['id_supplier']}}">
                                    @if( isset($supplier['only_counters']) ) {{$supplier['rows']}} @else {{count($supplier['rows'])}} @endif
                                </div>
                                <div class="item_brand_name">{{$supplier['supplier']}}</div>
                                <div class="item_order_items"><button type="button" name="createOrder" class="btn btn-dark" id="createOrder" onclick="saveOrder({{$supplier['id_supplier']}})">{{ __("messages.ORDER")}} </button></div>
                            </h5>
                            <div>
                                <input type="hidden" id="id_supplier" value="{{$supplier['id_supplier']}}">
                                <input type="hidden" id="supplier" value="{{$supplier['supplier']}}">
                                @if( !isset($supplier['only_counters']) )
                                    <table class="table_order_items" id="toggle_{{$loop->iteration}}" style="display:block">
                                        <tr>
                                            <td class="table_cell td_orders_reference">{{ __("messages.Reference")}}</td>
                                            <td class="table_cell td_orders_others">{{ __("messages.Stock")}}</td>
                                            <td class="table_cell td_orders_others">{{ __("messages.Arrive")}}</td>
                                            <td class="table_cell td_orders_others">{{ __("messages.Sold")}}</td>
                                            <td class="table_cell td_orders_quantity">{{ __("messages.Quantity")}}</td>
                                        </tr>
                                        @foreach($supplier['rows'] AS $product)
                                            <tr>
                                                <td class="table_cell td_orders_reference">
                                                    <span @if($product['warranty'] == 1) style="color: coral;font-weight: bold;" @endif title="{{$product['name']}}">
                                                        {{$product['reference']}} 
                                                        @if($product['notes'] !='') <div style="background-color: red; width: 14px; height: 14px; border-radius: 50px;float: right;margin-left: 5px;display: flex;margin-top: 3px;" title="{{$product['notes']}}"> <i class="fa-solid fa-question" style="color: #FFF; font-size: 9px;padding: 3px 4px;"></i> </div> @endif
                                                        @if($product['warranty'] == 1) <br>( WARRANTY ) @endif
                                                    </span>
                                                    </td>
                                                <td class="table_cell td_orders_others">   <span>{{$product['stock']+0}}</span></td>
                                                <td class="table_cell td_orders_others">   <span>{{$product['arrive']+0}}</span></td>
                                                <td class="table_cell td_orders_others">   <span>{{$product['sold']+0}}</span></td>
                                                <td class="table_cell td_orders_quantity"><input onblur="updateOrder($(this), {{$supplier['id_supplier']}})" reference="{{$product['reference']}}" style="width: 100px;" type="number" autocomplete="off" name="quantity['{{$key}}'][]" value="{{$product['quantity']}}"></td>
                                            </tr>
                                        @endforeach
                                        <tr id="orderNewRow_{{$supplier['id_supplier']}}">
                                            <td colspan="5"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="5"><div class="spacer-20"></div></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="table_cell td_orders_reference"><input type="text" class="search_reference" style="text-align: right;width: 100%;" name="quantity['new_reference'][]" onkeyup="getProductInfo($(this), {{$supplier['id_supplier']}})" value=""></td>
                                            <td class="table_cell td_orders_quantity" ><input type="number" id="newQuantity_{{$supplier['id_supplier']}}" name="quantity['new_quantity'][]" style="width: 100px" value=""></td>
                                        </tr>
                                        <tr>
                                            <td colspan="5"><div class="ajax_search_response" id="ajax_search_response_{{$supplier['id_supplier']}}"></div></td>
                                        </tr>
                                    </table>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@else
<div class="alert alert-warning" style="margin: 20px;">
    No products to order!
</div>
@endif
@endsection