<tr>
    <td class="table_cell td_orders_reference">
        @if($product->notes !='') <div style="background-color: red; width: 14px; height: 14px; border-radius: 50px;float: left;margin-right: 5px;display: flex;margin-top: 3px;" title="{{$product->notes}}"> <i class="fa-solid fa-question" style="color: #FFF; font-size: 9px;padding: 3px 4px;"></i> </div>@endif
        <span title="{{$product->name}}">
            {{$product->reference}}
        </span>
    </td>
    <td class="table_cell td_orders_others"><span title="{{$product->name}}">{{$product->stock}}</span></td>
    <td class="table_cell td_orders_others"><span title="{{$product->name}}">{{$product->arrive}}</span></td>
    <td class="table_cell td_orders_others"><span title="{{$product->name}}">{{$product->sold}}</span></td>
    <td class="table_cell td_orders_quantity"><input onblur="updateOrder($(this))" reference="{{$product->reference}}" style="width: 100px;" type="number" name="quantity['{{$product->supplier}}'][]" value="{{$product->quantity}}"></td>
</tr>
<tr id="orderNewRow_{{$product->id_supplier}}">
    <td colspan="5"></td>
</tr>