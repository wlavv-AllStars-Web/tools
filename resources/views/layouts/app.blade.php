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
            @guest
                <div style="margin: 60px 0">
                    @yield('content')
                </div>
            @else
                <div class="sideMenu">
                    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
                        <div class="container" style="display: inline-block;padding: 0;">
                            <a class="navbar-brand" href="https://www.allstars-web.com/" target="_blank">
                                <img src="/admin/images/allstarsweb.gif" style="width: calc(100% - 20px); margin: 10px; @guest display: none; @endif">
                            </a>
                            <div class="navbar-collapse" id="navbarSupportedContent">
                                <ul class="navbar-nav me-auto margin-auto" style="margin-top: 10px;display: inline-block;">
                                    <li class="nav-item text-center @if(Route::is('dashboard') ) active-link @endif"> 
                                        <a class="nav-link uppercase" href="{{ route('dashboard') }}"> 
                                            <div><i class="fa fa-xl fa-dashboard" style="font-size: 30px;"></i></div>
                                            <div style="margin-top:10px;">{{ __('menu.dashboard') }}</div>
                                        </a> 
                                    </li>
                                    <li class="nav-item text-center @if(Route::is('administration') ) active-link @endif"> 
                                        <a class="nav-link uppercase" href="{{ route('administration') }}"> 
                                            <div><i class="fa fa-xl fa-gear" style="font-size: 30px;"></i></div>
                                            <div style="margin-top:10px;">{{ __('menu.admin') }}</div>
                                        </a> 
                                    </li>
                                    <li class="nav-item text-center @if(Route::is('webmastering') ) active-link @endif"> 
                                        <a class="nav-link uppercase" href="{{ route('webmastering') }}">
                                            <div><i class="fa fa-xl fa-code" style="font-size: 30px;"></i></div>
                                            <div style="margin-top:10px;"> {{ __('menu.webmaster') }}</div>
                                        </a> 
                                    </li>
                                    <li class="nav-item text-center @if(Route::is('humanResources') ) active-link @endif"> 
                                        <a class="nav-link uppercase" href="{{ route('humanResources') }}">
                                            <div><i class="fa fa-xl fa-people-arrows" style="font-size: 30px;"></i></div>
                                            <div style="margin-top:10px;"> {{ __('menu.human resources') }}</div>
                                        </a> 
                                    </li>
                                    <li class="nav-item text-center @if(Route::is('finances') ) active-link @endif"> 
                                        <a class="nav-link uppercase" href="{{ route('finances') }}">
                                            <div><i class="fa fa-xl fa-chart-line" style="font-size: 30px;"></i></div>
                                            <div style="margin-top:10px;"> {{ __('menu.finance') }}</div>
                                        </a> 
                                    </li>
                                    <li class="nav-item text-center @if(Route::is('logistics') ) active-link @endif"> 
                                        <a class="nav-link uppercase" href="{{ route('logistics') }}">
                                            <div><i class="fa fa-xl fa-box" style="font-size: 30px;"></i></div>
                                            <div style="margin-top:10px;"> {{ __('menu.logistics') }}</div>
                                        </a> 
                                    </li>
                                    <li class="nav-item text-center @if(Route::is('marketing') ) active-link @endif"> 
                                        <a class="nav-link uppercase" href="{{ route('marketing') }}">
                                            <div><i class="fa fa-xl fa-bullhorn" style="font-size: 30px;"></i></div>
                                            <div style="margin-top:10px;"> {{ __('menu.marketing') }}</div>
                                        </a> 
                                    </li>
                                    <li class="nav-item text-center @if(Route::is('customerSuport') ) active-link @endif"> 
                                        <a class="nav-link uppercase" href="{{ route('customerSuport') }}">
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
                            </div>
                        </div>
                    </nav>
                </div>
                <div class="mainContainer" style="width: calc( 100% - 140px); float: left;">
                    <div class="navbar navbar-light shadow-sm" style="background-color: #ededed;border: 1px solid #ddd;">
                        <div style="display: contents;">
                            <div style="width: 65%; float: left;">  @include('includes.breadcrumbs') </div>
                            <div style="width: 35%; float: right;"> @include('includes.actions')     </div>
                        </div>
                    </div>
                    <div style="width: calc(100% - 20px);margin: 0 10px;display: inline-grid;"> @yield('content') </div>
                </div>
            @endguest
        </main>
    </div>
</body>
</html>
