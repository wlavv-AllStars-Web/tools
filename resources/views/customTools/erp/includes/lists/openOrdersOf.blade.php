<div id="suppliersOrders" style="width: 100%;">
    <div style="margin: 10px; border-bottom: 1px solid #888; text-align: center;padding: 5px;">
        @if($openOrders == 1) OPEN @else CLOSED @endif ORDERS OF <span style="color: dodgerblue; font-weight: bolder;">{{$supplier->name}}</span>
    </div>
    <table class="display" style="width:100%;text-align: center;">
        <thead>
            <tr>
                <th>ID</th>
                <th>REFERENCE</th>
                <th>ETA</th>
                <th>COMMENTS</th>
                <th>TRACKING</th>
                <th>PRODUCTS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders AS $item)
                <tr onclick="openOrder({{$item->id_bms_procurement_purchase_order}});" style="cursor: pointer" class="supplierRow">
                    <td> {{$item->id_bms_procurement_purchase_order}}</td>
                    <td> {{$item->reference}}</td>
                    <td> {{$item->eta}}</td>
                    <td> {{$item->comments_public}}</td>
                    <td> {{$item->shipping_tracking}}</td>
                    <td> {{count($item->rows)}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>