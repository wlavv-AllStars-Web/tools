@extends('layouts.app')
@section('content')

    @include("customTools.basePrice.includes.js")
    @include("customTools.basePrice.includes.css")
    
    <script>
        
        function getASDInfo(){
            
            let idManufacturer = $('#selectedManufacturerForASDData').val();

            const select = document.getElementById('selectedManufacturerForASDData');
            const brand = select.options[select.selectedIndex].text;

            $.ajax({
                url: "{{ route('basePrice.pricingData') }}",
                type: 'POST',
                dataType: 'json',
                data: {
                    id_manufacturer: idManufacturer,
                    brand: brand,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.erro) {
                        console.warn('Erro:', response.erro);
                        return;
                    }
        
                    if (!Array.isArray(response) || response.length === 0) {
                        $('#dataContent').html('<p style="margin-top: 30px;">Nenhum resultado encontrado.</p>');
                        return;
                    }

                    const purchaseConv = parseFloat($('#globalPurchaseConvertion').val()) || 1;
                    const sellingConv = parseFloat($('#globalSellingConvertion').val()) || 1;
                    
                    /**
                    let tabela = `
                        <table class="table table-bordered table-striped" style="margin-top: 30px;">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th colspan="2">PURCHASE</th>
                                    <th colspan="2">PRICE</th>
                                </tr>
                                <tr>
                                    <th style="width: 20%;">REFERENCE</th>
                                    <th style="width: 20%;">ASD</th>
                                    <th style="width: 20%;">CONVERTED</th>
                                    <th style="width: 20%;">ASD</th>
                                    <th style="width: 20%;">CONVERTED</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    **/
                    
                    let tabela = `
                        <table class="table table-bordered table-striped" style="margin-top: 30px;">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th colspan="2">PURCHASE</th>
                                </tr>
                                <tr>
                                    <th style="width: 20%;">REFERENCE</th>
                                    <th style="width: 20%;">ASD</th>
                                    <th style="width: 20%;">CONVERTED</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    
                    response.forEach(item => {
                        const precoVenda = parseFloat(item.price) || 0;
                        const precoCompra = parseFloat(item.wholesale_price) || 0;
        
                        const precoVendaConv = (precoVenda * sellingConv).toFixed(2);
                        const precoCompraConv = (precoCompra * purchaseConv).toFixed(2);
                        
                        /**
                        tabela += `
                            <tr>
                                <td>${item.reference}</td>
                                <td>${precoCompra.toFixed(2)}</td>
                                <td>${precoCompraConv}</td>
                                <td>${precoVenda.toFixed(2)}</td>
                                <td>${precoVendaConv}</td>
                            </tr>
                        `;
                        **/
        
                        tabela += `
                            <tr>
                                <td>${item.reference}</td>
                                <td>${precoCompra.toFixed(2)}</td>
                                <td>${precoCompraConv}</td>
                            </tr>
                        `;
                    });
                    
                    /**
                    tabela += `
                                <tr>
                                    <th style="width: 20%;"></th>
                                    <th style="width: 20%;"></th>
                                    <th style="width: 20%;"><button class="btn btn-success" onclick="updatePurchasePrice()"> UPDATE PURCHASE </button></th>
                                    <th style="width: 20%;"></th>
                                    <th style="width: 20%;"></th>
                                </tr>
                            </tbody>
                        </table>
                    `;
                    **/
        
                    tabela += `
                                <tr>
                                    <th style="width: 20%;"></th>
                                    <th style="width: 20%;"></th>
                                    <th style="width: 20%;"><button class="btn btn-success" onclick="updatePurchasePrice()"> UPDATE PURCHASE </button></th>
                                </tr>
                            </tbody>
                        </table>
                    `;
        
                    $('#dataContent').html(tabela);
                    
                },
                error: function(xhr, status, error) {
                    console.error('Erro AJAX:', error);
                }
            });
            
        }
                
        function updatePurchasePrice() {
            let id_manufacturer = $('#selectedManufacturerForASDData').val();
            const select = document.getElementById('selectedManufacturerForASDData');
            const brand = select.options[select.selectedIndex].text;
            const wholesale_convertion = parseFloat($('#globalPurchaseConvertion').val()) || 1;
        
            Swal.fire({
                title: 'Confirmar atualização?',
                text: `Deseja realmente atualizar os preços de compra da marca "${brand}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, atualizar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
        
                    $.ajax({
                        url: "{{ route('basePrice.updatePricing') }}",
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id_manufacturer: id_manufacturer,
                            brand: brand,
                            wholesale_convertion: wholesale_convertion,
                            type: 'wholesale',
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'A atualizar...',
                                text: 'Por favor, aguarde.',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Sucesso!',
                                text: 'Os preços foram atualizados com sucesso.',
                                icon: 'success'
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                title: 'Erro!',
                                text: 'Ocorreu um erro ao atualizar os preços.',
                                icon: 'error'
                            });
                            console.error('Erro AJAX:', error);
                        }
                    });
        
                } else {
                    Swal.fire({
                        title: 'Cancelado',
                        text: 'A atualização foi cancelada.',
                        icon: 'info',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }
        
        /**
        function updatePurchasePrice(){
            
            let id_manufacturer = $('#selectedManufacturerForASDData').val();

            const select = document.getElementById('selectedManufacturerForASDData');
            const brand = select.options[select.selectedIndex].text;
            
            const wholesale_convertion = parseFloat($('#globalPurchaseConvertion').val()) || 1;

            $.ajax({
                url: "{{route('basePrice.updatePricing')}}", 
                type: 'POST',
                dataType: 'json',
                data: {
                    id_manufacturer: id_manufacturer,
                    brand: brand,
                    wholesale_convertion: wholesale_convertion,
                    type: 'wholesale',
                     _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    alert('OK');
                },
                error: function(xhr, status, error) {
                    console.error('Erro AJAX:', error);
                }
            });
            
        }
        **/
        
        function updateSalePrice(){
            
            let id_manufacturer = $('#selectedManufacturerForASDData').val();

            const select = document.getElementById('selectedManufacturerForASDData');
            const brand = select.options[select.selectedIndex].text;
            
            const price_convertion = parseFloat($('#globalSellingConvertion').val()) || 1;

            alert(id_manufacturer);
            alert(brand);
            alert(price_convertion);
            
            /**
            $.ajax({
                url: '', 
                type: 'POST',
                dataType: 'json',
                data: {
                    brand: brand
                },
                success: function(response) {
                    
                    alert('OK');

                },
                error: function(xhr, status, error) {
                    console.error('Erro AJAX:', error);
                }
            });
            **/
        }
    </script>
    <div class="navbar navbar-light customPanel">
        <table style="width: 800pt;text-align: center;margin: 0 auto;">
            <tr>
                <td style="width: 25%;">BRAND</td>
                <td style="width: 25%;">PURCHASE PRICE</td>
                <td style="width: 25%;">PRICE</td>
                <td style="width: 25%;">RUN VERIFICATION</td>
            </tr>
            <tr>
                <td>
                    <select name="id_manufacturer" class="form-select" id="selectedManufacturerForASDData" onchange="getASDInfo()" style="text-align: center;">
                        <option value="">Seleciona uma marca</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id_manufacturer }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>                  
                </td>
                <td><input style="text-align: center;" type="text" id="globalPurchaseConvertion" value="1.0"></td>
                <td><input style="text-align: center;" type="text" id="globalSellingConvertion" value="1.0"></td>
                <td><button class="btn btn-success" onclick="getASDInfo()"> <i class="fa-solid fa-play"></i> </button></td>
            </tr>
            <tr>
                <td colspan="4">
                    <div id="dataContent"></div>
                </td>
            </tr>
        </table>
    </div>
    
    <div style="display: none;">
        <div class="navbar navbar-light customPanel">
            <form action="{{ route('basePrice.store') }}" method="POST" enctype="multipart/form-data" style="float: left;">
                @csrf
                <table style="width: 800px;text-align: center;">
                    <tr>
                        <td>DOWNLOAD TEMPLATE</td>
                        <td>SELECT MANUFACTURER</td>
                        <td>UPLOAD EDITED FILE</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>
                            <a style="margin: 0 auto;" href="/uploads/files/currency.csv" class="btn btn-primary" download="currency"><i class="fa-solid fa-download"></i> TEMPLATE </a>
                        </td>
                        <td>
                            <select name="id_manufacturer" class="form-select">
                                <option value="">Seleciona uma marca</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id_manufacturer }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>                     
                        </td>
                        <td>
                            <input type="file" name="src" id="src" required class="form-control" style="width: 300px;margin: 0 auto;">
                        </td>
                        <td>
                            <button class="btn btn-success" type="submit"><i class="fa-solid fa-floppy-disk"></i> SAVE</button>
                        </td>
                    </tr>
                </table>
            </form>
            <div style="float: right;"><table style="width: 250px;text-align: center;">
                    <tr>
                        <td colspan="4">CURRENT CONVERTION RATE'S</td>
                    </tr>
                    <tr>
                        <td>€</td>
                        <td>$</td>
                        <td>£</td>
                        <td>¥</td>
                    </tr>
                    <tr>
                        <td>{{ number_format($currency_eur->conversion_rate, 2) }}</td>
                        <td>{{ number_format($currency_usd->conversion_rate, 2) }}</td>
                        <td>{{ number_format($currency_gbp->conversion_rate, 2) }}</td>
                        <td>{{ number_format($currency_yen->conversion_rate, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="navbar navbar-light customPanel">
            <table class="table table-bordered table-striped align-middle text-center">
                <tr>
                    <td></td>
                    <td>MANUFACTURER</td>
                    <td># PRODUCTS</td>
                    <td>DEFAULT CURRENCY</td>
                    <td>EUR</td>
                    <td>USD</td>
                    <td>POUNDS</td>
                    <td>YEN</td>
                    <td>UPDATED AT</td>
                    <td></td>
                </tr>
                @foreach($rows as $row)
                <tr>
                    <td>
                        <a style="margin: 0 auto;" href="/uploads/files/basePrices/{{$row->id_manufacturer}}.csv" download="{{$row->name}}">
                            <i class="fa-solid fa-download" style="color: darkgreen"></i>
                        </a>
                    </td>
                    <td>{{$row->name}}</td>
                    <td>{{$row->nr_products}}</td>
                    <td>{{$row->currency}}</td>
                    <td @if( $row->nr_eur > 0) style="background-color:#d1e7dd; color:#0f5132;" @endif>{{$row->nr_eur}}</td>
                    <td @if( $row->nr_usd > 0) style="background-color:#d1e7dd; color:#0f5132;" @endif>{{$row->nr_usd}}</td>
                    <td @if( $row->nr_pounds > 0) style="background-color:#d1e7dd; color:#0f5132;" @endif>{{$row->nr_pounds}}</td>
                    <td @if( $row->nr_yen > 0) style="background-color:#d1e7dd; color:#0f5132;" @endif>{{$row->nr_yen}}</td>
                    <td>{{$row->updated_at->format('Y-m-d')}}</td>
                    <td>
                        <span><i class="fa-solid fa-play" style="color: red;cursor: pointer;" onclick="executeBasePriceUpdate({{$row->id_manufacturer}})"></i></span>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>


@endsection
