<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
    <div class="container" style="display: inline-block;padding: 0;">
        <a class="navbar-brand sideMenuLogo" href="https://www.allstars-web.com/" target="_blank">
            <img src="/admin/images/allstarsweb.gif" style="width: calc(100% - 20px); margin: 10px; @guest display: none; @endif">
        </a>
        <div class="navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto margin-auto sideMenuUL">
                <li class="nav-item text-center @if(Route::is('dashboard.index') ) active-link @endif"> 
                    <a class="nav-link uppercase" href="{{ route('dashboard.index') }}"> 
                        <div><i class="fa fa-xl fa-dashboard" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText">{{ __('menu.dashboard') }}</div>
                    </a> 
                </li>
                <li class="nav-item text-center @if(Route::is('administration.index') ) active-link @endif"> 
                    <a class="nav-link uppercase" href="{{ route('administration.index') }}"> 
                        <div><i class="fa fa-xl fa-gear" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText">{{ __('menu.admin') }}</div>
                    </a> 
                </li>
                <li class="nav-item text-center @if(Route::is('web.index') ) active-link @endif"> 
                    <a class="nav-link uppercase" href="{{ route('web.index') }}">
                        <div><i class="fa fa-xl fa-code" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> {{ __('menu.webmaster') }}</div>
                    </a> 
                </li>
                <li class="nav-item text-center @if(Route::is('hr.index') ) active-link @endif"> 
                    <a class="nav-link uppercase" href="{{ route('hr.index') }}">
                        <div><i class="fa fa-xl fa-people-arrows" style="font-size: 30px;"></i></div>
                        <div  class="sideMenuText"> {{ __('menu.human resources') }}</div>
                    </a> 
                </li>
                <li class="nav-item text-center @if(Route::is('finance.index') ) active-link @endif"> 
                    <a class="nav-link uppercase" href="{{ route('finance.index') }}">
                        <div><i class="fa fa-xl fa-chart-line" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> {{ __('menu.finance') }}</div>
                    </a> 
                </li>
                <li class="nav-item text-center @if(Route::is('logistics.index') ) active-link @endif"> 
                    <a class="nav-link uppercase" href="{{ route('logistics.index') }}">
                        <div><i class="fa fa-xl fa-box" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> {{ __('menu.logistics') }}</div>
                    </a> 
                </li>
                <li class="nav-item text-center @if(Route::is('marketing.index') ) active-link @endif"> 
                    <a class="nav-link uppercase" href="{{ route('marketing.index') }}">
                        <div><i class="fa fa-xl fa-bullhorn" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> {{ __('menu.marketing') }}</div>
                    </a> 
                </li>
                <li class="nav-item text-center @if(Route::is('customer.index') ) active-link @endif"> 
                    <a class="nav-link uppercase" href="{{ route('customer.index') }}">
                        <div><i class="fa fa-xl fa-headset" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> {{ __('menu.customer support') }}</div>
                    </a>
                </li>
                <li class="nav-item text-center @if(Route::is('modules') ) active-link @endif"> 
                    <a class="nav-link uppercase" href="{{ route('modules.index') }}">
                        <div><i class="fa fa-xl fa-boxes-packing" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> {{ __('menu.modules') }}</div>
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        <div><i class="fa fa-xl fa-user" style="font-size: 30px;color: dodgerblue"></i></div>
                        <div class="sideMenuText"> {{ Auth::user()->name }}</div>
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