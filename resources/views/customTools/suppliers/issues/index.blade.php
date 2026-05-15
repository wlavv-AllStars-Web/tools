@extends('layouts.app')
@section('content')

    {{--
        <div class="navbar navbar-light customPanel categorList">
            <div id="buttonIssuesSupplier" class="multiButton" onclick="select_issues_tab($(this), 'supplier')">SUPPLIER</div>
            <div id="buttonIssuesDelivery" class="multiButton multiButtonActive" onclick="select_issues_tab($(this), 'delivery')">DELIVERY</div>
            <div id="buttonIssuesWarranty" class="multiButton" onclick="select_issues_tab($(this), 'warranty')">WARRANTY</div>
        </div>
    --}}

    @if( $type == 1 )
        @include("customTools.suppliers.issues.includes.list.supplier", ['issues' => $supplierIssues ])
    @else
        @include("customTools.suppliers.issues.includes.list.delivery", ['issues' => $deliveryIssues ])
    @endif
    
    {{--
    @include("customTools.suppliers.issues.includes.list.warranty", ['issues' => $warrantyIssues ])
    --}}
    
    <style>
        /**
        #container-warranty{ display: none; }
        #container-supplier{ display: none; }
        **/
        .multiButton{ float: left;text-align: center;color: #333; padding: 10px; border: 1px solid #999; font-weight: bolder; cursor: pointer; }
        .multiButtonActive{ background-color: dodgerblue;}
        
        .item_done{ color: darkgreen !important; background-color: #d4edda !important; }
    </style>
    
    <script>
        
        function select_issues_tab(element, tag){
            
            $('#buttonIssuesDelivery').removeClass('multiButtonActive');
            $('#buttonIssuesWarranty').removeClass('multiButtonActive');
            $('#buttonIssuesSupplier').removeClass('multiButtonActive');
            
            $(element).addClass('multiButtonActive');
            
            $('.containerSupplierBlock').css('display', 'none');

            $('#container-'+tag).css('display', 'block');

        }
    </script>
@endsection