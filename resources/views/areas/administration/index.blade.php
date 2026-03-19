@extends('layouts.app')

@section('content')

    <div class="navbar navbar-light customPanel">
        <div class="listUL" style="margin: 0 auto;display: table;">
            @foreach($accessList AS $access)
            <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                <a href="{{$access['url']}}">     
                    <div>{!! $access['icon'] !!}</div>
                    <div style="line-height: 18px; padding: 5px 0;">{{ $access['name']}}</div>
                </a>
            </div>
            @endforeach
        </div>
    </div>

    @php
        $symbols = [
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            'YEN' => '¥',
        ];
    
        function fmt($v) {
            if (is_null($v) || $v === '') return '—';
            return number_format((float)$v, 6, '.', '');
        }
    @endphp
    

    <div class="navbar navbar-light customPanel">
        <table class="currency-table">
            <thead>
                <tr>
                    <th colspan="5">ACTIVE CURRNECY CONVERTION BY STORE</th>
                </tr>
                <tr>
                    <th></th>
                    @foreach($symbols as $code => $symbol)
                        <th>{{ $symbol }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rates as $provider => $currencies)
                    <tr>
                        <td class="currency-provider" style="text-align: right;"> <div style="margin: 0 5px;">{{ $provider }}</div></td>
                        @foreach($symbols as $code => $symbol)
                        <td style="border-left: 1px solid #999">
                            <table style="width: 100%;">
                                <tr>
                                    <td style="border-bottom: 0px solid #000;"><span style="font-weight: bolder;">P: </span><span style="color: darkgreen;"> {{ isset($currencies[$code]['purchase']) ? number_format((float)$currencies[$code]['purchase'], 4, '.', ',') : '—' }}</span></td>
                                    <td style="border-bottom: 0px solid #000;"><span style="font-weight: bolder;">S: </span><span style="color: red;">{{ isset($currencies[$code]['sale']) ? number_format((float)$currencies[$code]['sale'], 4, '.', ',') : '—' }}</span></td>
                                </tr>
                            </table>
                        </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>    
    </div>
    
    {!! $counters !!}
    
    <style>
        .currency-table th, .currency-table td{ padding: 0 !important; }
        .currency-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .currency-table th, .currency-table td { padding: 10px 14px; text-align: center; border-bottom: 1px solid #e5e7eb; }
        .currency-table th { background: #f8fafc; font-weight: 600; }
        .currency-provider { font-weight: 700; background: #f1f5f9; }
        .rate-box { display: flex; flex-direction: column; gap: 4px; }
        .rate-purchase { color: #059669; font-size: 12px; float: left; }
        .rate-sale { color: #dc2626; font-size: 12px; float: left; }
    </style>
    
@endsection