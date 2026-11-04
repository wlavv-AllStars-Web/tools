<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'WebTools') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="{{asset('admin/css/sweetalert2.min.css')}}">
    <link rel="stylesheet" href="{{asset('admin/css/dropzone.min.css')}}"/>
    <link rel="stylesheet" href="{{asset('admin/css/app.css')}}">

    @stack('styles')
    
    <!-- Scripts -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- Scripts adicionais -->
    <script src="{{asset('admin/js/sweetalert2.min.js')}}"></script>
    <script src="{{asset('admin/js/dropzone.min.js')}}"></script>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @include('includes.js')
</head>
<body>

    <div id="app">                            
        <main>
            @guest
                <div style="margin: 60px 0">
                    @yield('content')
                </div>
            @else
                @auth
                    <div class="nt-app-shell">
                        @include('includes.backOfficeSidebar')
                        <div class="nt-overlay" data-nt-close-sidebar></div>
                        <div class="nt-main-container">
                            <header class="nt-topbar">
                                <button class="nt-menu-button" type="button" aria-controls="nt-sidebar" aria-label="Abrir ou recolher navegação" data-nt-toggle-sidebar><i class="fa-solid fa-bars"></i></button>
                                <div class="nt-breadcrumbs">@include('includes.breadcrumbs')</div>
                                <div class="nt-page-actions" id="desktopAction">@include('includes.actions')</div>
                                <form action="{{ route('search.globalSearch') }}" method="POST" class="nt-global-search" id="formGlobalSearch">
                                    @csrf
                                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                                    <input type="search" id="globalSearch" name="tag" placeholder="Pesquisar..." aria-label="Pesquisa global">
                                    <kbd>Ctrl K</kbd>
                                </form>
                                <form action="{{ route('logout') }}" method="POST" class="nt-logout-form">@csrf<button type="submit" class="nt-logout"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Terminar sessão</span></button></form>
                            </header>
                            <div id="yieldContent">
                            @if(request()->routeIs('erp.oms.*', 'admin.tools.oms.*'))
                                @include('modules.oms.partials.common_search')
                            @endif
                            @yield('content')
                            </div>
                        </div>
                    </div>
                @endauth
            @endguest
        </main>
    </div>
    @stack('scripts')
    <script>
        (() => {
            const body = document.body;
            const mobile = () => window.matchMedia('(max-width: 900px)').matches;
            const setCollapsed = (collapsed) => { body.classList.toggle('nt-sidebar-collapsed', collapsed); localStorage.setItem('nt-sidebar-collapsed', collapsed ? '1' : '0'); };
            if (!mobile() && localStorage.getItem('nt-sidebar-collapsed') === '1') setCollapsed(true);
            document.querySelector('[data-nt-toggle-sidebar]')?.addEventListener('click', () => mobile() ? body.classList.toggle('nt-sidebar-mobile-open') : setCollapsed(!body.classList.contains('nt-sidebar-collapsed')));
            document.querySelector('[data-nt-close-sidebar]')?.addEventListener('click', () => body.classList.remove('nt-sidebar-mobile-open'));
            document.querySelectorAll('.nt-nav-toggle[type="button"]').forEach((button) => button.addEventListener('click', () => { const group = button.closest('.nt-nav-group'); const open = !group.classList.contains('is-open'); group.classList.toggle('is-open', open); button.setAttribute('aria-expanded', open); }));
            document.addEventListener('keydown', (event) => { if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); document.getElementById('globalSearch')?.focus(); } if (event.key === 'Escape') body.classList.remove('nt-sidebar-mobile-open'); });
        })();
    </script>
</body>
</html>
