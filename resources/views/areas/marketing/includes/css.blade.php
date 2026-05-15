@extends('layouts.app')

@section('content')

    <style> table, th, td { border: 1px solid black; } </style>
    
    <div class="navbar navbar-light customPanel" style="margin-top: 10px;;display: table;">
        <div style="text-align: center;margin: 30px;">
            <h3 style="color: red; text-transform: uppercase;">{{ __('tags.Current stock value: ') }}</h3>
            <h3 style="color: darkgreen; text-transform: uppercase;font-weight: bolder; font-size: 34px;">{{number_format($total, 2, '.', ' ')}} €</h3>
        </div>
        <div style="text-align: center;margin: 30px;font-size: 28px;">
            <a class="btn btn-info" href="{{$link}}" download> <i class="fa-solid fa-download" style="font-size: 28px;"></i><span style="margin-left: 10px;font-size: 28px;">{{ __('tags.Download') }}</span></a>
        </div>
        <table style="width: 800px;text-align: center;margin: 20px auto;" border="1">
            <tr>
                <td style="font-weight: bolder; font-size: 16px;">{{ __('tags.reference') }}</td>
                <td style="font-weight: bolder; font-size: 16px;">{{ __('tags.quantity') }}</td>
                <td style="font-weight: bolder; font-size: 16px;">WHOLESALE</td>
                <td style="font-weight: bolder; font-size: 16px;">CURRENCY</td>
                <td style="font-weight: bolder; font-size: 16px;">WHOLESALE</td>
                <td style="font-weight: bolder; font-size: 16px;">{{ __('tags.total') }}</td>
            </tr>
            @foreach( $array AS $item)
                <tr>
                    <td>{{$item->reference}}</td>
                    <td>{{$item->quantity}}</td>
                    <td>{{round($item->wholesale_price, 2)}}</td>
                    <td>
                        {{$item->currency}} 
                        @if( $rates[$item->currency] != 1.00) 
                            ( {{round($rates[$item->currency], 5)}} ) 
                        @else 
                            ( 1.00 ) 
                        @endif
                    </td>
                    <td>{{round($item->eur_convertion, 2)}} €</td>
                    <td>{{round($item->total_row, 2)}} €</td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection