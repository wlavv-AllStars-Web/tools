@php
    $areas = [
        ['label' => 'Accounting', 'icon' => 'calculator', 'route' => 'finance.index', 'match' => 'finance.index'],
        ['label' => 'Purchase', 'icon' => 'cart', 'route' => 'purchase.index', 'match' => 'purchase.index'],
        ['label' => 'Logistics', 'icon' => 'box', 'route' => 'logistics.index', 'match' => 'logistics.index'],
        ['label' => 'Sales', 'icon' => 'sales', 'route' => 'sales.index', 'match' => 'sales.index'],
        ['label' => 'After Sales', 'icon' => 'support', 'route' => 'afterSales.index', 'match' => 'afterSales.index'],
        ['label' => 'Studio', 'icon' => 'studio', 'route' => 'marketing.index', 'match' => 'marketing.index'],
        ['label' => 'Design', 'icon' => 'design', 'route' => 'design.index', 'match' => 'design.index'],
        ['label' => 'Webmaster', 'icon' => 'code', 'route' => 'web.index', 'match' => 'web.index', 'users' => [43]],
    ];
    $tools = [
        ['label' => 'ERP', 'icon' => 'warehouse', 'route' => 'erp.oms.simple', 'match' => 'erp.oms.*'],
        ['label' => 'Ship stats', 'icon' => 'truck', 'route' => 'logistics.tools.shipping.index', 'match' => 'logistics.tools.shipping.*'],
        ['label' => 'Suppliers map', 'icon' => 'map', 'route' => 'purchase.tools.suppliers.map.index', 'match' => 'suppliersMap.*|purchase.tools.suppliers.map.*|backoffice.tools.suppliers.map.*'],
        ['label' => 'Documents', 'icon' => 'folder', 'route' => 'documentsManager.index', 'match' => 'documentsManager.index'],
    ];
@endphp

<aside class="nt-sidebar" id="nt-sidebar" aria-label="Navegação principal">
    <a class="nt-brand" href="{{ route('dashboard.index') }}">
        <img class="nt-brand-logo" src="{{ asset('admin/images/all-stars-group.svg') }}" alt="All Stars Group">
    </a>
    <nav class="nt-nav">
        @foreach ($areas as $item)
            @if (empty($item['users']) || in_array((int) Auth::id(), $item['users'], true))
                @php($itemActive = collect(explode('|', $item['match']))->contains(fn ($match) => request()->routeIs($match)))
                <div class="nt-nav-group {{ $itemActive ? 'is-active' : '' }}"><a class="nt-nav-toggle" href="{{ route($item['route']) }}" title="{{ $item['label'] }}">@include('includes.backOfficeIcon', ['icon' => $item['icon']])<span>{{ $item['label'] }}</span></a></div>
            @endif
        @endforeach
        <div class="nt-nav-divider" role="separator" aria-label="Ferramentas"></div>
        @foreach ($tools as $item)
            @php($itemActive = collect(explode('|', $item['match']))->contains(fn ($match) => request()->routeIs($match)))
            <div class="nt-nav-group {{ $itemActive ? 'is-active' : '' }}"><a class="nt-nav-toggle" href="{{ route($item['route']) }}" title="{{ $item['label'] }}">@include('includes.backOfficeIcon', ['icon' => $item['icon']])<span>{{ $item['label'] }}</span></a></div>
        @endforeach
    </nav>
</aside>
