@extends('layouts.app')

@section('content')
<div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                   
                    <ul class="listUL">
                        <li class="rowStyling">
                            <a href="#" onclick="$('#clientsInfo').toggle();">GET CLIENTS INFO</a>
                            <div id="clientsInfo" style="display: none;padding: 20px;background-color: #efefef;">
                                <ul class="listUL">
                                    <li class="rowStyling"><a href="">Action 1</a></li>
                                    <li class="rowStyling"><a href="">Action 2</a></li>
                                    <li class="rowStyling"><a href="">Action 3</a></li>
                                    <li class="rowStyling"><a href="">Action 4</a></li>
                                    <li class="rowStyling"><a href="">Action 5</a></li>
                                    <li class="rowStyling"><a href="">Action 6</a></li>
                                </ul>
                            </div>
                        </li>
                        <li class="rowStyling">
                            <a href="#" onclick="$('#suppliersInfo').toggle();">GET SUPPLIERS INFO</a>
                            <div id="suppliersInfo" style="display: none;padding: 20px;background-color: #efefef;">
                                <ul class="listUL" style="display: flow-root;">
                                    <li class="rowStyling" style="display: flow-root;">
                                        <a href="#"><img src="https://www.all-stars-distribution.com/img/m/2.jpg"  style="border: 1px solid #666; float: left;margin: 5px;padding: 5px;"></a>
                                        <a href="#"><img src="https://www.all-stars-distribution.com/img/m/4.jpg"  style="border: 1px solid #666; float: left;margin: 5px;padding: 5px;"></a>
                                        <a href="#"><img src="https://www.all-stars-distribution.com/img/m/5.jpg"  style="border: 1px solid #666; float: left;margin: 5px;padding: 5px;"></a>
                                        <a href="#"><img src="https://www.all-stars-distribution.com/img/m/6.jpg"  style="border: 1px solid #666; float: left;margin: 5px;padding: 5px;"></a>
                                        <a href="#"><img src="https://www.all-stars-distribution.com/img/m/9.jpg"  style="border: 1px solid #666; float: left;margin: 5px;padding: 5px;"></a>
                                        <a href="#"><img src="https://www.all-stars-distribution.com/img/m/10.jpg" style="border: 1px solid #666; float: left;margin: 5px;padding: 5px;"></a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="rowStyling"><a href="">GET PRODUCTS INFO</a></li>
                        <li class="rowStyling"><a href="">GET TRANSLATIONS INFO</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
