@extends('layouts.app')
@section('content')

    <style>
        #yieldContent{ display: block !important; }
    </style>
    <div id="modalSupplierMapContent">
        @include("customTools.suppliers.map.includes.modals.modal")
        <div class="modal fade" id="myModalEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"></div>
    </div>
    
    <div style="width: 100%; height: 10px;"></div>

    <div class="navbar navbar-light customPanel categorList" style="margin: 5px;text-align: right;">
        <button class="btn btn-success" onclick="$('#myModal').modal('toggle');"><i class="fa-solid fa-plus"></i> ADD SUPPLIER INFO</button>
    </div>
    
    @foreach($supplierMap AS $supplier)
        <div class="navbar navbar-light customPanel categorList" style="width: calc( ( 100% / 2) - 10px );float:left;display: flex;margin: 5px;">
            <div style="width: 120px; background-color: #eee;float:left;border-radius: 5px;border: 1px solid #888;cursor: pointer;" onclick="$('.details_panel').css('display', 'none');$('#details_{{$supplier['id_manufacturer']}}').css('display', 'block');">
               @if($supplier['id_manufacturer'] > 0)
                   <img src="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/img/m/{{$supplier['id_manufacturer']}}.jpg" style="width: 117px;border-radius: 5px;">
               @else
                   <img src="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/img/tmp/supplier_{{$supplier['supplier_map']->supplier->id_supplier}}.jpg" style="width: 117px;border-radius: 5px;">
               @endif
            </div>
            <div style="width: calc( 100% - 200px); float: left;padding: 0 10px;">
                <div><span style="color: #000; font-weight: bolder;">SUPPLIER:       </span><span style="color: #333;"> {{$supplier['supplier_map']->supplier->name}}</span></div>
                @if( is_null( $supplier['supplier_map'] )) 
                    <div><span style="color: #000; font-weight: bolder;">BRAND:  </span><span style="color: #333;"> - </span></div>
                    <div><span style="color: #000; font-weight: bolder;">CONTACT:       </span><span style="color: #333;"> - </span></div>
                    <div><span style="color: #000; font-weight: bolder;">EMAIL:         </span><span style="color: #333;"> - </span></div>
                    <div><span style="color: #000; font-weight: bolder;">PHONE:         </span><span style="color: #333;"> - </span></div>
                @else
                    <div><span style="color: #000; font-weight: bolder;">BRAND:  </span><span style="color: #333;"> {{ (isset($supplier['supplier_map']->manufacturer)) ? $supplier['supplier_map']->manufacturer->name : 'NONE'}}</span></div>
                    <div><span style="color: #000; font-weight: bolder;">CONTACT:       </span><span style="color: #333;"> {{$supplier['supplier_map']->contact}}</span></div>
                    <div><span style="color: #000; font-weight: bolder;">EMAIL:         </span><span style="color: #333;" onclick="copyToClipboard(this)">{!! str_replace(';', '</span> | <span onclick="copyToClipboard(this)">', $supplier['supplier_map']->email) !!}</span></div>
                    <div><span style="color: #000; font-weight: bolder;">PHONE:         </span><span style="color: #333;" onclick="copyToClipboard(this)"> {{$supplier['supplier_map']->phone}}</span></div>
                @endif
                    <div><span style="color: #000; font-weight: bolder;">WARRANTY:         </span><span style="color: #333;" onclick="copyToClipboard(this)"> {{$supplier['supplier_map']->warranty_link}}</span></div>
            </div>
            <div style="width: 80px; float: left;padding: 0 10px;">
                <button class="btn btn-warning" style="margin-top: 10px;color: #FFF;" onclick="getModelContentAndShow({{$supplier['supplier_map']->id}})"><i class="fa-solid fa-pen"></i></button>
                <a href="{{route('admin.tools.oms.supplier_terms.index', $supplier['supplier_map']->supplier->id_supplier)}}" class="btn btn-primary" style="margin-top: 10px;color: #FFF;"><i class="fa-solid fa-industry"></i></A>
                
                
            </div>
            <div id="details_{{$supplier['id_manufacturer']}}" class="details_panel" style="display: none; width: 100%;margin-top: 10px;">
                <div style="width:100%; float: left;padding: 0;">
                    @if( !is_null( $supplier['supplier_map'] )) 
                        <table style="text-center;width: 100%;">
                            <tr style="display: none;">
                                <td colspan="2" style="height: 40px;"></td>
                            </tr>
                            <tr style="display: none;">
                                <td colspan="2" style="width: 100%;text-align: center;background-color: #efefef;border: 1px solid #999;"><span style="color: #000; font-weight: bolder;">RESOURCES</span></td>
                            </tr>
                            <tr style="display: none;">
                                <td colspan="2" style="width: 100%;">
                                    <table style="text-align: center;width: 100%;">
                                        <tr>
                                            <td style="width: 20%; color: #000; font-weight: bolder;"></td>
                                            <td style="width: 20%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">125px</span></td>
                                            <td style="width: 20%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">600px</span></td>
                                            <td style="width: 20%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">VIDEO</span></td>
                                            <td style="width: 20%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">ACTIVE</span></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: right;"><span style="color: #000; font-weight: bolder;">MANUFACTURER</span></td>
                                            <td>@if($supplier['supplier_map']->manufacturer_125 == 1) <i class="fa-solid fa-check" style="color: green;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                            <td>@if($supplier['supplier_map']->manufacturer_600 == 1) <i class="fa-solid fa-check" style="color: green;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                            <td>-</span></td>
                                            <td>@if( isset($supplier['supplier_map']->manufacturer) && ( $supplier['supplier_map']->manufacturer->active ) == 1) <i class="fa-solid fa-check" style="color: green;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: right;"><span style="color: #000; font-weight: bolder;">SUPPLIER</span></td>
                                            <td>@if($supplier['supplier_map']->supplier_125 == 1) <i class="fa-solid fa-check" style="color: green;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                            <td>@if($supplier['supplier_map']->supplier_600 == 1) <i class="fa-solid fa-check" style="color: green;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                            <td>@if($supplier['supplier_map']->video == 1) <i class="fa-solid fa-check" style="color: green;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                            <td>@if($supplier['supplier_map']->supplier->active == 1) <i class="fa-solid fa-check" style="color: green;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr style="display: none;">
                                <td colspan="2" style="height: 40px;"></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="width: 100%;text-align: center;background-color: #efefef;border: 1px solid #999;"><span style="color: #000; font-weight: bolder;">ADDRESS</span></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <table style="text-align: center;width: 100%;">
                                        <tr>
                                            <td style="color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">ADDRESS</span></td>
                                            <td style="color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">COUNTRY</span></td>
                                            <td style="color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">WEBSITE</span></td>
                                        </tr>
                                        <tr>
                                            <td style="color: #000;" onclick="copyToClipboard(this)"> {{$supplier['supplier_map']->address}}</td>
                                            <td style="color: #000;" onclick="copyToClipboard(this)"> {{$supplier['supplier_map']->country}}</td>
                                            <td style="color: #000;"> <a style="text-decoration: none; color: dodgerblue;" target="_blank" href="{{$supplier['supplier_map']->website}}">{{$supplier['supplier_map']->website}}</a></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="height: 5px;"></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="width: 100%;text-align: center;background-color: #efefef;border: 1px solid #999;"><span style="color: #000; font-weight: bolder;">STORE DETAILS</span></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="width: 100;">
                                    <table style="text-align: center;width: 100%;">
                                        <tr>
                                            <td style="width: 25%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">DEALER WEBSITE</span></td>
                                            <td style="width: 25%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">WARRANTY</span></td>
                                            <td style="width: 25%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">DESCRIPTION</span></td>
                                            <td style="width: 25%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">EAN-13</span></td>
                                        </tr>
                                        <tr>
                                            <td style="color: #000;"> {{$supplier['supplier_map']->dealer_website}}</td>
                                            <td style="color: #000;"> @if($supplier['supplier_map']->warranty == 1) <i class="fa-solid fa-check" style="color: green;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                            <td style="color: #000;"> @if($supplier['supplier_map']->description == 1) <i class="fa-solid fa-check" style="color: green;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                            <td style="color: #000;"> @if($supplier['supplier_map']->ean13 == 1) <i class="fa-solid fa-check" style="color: green;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="height: 5px;"></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="width: 100%;text-align: center;background-color: #efefef;border: 1px solid #999;"><span style="color: #000; font-weight: bolder;">CREDENTIALS </span></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="width: 100%;">
                                    <table style="text-align: center;width: 100%;">
                                        <tr>
                                            <td style="width: 33%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">LINK</span></td>
                                            <td style="width: 34%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">USERNAME</span></td>
                                            <td style="width: 33%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">PASSWORD</span></td>
                                        </tr>
                                        <tr>
                                            <td style="color: #000;">@if(strlen($supplier['supplier_map']->b2b_link) > 0) <a style="text-decoration: none; color: dodgerblue;" target="_blank" href="{{$supplier['supplier_map']->b2b_link}}">LINK</a>@else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                            <td style="color: #000;" onclick="copyToClipboard(this)">@if(strlen($supplier['supplier_map']->b2b_username) > 0)  {{$supplier['supplier_map']->b2b_username}} @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                            <td style="color: #000;" onclick="copyToClipboard(this)">@if(strlen($supplier['supplier_map']->b2b_password) > 0)  {{$supplier['supplier_map']->b2b_password}} @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="height: 5px;"></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="width: 100%;text-align: center;background-color: #efefef;border: 1px solid #999;"><span style="color: #000; font-weight: bolder;">RESOURCES </span></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="width: 100%;">
                                    <table style="text-align: center;width: 100%;">
                                        <tr>
                                            <td style="width: 25%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">PICS</span></td>
                                            <td style="width: 25%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">CATALOGUE</span></td>
                                            <td style="width: 25%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">INSTRUCTIONS</span></td>
                                            <td style="width: 25%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">INVENTORY</span></td>
                                        </tr>
                                        <tr>
                                            <td style="color: #000;">@if(strlen($supplier['supplier_map']->pics) > 0) <a style="text-decoration: none; color: dodgerblue;" target="_blank" href="{{$supplier['supplier_map']->pics}}">LINK</a>@else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                            <td style="color: #000;">@if(strlen($supplier['supplier_map']->catalogue) > 0) <a style="text-decoration: none; color: dodgerblue;" target="_blank" href="{{$supplier['supplier_map']->catalogue}}">LINK</a>@else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                            <td style="color: #000;">@if(strlen($supplier['supplier_map']->instructions) > 0) <a style="text-decoration: none; color: dodgerblue;" target="_blank" href="{{$supplier['supplier_map']->instructions}}">LINK</a>@else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                            <td style="color: #000;">@if(strlen($supplier['supplier_map']->inventory) > 0) <a style="text-decoration: none; color: dodgerblue;" target="_blank" href="{{$supplier['supplier_map']->inventory}}">LINK</a>@else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            
                            <tr>
                                <td colspan="2" style="height: 5px;"></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="width: 100%;text-align: center;background-color: #efefef;border: 1px solid #999;"><span style="color: #000; font-weight: bolder;">PAYMENT DETAILS </span></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="width: 100%;">
                                    <table style="text-align: center;width: 100%;">
                                        <tr>
                                            <td style="width: 15%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">INCOTERM</span></td>
                                            <td style="width: 15%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">CURRENCY</span></td>
                                            <td style="width: 15%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">DISCOUNT</span></td>
                                            <td style="width: 15%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">TERMS</span></td>
                                            <td style="width: 40%; color: #000; font-weight: bolder;"><span style="color: #000; font-weight: bolder;">BANK DETAILS</span></td>
                                        </tr>
                                        <tr>
                                            <td style="color: #000;">@if(strlen($supplier['supplier_map']->pics) > 0) {{$supplier['supplier_map']->incoterm}} @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                            <td style="color: #000;">@if(strlen($supplier['supplier_map']->currency) > 0) {{$supplier['supplier_map']->currency}} @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                            <td style="color: #000;">@if(strlen($supplier['supplier_map']->discount) > 0) {{$supplier['supplier_map']->discount}} @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                            <td style="color: #000;">@if(strlen($supplier['supplier_map']->terms) > 0) {{$supplier['supplier_map']->terms}} @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                            <td style="color: #000;">@if(strlen($supplier['supplier_map']->iban) > 0) <span onclick="copyToClipboard(this)">{{$supplier['supplier_map']->iban}} </span> | <span onclick="copyToClipboard(this)">{{$supplier['supplier_map']->swift}} </span>@else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    @else
                        <div style="color: #000; font-weight: bolder;text-align: center;">WITHOUT DETAILS AVAILABLE!</div>
                        <div style="text-align: center;">
                            <button class="btn btn-warning" style="margin-top: 10px;color: #FFF;" onclick="$('#myModal').modal('toggle');"><i class="fa-solid fa-pen"></i> ADD SUPPLIER INFO</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
    
    <script>
    
        function getModelContentAndShow(id){

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: '{{route("suppliersMap.modal")}}',
                method: 'POST',
                dataType: 'json',
                data: {
                    id: id
                },
                success: function(response) {
                    
                    $('#myModalEdit').replaceWith(response.html);
                    $('#myModalEdit').modal('toggle');
                },
                error: function(xhr) {
                    console.error('Erro ao carregar modal:', xhr.responseText);
                    alert('Erro ao carregar o modal');
                }
            });
    
        }
    
        function copyToClipboard(el) {
            const text = el.innerText;
    
            navigator.clipboard.writeText(text).then(() => {
                el.style.backgroundColor = '#d1fae5';
                setTimeout(() => {
                    el.innerText = text;
                    el.style.backgroundColor = '';
                }, 1500);
            }).catch(err => {
                console.error('Erro ao copiar: ', err);
            });
        }
    </script>
@endsection
