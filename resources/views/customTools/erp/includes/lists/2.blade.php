<div class="col-lg-12">
    <div style="width: 350px; float: left;">
        <div class="navbar navbar-light customPanel" style="display: flex;">
            <table class="display" style="width:100%;text-align: center;">
                <thead>
                    <tr>
                        <th style="text-align: right;padding-right: 5px;">SUPPLIER</th>
                        <th style="text-align: left;padding-left: 5px;">ORDERS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data AS $item)
                        <tr onclick="getSupplierOrders({{$item->supplier->id_supplier}}, 0)" style="cursor: pointer" class="supplierRow">
                            <td style="text-align: right;padding-right: 5px;"> {{$item->supplier->name}} </td>
                            <td style="text-align: left;padding-left: 5px;">  {{$item->total_orders}}   </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div style="width: calc( 100% - 370px); margin-left: 20px; float: left;">
        <div class="navbar navbar-light customPanel" style="display: flex;">
            <div id="suppliersOrders" style="width: 100%;">
                <div class="alert alert-warning" style="margin-bottom: 0;">SELECT SUPPLIER ON THE LEFT TO SHOW CLOSED ORDERS</div>
            </div>
        </div>    
    </div>    
</div>