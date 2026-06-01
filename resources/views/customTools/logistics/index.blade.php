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
            if (!result.isConfirmed) {
                return;
            }

            const code = String(result.value || '').trim();

            if (!isValidBarcodeCode(code)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid code',
                    text: 'Please insert a valid barcode code.'
                });
                return;
            }

            window.open('{{ config('allstars.services.webtools.base_url') }}/barcode/stand/cell/print/' + encodeURIComponent(code), '_blank');
        });

    } 

    function isValidEan13(code) {
        if (!/^\d{13}$/.test(code)) {
            return false;
        }

        const digits = code.split('').map(Number);
        const checkDigit = digits.pop();
        const sum = digits.reduce((total, digit, index) => {
            return total + digit * (index % 2 === 0 ? 1 : 3);
        }, 0);

        return (10 - (sum % 10)) % 10 === checkDigit;
    }

    function isValidBarcodeCode(code) {
        if (!code || ['undefined', 'null'].includes(code.toLowerCase())) {
            return false;
        }

        if (isValidEan13(code)) {
            return true;
        }

        return /^[A-Za-z0-9._-]{1,64}$/.test(code);
    }

</script>

@section('content')

    <div class="navbar navbar-light customPanel">

        <div class="row">
            <div class="col-lg-12">
                <div class="listUL" style="margin: 0 auto;display: table;">
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{ route('logistics.tools.shipping.index') }}" title="IMPORTATIONS">   
                            <div><i class="fa-solid fa-plane-departure" style="font-size: 40px;"></i></div>
                            <div>STATS</div>
                        </a>
                    </div>
                    
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{ route('logistics.tools.carrier_issues.legacy_index') }}" title="ISSUES">   
                            <div><i class="fa-solid fa-triangle-exclamation" style="font-size: 40px;"></i></div>
                            <div>{{ __("messages.issues.carrier")}}</div>
                        </a>
                    </div>
                    
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{ route('logistics.tools.picking.legacy_index') }}" title="PICKING">   
                            <div><i class="fa-solid fa-warehouse" style="font-size: 40px;"></i></div>
                            <div>{{ __("messages.PICKING")}}</div>
                        </a>
                    </div>
                    
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{ route('logistics.tools.housing.legacy_index') }}" title="PICKING">   
                            <div><i class="fa-solid fa-location-crosshairs" style="font-size: 40px;"></i></div>
                            <div>{{ __("messages.HOUSING")}}</div>
                        </a>
                    </div>
                    
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{ route('logistics.tools.stockEntry.show', 1) }}" title="PICKING">   
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
                        <a href="{{ route('logistics.tools.suppliers.issues.index', ['type' => 2]) }}" title="SUPPLIER">   
                            <div><i class="fa-solid fa-boxes-packing" style="font-size: 40px;"></i></div>
                            <div style="line-height: 1.2;">Supplier's Delivery</div>
                        </a>
                    </div>
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{ route('logistics.tools.safety_check.index') }}" title="Safety Check">   
                            <div><i class="fa-solid fa-shield-halved" style="font-size: 40px;"></i></div>
                            <div style="line-height: 1.2;">Safety Check</div>
                        </a>
                    </div>
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{ route('logistics.tools.shipments_check.index') }}" title="Carrier Check">   
                            <div><i class="fa-solid fa-truck-fast" style="font-size: 40px;"></i></div>
                            <div style="line-height: 1.2;">Carrier Check</div>
                        </a>
                    </div>
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{ route('logistics.tools.carrier_end_of_day.index') }}" title="Carrier end of day">   
                            <div><i class="fa-solid fa-file-pdf" style="font-size: 40px;"></i></div>
                            <div style="line-height: 1.2;">End of Day</div>
                        </a>
                    </div>
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{ route('logistics.tools.rma_check.index') }}" title="RMA Check">   
                            <div><i class="fa-solid fa-barcode" style="font-size: 40px;"></i></div>
                            <div style="line-height: 1.2;">RMA Check</div>
                        </a>
                    </div>
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{ route('logistics.tools.inventory.index') }}" title="Inventory">   
                            <div><i class="fa-solid fa-clipboard-list" style="font-size: 40px;"></i></div>
                            <div style="line-height: 1.2;">Inventory</div>
                        </a>
                    </div>
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{ route('logistics.tools.oms.logistic_containers.index') }}" title="Carrier Check">   
                            <div><i class="fa-solid fa-dumpster" style="font-size: 40px;"></i></div>
                            <div style="line-height: 1.2;">OMS - Containers</div>
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
@endsection
