@php
    $navigation = [
        ['label' => 'Painel', 'icon' => 'fa-house', 'items' => [
            ['label' => 'Control panel', 'route' => 'dashboard.index', 'match' => 'dashboard.index'],
        ]],
        ['label' => 'Operações', 'icon' => 'fa-warehouse', 'items' => [
            ['label' => __('menu.logistics'), 'route' => 'logistics.index', 'match' => 'logistics.index'],
            ['label' => 'ERP', 'route' => 'erp.oms.simple', 'match' => 'erp.oms.*'],
            ['label' => 'Ship stats', 'route' => 'logistics.tools.shipping.index', 'match' => 'logistics.tools.shipping.*'],
        ]],
        ['label' => 'Comercial', 'icon' => 'fa-chart-line', 'items' => [
            ['label' => 'Accounting', 'route' => 'finance.index', 'match' => 'finance.index'],
            ['label' => 'Purchase', 'route' => 'purchase.index', 'match' => 'purchase.index'],
            ['label' => 'Sales', 'route' => 'sales.index', 'match' => 'sales.index'],
        ]],
        ['label' => 'Studio', 'icon' => 'fa-wand-magic-sparkles', 'items' => [
            ['label' => 'Studio', 'route' => 'marketing.index', 'match' => 'marketing.index'],
            ['label' => 'Design', 'route' => 'design.index', 'match' => 'design.index'],
            ['label' => __('menu.webmaster'), 'route' => 'web.index', 'match' => 'web.index', 'users' => [2, 43]],
        ]],
        ['label' => 'Recursos', 'icon' => 'fa-folder-open', 'items' => [
            ['label' => 'Documents', 'route' => 'documentsManager.index', 'match' => 'documentsManager.index'],
            ['label' => 'Supplier map', 'route' => 'purchase.tools.suppliers.map.index', 'match' => 'suppliersMap.*|purchase.tools.suppliers.map.*|backoffice.tools.suppliers.map.*'],
        ]],
        ['label' => 'Pós-venda', 'icon' => 'fa-headset', 'items' => [
            ['label' => 'After sales', 'route' => 'afterSales.index', 'match' => 'afterSales.index'],
        ]],
        ['label' => 'Administração', 'icon' => 'fa-shield-halved', 'items' => [
            ['label' => __('menu.admin'), 'route' => 'administration.index', 'match' => 'administration.index'],
        ]],
    ];
    $visibleNavigation = collect($navigation)->map(function ($group) {
        $group['items'] = collect($group['items'])->filter(fn ($item) => empty($item['users']) || in_array((int) Auth::id(), $item['users'], true))->values()->all();
        return $group;
    })->filter(fn ($group) => count($group['items']))->values();
@endphp

<aside class="nt-sidebar" id="nt-sidebar" aria-label="Navegação principal">
    <a class="nt-brand" href="{{ route('dashboard.index') }}">
        <span class="nt-brand-mark">N</span>
        <span class="nt-brand-copy"><strong>newTools</strong><small>Back Office</small></span>
    </a>
    <nav class="nt-nav">
        @foreach ($visibleNavigation as $index => $group)
            @php
                $active = collect($group['items'])->contains(function ($item) {
                    return collect(explode('|', $item['match']))->contains(fn ($match) => request()->routeIs($match));
                });
                $single = count($group['items']) === 1;
            @endphp
            <div class="nt-nav-group {{ $active ? 'is-active is-open' : '' }}">
                @if ($single)
                    @php($item = $group['items'][0])
                    <a class="nt-nav-toggle" href="{{ route($item['route']) }}" title="{{ $group['label'] }}">
                        <i class="fa-solid {{ $group['icon'] }}" aria-hidden="true"></i><span>{{ $group['label'] }}</span>
                    </a>
                @else
                    <button class="nt-nav-toggle" type="button" aria-expanded="{{ $active ? 'true' : 'false' }}" aria-controls="nt-nav-group-{{ $index }}" title="{{ $group['label'] }}">
                        <i class="fa-solid {{ $group['icon'] }}" aria-hidden="true"></i><span>{{ $group['label'] }}</span><i class="fa-solid fa-chevron-right nt-chevron" aria-hidden="true"></i>
                    </button>
                    <div class="nt-subnav" id="nt-nav-group-{{ $index }}">
                        @foreach ($group['items'] as $item)
                            @php($itemActive = collect(explode('|', $item['match']))->contains(fn ($match) => request()->routeIs($match)))
                            <a class="{{ $itemActive ? 'is-active' : '' }}" href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </nav>
</aside>
