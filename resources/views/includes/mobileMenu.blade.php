<div style="background-color: #FFF;border-bottom: 1px solid #ccc;" class="text-center" onclick="$('#mobileMenu').toggle()">
    <span>{{ __('menu.menuMobile') }}</span><i class="fa fa-xl fa-chevron-down" style="font-size: 16px; padding-left: 5px;"></i>
</div>
<div id="mobileMenu">
    <div style="display: inline-block; background-color: #fff;padding:10px;border-bottom: 1px solid #ccc;width: 100%;">

        @if((Auth::id() == 2) || (Auth::id() == 43))
        <div class="text-center mobileMenuItem @if(Route::is('web.index')) active-link @endif">
            <a class="nav-link uppercase" href="{{ route('web.index') }}">
                <div><i class="fa-solid fa-code" style="font-size: 30px;"></i></div>
                <div class="sideMenuText">{{ __('menu.webmaster') }}</div>
            </a>
        </div>
        @endif
        <div class="text-center mobileMenuItem @if(Route::is('finance.index') ) active-link @endif"> 
            <a class="nav-link uppercase" href="{{ route('finance.index') }}">
                <div><i class="fa fa-xl fa-chart-line" style="font-size: 30px;"></i></div>
                <div class="sideMenuText">Accounting</div>
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
                <div class="sideMenuText">Studio</div>
            </a> 
        </div>
        <div class="text-center mobileMenuItem @if(Route::is('purchase.index') ) active-link @endif"> 
            <a class="nav-link uppercase" href="{{ route('purchase.index') }}">
                <div><i class="fa-solid fa-dollar-sign" style="font-size: 30px;"></i></div>
                <div class="sideMenuText">Purchase</div>
            </a> 
        </div>
        <div class="text-center mobileMenuItem @if(Route::is('sales.index') ) active-link @endif"> 
            <a class="nav-link uppercase" href="{{ route('sales.index') }}">
                <div><i class="fa fa-xl fa-headset" style="font-size: 30px;"></i></div>
                <div class="sideMenuText"> {{ __('menu.sales') }}</div>
            </a>
        </div>
        <div class="text-center mobileMenuItem @if(request()->routeIs('erp.oms.*', 'admin.tools.oms.*')) active-link @endif">
            <a class="nav-link uppercase" href="{{ route('erp.oms.simple') }}">
                <div><i class="fa-solid fa-warehouse" style="font-size: 30px;"></i></div>
                <div class="sideMenuText"> ERP </div>
            </a>
        </div>
        <div class="text-center mobileMenuItem @if(Route::is('documentsManager.index')) active-link @endif">
            <a class="nav-link uppercase" href="{{ route('documentsManager.index') }}">
                <div><i class="fa-solid fa-folder" style="font-size: 30px;"></i></div>
                <div class="sideMenuText">Documents</div>
            </a>
        </div>
        <div class="text-center mobileMenuItem @if(request()->routeIs('purchase.tools.suppliers.map.*')) active-link @endif">
            <a class="nav-link uppercase" href="{{ route('purchase.tools.suppliers.map.index') }}">
                <div><i class="fa-solid fa-map-location-dot" style="font-size: 30px;"></i></div>
                <div class="sideMenuText">Supplier map</div>
            </a>
        </div>
        <div class="text-center mobileMenuItem @if(request()->routeIs('logistics.tools.shipping.*')) active-link @endif">
            <a class="nav-link uppercase" href="{{ route('logistics.tools.shipping.index') }}">
                <div><i class="fa-solid fa-truck-fast" style="font-size: 30px;"></i></div>
                <div class="sideMenuText">Ship stats</div>
            </a>
        </div>
        <div class="text-center mobileMenuItem @if(Route::is('afterSales.index')) active-link @endif">
            <a class="nav-link uppercase" href="{{ route('afterSales.index') }}">
                <div><i class="fa-solid fa-headset" style="font-size: 30px;"></i></div>
                <div class="sideMenuText">After sales</div>
            </a>
        </div>
        <div class="text-center mobileMenuItem @if(Route::is('design.index')) active-link @endif">
            <a class="nav-link uppercase" href="{{ route('design.index') }}">
                <div><i class="fa-solid fa-pen-ruler" style="font-size: 30px;"></i></div>
                <div class="sideMenuText">Design</div>
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
