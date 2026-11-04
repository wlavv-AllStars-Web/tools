@php
    $areas = [
        ['label' => 'Accounting', 'icon' => 'fa-calculator', 'route' => 'finance.index', 'match' => 'finance.index'],
        ['label' => 'Purchase', 'icon' => 'fa-cart-shopping', 'route' => 'purchase.index', 'match' => 'purchase.index'],
        ['label' => 'Logistics', 'icon' => 'fa-boxes-stacked', 'route' => 'logistics.index', 'match' => 'logistics.index'],
        ['label' => 'Sales', 'icon' => 'fa-chart-line', 'route' => 'sales.index', 'match' => 'sales.index'],
        ['label' => 'After Sales', 'icon' => 'fa-headset', 'route' => 'afterSales.index', 'match' => 'afterSales.index'],
        ['label' => 'Studio', 'icon' => 'fa-wand-magic-sparkles', 'route' => 'marketing.index', 'match' => 'marketing.index'],
        ['label' => 'Design', 'icon' => 'fa-pen-ruler', 'route' => 'design.index', 'match' => 'design.index'],
        ['label' => 'Webmaster', 'icon' => 'fa-code', 'route' => 'web.index', 'match' => 'web.index', 'users' => [43]],
    ];
    $tools = [
        ['label' => 'ERP', 'icon' => 'fa-warehouse', 'route' => 'erp.oms.simple', 'match' => 'erp.oms.*'],
        ['label' => 'Ship stats', 'icon' => 'fa-truck-fast', 'route' => 'logistics.tools.shipping.index', 'match' => 'logistics.tools.shipping.*'],
        ['label' => 'Suppliers map', 'icon' => 'fa-map-location-dot', 'route' => 'purchase.tools.suppliers.map.index', 'match' => 'suppliersMap.*|purchase.tools.suppliers.map.*|backoffice.tools.suppliers.map.*'],
        ['label' => 'Documents', 'icon' => 'fa-folder-open', 'route' => 'documentsManager.index', 'match' => 'documentsManager.index'],
    ];
@endphp

<aside class="nt-sidebar" id="nt-sidebar" aria-label="Navegação principal">
    <a class="nt-brand" href="{{ route('dashboard.index') }}">
        <span class="nt-brand-mark">N</span>
        <span class="nt-brand-copy"><strong>newTools</strong><small>Back Office</small></span>
    </a>
    <nav class="nt-nav">
        @foreach ($areas as $item)
            @if (empty($item['users']) || in_array((int) Auth::id(), $item['users'], true))
                @php($itemActive = collect(explode('|', $item['match']))->contains(fn ($match) => request()->routeIs($match)))
                <div class="nt-nav-group {{ $itemActive ? 'is-active' : '' }}"><a class="nt-nav-toggle" href="{{ route($item['route']) }}" title="{{ $item['label'] }}"><i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i><span>{{ $item['label'] }}</span></a></div>
            @endif
        @endforeach
        <div class="nt-nav-divider" role="separator" aria-label="Ferramentas"></div>
        @foreach ($tools as $item)
            @php($itemActive = collect(explode('|', $item['match']))->contains(fn ($match) => request()->routeIs($match)))
            <div class="nt-nav-group {{ $itemActive ? 'is-active' : '' }}"><a class="nt-nav-toggle" href="{{ route($item['route']) }}" title="{{ $item['label'] }}"><i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i><span>{{ $item['label'] }}</span></a></div>
        @endforeach
    </nav>
</aside>
