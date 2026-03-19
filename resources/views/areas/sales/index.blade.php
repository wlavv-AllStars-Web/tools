@extends('layouts.app')

    @include("areas.dashboard.includes.js")

@section('content')

    <div class="navbar navbar-light customPanel">
        <div class="listUL" style="margin: 0 auto;display: table;">
            @foreach($accessList AS $access)
            <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                <a href="{{$access['url']}}">     
                    <div>{!! $access['icon'] !!}</div>
                    <div style="line-height: 18px;">{{ $access['name']}}</div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    
    <div class="navbar navbar-light customPanel" style="display: none;">
        <div class="panel panel-default" style="display: flow-root">
            <div class="panel-heading text-center" style="cursor:pointer; text-transform:uppercase;">
                <div @if(count($racio) > 0) onclick="$('#price_map').toggle()" @endif style="height: 100px; border-radius: 5px; padding: 5px 0; color: white; @if(count($racio) > 0) background-color: red; @else background-color: #0BDA51; @endif cursor: pointer; ">
                    <div style="font-size: 35px" id="price_map_quantity">{{COUNT($racio)}}</div>
                    <div style="font-size: 16px; margin: 0 10px;">PRICE MAP ALERT</div>
                </div>
            </div>
            <div id="price_map" data-open="0" class="panel-body" style="display: none; overflow-x: scroll;"> 
                @if( count($racio) > 0)
                    <table class="table table-bordered customTable text-center">
                        <tbody>
                            <tr style="text-transform: uppercase">
                                {{--
                                <td style="background-color: #f8d7da; color: #842029;">  ACTIVE  </td>
                                <td style="background-color: #f8d7da; color: #842029;">  DEPRECATED  </td>
                                --}}
                                <td style="background-color: #f8d7da; color: #842029;">  WHOLESALE  </td>
                                <td style="background-color: #f8d7da; color: #842029;">  PRICE  </td>
                                <td style="background-color: #f8d7da; color: #842029;">  RACIO  </td>
                                <td style="background-color: #f8d7da; color: #842029;">  DISCOUNT  </td>
                                <td>  REFERENCE  </td>
                                {{--
                                <td style="background-color: #cff4fc; color: #055160;">  ACTIVE  </td>
                                <td style="background-color: #cff4fc; color: #055160;">  DEPRECATED  </td>
                                --}}
                                <td style="background-color: #cff4fc; color: #055160;">  WHOLESALE  </td>
                                <td style="background-color: #cff4fc; color: #055160;">  PRICE  </td>
                                <td style="background-color: #cff4fc; color: #055160;">  RACIO  </td>
                                <td style="background-color: #cff4fc; color: #055160;">  DISCOUNT  </td>
                            </tr>
                            @foreach($racio as $item)
                                <tr style="text-transform: uppercase">
                                    {{--
                                    <td>@if($item->asm_active == 1) <i class="fa-solid fa-check" style="color: green;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                    <td>@if($item->asm_deprecated == 1) <i class="fa-solid fa-check" style="color: green;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                    --}}
                                    <td>{{number_format($item->asm_wholesale_price, 2, '.', ' ')}} €</td>
                                    <td>{{number_format($item->asm_price, 2, '.', ' ')}} €</td>
                                    <td>{{number_format($item->asm_racio, 2, '.', ' ')}} %</td>
                                    <td>{{number_format($item->asm_discount, 2, '.', ' ')}} %</td>
                                    <td>{{$item->reference}}</td>
                                    
                                    {{--
                                    <td>@if($item->asd_active == 1) <i class="fa-solid fa-check" style="color: green;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                    <td>@if($item->asd_deprecated == 1) <i class="fa-solid fa-check" style="color: green;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                    --}}
                                    <td>{{number_format($item->asd_wholesale_price, 2, '.', ' ')}} €</td>
                                    <td>{{number_format($item->asd_price, 2, '.', ' ')}} €</td>
                                    <td>{{number_format($item->asd_racio, 2, '.', ' ')}} %</td>
                                    <td>{{number_format($item->asd_discount, 2, '.', ' ')}} %</td>
                                </tr>                            
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>NO DATA TO DISPLAY</p>
                @endif
            </div>
        </div>
    </div>
    
    {!! $counters !!}
            
@endsection
