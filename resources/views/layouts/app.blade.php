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
                    <div class="sideMenu"> @include('includes.desktopMenu') </div>
                    <div class="mainContainer">
                        <script>

                            let divVisivel = false;
                            
                            document.addEventListener('keydown', function (event) {

                                if (event.ctrlKey && event.shiftKey && event.key.toLowerCase() === 's') {
                                    event.preventDefault();
                                    const div = document.getElementById('extraMenu');
                                    divVisivel = !divVisivel;
                                    div.style.display = divVisivel ? 'block' : 'none';
                                    
                                    $('#globalSearch').focus();
                                }
                                
                            });
  
                        </script>
                        <div id="headerBreadcrumbsContainer" class="navbar navbar-light shadow-sm" style="background-color: #ededed;border: 1px solid #ddd;padding-top: 0; margin-top: 0;">
                            <div style="display: contents;">
                                <div style="width: 100%; background-color: #ccc; border-bottom: 1px solid #999;display: none;" id="extraMenu">
                                    <div class="text-center" style="height: auto; margin-bottom: 10px;height: 30px; float: right;display: none;"> 
                                        <div id="languageSelector" style="text-transform: uppercase; text-align: center; width: 100%; height: 35px; float: right" onclick="$('#languageSelector').toggle();$('#languageSelectorContainer').toggle();">
                                            <img style="width: 25px; border-radius: 25px; margin: 7px 5px; float: right; border: 1px solid #999;" src="/images/flags/{{app()->getLocale()}}.png">
                                        </div>
                                        <div id="languageSelectorContainer" style="display: none;width: 100%; height: 35px;">
                                            <a style="width: 25%; float: left; text-align: center;" class="nav-link uppercase" href="/language/en"> <img style="width: 25px; border-radius: 25px; margin: 7px 5px; border: 1px solid #999;" src="/images/flags/en.png"> </a>
                                            <a style="width: 25%; float: left; text-align: center;" class="nav-link uppercase" href="/language/es"> <img style="width: 25px; border-radius: 25px; margin: 7px 5px; border: 1px solid #999;" src="/images/flags/es.png"> </a>
                                            <a style="width: 25%; float: left; text-align: center;" class="nav-link uppercase" href="/language/fr"> <img style="width: 25px; border-radius: 25px; margin: 7px 5px; border: 1px solid #999;" src="/images/flags/fr.png"> </a>
                                            <a style="width: 25%; float: left; text-align: center;" class="nav-link uppercase" href="/language/pt"> <img style="width: 25px; border-radius: 25px; margin: 7px 5px; border: 1px solid #999;" src="/images/flags/pt.png"> </a>
                                        </div>
                                    </div>
                                    <div>
                                        <form action="{{route('search.globalSearch')}}" method="POST" id="formGlobalSearch" style="margin: 0px;">
                                            @csrf
                                            <input type="text" id="globalSearch" name="tag" value="" style="width: calc( 100% - 150px );float: left;" placeholder="SEARCH...">
                                            <button style="width: 150px;float: right;padding: 2px;border-radius: 0px;" class="btn btn-success" type="submit">Pesquisar</button>
                                        </form>
                                    </div>
                                </div>
                                <div style="margin-top: 10px;width: 100%;">
                                    <div style="width: 65%; float: left;"  id="breadcrumbs">  @include('includes.breadcrumbs') </div>
                                    <div style="width: 35%; float: right;" id="desktopAction"> @include('includes.actions')    </div>                                    
                                </div>


                            </div>
                        </div>

                        <div id="mainMenuMobile"> @include('includes.mobileMenu') </div>
                        <div id="mobileAction">   @include('includes.mobileAction') </div>

                        <div id="yieldContent" style="width: calc(100% - 20px);margin: 0 10px;display: inline-grid;"> @yield('content') </div>
                    </div>
                @endauth
            @endguest
        </main>
    </div>
</body>
</html>
