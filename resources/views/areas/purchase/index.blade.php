@extends('layouts.app')

@section('content')
    <div class="row" style="margin: 0;">
        <div class="col-lg-3">
                <div class="navbar navbar-light customPanel">
        <div class="panel panel-default" style="display: flow-root">
            <div class="panel-body sales_history" style="min-height: 100px;">
                <div class="row" style="margin: 0;">
                    <div class="col-lg-12"><h5 style="margin-top:7px;text-align: center;">{{ __('tags.SALES REPORT') }}</h5></div>
                    <div class="col-lg-12">
                        <div style="width: 40%;float: left;">
                            <select name="brand_sales_history" id="brand_sales_history" style="width: calc( 100% - 10px ); font-size: 22px;">
                                <option value="0">{{ __('tags.BRAND') }}</option>
                                @foreach($manufacturers AS $manufacturer)
                                    <option value="{{ $manufacturer->id_manufacturer }}">{{ $manufacturer->name }}</option>
                                @endforeach
                            </select>
                            <span class="alert alert-danger" style="width: calc( 100% - 10px );display: none;text-align: center; margin-top: 5px;padding: 5px;" id="alert_sales_history_brand"></span>
                        </div>
                        <div style="width: 40%;float: left;">
                            <input id="date_sales_history" name="date_sales_history" type="text" value="" placeholder="2024-01-01" style="width: calc( 100% - 10px ); font-size: 19px;">
                            <span class="alert alert-danger" style="width: calc( 100% - 10px );display: none;text-align: center; margin-top: 5px;padding: 5px;" id="alert_sales_history_date">{{ __('messages.Please fill the date') }}</span>
                        </div>
                        <div style="width: 20%;float: left;">
                            <span onclick="getSalesHistory()" class="btn btn-dark" style="width: 100%">{{ __('tags.FIND') }}</span>
                        </div>
                    </div>
                    <div class="col-lg-12"><div id="sales_history"></div></div>
                </div>
            </div>
        </div>
    </div>

        </div>
        <div class="col-lg-9">
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
            <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                <a href="{{ route('suppliersIssues.index', ['type' => 1]) }}" title="SUPPLIER">   
                    <div><i class="fa-solid fa-boxes-packing" style="font-size: 40px;"></i></div>
                    <div style="line-height: 1.2;">Supplier's Issues</div>
                </a>
            </div>
        </div>
    </div>
            </div>
        </div>
    </div>

    {!! $counters !!}    

    <script>
        function getSalesHistory() {
            let fail = 0;
            const date = $('#date_sales_history').val();
            const brand = $('#brand_sales_history').val();

            if (date === '') {
                fail++;
                $('#alert_sales_history_date').text('{{ __('messages.Please fill the date') }}').show();
                $('#date_sales_history').css('border', '2px solid red');
            } else {
                $('#alert_sales_history_date').hide();
                $('#date_sales_history').css('border', '2px solid green');
            }

            if (brand === '0') {
                fail++;
                $('#alert_sales_history_brand').text('{{ __('messages.Please select the brand') }}').show();
                $('#brand_sales_history').css('border', '2px solid red');
            } else {
                $('#alert_sales_history_brand').hide();
                $('#brand_sales_history').css('border', '2px solid green');
            }

            if (!/^\d{4}-\d{1,2}-\d{1,2}$/.test(date)) {
                fail++;
                $('#alert_sales_history_date').text('{{ __('messages.Invalid date') }}').show();
                $('#date_sales_history').css('border', '2px solid red');
            }

            if (fail > 0) {
                Swal.fire('{{ __('messages.Please review the form inputs!') }}', '', 'warning');
                return;
            }

            $.ajax({
                type: 'POST',
                url: "{{ route('dashboard.post') }}",
                dataType: 'json',
                data: {
                    _token: "{{ csrf_token() }}",
                    action: 'getSalesHistory',
                    brand: brand,
                    date: date,
                },
                success: function (response) {
                    $('#sales_history').replaceWith(response.html);
                },
            });
        }
    </script>
@endsection
