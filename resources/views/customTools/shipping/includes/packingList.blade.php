@extends('layouts.app') {{-- ajusta ao teu layout --}}

@section('content')


    <style>
        input{ text-align: center; background-color: #FFF !important;}
    </style>

    <form method="POST">
        @csrf

        <div class="row">
            <div class="col-lg-12">
                <div class="navbar navbar-light customPanel">
                    <div style="margin-bottom: 10px;text-align: left;">
                        <button type="submit" formaction="{{ route('shipping.packingList.exportXls') }}" class="btn btn-success"> Export Excel (.xls) </button> | <strong>PO IDs:</strong> {{ implode(', ', $poIds) }}
                    </div>
                </div>
            </div>
        </div>

    
        <div class="row">
            <div class="col-lg-12">
                <div class="navbar navbar-light customPanel">
                    
                    <div class="row" style="text-align:left;">
                    
                        {{-- SHIPPER --}}
                        <div class="col-lg-4">
                            <div class="navbar navbar-light customPanel" style="padding:12px;">
                                <h5 style="margin:0 0 10px 0;">SHIPPER</h5>
                    
                                <div class="input-group input-group-sm mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="width:120px;">Company</span>
                                    </div>
                                    <input style="text-align: left;" class="form-control" name="shipper[company]" value="{{ $supplier?->name }}">
                                </div>
                    
                                <div class="input-group input-group-sm mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="width:120px;">Address</span>
                                    </div>
                                    <input style="text-align: left;" class="form-control" name="shipper[address]" value="{{ $supplier_map?->address }}">
                                </div>
                    
                                <div class="input-group input-group-sm mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="width:120px;">City</span>
                                    </div>
                                    <input style="text-align: left;" class="form-control" name="shipper[city]" value="">
                                </div>
                    
                                <div class="input-group input-group-sm mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="width:120px;">Country</span>
                                    </div>
                                    <input style="text-align: left;" class="form-control" name="shipper[country]" value="{{ $supplier_map?->country }}">
                                </div>
                    
                                <div class="input-group input-group-sm mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="width:120px;">EORI</span>
                                    </div>
                                    <input style="text-align: left;" class="form-control" name="shipper[eori]" value="">
                                </div>
                    
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="width:120px;">VAT</span>
                                    </div>
                                    <input style="text-align: left;" class="form-control" name="shipper[vat]" value="">
                                </div>
                            </div>
                        </div>
                    
                        {{-- RECEIVER --}}
                        <div class="col-lg-4">
                            <div class="navbar navbar-light customPanel" style="padding:12px;">
                                <h5 style="margin:0 0 10px 0;">RECEIVER</h5>
                    
                                <div class="input-group input-group-sm mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="width:120px;">Company</span>
                                    </div>
                                    <input style="text-align: left;" class="form-control" name="receiver[company]" value="All Stars Distribution">
                                </div>
                    
                                <div class="input-group input-group-sm mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="width:120px;">Address</span>
                                    </div>
                                    <input style="text-align: left;" class="form-control" name="receiver[address]" value="Z.I de Grandra - Lote 6">
                                </div>
                    
                                <div class="input-group input-group-sm mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="width:120px;">City</span>
                                    </div>
                                    <input style="text-align: left;" class="form-control" name="receiver[city]" value="4930-311 - Valença">
                                </div>
                    
                                <div class="input-group input-group-sm mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="width:120px;">Country</span>
                                    </div>
                                    <input style="text-align: left;" class="form-control" name="receiver[country]" value="Portugal">
                                </div>
                    
                                <div class="input-group input-group-sm mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="width:120px;">EORI</span>
                                    </div>
                                    <input style="text-align: left;" class="form-control" name="receiver[eori]" value="PT513881387">
                                </div>
                    
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="width:120px;">VAT</span>
                                    </div>
                                    <input style="text-align: left;" class="form-control" name="receiver[vat]" value="PT513881387">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="navbar navbar-light customPanel" style="padding:12px;">
                                <h5 style="margin:0 0 10px 0;">SHIPPING DETAILS</h5>
                                <textarea rows="11" style="text-align:left;" class="form-control" name="shippingDetail">{{$formattedText}}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    
        <div class="row">
            <div class="col-lg-12">
                <div class="navbar navbar-light customPanel">

                    <div id="toPrint">
                        <table class="table table-bordered table-sm text-center" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="width: 300px;text-align: right;">REFERENCE</th>
                                    <th style="width: 500px;text-align: left;">Name</th>
                                    <th>HS Code</th>
                                    <th>WEIGHT<br><small>( kg )</small></th>
                                    <th>WIDTH<br><small>( cm )</small></th>
                                    <th>HEIGHT<br><small>( cm )</small></th>
                                    <th>DEPTH<br><small>( cm )</small></th>
                                    <th>PRICE</th>
                                    <th>QTY</th>
                                    <th>TOTAL ROW</th>
                                </tr>
                            </thead>
                            <tbody>
                            @php
                                $totalWeight = 0;
                                $totalQty = 0;
                                $totalPriceRow = 0;
                            @endphp
                            
                            @forelse($rows as $i => $r)
                            
                                @php
                                    $weight = (float)$r->weight;
                                    $qty = (float)$r->quantidade;
                            
                                    $totalWeight += ($weight * $qty);
                                    $totalQty += $qty;
                                    $totalPriceRow += $r->row_price;
                                @endphp
                            
                                <tr>
                                    <td>
                                        <input class="form-control" name="items[{{ $i }}][referencia]" value="{{ $r->referencia }}" style="text-align: right;">
                                    </td>
                                    <td>
                                        <input class="form-control" name="items[{{ $i }}][name]" value="{{ $r->name }}" style="text-align: left;">
                                    </td>
                                    <td>
                                        <input class="form-control" name="items[{{ $i }}][hs_code]" value="{{ $r->hs_code }}">
                                    </td>
                                    <td>
                                        <input class="form-control" name="items[{{ $i }}][weight]" value="{{ number_format($weight, 2, '.', '') }}">
                                    </td>
                                    <td>
                                        <input class="form-control" name="items[{{ $i }}][comprimento]" value="{{ number_format((float)$r->comprimento, 2, '.', '') }}">
                                    </td>
                                    <td>
                                        <input class="form-control" name="items[{{ $i }}][largura]" value="{{ number_format((float)$r->largura, 2, '.', '') }}">
                                    </td>
                                    <td>
                                        <input class="form-control" name="items[{{ $i }}][altura]" value="{{ number_format((float)$r->altura, 2, '.', '') }}">
                                    </td>
                                    <td>
                                        <input class="form-control" name="items[{{ $i }}][wholesale_price]" value="{{  number_format((float)$r->wholesale_price, 2, '.', '') }}">
                                    </td>
                                    <td>
                                        <input class="form-control" name="items[{{ $i }}][quantidade]" value="{{ $qty }}">
                                    </td>
                                    <td>
                                        <input class="form-control" name="items[{{ $i }}][row_price]" value="{{  number_format((float)$r->row_price, 2, '.', '') }}">
                                    </td>
                                </tr>
                            
                            @empty
                                <tr><td colspan="7">Sem linhas elegíveis (wm_faturado <= qty_received).</td></tr>
                            @endforelse
                            </tbody>
                            
                            <tfoot>
                                <tr style="font-weight: bold; background-color: #f5f5f5;">
                                    <td style="text-align: right;">TOTALS</td>
                                    <td colspan="2"></td>
                                    <td>
                                        <input class="form-control" name="totals[total_weight]" id="total_weight" value="{{ number_format($totalWeight, 2, '.', '') }}">
                                    </td>
                                    <td colspan="4"></td>
                                    <td>
                                        <input class="form-control" name="totals[total_qty]" id="total_qty" value="{{ number_format($totalQty, 0, '.', '') }}">
                                    </td>
                                    <td>
                                        <input class="form-control" name="totals[total_price]" id="total_price" value="{{ number_format($totalPriceRow, 2, '.', '') }}">
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>

<script>
function recalcTotals() {

    let totalWeight = 0;
    let totalQty = 0;

    document.querySelectorAll('input[name^="items"]').forEach(function(el) {

        if (el.name.includes('[weight]')) {
            let index = el.name.match(/\[(\d+)\]/)[1];
            let weight = parseFloat(el.value) || 0;

            let qtyInput = document.querySelector('input[name="items['+index+'][quantidade]"]');
            let qty = parseFloat(qtyInput.value) || 0;

            totalWeight += weight * qty;
        }

        if (el.name.includes('[quantidade]')) {
            totalQty += parseFloat(el.value) || 0;
        }
    });

    document.getElementById('total_weight').value = totalWeight.toFixed(2);
    document.getElementById('total_qty').value = totalQty.toFixed(0);
}

// recalcula sempre que alterarem peso ou quantidade
document.addEventListener('input', function(e) {
    if (e.target.name.includes('[weight]') || e.target.name.includes('[quantidade]')) {
        recalcTotals();
    }
});
</script>


@endsection
