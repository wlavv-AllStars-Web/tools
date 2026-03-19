@extends('layouts.app')
@section('content')

    @include('customTools.erp.includes.css')
    @include('customTools.erp.includes.js')
    
    <div class="row">
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel" style="display: flex;text-align: center;">
                <div style="float: left; min-width: 200px; height: 100px; background-color: dodgerblue; border-radius: 5px; border-radius: 5px; box-shadow: 0 0 5px #666;padding: 10px 10px;">
                    <div style="color: #FFF; font-size: 18px; font-weight: bold;margin: 10px;">ORDER <BR> INFORMATION</div>
                </div>
                <div style="float: left; min-width: 200px; height: 100px; background-color: #BD3B1B; border-radius: 5px; border-radius: 5px; box-shadow: 0 0 5px #666;padding: 10px 10px;">
                    <div style="color: #FFF; font-size: 18px; font-weight: bold;border-bottom: 1px solid #FFF;margin: 0 5px;">ROWS</div>
                    <div style="color: #FFF; font-size: 40px; font-weight: bold;">{{$sums->number_of_rows}}</div>
                </div>
                <div style="float: left; min-width: 200px; height: 100px; background-color: #D8A800; border-radius: 5px; border-radius: 5px; box-shadow: 0 0 5px #666;padding: 10px 10px;">
                    <div style="color: #FFF; font-size: 18px; font-weight: bold;border-bottom: 1px solid #FFF;margin: 0 5px;">ORDERED</div>
                    <div style="color: #FFF; font-size: 40px; font-weight: bold;">{{$sums->total_qty_ordered}}</div>
                </div>
                <div style="float: left; min-width: 200px; height: 100px; background-color: #B9D870; border-radius: 5px; border-radius: 5px; box-shadow: 0 0 5px #666;padding: 10px 10px;">
                    <div style="color: #FFF; font-size: 18px; font-weight: bold;border-bottom: 1px solid #FFF;margin: 0 5px;">BILLED</div>
                    <div style="color: #FFF; font-size: 40px; font-weight: bold;">{{$sums->total_qty_faturado}}</div>
                </div>
                <div style="float: left; min-width: 200px; height: 100px; background-color: #B6C61A; border-radius: 5px; border-radius: 5px; box-shadow: 0 0 5px #666;padding: 10px 10px;">
                    <div style="color: #FFF; font-size: 18px; font-weight: bold;border-bottom: 1px solid #FFF;margin: 0 5px;">RECEIVED</div>
                    <div style="color: #FFF; font-size: 40px; font-weight: bold;">{{$sums->total_qty_received}}</div>
                </div>
                <div style="float: left; min-width: 200px; height: 100px; background-color: #006344; border-radius: 5px; border-radius: 5px; box-shadow: 0 0 5px #666;padding: 10px 10px;">
                    <div style="color: #FFF; font-size: 18px; font-weight: bold;border-bottom: 1px solid #FFF;margin: 0 5px;">TOTAL</div>
                    <div style="color: #FFF; font-size: 40px; font-weight: bold;">{{number_format($order_total, 2, ',', ' ')}}</div>
                </div>
                <div style="float: left; min-width: 200px; height: 100px; background-color: #444; border-radius: 5px; border-radius: 5px; box-shadow: 0 0 5px #666;padding: 10px 10px;">
                    <div style="color: #FFF; font-size: 18px; font-weight: bold;margin: 10px;">SHOW <br> DIFFERENCES</div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel">
            <table id="ordersTable" class="display" style="width:100%;text-align: center;">
                <thead>
                    <tr>
                        <th>NEW?</th>
                        <th>REFERENCE</th>
                        <th>NAME</th>
                        <th>ORDERED</th>
                        <th>INV. DATE</th>
                        <th>BILLED</th>
                        <th>RECEIVED</th>
                        <th>STOCK</th>
                        <th>ARRIVE</th>
                        <th>BARCODE</th>
                        <th>END?</th>
                        <th>PURCHASE</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->rows AS $row)
                        @if(isset($row->product))
                            @include("customTools.erp.includes.lists.order.product", ['row' => $row])
                        @elseif(isset($row->attribute))
                            @include("customTools.erp.includes.lists.order.attribute", ['row' => $row])
                        @else                            
                            @include("customTools.erp.includes.lists.order.none")
                        @endif
                    @endforeach
                </tbody>
            </table>
            
            
            </div>
        </div>
    </div>
    
@endsection