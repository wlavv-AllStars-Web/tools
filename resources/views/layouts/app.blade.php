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
    <link rel="stylesheet" href="{{asset('admin/css/sweetalert2.min.css')}}">
    <link rel="stylesheet" href="{{asset('admin/css/app.css')}}">
    <!-- Scripts -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>    
    <script src="{{asset('admin/js/sweetalert2.min.js')}}"></script>
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
                @auth
                    <div class="sideMenu"> @include('includes.desktopMenu') </div>
                    <div class="mainContainer">
                        <div class="navbar navbar-light shadow-sm" style="background-color: #ededed;border: 1px solid #ddd;">
                            <div style="display: contents;">

                                <div style="width: 65%; float: left;"  id="breadcrumbs">  @include('includes.breadcrumbs') </div>
                                <div style="width: 35%; float: right;" id="desktopAction"> @include('includes.actions')    </div>

                            </div>
                        </div>

                        <div id="mainMenuMobile"> @include('includes.mobileMenu') </div>
                        <div id="mobileAction">   @include('includes.mobileAction') </div>

                        <div style="width: calc(100% - 20px);margin: 0 10px;display: inline-grid;"> @yield('content') </div>
                    </div>
                @endauth
            @endguest
        </main>
    </div>
</body>
</html>
