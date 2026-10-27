<tr>
    <td class="table_cell td_orders_reference">
        @if($product->is_automatic)
            <span class="auto-order-automatic-icon" title="Added automatically from the replenishment calculation" aria-label="Added automatically">&#9889;</span>
        @endif
        @if($product->notes !='') <div style="background-color: red; width: 14px; height: 14px; border-radius: 50px;float: left;margin-right: 5px;display: flex;margin-top: 3px;" title="{{$product->notes}}"> <i class="fa-solid fa-question" style="color: #FFF; font-size: 9px;padding: 3px 4px;"></i> </div>@endif
        <span title="{{$product->name}}">
            {{$product->reference}}
        </span>
    </td>
    <td class="table_cell td_orders_others"><span title="{{$product->name}}">{{$product->stock}}</span></td>
    <td class="table_cell td_orders_others"><span title="{{$product->name}}">{{$product->arrive}}</span></td>
    <td class="table_cell td_orders_others"><span title="{{$product->name}}">{{$product->sold}}</span></td>
    <td class="table_cell td_orders_quantity @if($product->is_above_automatic_expected) auto-order-quantity-above-expected @endif"
        @if($product->is_above_automatic_expected) title="Current quantity is above the automatic calculation (expected: {{$product->automatic_expected_quantity}})" @endif>
        <input onblur="updateOrder($(this), {{$product->id_supplier}})" reference="{{$product->reference}}" style="width: 100px;" type="number" name="quantity['{{$product->supplier}}'][]" value="{{$product->quantity}}">
        @if($product->is_above_automatic_expected)
            <small class="auto-order-expected-note">Expected: {{$product->automatic_expected_quantity}}</small>
        @endif
    </td>
</tr>
<tr id="orderNewRow_{{$product->id_supplier}}">
    <td colspan="5"></td>
</tr>
