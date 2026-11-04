@extends('layouts.app')

    @include("areas.dashboard.includes.js")
    @include("areas.dashboard.includes.css")

@section('content')

    <section class="nt-dashboard-quick" aria-labelledby="nt-quick-actions-title">
        <h2 class="nt-dashboard-quick__title" id="nt-quick-actions-title">Quick actions</h2>
        <a href="{{ route('erp.oms.simple') }}"><i class="fa-solid fa-warehouse" aria-hidden="true"></i><span>ERP</span></a>
        <a href="{{ route('purchase.index') }}"><i class="fa-solid fa-cart-shopping" aria-hidden="true"></i><span>Purchase</span></a>
        <a href="{{ route('sales.index') }}"><i class="fa-solid fa-chart-line" aria-hidden="true"></i><span>Sales</span></a>
        <a href="{{ route('documentsManager.index') }}"><i class="fa-regular fa-folder-open" aria-hidden="true"></i><span>Documents</span></a>
        <a href="{{ route('logistics.tools.shipping.index') }}"><i class="fa-solid fa-truck-fast" aria-hidden="true"></i><span>Ship stats</span></a>
        <a href="{{ route('afterSales.index') }}"><i class="fa-solid fa-headset" aria-hidden="true"></i><span>After sales</span></a>
    </section>

    <div id="dashboard_content">
        {!! $kpiContent !!}
    </div>

@endsection
