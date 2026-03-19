@extends('layouts.app')

<style>
    
     .custom_title{ text-transform: uppercase; color: dodgerblue; font-weight: bolder; font-size: 40px; }
     
</style>

<script>
    
    function getShippingReport(){
    
        Swal.fire({
          title: '{{ __("messages.Loading...")}}',
          html: '{{ __("messages.Please wait a moment")}}'
        });
        Swal.showLoading()
    
        $.ajax({
            type: 'POST',
            url: "{{route('dashboard.shipping_report')}}",
            dataType: "json",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
    
                Swal.hideLoading();
                Swal.close();
                
                var data = JSON.parse(JSON.stringify(response));
                
                $('#shipping_report').replaceWith(data.html);
    
            }       
        });
    } 
    
    function generateStandCell(){
        
        Swal.fire({
            title: "INSERT CODE",
            input: "text",
            inputAttributes: {
            autocapitalize: "off"
            },
            showCancelButton: true,
            confirmButtonText: "PRINT",
        }).then((result) => {
            window.open('https://webtools.all-stars-motorsport.com/barcode/stand/cell/print/' + result.value, '_blank');
        });

    } 

</script>

@section('content')

    <div class="navbar navbar-light customPanel">

        <div class="row">
            <div class="col-lg-12">
                <div class="listUL" style="margin: 0 auto;display: table;">
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{ route('shipping.index') }}" title="IMPORTATIONS">   
                            <div><i class="fa-solid fa-plane-departure" style="font-size: 40px;"></i></div>
                            <div>STATS</div>
                        </a>
                    </div>
                    
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{ route('carrierIssues.index') }}" title="ISSUES">   
                            <div><i class="fa-solid fa-triangle-exclamation" style="font-size: 40px;"></i></div>
                            <div>{{ __("messages.issues.carrier")}}</div>
                        </a>
                    </div>
                    
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{ route('picking.index') }}" title="PICKING">   
                            <div><i class="fa-solid fa-warehouse" style="font-size: 40px;"></i></div>
                            <div>{{ __("messages.PICKING")}}</div>
                        </a>
                    </div>
                    
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{ route('housing.index') }}" title="PICKING">   
                            <div><i class="fa-solid fa-location-crosshairs" style="font-size: 40px;"></i></div>
                            <div>{{ __("messages.HOUSING")}}</div>
                        </a>
                    </div>
                    
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{ route('stockEntry.show', 1) }}" title="PICKING">   
                            <div><i class="fa-solid fa-boxes-stacked" style="font-size: 40px;"></i></div>
                            <div>ENTRY</div>
                        </a>
                    </div>
                    {{--
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <span onclick="getShippingReport()" title="SHIPPING REPORT">   
                            <div><i class="fa-solid fa-person-chalkboard" style="font-size: 40px;"></i></div>
                            <div>SHIPPING</div>
                        </span>
                    </div>
                    --}}
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <span onclick="generateStandCell()" title="GENERATE AND PRINT">   
                            <div><i class="fa-solid fa-barcode" style="font-size: 40px;"></i></div>
                            <div>PRINT</div>
                        </span>
                    </div>
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{ route('suppliersIssues.index', ['type' => 2]) }}" title="SUPPLIER">   
                            <div><i class="fa-solid fa-boxes-packing" style="font-size: 40px;"></i></div>
                            <div style="line-height: 1.2;">Supplier's Delivery</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12" id="shipping_report"> </div>
    </div>
    
    {!! $counters !!}
    
    <div class="row">
        @foreach( $panels AS $panel)
            @include("areas.dashboard.includes.panels", $panel)
        @endforeach
    </div>
    

    
    <div class="navbar navbar-light customPanel" style="display: none;">
        <ul class="listUL">
            <li class="rowStyling"> <a href="{{route('products.index')}}">      {{ __('messages.Products')}}</a></li>
            <li class="rowStyling"> <a href="{{route('manufacturers.index')}}"> {{ __('messages.Manufacturers')}}</a></li>
            <li class="rowStyling"> <a href="{{route('suppliers.index')}}">     {{ __('messages.Suppliers')}}</a></li>
        </ul>
    </div>
@endsection