<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{asset('admin/css/app.css')}}">
    <!-- Scripts -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>    
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <main>
            <div class="sideMenu">
                <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
                    <div class="container" style="display: inline-block;padding: 0;">
                        <a class="navbar-brand" href="https://www.allstars-web.com/" target="_blank">
                            <img src="/admin/images/allstarsweb.gif" style="width: calc(100% - 20px); margin: 10px; @guest display: none; @endif">
                        </a>
                        <div class="navbar-collapse" id="navbarSupportedContent">
                            @guest

                            @else
                                <ul class="navbar-nav me-auto margin-auto" style="margin-top: 10px;display: inline-block;">
                                    <li class="nav-item text-center @if(Route::is('dashboard') ) active-link @endif"> 
                                        <a class="nav-link uppercase" href="{{ route('dashboard') }}"> 
                                            <div><i class="fa fa-xl fa-dashboard" style="font-size: 30px;"></i></div>
                                            <div style="margin-top:10px;">{{ __('menu.dashboard') }}</div>
                                        </a> 
                                    </li>
                                    <li class="nav-item text-center @if(Route::is('adminPanels') ) active-link @endif"> 
                                        <a class="nav-link uppercase" href="{{ route('adminPanels') }}"> 
                                            <div><i class="fa fa-xl fa-gear" style="font-size: 30px;"></i></div>
                                            <div style="margin-top:10px;">{{ __('menu.admin') }}</div>
                                        </a> 
                                    </li>
                                    <li class="nav-item text-center @if(Route::is('webmasterPanels') ) active-link @endif"> 
                                        <a class="nav-link uppercase" href="{{ route('webmasterPanels') }}">
                                            <div><i class="fa fa-xl fa-code" style="font-size: 30px;"></i></div>
                                            <div style="margin-top:10px;"> {{ __('menu.webmaster') }}</div>
                                        </a> 
                                    </li>
                                    <li class="nav-item text-center @if(Route::is('hrPanels') ) active-link @endif"> 
                                        <a class="nav-link uppercase" href="{{ route('hrPanels') }}">
                                            <div><i class="fa fa-xl fa-people-arrows" style="font-size: 30px;"></i></div>
                                            <div style="margin-top:10px;"> {{ __('menu.human resources') }}</div>
                                        </a> 
                                    </li>
                                    <li class="nav-item text-center @if(Route::is('financePanels') ) active-link @endif"> 
                                        <a class="nav-link uppercase" href="{{ route('financePanels') }}">
                                            <div><i class="fa fa-xl fa-chart-line" style="font-size: 30px;"></i></div>
                                            <div style="margin-top:10px;"> {{ __('menu.finance') }}</div>
                                        </a> 
                                    </li>
                                    <li class="nav-item text-center @if(Route::is('logisticsPanels') ) active-link @endif"> 
                                        <a class="nav-link uppercase" href="{{ route('logisticsPanels') }}">
                                            <div><i class="fa fa-xl fa-box" style="font-size: 30px;"></i></div>
                                            <div style="margin-top:10px;"> {{ __('menu.logistics') }}</div>
                                        </a> 
                                    </li>
                                    <li class="nav-item text-center @if(Route::is('marketingPanels') ) active-link @endif"> 
                                        <a class="nav-link uppercase" href="{{ route('marketingPanels') }}">
                                            <div><i class="fa fa-xl fa-bullhorn" style="font-size: 30px;"></i></div>
                                            <div style="margin-top:10px;"> {{ __('menu.marketing') }}</div>
                                        </a> 
                                    </li>
                                    <li class="nav-item text-center @if(Route::is('customerSuportPanels') ) active-link @endif"> 
                                        <a class="nav-link uppercase" href="{{ route('customerSuportPanels') }}">
                                            <div><i class="fa fa-xl fa-headset" style="font-size: 30px;"></i></div>
                                            <div style="margin-top:10px;"> {{ __('menu.customer support') }}</div>
                                        </a>
                                    </li>

                                    <li class="nav-item dropdown">
                                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                            <div><i class="fa fa-xl fa-user" style="font-size: 30px;color: dodgerblue"></i></div>
                                            <div style="margin-top:10px;"> {{ Auth::user()->name }}</div>
                                        </a>

                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                            <a class="dropdown-item" href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                            document.getElementById('logout-form').submit();">
                                                {{ __('Logout') }}
                                            </a>

                                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                                @csrf
                                            </form>
                                        </div>
                                    </li>
                                </ul>
                            @endguest
                        </div>
                    </div>
                </nav>


            </div>
            <div>
                <nav class="navbar navbar-expand-md navbar-light shadow-sm" style="background-color: #ddd;">
                    <div class="container" style="display: inline-block;padding: 0; height: 50px;text-align: center;">
                        <div class="card-header text-center"><b>{{ Auth::user()->name }}</b>, {{ __('messages.welcome to') }} <b>{{ __('messages.' . Route::currentRouteName()) }}</b></div>

                        </div>
                    </div>
                    <div class="mainContainer">
                        @yield('content')
                    </div>
                </nav>
            </div>
        </main>
    </div>
</body>
</html>
