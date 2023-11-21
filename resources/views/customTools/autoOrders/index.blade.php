@extends('layouts.app')
@section('content')

    @include("customTools.autoOrders.includes.css")
    @include("customTools.autoOrders.includes.js")

    <div class="navbar navbar-light customPanel">
        <div class="card">
            <div class="card-body">
                <div>
                    @foreach($auto_orders AS $key => $manufacturer)
                        <div class="card" style="margin-top: 10px;border-radius: 0;" onclick="$('#toggle_{{$manufacturer['id_manufacturer']}}').toggle()">
                            <div class="card-body" style="padding: 0px;">
                                <h5 class="card-title" style="margin-bottom: 0;height: 32px;">
                                    <div style="padding: 7px;font-size: 16px;color: #FFF;background-color: dodgerblue; width: 50px; height: 32px;text-align: center;float: left;margin-right: 5px;padding: 6px;">{{$manufacturer['counter']}}</div>
                                    <div style="float: left;padding: 5px 0;">{{$manufacturer['name']}}</div>
                                    <div style="margin: 0 5px;padding: 5px;float: right;">
                                        <a href="{{ route('autoOrders.edit', $manufacturer['id_manufacturer']) }}" target="_blank" style="text-decoration: none; float: right;color: #333; margin-right: 5px;">CLEAN</a>
                                        <span style="margin: 0 5px;float: right;">|</span>
                                        <a href="{{$manufacturer['export']}}" target="_blank" style="text-decoration: none; float: right;color: #333;">EXPORT</a>
                                    </div>
                                </h5>
                                <div style="border-top: 1px solid #bbb;">
                                    <table style="width: 100%; border: none;background-color: white;text-align: center;display: none;" id="toggle_{{$manufacturer['id_manufacturer']}}">
                                        <tr>
                                            <td class="table_cell" style="border-left: none; width: 400px; overflow: hidden; text-align: left;"></td>
                                            <td class="table_cell" style="width: 130px; overflow: hidden">Referencia</td>
                                            <td class="table_cell" style="width: 150px; overflow: hidden">Combinação</td>
                                            <td class="table_cell" style="width: 80px; overflow: hidden">Status</td>
                                            <td class="table_cell" style="width: 80px; overflow: hidden">Stock</td>
                                            <td class="table_cell" style="width: 80px; overflow: hidden">Stock Arrive</td>
                                            <td class="table_cell" style="width: 100px; overflow: hidden">Vendidos</td>
                                            <td class="table_cell" style="width: 110px; overflow: hidden"></td>
                                        </tr>
                                        @foreach($manufacturer['products'] AS $product)
                                            <tr>
                                                <td class="table_cell" style="border-left: none; display: block; overflow: hidden; text-align: left;overflow: hidden;height: 27px;">{{$product['product_name']}}</td>
                                                <td class="table_cell" style="width: 130px; overflow: hidden">
                                                    @if ($product['id_product_attribute'] == 0)
                                                        {{$product['reference']}}
                                                    @else
                                                        {{$product['attr_reference']}}
                                                    @endif
                                                </td>
                                                <td class="table_cell" style="width: 150px; overflow: hidden">{{$product['combination']}}</td>
                                                <td class="table_cell" style="width: 80px; overflow: hidden">
                                                    @if ($product['status'] == 'In Stock')
                                                        <span style="color: green;text-transform: uppercase">{{$product['status']}}</span>
                                                    @else
                                                        <span style="color: red;text-transform: uppercase">{{$product['status']}}</span>
                                                    @endif
                                                </td>
                                                <td class="table_cell" style="width: 80px; overflow: hidden">{{$product['qtd_in_stock']}}</td>
                                                <td class="table_cell" style="width: 80px; overflow: hidden">{{$product['stock_arrive']}}</td>
                                                <td class="table_cell" style="width: 100px; overflow: hidden">{{$product['qtd_item']}}</td>
                                                <td class="table_cell" style="width: 110px; overflow: hidden">
                                                    <a href="#" class="btn btn-dark" style="border-radius: 0; margin: 2px;font-size: 14px;padding: 2px 5px;"> SET AS ORDERED</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <a type="button" class="btn btn-dark" style="margin-top: 15px; text-align: center; border-radius: 0; width: 100%;" href="{{ $export_all }}" target="_blank">EXPORT ALL BRANDS</a>
            </div>
        </div>
    </div>
@endsection