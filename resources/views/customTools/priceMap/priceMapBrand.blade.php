<tr class="brand_price_map">
    <td colspan="8" style="background-color: red;color:white;border: 1px solid red;">ALL STARS MOTORSPORT</td>
    <td style="background-color: #999;border: 1px solid #999;"></td>
    <td colspan="8" style="background-color: dodgerblue;color:white;border: 1px solid dodgerblue;">ALL STARS DISTRIBUTION</td>
</tr>
<tr class="brand_price_map">
    <td>ACTIVE</td>
    <td>A.V.S.</td>
    <td>MAIN REF</td>
    <td>VARIATIONS REF</td>
    <td>RRP ExVat</td>
    <td>DISCOUNT</td>
    <td>PURCHASE</td>
    <td>COEF MARGIN</td>
    
    <td style="background-color: #999;border: 1px solid #999;"></td>
    
    <td>ACTIVE</td>
    <td>A.V.S.</td>
    <td>MAIN REF</td>
    <td>VARIATIONS REF</td>
    <td>RRP ExVat</td>
    <td>DISCOUNT</td>
    <td>PURCHASE</td>
    <td>COEF MARGIN</td>
</tr>
@foreach($products AS $product)
    <tr class="brand_price_map">
        <td>@if($product['active'] == 1)            <i class="fa-solid fa-check" style="color: darkgreen;"></i> @elseif($product['active'] == 2)        <i class="fa-solid fa-minus" style="color: dodgerblue;"></i> @else <i class="fa-regular fa-circle-xmark" style="color: red;"></i> @endif</td>
        <td>@if($product['deprecated'] == 1)        <i class="fa-solid fa-check" style="color: darkgreen;"></i> @elseif($product['deprecated'] == 2)    <i class="fa-solid fa-minus" style="color: dodgerblue;"></i> @else <i class="fa-regular fa-circle-xmark" style="color: red;"></i> @endif</td>
        <td>{{$product['reference']}}</td>
        <td>{{$product['attr_reference']}}</td>
        <td>@if($product['price'] == 0)<span style="color:red;">@else<span>@endif{{$product['price']}}</span></td>
        <td>{{$product['discount']}}</td>
        <td>
            @if( ( $product['wholesale_price'] !='' ) && ( $product['asd_wholesale_price'] !='' ) )
                @if( $product['wholesale_price'] != $product['asd_wholesale_price'] ) <span style="color: red;"> {{$product['wholesale_price']}} </span> @else {{$product['wholesale_price']}} @endif
            @else
                {{$product['wholesale_price']}}
            @endif
        </td>
        <td>
            @if( $product['racio'] !='' ) 
                @if( $product['racio'] < 5.01 ) <span style="color: red;"> {{$product['racio']}} %</span> @else {{$product['racio']}} % @endif
            @endif
        </td>
        
        <td style="background-color: #999;border: 1px solid #999;"></td>

        <td>@if($product['asd_active'] == 1)        <i class="fa-solid fa-check" style="color: darkgreen;"></i> @elseif($product['asd_active'] == 2)    <i class="fa-solid fa-minus" style="color: dodgerblue;"></i> @else <i class="fa-regular fa-circle-xmark" style="color: red;"></i> @endif</td>
        <td>@if($product['asd_deprecated'] == 1)    <i class="fa-solid fa-check" style="color: darkgreen;"></i> @elseif($product['asd_deprecated'] == 2)<i class="fa-solid fa-minus" style="color: dodgerblue;"></i> @else <i class="fa-regular fa-circle-xmark" style="color: red;"></i> @endif</td>
        <td>{{$product['asd_reference']}}</td>
        <td>{{$product['asd_attr_reference']}}</td>
        <td>@if($product['asd_price'] == 0)<span style="color:red;">@else<span>@endif{{$product['asd_price']}}</span></td>
        <td>{{$product['asd_discount']}}</td>
        <td>
            @if( ( $product['wholesale_price'] !='' ) && ( $product['asd_wholesale_price'] !='' ) )
                @if( $product['wholesale_price'] != $product['asd_wholesale_price'] ) <span style="color: red;"> {{$product['asd_wholesale_price']}} </span> @else {{$product['asd_wholesale_price']}} @endif
            @else
                {{$product['asd_wholesale_price']}}
            @endif
        </td>
        <td>
            @if( $product['asd_racio'] !='' ) 
                @if( $product['asd_racio'] < 5.01 ) <span style="color: red;"> {{$product['asd_racio']}} %</span> @else {{$product['asd_racio']}} % @endif
            @endif
        </td>

    </tr>
@endforeach