<table style="width: 100%; border: none;background-color: white;text-align: center;">
    <tr>
        <td class="table_cell" style="width: 130px; overflow: hidden">{{ __("messages.Supplier")}}</td>
        <td class="table_cell" style="border-left: none; width: 500px; overflow: hidden; text-align: left;">{{ __("messages.Product")}}</td>
        <td class="table_cell" style="width: 130px; overflow: hidden">{{ __("messages.Reference")}}</td>
        <td class="table_cell" style="width: 100px; overflow: hidden">{{ __("messages.Quantity")}}</td>
        <td class="table_cell" style="width: 80px;  overflow: hidden">{{ __("messages.Stock")}}</td>
        <td class="table_cell" style="width: 80px;  overflow: hidden">{{ __("messages.Arrive")}}</td>
        <td class="table_cell" style="width: 100px; overflow: hidden">{{ __("messages.Sold")}}<br>{{ __("messages.( 12 months )")}}</td>
        <td class="table_cell" style="width: 110px; overflow: hidden">{{ __("messages.Order")}}</td>
        <td class="table_cell" style="width: 110px; overflow: hidden"></td>
    </tr>
    @foreach($products AS $product)
        @if($product["end_of_life"] == 1) 
        <tr id="tr_setAsOrdered_{{$product['id']}}" style="height: 27px; background-color: #ffbbbb;">
        @else
        <tr id="tr_setAsOrdered_{{$product['id']}}">
        @endif
            <td class="table_cell" style="width: 130px;"><div style="overflow: hidden;height: 27px;"> {{$product['supplier']}}</div></td>
            <td class="table_cell" style="border-left: none; text-align: left;"> 
                <div style="overflow: hidden;height: 27px;"> 
                    @if($product['notes'] !='') <div style="background-color: red; width: 14px; height: 14px; border-radius: 50px;float: left;margin-right: 5px;display: flex;margin-top: 3px;" title="{{$product->notes}}"> <i class="fa-solid fa-question" style="color: #FFF; font-size: 9px;padding: 3px 4px;"></i> </div>@endif {{$product['name']}} 
                </div>
            </td>
            <td class="table_cell" style="width: 130px; overflow: hidden"> @if ($product['id_product_attribute'] == 0) {{$product['reference']}} @else {{$product['attr_reference']}} @endif </td>
            <td class="table_cell" style="width: 100px; overflow: hidden">{{$product['quantity']}}</td>
            <td class="table_cell" style="width: 80px; overflow: hidden">{{$product['qtd_in_stock']}}</td>
            <td class="table_cell" style="width: 80px; overflow: hidden">@if ($product['stock_arrivepa'] > 0) {{$product['stock_arrivepa']}} @else {{$product['stock_arrive']}} @endif</td>
            <td class="table_cell" style="width: 100px; overflow: hidden">{{$product['sold']}}</td>
            <td class="table_cell" style="width: 100px; overflow: hidden">
                @php
                    $should = $product['sold'] - $product['stock_arrive'] - $product['qtd_in_stock'];
                @endphp
                <input id="stock_to_order_{{$product['id_manufacturer']}}_{{ $loop->index }}" style="width: 100px; text-align: center;" type="number" autocomplete="off" name="quantity[]" value="@if($should < 0){{$product['quantity']}}@else{{$should}}@endif">
            </td>
            <td class="table_cell" style="width: 110px; overflow: hidden">
                @if( $product["not_to_order"] == 0 )
                    <button type="button" name="setAsOrdered" onclick="setAsOrdered({{$product['id']}}, '{{addslashes($product['manufacturer'])}}', {{$product['id_manufacturer']}}, {{ $loop->index }})" @if($product['end_of_life'] == 1) class="btn btn-warning" @else class="btn btn-success" @endif style="border-radius: 0; margin: 5px 2px;font-size: 14px;padding: 2px 5px;">{{ __("messages.ADD")}}</button>
                @else
                    <span style="background-color:red;color: #FFF; font-weight: bolder;padding: 5px;">{{ __("messages.NOT TO ORDER")}}</span>
                @endif
            </td>
        </tr>
    @endforeach
</table>
