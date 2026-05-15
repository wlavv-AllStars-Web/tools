<div style="background-color: #FFF;border-bottom: 1px solid #ccc;" class="text-center" onclick="$('#mobileMenu').toggle()">
    <span>{{ __('menu.menuMobile') }}</span><i class="fa fa-xl fa-chevron-down" style="font-size: 16px; padding-left: 5px;"></i>
</div>
<div id="mobileMenu">
    <div style="display: inline-block; background-color: #fff;padding:10px;border-bottom: 1px solid #ccc;width: 100%;">
        <div class="text-center mobileMenuItem @if(Route::is('dashboard.index') ) active-link @endif"> 
            <a class="nav-link uppercase" href="{{ route('dashboard.index') }}"> 
                <div><i class="fa-solid fa-chart-pie" style="font-size: 30px;"></i></div>
                <div class="sideMenuText">{{ __('menu.dashboard') }}</div>
            </a> 
        </div>
        <div class="text-center mobileMenuItem @if(Route::is('finance.index') ) active-link @endif"> 
            <a class="nav-link uppercase" href="{{ route('finance.index') }}">
                <div><i class="fa fa-xl fa-chart-line" style="font-size: 30px;"></i></div>
                <div class="sideMenuText"> {{ __('menu.finance') }}</div>
            </a> 
        </div>
        <div class="text-center mobileMenuItem @if(Route::is('administration.index') ) active-link @endif"> 
            <a class="nav-link uppercase" href="{{ route('administration.index') }}">
                <div><i class="fa fa-xl fa-people-roof" style="font-size: 30px;"></i></div>
                <div class="sideMenuText"> {{ __('menu.admin') }}</div>
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
        <div class="text-center mobileMenuItem @if(Route::is('purchase.index') ) active-link @endif"> 
            <a class="nav-link uppercase" href="{{ route('purchase.index') }}">
                <div><i class="fa-solid fa-dollar-sign" style="font-size: 30px;"></i></div>
                <div class="sideMenuText"> {{ __('menu.Purchase') }}</div>
            </a> 
        </div>
        <div class="text-center mobileMenuItem @if(Route::is('sales.index') ) active-link @endif"> 
            <a class="nav-link uppercase" href="{{ route('sales.index') }}">
                <div><i class="fa fa-xl fa-headset" style="font-size: 30px;"></i></div>
                <div class="sideMenuText"> {{ __('menu.sales') }}</div>
            </a>
        </div>

        @if(auth()->user()->id == 59 || auth()->user()->id == 94 || auth()->user()->id == 43 )
            <div class="text-center mobileMenuItem"> 
                <a class="nav-link uppercase" href="{{ route('checklist.index') }}">
                    <div><i class="fa-solid fa-clipboard-list" style="font-size:22px;"></i></div>
                    <div class="sideMenuText"> {{ __('messages.Checklist Manager') }}</div>
                </a>
            </div>
            <div class="text-center mobileMenuItem @if(Route::is('sales.index') ) active-link @endif"> 
                <a class="nav-link uppercase" href="{{ route('checklist.today') }}">
                    <div><i class="fa-solid fa-list-check" style="font-size: 22px;"></i></div>
                    <div class="sideMenuText"> {{ __('messages.Checklist') }}</div>
                </a>
            </div>
        @else
            <div class="text-center mobileMenuItem @if(Route::is('sales.index') ) active-link @endif"> 
                <a class="nav-link uppercase" href="{{ route('checklist.today') }}">
                    <div><i class="fa-solid fa-clipboard-list" style="font-size:30px;"></i></div>
                    <div class="sideMenuText"> {{ __('messages.Checklist') }}</div>
                </a>
            </div>
        @endif
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