@extends('layouts.app')

@section('content')
    <div class="navbar navbar-light customPanel">
        <h2 class="nt-section-title">Quick actions</h2>
        <div class="listUL" style="margin: 0 auto;display: table;">
            @foreach($accessList AS $access)
            <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;">
                <a href="{{$access['url']}}">
                    <div>{!! $access['icon'] !!}</div>
                    <div style="line-height: 18px; padding: 5px 0;">{{ $access['name']}}</div>
                </a>
            </div>
            @endforeach
            <div class="nt-quick-sales-report">
                <div class="nt-quick-sales-report__title">{{ __('tags.SALES REPORT') }}</div>
                <div class="nt-quick-sales-report__fields">
                    <div>
                        <select name="brand_sales_history" id="brand_sales_history">
                            <option value="0">{{ __('tags.BRAND') }}</option>
                            @foreach($manufacturers AS $manufacturer)
                                <option value="{{ $manufacturer->id_manufacturer }}">{{ $manufacturer->name }}</option>
                            @endforeach
                        </select>
                        <span class="alert alert-danger" id="alert_sales_history_brand"></span>
                    </div>
                    <div>
                        <input id="date_sales_history" name="date_sales_history" type="text" value="" placeholder="2024-01-01">
                        <span class="alert alert-danger" id="alert_sales_history_date">{{ __('messages.Please fill the date') }}</span>
                    </div>
                    <button type="button" onclick="getSalesHistory()" class="btn btn-dark">{{ __('tags.FIND') }}</button>
                </div>
                <div id="sales_history"></div>
            </div>
        </div>
    </div>

    <section class="nt-monitoring-section">
        <h2 class="nt-section-title">Monitoring</h2>
        {!! $counters !!}
    </section>

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
