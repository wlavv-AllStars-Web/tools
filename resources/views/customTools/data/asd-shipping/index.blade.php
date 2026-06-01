@extends('layouts.app')

@section('content')
    <style>
        .asd-shipping-wrap{width:100%;padding:14px;}
        .asd-shipping-panels{display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:start;}
        .asd-shipping-panel{border:1px solid #ddd;background:#fff;border-radius:6px;overflow:hidden;}
        .asd-shipping-panel-header{background:#f5f5f5;border-bottom:1px solid #ddd;padding:10px 12px;font-weight:800;display:flex;justify-content:space-between;align-items:center;}
        .asd-shipping-list{display:flex;flex-direction:column;gap:8px;padding:10px;max-height:calc(100vh - 245px);overflow:auto;}
        .asd-shipping-row{display:grid;grid-template-columns:minmax(130px,1fr) 150px 150px 150px 38px;gap:8px;align-items:center;border:1px solid #e5e7eb;border-radius:6px;padding:8px;background:#fff;}
        .asd-shipping-row{min-width:800px;}
        .asd-shipping-row-head{position:sticky;top:0;z-index:2;background:#f8fafc;font-size:11px;font-weight:900;text-align:center;color:#475569;text-transform:uppercase;}
        .asd-shipping-row-head div:nth-child(n+2):nth-child(-n+4){text-align:center;}
        .asd-shipping-country strong{display:block;color:#111827;font-size:14px;line-height:1.1;}
        .asd-shipping-country small{display:block;color:#64748b;font-size:11px;margin-top:2px;}
        .asd-shipping-row input{text-align:center;font-weight:800;}
        .asd-shipping-row .btn{height:38px;display:flex;align-items:center;justify-content:center;}
        .asd-shipping-empty{padding:16px;text-align:center;color:#64748b;}
        @media (max-width:980px){
            .asd-shipping-panels{grid-template-columns:1fr;}
            .asd-shipping-list{max-height:none;}
        }
        @media (max-width:560px){
            .asd-shipping-row{grid-template-columns:1fr 1fr 1fr 38px;}
            .asd-shipping-row{min-width:0;}
            .asd-shipping-country{grid-column:1/-1;}
        }
    </style>

    <div class="asd-shipping-wrap">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif

        <div class="asd-shipping-panels">
            @include('customTools.data.asd-shipping.partials.panel', [
                'title' => 'Paises ativos',
                'countries' => $activeCountries,
                'empty' => 'Sem paises ativos.',
            ])

            @include('customTools.data.asd-shipping.partials.panel', [
                'title' => 'Paises inativos',
                'countries' => $inactiveCountries,
                'empty' => 'Sem paises inativos.',
            ])
        </div>
    </div>
@endsection
