@extends('layouts.app')

@push('styles')
    <style>
        @media (max-width:760px){
            #headerBreadcrumbsContainer{display:none!important;}
        }
    </style>
@endpush

@section('content')
    @include('customTools.logistics.inventory.partials.styles')

    <div class="inventory-pda-wrap">
        @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif

        <form method="POST" action="{{ route('logistics.tools.inventory.work.store') }}">
            @csrf
            <div class="inventory-pda-scan">
                <label>Celula</label>
                <div class="inventory-pda-scan-row">
                    <input id="cellScanInput" class="form-control inventory-pda-input" name="cell_scan" value="" placeholder="Scan celula" autofocus autocomplete="off">
                    <button class="btn btn-primary" type="submit" title="Abrir celula"><i class="fa-solid fa-location-dot"></i></button>
                </div>
            </div>
        </form>
    </div>

    <style>
        .inventory-pda-wrap{max-width:640px;margin:0 auto;padding:10px 8px;}
        .inventory-pda-header{display:grid;grid-template-columns:1fr;gap:10px;align-items:center;text-align:center;margin-bottom:10px;}
        .inventory-pda-header h3{margin:0;font-size:22px;font-weight:800;color:#222;line-height:1;}
        .inventory-pda-header strong{display:block;font-size:34px;color:#111;line-height:1.1;}
        .inventory-pda-scan{border:1px solid #ddd;border-radius:6px;background:#fff;padding:10px;margin-bottom:10px;text-align:center;}
        .inventory-pda-scan label{display:block;font-size:12px;text-transform:uppercase;color:#666;font-weight:800;margin-bottom:4px;}
        .inventory-pda-scan-row{display:grid;grid-template-columns:1fr 58px;gap:8px;align-items:center;}
        .inventory-pda-scan-row .btn{height:54px;font-weight:800;}
        .inventory-pda-input{height:54px;font-size:28px;text-align:center;font-weight:800;border:2px solid #aaa;border-radius:6px;background:#fff;}
    </style>

    <script>
        $('#cellScanInput').on('keyup', function(event) {
            if (event.keyCode === 13) {
                event.preventDefault();
                $(this).closest('form').submit();
            }
        });
    </script>
@endsection
