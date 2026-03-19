@extends('layouts.app')

<script>

    window.onload = function() {
        
        $(document).ready(function () {
    
            $('#ordersTable').DataTable({
                "paging": false,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "searching": false
            });
            
        });
            
    };

    function triggerSweetalert(type, id_order, id_element, reference, id_product, id_product_attribute){

        if(type == 1){
            
            Swal.fire({
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: "YES",
                denyButtonText: "NO",
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#d33",
            }).then((result) => {

                if (result.isConfirmed) {
                    $('#' + id_element + id_order + '_' + id_product + '_' + id_product_attribute).replaceWith('<div id="' + id_element + id_order + '_' + id_product + '_' + id_product_attribute + '"> <i class="fa-solid fa-check" style="font-size: 24px;color: #28a745" onclick="triggerSweetalert(1, ' + id_order + ', \'' + id_element + '\', \'' + reference + '\', ' + id_product + ', ' + id_product_attribute + ')"></i> </div>');
                } else if (result.isDenied) {
                    $('#' + id_element + id_order + '_' + id_product + '_' + id_product_attribute).replaceWith('<div id="' + id_element + id_order + '_' + id_product + '_' + id_product_attribute + '"> <i class="fa-solid fa-xmark" style="font-size: 24px;color: #d33" onclick="triggerSweetalert(1, ' + id_order + ', \'' + id_element + '\', \'' + reference + '\', ' + id_product + ', ' + id_product_attribute + ')"></i> </div>');
                }

                $.ajax({
                    type: 'POST',
                    url: "{{route('backorders.updateInfo')}}",
                    dataType: "json",
                    data: {
                        _token: "{{ csrf_token() }}",
                        type: type,
                        column: id_element,
                        id_order: id_order,
                        id_product: id_product,
                        id_product_attribute: id_product_attribute,
                        reference: reference,
                        value: (result.isConfirmed) ? 1 : 0
                    },
                    success: function(response) {
                        //Swal.fire("Success!", "Your data has been saved.", "success");
                    }
                });
                    
            });

        }

        if(type == 2){

            Swal.fire({
                input: 'text',
                showCancelButton: true        
            }).then((result) => {
                if (result.value) {
                    $('#' + id_element + id_order + '_' + reference.replace('/', '')).replaceWith('<div id="' + id_element + id_order + '_' + reference.replace('/', '') + '"> <span onclick="triggerSweetalert(2, ' + id_order + ', \'' + id_element + '\', \'' + reference + '\', ' + id_product + ', ' + id_product_attribute + ')" style="cursor: pointer">' + result.value + '</span></div>');     

                    $.ajax({
                        type: 'POST',
                        url: "{{route('backorders.updateInfo')}}",
                        dataType: "json",
                        data: {
                            _token: "{{ csrf_token() }}",
                            type: type,
                            column: id_element,
                            id_order: id_order,
                            id_product: id_product,
                            id_product_attribute: id_product_attribute,
                            reference: reference,
                            value: result.value
                        },
                        success: function(response) {
                            //Swal.fire("Success!", "Your data has been saved.", "success");
                        }
                    });
                
                }
            });
        }    
        
        if(type == 3 ){
            
            alert('NOT used!');
            
            /**
            Swal.fire({
                input: 'select',
                inputOptions: {
                    '1': 'ACTION 1',
                    '2': 'ACTION 2',
                    '3': 'ACTION 3'
                },
                showCancelButton: true,
            }).then(function (result) {
                
                console.log(result);
                alert(result.value);
                
                
            });
            **/

        }
        
        if(type == 4){

            Swal.fire({
                title: 'UPDATE ETA',
                html: '<input type="date" id="dataInput" class="swal2-input">',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'OK',
                preConfirm: () => {
                    let dataSelecionada = document.getElementById('dataInput').value;
                    if (!dataSelecionada) {
                        Swal.showValidationMessage('Por favor, selecione uma data!');
                    }
                    return dataSelecionada;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('NEW DELAY DATE', `You saved: <span style="font-weight: bolder"> ${result.value} </span> as new delay date!`, 'success');

                    $('#' + id_element + id_order + '_' + id_product + '_' + id_product_attribute).replaceWith('<div id="' + id_element + id_order + '_' + id_product + '_' + id_product_attribute + '"> <span onclick="triggerSweetalert(4, ' + id_order + ', \'' + id_element + '\', \'' + reference + '\', ' + id_product + ', ' + id_product_attribute + ')" style="cursor: pointer">' + result.value + '</span></div>');                
                    
                    $.ajax({
                        type: 'POST',
                        url: "{{route('backorders.updateInfo')}}",
                        dataType: "json",
                        data: {
                            _token: "{{ csrf_token() }}",
                            type: type,
                            column: id_element,
                            id_order: id_order,
                            id_product: id_product,
                            id_product_attribute: id_product_attribute,
                            reference: reference,
                            value: result.value
                        },
                        success: function(response) {
                            //Swal.fire("Success!", "Your data has been saved.", "success");
                        }
                    });
                    
                }
            });
        
        }
    }

    function openModelUpload(id_product, id_product_attribute, reference){
        

        $.ajax({
            type: 'POST',
            url: "{{route('backorders.getProductInfo')}}",
            dataType: "json",
            data: {
                _token: "{{ csrf_token() }}",
                id_product: id_product,
                id_product_attribute: id_product_attribute,
                reference: reference
            },
            success: function(response) {
    
                console.log(response);  // Mostra o conteúdo no console para depuração
                
                // Se a resposta for HTML, insira diretamente em #contentView
                if (response.html) {
                    $('#contentView').html(response.html);
                } else {
                    // Se for outro tipo de dado, mostre ou faça outra coisa
                    alert('Erro: conteúdo inesperado');
                }
                
            }
        });
                    
        $('#exampleModalLabel').text(reference).css('margin', '0 auto');
        $('#myModal').modal('toggle');
        
    }

    function updateColor(id_order, id_product, id_product_attribute, color){

        $.ajax({
            type: 'POST',
            url: "{{route('backorders.setRowColor')}}",
            dataType: "json",
            data: {
                _token: "{{ csrf_token() }}",
                id_order: id_order,
                id_product: id_product,
                id_product_attribute: id_product_attribute,
                color: color
            },
            success: function(response) {
                
                console.log('.tr_' + id_order + '_' + id_product + '_' + id_product_attribute + ' td');
                $('.tr_' + id_order + '_' + id_product + '_' + id_product_attribute + ' td').css('background-color', color);
            }
        });
        
    }
    
    function getOrderDetails(id_order){

        $('#contentView').html('LOADING!');

        $.ajax({
            type: 'POST',
            url: "{{route('backorders.getBackorderDetail')}}",
            dataType: "json",
            data: {
                _token: "{{ csrf_token() }}",
                id_order: id_order
            },
            success: function(response) {
                
                if (response.html) {
                    $('#contentView').html(response.html);
                } else {
                    // Se for outro tipo de dado, mostre ou faça outra coisa
                    alert('Erro: conteúdo inesperado');
                }
                
            }
        });
                    
        $('#exampleModalLabel').text('ORDER: ' + id_order).css('margin', '0 auto');
        $('#myModal').modal('toggle');  
        
    }
</script>

@section('content')
    <div class="navbar navbar-light customPanel" style="color: red;display: none;">
        <div style="float: left;margin-bottom: 0;padding-bottom: 0;">
            <table class="table table-bordered" border="1" style="text-align: center;margin-bottom: 0;">
                <tr>
                    <td colspan="2" style="width: 150px; background-color: #ff7f7f;">BACKORDERS</td>
                </tr>
                <tr>
                    <td style="width: 150px;color: red; font-weight: bolder;">ASM</td>
                    <td style="width: 150px;color: dodgerblue; font-weight: bolder;">ASD</td>
                </tr>
                <tr style="vertical-align: middle;">
                    <td> {{$counters['asm_backorder']}} </td>
                    <td> {{$counters['asd_backorder']}} </td>
                </tr>
            </table>
        </div>
        
        <div style="text-align: center;float: left; width: calc( 100% - 650px )"> </div>
        
        <div style="float: right;margin-bottom: 0;padding-bottom: 0;">
            <table class="table table-bordered" border="1" style="text-align: center;margin-bottom: 0;">
                <tr>
                    <td colspan="2" style="width: 150px; background-color: #FFD580;">PARTIALS</td>
                </tr>
                <tr>
                    <td style="width: 150px;color: red; font-weight: bolder;">ASM</td>
                    <td style="width: 150px;color: dodgerblue; font-weight: bolder;">ASD</td>
                </tr>
                <tr style="vertical-align: middle;">
                    <td> {{$counters['asm_partial']}} </td>
                    <td> {{$counters['asd_partial']}} </td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="navbar navbar-light customPanel"><div id="sales_history">
        <div id="backorders_panels">
            <table id="ordersTable" class="table table-striped table-bordered table-hover" border="1" style="width: 100%; text-align: center;">
                <thead>
                    <tr style="vertical-align: middle;text-align: center;">
                        <td style="width: 70px;">ORDER</td>
                        <td style="text-align: center;"></td>
                        <td style="text-align: center;">DATE</td>
                        <td style="text-align: center;">STOCK</td>
                        <td style="text-align: center;">REFERENCES</td>
                        <td style="text-align: center;">SUPPLIER</td>
                        <td style="text-align: center;"></td>
                        <td style="text-align: center;">EXP.</td>
                        <td style="text-align: center;">INV.</td>
                        <td style="text-align: center;">ETA</td>
                        <td style="text-align: center;">CONTACT</td>
                        <td style="text-align: center;">ANSWER</td>
                        <td style="text-align: center;">DELAY</td>
                        <td style="text-align: center;"></td>
                        <td style="text-align: center;"></td>
                    </tr>
                </thead>
                <tbody>
                    @foreach($backorders AS $backorder)
                        @if($backorder->stock  < 0)
                        <tr class="main_rows_orders tr_{{$backorder->id_order}}_{{$backorder->original_id_product}}_{{$backorder->original_id_product_attribute}}">
                            <td onclick="getOrderDetails({{ $backorder->id_order }})" style="@if($backorder->store == 'ASD') color: dodgerblue; @endif @if($backorder->color != '#FFF') background-color: {{$backorder->color}} @endif cursor: pointer;">
                                <span> <i class="fa-solid fa-cubes"></i> </span><span style="cursor: pointer;">{{ $backorder->id_order }}</span>
                            </td>
                            <td style="@if($backorder->store == 'ASD') color: dodgerblue; @endif @if($backorder->color != '#FFF') background-color: {{$backorder->color}} @endif">
                                @if( $backorder->id_country > 0)
                                    <img title="{{$backorder->country}}" alt="{{$backorder->country}}" src="https://www.all-stars-motorsport.com/img/flags/Flags_{{$backorder->id_country}}.webp" style="height: 15px; width: 20px;">
                                @endif
                            </td>
                            <td style="@if($backorder->store == 'ASD') color: dodgerblue; @endif @if($backorder->color != '#FFF') background-color: {{$backorder->color}} @endif">{{ date_format(date_create($backorder->order_date), 'Y-m-d') }}</td>
                            <td style="@if($backorder->store == 'ASD' && $backorder->stock<0) color: dodgerblue; @elseif($backorder->store == 'ASM' && $backorder->stock<0) color: black; @else color: green; @endif @if($backorder->color != '#FFF') background-color: {{$backorder->color}} @endif">{{ $backorder->stock }}</td>
                            <td style="@if($backorder->color != '#FFF') background-color: {{$backorder->color}} @endif">
                                @if(isset($backorder->brand))
                                    <div style="text-align: left;float: left;">
                                        <div style="padding: 2px 7px;width: 25px; height: 25px; border-radius: 25px; @if($backorder->type == 'partial') background-color: #FFD580; @else background-color: #ff7f7f; @endif ;float: left; margin-right: 10px;">
                                            {{ $backorder->sold}}
                                        </div> 
                                        <span>{{ $backorder->reference }}</span>
                                    </div>
                                    @if($backorder->id_product_attribute > 0)
                                        <i style="color: dodgerblue; float:right;font-size: 24px; cursor: pointer;" onclick="openModelUpload({{$backorder->id_product}}, {{$backorder->id_product_attribute}}, '{{$backorder->reference}}')" class="fa-solid fa-circle-info"></i>
                                    @endif
                                @endif
                            </td>
                            <td style="@if($backorder->color != '#FFF') background-color: {{$backorder->color}} @endif">@if(isset($backorder->brand)) {{ $backorder->brand }} @else @endif</td>
                            <td style="@if($backorder->color != '#FFF') background-color: {{$backorder->color}} @endif"><img src="/admin/icons/{{$backorder->module}}.webp" style="width: 40px"></td>
                            <td style="@if($backorder->color != '#FFF') background-color: {{$backorder->color}} @endif">
                                <div style="color: orange; margin: 0 auto; font-weight: bolder;"> {{$backorder->erp_qty_expected}} </div>
                            </td>
                            <td style="@if($backorder->color != '#FFF') background-color: {{$backorder->color}} @endif">
                                <div style="color: green; margin: 0 auto; font-weight: bolder;"> {{$backorder->erp_qty_invoiced}} </div>
                            </td>
                            <td style="@if($backorder->color != '#FFF') background-color: {{$backorder->color}} @endif">
                                <div id="container_eta_{{$backorder->id_order}}_{{str_replace('/', '', $backorder->reference)}}"> 
                                    <span onclick="triggerSweetalert(2, {{$backorder->id_order}}, 'container_eta_', '{{$backorder->reference}}', {{$backorder->id_product}}, {{$backorder->id_product_attribute}})" style="cursor: pointer">
                                        @if( strlen($backorder->eta) == 0)
                                            <i style="color: dodgerblue; font-size: 24px; cursor: pointer;" class="fa-solid fa-circle-question"></i>
                                        @else
                                            @if( $backorder->eta == '0000-00-00')
                                                <i style="color: dodgerblue; font-size: 24px; cursor: pointer;" class="fa-solid fa-circle-question"></i>
                                            @else
                                                {{$backorder->eta}}
                                            @endif
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td style="@if($backorder->color != '#FFF') background-color: {{$backorder->color}} @endif">
                                <div id="container_customer_contact_{{$backorder->id_order}}_{{$backorder->id_product}}_{{$backorder->id_product_attribute}}"> 
                                    <span onclick="triggerSweetalert(2, {{$backorder->id_order}}, 'container_customer_contact_', '{{$backorder->reference}}', {{$backorder->id_product}}, {{$backorder->id_product_attribute}})" style="cursor: pointer">
                                        @if( strlen($backorder->customer_contact) == 0)
                                        <i style="color: dodgerblue; font-size: 24px; cursor: pointer;" class="fa-solid fa-circle-question"></i>
                                        @else
                                            {{$backorder->customer_contact}}
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td style="@if($backorder->color != '#FFF') background-color: {{$backorder->color}} @endif">
                                <div id="container_customer_answer_{{$backorder->id_order}}_{{$backorder->id_product}}_{{$backorder->id_product_attribute}}"> 
                                    <span onclick="triggerSweetalert(2, {{$backorder->id_order}}, 'container_customer_answer_', '{{$backorder->reference}}', {{$backorder->id_product}}, {{$backorder->id_product_attribute}})" style="cursor: pointer">
                                        @if( strlen($backorder->customer_answer) == 0)
                                        <i style="color: dodgerblue; font-size: 24px; cursor: pointer;" class="fa-solid fa-circle-question"></i>
                                        @else
                                            {{$backorder->customer_answer}}
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td style="@if($backorder->color != '#FFF') background-color: {{$backorder->color}} @endif">
                                <div id="container_delay_{{$backorder->id_order}}_{{$backorder->id_product}}_{{$backorder->id_product_attribute}}"> 
                                    <span onclick="triggerSweetalert(2, {{$backorder->id_order}}, 'container_delay_', '{{$backorder->reference}}', {{$backorder->id_product}}, {{$backorder->id_product_attribute}})" style="cursor: pointer">{{$backorder->expected_days}}</span>
                                </div>
                            </td>
                            <td style="@if($backorder->color != '#FFF') background-color: {{$backorder->color}} @endif"> @if($backorder->days > $backorder->expected) <span style="color: red;">{{$backorder->days}}</span> @else <span style="color: darkgreen;">{{$backorder->days}}</span> @endif </td>
                            <td style="@if($backorder->color != '#FFF') background-color: {{$backorder->color}} @endif">
                                <div style="display: inline-flex;">
                                    <button onclick="updateColor({{$backorder->id_order}}, {{$backorder->original_id_product}}, {{$backorder->original_id_product_attribute+0}}, '#d4edda')" style="padding: 7px;margin: 0 5px;" class="btn btn-success"> <i class="fa-solid fa-check"></i> </button>
                                    <button onclick="updateColor({{$backorder->id_order}}, {{$backorder->original_id_product}}, {{$backorder->original_id_product_attribute+0}}, '#fff3cd')" style="padding: 7px;margin: 0 5px;" class="btn btn-warning"> <i class="fa-solid fa-triangle-exclamation" style="color: #FFF;"></i> </button>
                                    <button onclick="updateColor({{$backorder->id_order}}, {{$backorder->original_id_product}}, {{$backorder->original_id_product_attribute+0}}, '#f5c6cb')" style="padding: 7px;margin: 0 5px;" class="btn btn-danger">  <i class="fa-solid fa-bolt"></i> </button>                                    
                                </div>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="modal fade bd-example-modal-lg" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Upload</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div id="contentView"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection