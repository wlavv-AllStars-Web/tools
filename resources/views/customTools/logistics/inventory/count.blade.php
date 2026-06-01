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
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif

        @if(!$cellConfirmed)
            <form method="POST" action="{{ route('logistics.tools.inventory.count.store', $schedule->id) }}">
                @csrf
                <div class="inventory-pda-scan">
                    <label>Celula</label>
                    <div class="inventory-pda-scan-row">
                        <input id="cellScanInput" class="form-control inventory-pda-input" name="cell_scan" value="" placeholder="Scan celula" autofocus autocomplete="off">
                        <button class="btn btn-primary" type="submit" title="Confirmar celula"><i class="fa-solid fa-location-dot"></i></button>
                    </div>
                </div>
            </form>
        @else
            <form method="POST" action="{{ route('logistics.tools.inventory.count.store', $schedule->id) }}">
            @csrf
            <div class="inventory-pda-location">{{ $schedule->cell }}</div>

            <div class="inventory-pda-scan">
                <label>EAN / Referencia</label>
                <div class="inventory-pda-scan-row">
                    <input id="scanInput" class="form-control inventory-pda-input" name="scan_code" value="" placeholder="Scan" autofocus autocomplete="off">
                    <button class="btn btn-primary" type="submit" title="Registar"><i class="fa-solid fa-barcode"></i></button>
                </div>
            </div>
            </form>

            <form method="POST" action="{{ route('logistics.tools.inventory.count.store', $schedule->id) }}" onsubmit="return confirm('Concluir inventario desta celula?');">
                @csrf
                <input type="hidden" name="finish" value="1">
                <button class="btn btn-success w-100 mb-3" type="submit"><i class="fa-solid fa-check"></i> {{ $recountActive ? 'Concluir recontagem' : 'Concluir inventario' }}</button>
            </form>

            @if($counts->isEmpty())
                <div class="alert alert-warning mb-0">Nao foram encontrados produtos nesta celula/coluna.</div>
            @else
                <div class="inventory-pda-list">
                    @foreach($counts as $index => $row)
                        @php
                            $scanned = (int) ($row->counted_quantity ?? 0);
                            $isDiff = $row->counted_quantity !== null && $scanned !== (int) $row->current_quantity;
                        @endphp
                        <div class="inventory-pda-item {{ $row->counted_quantity !== null ? ($isDiff ? 'diff' : 'done') : '' }}">
                            <div class="inventory-pda-line">
                                <div class="inventory-pda-product-code">
                                    <strong>{{ $row->reference ?: '-' }}</strong>
                                    <small>{{ $row->ean13 ?: '-' }}</small>
                                </div>
                                <b>{{ $scanned }}</b>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>

    <style>
        .inventory-pda-wrap{max-width:640px;margin:0 auto;padding:10px 8px;}
        .inventory-pda-header{display:grid;grid-template-columns:1fr;gap:10px;align-items:center;text-align:center;margin-bottom:10px;}
        .inventory-pda-header h3{margin:0;font-size:22px;font-weight:800;color:#222;line-height:1;}
        .inventory-pda-header strong{display:block;font-size:34px;color:#111;line-height:1.1;}
        .inventory-pda-back{height:48px;display:flex;align-items:center;justify-content:center;}
        .inventory-pda-toolbar{position:sticky;top:0;z-index:5;display:flex;justify-content:space-between;align-items:center;gap:8px;background:#f8f9fa;border:1px solid #ddd;border-radius:6px;padding:8px;margin-bottom:10px;}
        .inventory-pda-toolbar span{font-size:22px;font-weight:800;color:#222;}
        .inventory-pda-location{border:1px solid #d1d5db;border-radius:6px;background:#111827;color:#fff;text-align:center;font-size:32px;font-weight:900;line-height:1;padding:12px;margin-bottom:10px;}
        .inventory-pda-scan{border:1px solid #ddd;border-radius:6px;background:#fff;padding:10px;margin-bottom:10px;text-align:center;}
        .inventory-pda-scan label{display:block;font-size:12px;text-transform:uppercase;color:#666;font-weight:800;margin-bottom:4px;}
        .inventory-pda-scan-row{display:grid;grid-template-columns:1fr 58px;gap:8px;align-items:center;}
        .inventory-pda-scan-row .btn{height:54px;font-weight:800;}
        .inventory-pda-list{display:flex;flex-direction:column;gap:10px;}
        .inventory-pda-item{border:1px solid #d1d5db;border-radius:6px;background:#fff;padding:8px 10px;text-align:left;}
        .inventory-pda-item.done{border-color:#86efac;background:#f0fdf4;}
        .inventory-pda-item.diff{border-color:#fca5a5;background:#fef2f2;}
        .inventory-pda-line{display:grid;grid-template-columns:minmax(0,1fr) 48px;gap:8px;align-items:center;}
        .inventory-pda-product-code{display:flex;align-items:baseline;gap:5px;min-width:0;}
        .inventory-pda-line strong{font-size:17px;line-height:1.15;color:#111;word-break:break-word;}
        .inventory-pda-line small{font-size:12px;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
        .inventory-pda-line b{font-size:22px;color:#111827;text-align:center;}
        .inventory-pda-meta{display:flex;justify-content:space-between;gap:8px;color:#555;font-size:14px;border-top:1px solid #eee;padding-top:6px;margin-bottom:8px;}
        .inventory-pda-input{height:54px;font-size:28px;text-align:center;font-weight:800;border:2px solid #aaa;border-radius:6px;background:#fff;}
        .inventory-pda-input:focus{border-color:#0d6efd;box-shadow:0 0 0 .2rem rgba(13,110,253,.18);}
        .inventory-pda-scan-count{display:grid;grid-template-columns:1fr;gap:8px;text-align:center;}
        .inventory-pda-scan-count span{border:1px solid #e5e7eb;border-radius:6px;background:#f8fafc;padding:8px;color:#475569;}
        .inventory-pda-scan-count strong{display:block;font-size:24px;color:#111827;}
        @media (min-width:780px){
            .inventory-pda-wrap{padding-top:18px;}
            .inventory-pda-header h3{font-size:24px;}
        }
        @media (max-width:420px){
            .inventory-pda-line{grid-template-columns:minmax(0,1fr) 42px;}
            .inventory-pda-product-code{display:block;}
            .inventory-pda-line small{display:block;margin-top:2px;}
        }
    </style>

    <script>
        $('#scanInput,#cellScanInput').on('keyup', function(event) {
            if (event.keyCode === 13) {
                event.preventDefault();
                $(this).closest('form').submit();
            }
        });
    </script>
@endsection
