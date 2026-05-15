<div id="contentView">
    <div class="alert alert-secondary" style="text-align: center;border-radius: 5px 5px 0 0;margin-bottom: 0;" role="alert">MAIN PRODUCT</div>
    <table class="table table-bordered" style="width: 100%;text-align: center;">
        <tr>
            <td>REFERENCE</td>
            <td>PURCHASE</td>
            <td>PRICE</td>
            <td>DIFF</td>
            <td>STOCK</td>
            <td>EXPECTED</td>
            <td>INVOICED</td>
        </tr>
        <tr>
            <td>{{$data->father->reference}}</td>
            <td>{{number_format($data->father->wholesale_price, 2, ',', '.')}} &euro;</td>
            <td>{{number_format($data->father->price, 2, ',', '.')}} &euro;</td>
            <td>{{number_format(($data->father->price - $data->father->wholesale_price), 2, ',', '.')}} &euro;</td>
            <td>{{$data->father->stock->quantity}}</td>
            <td>{{$data->father->erp_expected->qty_expected}}</td>
            <td>{{$data->father->erp_invoiced->qty_wmfaturado}}</td>
        </tr>
    </table>

    @if(!is_null($data->father->attribute) && (count($data->father->attribute) > 0))
        <div class="alert alert-secondary" style="text-align: center;border-radius: 5px 5px 0 0;margin-bottom: 0;margin-top: 20px;" role="alert">PRODUCT VARIATIONS</div>

        <table class="table table-bordered" style="width: 100%;text-align: center;">
            <tr>
                <td>REFERENCE</td>
                <td>PURCHASE</td>
                <td>PRICE</td>
                <td>DIFF</td>
                <td>STOCK</td>
                <td>EXPECTED</td>
                <td>INVOICED</td>
            </tr>
            @foreach($data->father->attribute AS $son)
                @php
                    $isSelectedAttribute = (int) $son->id_product_attribute === (int) $data->id_product_attribute;
                @endphp
                <tr>
                    <td @if($isSelectedAttribute) class="custom-alert-danger" @endif>{{$son->reference}}</td>
                    <td @if($isSelectedAttribute) class="custom-alert-danger" @endif>{{number_format(($data->father->wholesale_price + $son->wholesale_price), 2, ',', '.')}} &euro;</td>
                    <td @if($isSelectedAttribute) class="custom-alert-danger" @endif>{{number_format(($data->father->price + $son->price), 2, ',', '.')}} &euro;</td>
                    <td @if($isSelectedAttribute) class="custom-alert-danger" @endif>{{number_format((($data->father->price + $son->price) - ($data->father->wholesale_price + $son->wholesale_price)), 2, ',', '.')}} &euro;</td>
                    <td @if($isSelectedAttribute) class="custom-alert-danger" @endif>{{$son->stock->quantity}}</td>
                    <td @if($isSelectedAttribute) class="custom-alert-danger" @endif>{{$son->erp_expected->qty_expected}}</td>
                    <td @if($isSelectedAttribute) class="custom-alert-danger" @endif>{{$son->erp_invoiced->qty_wmfaturado}}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <style>
        .custom-alert-danger {
            background-color: #f8d7da !important;
            border: 1px solid #f5c6cb !important;
            border-radius: 0;
            color: #721c24 !important;
            font-size: 0.875rem;
            padding: 10px;
            text-align: center;
        }
    </style>
</div>
