<tr>
    <td>
        @if($row->is_new)
            <a href="#" is_new="1" style="text-decoration:none;background-color: pink !important;  width: 20px; height: 20px;border:1px solid grey;">&nbsp; &nbsp; &nbsp;</a>
        @else
            <a href="#" is_new="1" style="text-decoration:none;background-color: grey !important;  width: 20px; height: 20px;border:1px solid grey;">&nbsp; &nbsp; &nbsp;</a>
        @endif
    </td>
    <td>{{$row->sku}}</td>
    <td>{{$row->name}}</td>
    <td>{{$row->qty_ordered}}</td>
    <td>{{$row->date_wmfaturado}}</td>
    <td>
        <div class="input-group" style="width: 100px;">
            <input type="text" class="form-control" style="height: 30px;" value="{{$row->qty_wmfaturado}}" aria-label="Recipient's username" aria-describedby="basic-addon2">
            <div class="input-group-append">
                @if($row->qty_ordered == $row->qty_wmfaturado)
                    <span class="input-group-text alert alert-success" style="padding: 6px;border-radius: 0 5px 5px 0;" id="basic-addon2" ><i class="fa-solid fa-check" style="font-size: 16px;"></i></span>
                @elseif( ($row->qty_ordered != $row->qty_wmfaturado) && ( $row->qty_wmfaturado > 0 ) )
                    <span class="input-group-text alert alert-warning" style="padding: 6px;border-radius: 0 5px 5px 0;" id="basic-addon2"><i class="fa-regular fa-clock" style="font-size: 16px;"></i></span>
                @else
                    <span class="input-group-text alert alert-danger" style="padding: 6px;border-radius: 0 5px 5px 0;" id="basic-addon2"><i class="fa-solid fa-x" style="font-size: 16px;"></i></span>
                @endif
            </div>
        </div>
    </td>
    <td>
        <div class="input-group" style="width: 100px;">
            <input type="text" class="form-control" style="height: 30px;" value="{{$row->qty_received}}" aria-label="Recipient's username" aria-describedby="basic-addon2">
            <div class="input-group-append">
                @if($row->qty_received == $row->qty_wmfaturado)
                    <span class="input-group-text alert alert-success" style="padding: 6px;border-radius: 0 5px 5px 0;" id="basic-addon2" ><i class="fa-solid fa-check" style="font-size: 16px;"></i></span>
                @elseif( ($row->qty_ordered != $row->qty_wmfaturado) && ( $row->qty_wmfaturado > 0 ) )
                    <span class="input-group-text alert alert-warning" style="padding: 6px;border-radius: 0 5px 5px 0;" id="basic-addon2"><i class="fa-regular fa-clock" style="font-size: 16px;"></i></span>
                @else
                    <span class="input-group-text alert alert-danger" style="padding: 6px;border-radius: 0 5px 5px 0;" id="basic-addon2"><i class="fa-solid fa-x" style="font-size: 16px;"></i></span>
                @endif
            </div>
        </div>
    </td>
    <td>{{$row->attribute->stock->quantity}}</td>
    <td>{{$row->attribute->stock_arrive}}</td>
    <td>{{$row->wmean13}}</td>
    <td>
        <div class="input-group" style="width: 40px;">
            <div class="input-group-append">
                @if( ($row->qty_received == $row->qty_wmfaturado ) && ( $row->qty_received == $row->qty_ordered ) )
                    <span class="input-group-text alert alert-success" style="padding: 6px;border-radius: 5px;" id="basic-addon2" ><i class="fa-solid fa-check" style="font-size: 16px;"></i></span>
                @else
                    <span class="input-group-text alert alert-danger" style="padding: 6px;border-radius: 5px;" id="basic-addon2"><i class="fa-regular fa-x" style="font-size: 16px;"></i></span>
                @endif
            </div>
        </div>
    </td>
    <td>{{$row->attribute->wholesale_price}}</td>
    <td><i class="fa-solid fa-trash" style="color: red;font-size: 18px;"></i></td>
</tr>