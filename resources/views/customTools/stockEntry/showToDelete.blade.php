@extends('layouts.app')
@section('content')
    @include("customTools.stockEntry.includes.js")
    <div class="navbar navbar-light customPanel">
        <div style="display: table;width: 100%;">
            <table style="width: 100%;background-color: #ddd;border: 1px solid #ccc; text-align: center;">
                <tr style="border-bottom: 1px solid #ccc;">
                    <td class="hide_mobile">{{ __("messages.id_order")}}</td>
                    <td>{{ __("messages.ORDER REFERENCE")}}</td>
                    <td>{{ __("messages.SKU")}}</td>
                    <td>{{ __("messages.QUANTITY")}}</td>
                    <td class="hide_mobile">{{ __("messages.OPERATOR")}}</td>
                    <td></td>
                </tr>
                @foreach ($entries as $entry)

                    @if ($entry['deleted'] == 1 )
                    <tr style="border-bottom: 1px solid #ccc;" class="alert alert-danger">
                    @else
                    <tr style="border-bottom: 1px solid #ccc;background-color: #fff;">
                    @endif
                        <td class="hide_mobile">{{$entry['po_id']}}</td>
                        <td>{{$entry['reference']}}</td>
                        <td>{{$entry['sku']}}</td>
                        <td>{{$entry['qty']}}</td>
                        <td class="hide_mobile">{{$entry['firstname']}} {{$entry['lastname']}}</td>
                        <td style="padding: 5px;">
                            <button type="button" class="btn btn-danger" onclick="destroyEntry({{$entry['id_bms_procurement_purchase_order_reception']}})">
                                <i style="pointer-events: none;" class="f-left fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </table>

            <form id="destroyEntryForm" action="#" method="POST">
                @csrf
                @method("DELETE")
            </form>
        </div>
    </div>
@endsection