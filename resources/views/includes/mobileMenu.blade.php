<div style="background-color: #FFF;border-bottom: 1px solid #ccc;" class="text-center" onclick="$('#mobileMenu').toggle()">
    <span>{{ __('menu.menuMobile') }}</span><i class="fa fa-xl fa-chevron-down" style="font-size: 16px; padding-left: 5px;"></i>
</div>
<div id="mobileMenu">
    <div style="display: inline-block; background-color: #fff;padding:10px;border-bottom: 1px solid #ccc;width: 100%;">
        <div class="text-center mobileMenuItem @if(Route::is('dashboard.index') ) active-link @endif"> 
            <a class="nav-link uppercase" href="{{ route('dashboard.index') }}"> 
                <div><i class="fa fa-xl fa-dashboard" style="font-size: 30px;"></i></div>
                <div class="sideMenuText">{{ __('menu.dashboard') }}</div>
            </a> 
        </div>
        <div class="text-center mobileMenuItem @if(Route::is('administration.index') ) active-link @endif"> 
            <a class="nav-link uppercase" href="{{ route('administration.index') }}"> 
                <div><i class="fa fa-xl fa-gear" style="font-size: 30px;"></i></div>
                <div class="sideMenuText">{{ __('menu.admin') }}</div>
            </a> 
        </div>
        <div class="text-center mobileMenuItem @if(Route::is('web.index') ) active-link @endif"> 
            <a class="nav-link uppercase" href="{{ route('web.index') }}">
                <div><i class="fa fa-xl fa-code" style="font-size: 30px;"></i></div>
                <div class="sideMenuText"> {{ __('menu.webmaster') }}</div>
            </a> 
        </div>
        <div class="text-center mobileMenuItem @if(Route::is('hr.index') ) active-link @endif"> 
            <a class="nav-link uppercase" href="{{ route('hr.index') }}">
                <div><i class="fa fa-xl fa-people-arrows" style="font-size: 30px;"></i></div>
                <div  class="sideMenuText"> {{ __('menu.human resources') }}</div>
            </a> 
        </div>
        <div class="text-center mobileMenuItem @if(Route::is('finance.index') ) active-link @endif"> 
            <a class="nav-link uppercase" href="{{ route('finance.index') }}">
                <div><i class="fa fa-xl fa-chart-line" style="font-size: 30px;"></i></div>
                <div class="sideMenuText"> {{ __('menu.finance') }}</div>
            </a> 
        </div>
        <div class="text-center mobileMenuItem @if(Route::is('logistics.index') ) active-link @endif"> 
            <a class="nav-link uppercase" href="{{ route('logistics.index') }}">
                <div><i class="fa fa-xl fa-box" style="font-size: 30px;"></i></div>
                <div class="sideMenuText"> {{ __('menu.logistics') }}</div>
            </a> 
        </div>
        <div class="text-center mobileMenuItem @if(Route::is('marketing.index') ) active-link @endif"> 
            <a class="nav-link uppercase" href="{{ route('marketing.index') }}">
                <div><i class="fa fa-xl fa-bullhorn" style="font-size: 30px;"></i></div>
                <div class="sideMenuText"> {{ __('menu.marketing') }}</div>
            </a> 
        </div>
        <div class="text-center mobileMenuItem @if(Route::is('customer.index') ) active-link @endif"> 
            <a class="nav-link uppercase" href="{{ route('customer.index') }}">
                <div><i class="fa fa-xl fa-headset" style="font-size: 30px;"></i></div>
                <div class="sideMenuText"> {{ __('menu.customer support') }}</div>
            </a>
        </div>
        <div class="text-center mobileMenuItem @if(Route::is('modules') ) active-link @endif"> 
            <a class="nav-link uppercase" href="{{ route('modules.index') }}">
                <div><i class="fa fa-xl fa-headset" style="font-size: 30px;"></i></div>
                <div class="sideMenuText"> {{ __('menu.modules') }}</div>
            </a>
        </div>
        <div class="text-center mobileMenuItem"> 
            <a class="nav-link uppercase" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <div><i class="fa fa-xl fa-sign-out" style="font-size: 30px;"></i></div>
                <div class="sideMenuText"> {{ __('menu.logout') }}</div>
            </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
</div>