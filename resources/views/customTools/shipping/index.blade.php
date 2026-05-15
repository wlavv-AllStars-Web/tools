@extends('layouts.app')
@section('content')

    <script>

        function openModalWithPackaging(id_shipping){
            
            Swal.fire({
                title: "PACKAGING",
                html: $('.packaging_' + id_shipping).html(),
                showCloseButton: false,
                showCancelButton: false,
                focusConfirm: false
            });
        
        }

    </script>

    <div class="row">
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel">
                <a href="{{route('shipping.add')}}" style="color: #666;text-decoration: none;">ADD NEW SHIPMENT ?</A>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel">
                <div style="margin-bottom: 10px; display: flex;">
                    <div id="button_WAITING"    style="width: 33%; float: left;text-align: center;background-color: dodgerblue;  color: #fff; padding: 10px; border: 1px solid #999; font-weight: bolder; cursor: pointer;" onclick="select_waiting()">    WAITING</div>
                    <div id="button_IN_TRANSIT" style="width: 34%; float: left;text-align: center;background-color: transparent; color: #333; padding: 10px; border: 1px solid #999; font-weight: bolder; cursor: pointer;" onclick="select_in_transit()"> IN TRANSIT</div>
                    <div id="button_RECEIVED"   style="width: 33%; float: left;text-align: center;background-color: transparent; color: #333; padding: 10px; border: 1px solid #999; font-weight: bolder; cursor: pointer;" onclick="select_received()">   RECEIVED</div>
                    <div id="button_CANCELLED"  style="width: 33%; float: left;text-align: center;background-color: transparent; color: #333; padding: 10px; border: 1px solid #999; font-weight: bolder; cursor: pointer;" onclick="select_cancelled()">  CANCELLED</div>
                </div>
                @include("customTools.shipping.includes.listing.received",   ['shipments' => $shipments_received,   'suppliers' => $suppliers, 'carriers' => $carriers])
                @include("customTools.shipping.includes.listing.in_transit", ['shipments' => $shipments_in_transit, 'suppliers' => $suppliers, 'carriers' => $carriers])
                @include("customTools.shipping.includes.listing.waiting",    ['shipments' => $shipments_waiting,    'suppliers' => $suppliers, 'carriers' => $carriers])
                @include("customTools.shipping.includes.listing.cancelled",  ['shipments' => $shipments_cancelled,  'suppliers' => $suppliers, 'carriers' => $carriers])
            </div>
        </div>
    </div>

    <script>
        
        function select_received(){
            
            $('#button_RECEIVED').css('color', '#FFF').css('background-color', 'dodgerblue');
            $('#button_IN_TRANSIT').css('color', '#333').css('background-color', 'transparent');
            $('#button_WAITING').css('color', '#333').css('background-color', 'transparent');
            $('#button_CANCELLED').css('color', '#333').css('background-color', 'transparent');

            $('.containers').css('display', 'none');
            $('#container_RECEIVED').css('display', 'block');
        }
        
        function select_in_transit(){
            
            $('#button_RECEIVED').css('color', '#333').css('background-color', 'transparent');
            $('#button_IN_TRANSIT').css('color', '#FFF').css('background-color', 'dodgerblue');
            $('#button_WAITING').css('color', '#333').css('background-color', 'transparent');
            $('#button_CANCELLED').css('color', '#333').css('background-color', 'transparent');

            $('.containers').css('display', 'none');
            $('#container_IN_TRANSIT').css('display', 'block');

        }
        
        function select_waiting(){
            
            $('#button_RECEIVED').css('color', '#333').css('background-color', 'transparent');
            $('#button_IN_TRANSIT').css('color', '#333').css('background-color', 'transparent');
            $('#button_WAITING').css('color', '#fff').css('background-color', 'dodgerblue');
            $('#button_CANCELLED').css('color', '#333').css('background-color', 'transparent');

            $('.containers').css('display', 'none');
            $('#container_WAITING').css('display', 'block');

        }
        
        function select_cancelled(){
            
            $('#button_RECEIVED').css('color', '#333').css('background-color', 'transparent');
            $('#button_IN_TRANSIT').css('color', '#333').css('background-color', 'transparent');
            $('#button_WAITING').css('color', '#333').css('background-color', 'transparent');
            $('#button_CANCELLED').css('color', '#fff').css('background-color', 'dodgerblue');
            
            $('.containers').css('display', 'none');
            $('#container_CANCELLED').css('display', 'block');

        }
        
        $(".searchInput").focus(function () {
            $('.searchInput').val();
            
            console.log('focus');
        });

        function searchFor(tag, element){
            
            valor = element.val();
            
            attr = 'search_' + tag;
            
            let termo = valor.toLowerCase();
        
            document.querySelectorAll('tr.list_shipments_rows').forEach(tr => {
                let carrier = tr.getAttribute(attr) || ''; 
                if (carrier.toLowerCase().includes(termo)) {
                    tr.style.display = '';
                } else {
                    tr.style.display = 'none';
                }
            });
        }
        
        function downloadData(status){

            $.ajax({
                type: 'POST',
                url: "{{route('shipping.downloadData')}}",
                data: {
                    _token: "{{ csrf_token() }}",
                    status: status
                },
                success: function(response) {
                    if (response.file) {
                        let fileUrl = "/uploads/" + response.file; // Caminho no storage público
        
                        let link = document.createElement("a");
                        link.href = fileUrl;
                        link.download = response.file.split('/').pop(); // Apenas o nome do arquivo
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        alert("Erro ao gerar o CSV.");
                    } 
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
    
    <style>
        td.type_CONTAINER_20, 
        td.type_CONTAINER_40, 
        td.type_PALLET, 
        td.type_BOX,
        td.route_SEA_LCL,
        td.route_SEA_FCL,
        td.route_AIR, 
        td.route_LAND,
        td.incoterm_CPT,
        td.incoterm_DAP,
        td.incoterm_DDP,
        td.incoterm_EXW,
        td.incoterm_FCA, 
        td.incoterm_FOB{ color: #000;}
        
        #container_WAITING{    display: block; }
        #container_IN_TRANSIT{ display: none; }
        #container_RECEIVED{   display: none; }
        #container_CANCELLED{   display: none; }
    

        .barra-progresso-container {
            width: 100%;
            height: 50px;
            background-color: #ddd;
            border-radius: 25px;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .barra-progresso {
            height: 100%;
            border-radius: 25px;
            background: linear-gradient(to right, red, yellow, green);
            width: 70%; /* Modifique este valor para simular a variação */
            transition: width 0.3s ease;
        }

        .texto {
            position: relative;
            text-align: center;
            top: 50%;
            transform: translateY(-50%);
            font-weight: bold;
            color: white;
        }
        
        .left_column{ text-align: right; padding-right: 5px;font-weight: bolder; width: 50%;}
        .right_column{ text-align: left; padding-left: 5px;}
    </style>
@endsection