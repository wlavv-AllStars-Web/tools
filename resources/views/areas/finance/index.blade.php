@extends('layouts.app')


@section('content')

    @include("areas.finance.includes.js")

    <div class="navbar navbar-light customPanel">
        <div class="row">
            <div class="col-lg-12">
                <div class="listUL" style="margin: 0 auto;display: table;">
                    @foreach($accessList AS $access)
                        <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                            <a href="{{$access['url']}}">     
                                <div>{!! $access['icon'] !!}</div>
                                <div>{{ $access['name']}}</div>
                            </a>
                        </div>
                    @endforeach

                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{route('finance.tools.refunds.index')}}">     
                            <div><i class="fa-solid fa-file-invoice-dollar" style="font-size: 40px;"></i></div>
                            <div>REFUNDS</div>
                        </a>
                    </div>
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{route('finance.tools.vat.check')}}">     
                            <div><i class="fa-solid fa-user-check" style="font-size: 40px;"></i></div>
                            <div>VAT</div>
                        </a>
                    </div>
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{route('finance.tools.payment_links.index')}}">     
                            <div><i class="fa-solid fa-money-check-dollar" style="font-size: 40px;"></i></div>
                            <div>LINK</div>
                        </a>
                    </div>

                </div>
            </div>
            {{--
            <div class="col-lg-6">
                <div class="row" style="border: 1px solid #ccc;padding: 5px;">
                    <div class="col-lg-12"><h5 style="margin-top:7px;text-align: center;">{{ __('tags.currency rate - 1€')}}</h5></div>
                    <div class="col-lg-2">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1" style="border-radius: 5px 0 0 5px;">元</span>
                            </div>
                            <input class="form-control" id="yuan" name="yuan" type="text" value="{{$rates->yuan}}" placeholder="元" aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1" style="border-radius: 5px 0 0 5px;">£</span>
                            </div>
                            <input class="form-control" id="pound" name="pound" type="text" value="{{$rates->pound}}" placeholder="£" aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1" style="border-radius: 5px 0 0 5px;">$</span>
                            </div>
                            <input class="form-control" id="dollar" name="dollar" type="text" value="{{$rates->usd}}" placeholder="$" aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1" style="border-radius: 5px 0 0 5px;">¥</span>
                            </div>
                            <input class="form-control" id="yen" name="yen" type="text" value="{{$rates->yen}}" placeholder="¥" aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <span class="btn btn-primary" onclick="saveCurrencyRate()" style="width: 100%"> {{ __('tags.save')}} </span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3">
                <div class="listUL" style="margin: 0 auto;">
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{route('finance.tools.refunds.index')}}">     
                            <div><i class="fa-solid fa-file-invoice-dollar" style="font-size: 40px;"></i></div>
                            <div>REFUNDS</div>
                        </a>
                    </div>
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{route('finance.tools.vat.check')}}">     
                            <div><i class="fa-solid fa-user-check" style="font-size: 40px;"></i></div>
                            <div>VAT</div>
                        </a>
                    </div>
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{route('finance.tools.payment_links.index')}}">     
                            <div><i class="fa-solid fa-money-check-dollar" style="font-size: 40px;"></i></div>
                            <div>LINK</div>
                        </a>
                    </div>


                </div>
            </div>
            --}}
        </div>
    </div>
    
    {!! $counters !!}
@endsection
