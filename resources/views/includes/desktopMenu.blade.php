<nav class="side-menu-nav">
    <div class="side-menu-container">
        <a class="navbar-brand sideMenuLogo" href="{{ route('dashboard.index') }}">
            <img src="/admin/images/allstarsweb.gif" alt="All Stars Group">
        </a>
        <div class="side-menu-heading"><span class="side-menu-heading__accent"></span>Control panel</div>
        <div class="side-menu-content">
            <ul class="sideMenuUL">

                @if( ( Auth::id() == 2 ) || ( Auth::id() == 43 ) )
                <li class="nav-item text-center @if(Route::is('web.index') ) active-link @endif" style="display: inline-block;height: 90px;"> 
                    <a class="nav-link uppercase" href="{{ route('web.index') }}">
                        <div><i class="fa-solid fa-code" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> {{ __('menu.webmaster') }}</div>
                    </a> 
                </li>
                @endif
                <li class="nav-item text-center @if(Route::is('finance.index') ) active-link @endif" style="display: inline-block;height: 90px;"> 
                    <a class="nav-link uppercase" href="{{ route('finance.index') }}">
                        <div><i class="fa-solid fa-chart-line" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> ACCOUNTING </div>
                    </a> 
                </li>
                <li class="nav-item text-center @if(Route::is('logistics.index') ) active-link @endif" style="display: inline-block;height: 90px;"> 
                    <a class="nav-link uppercase" href="{{ route('logistics.index') }}">
                        <div><i class="fa-solid fa-box" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> {{ __('menu.logistics') }}</div>
                    </a> 
                </li>
                <li class="nav-item text-center @if(Route::is('marketing.index') ) active-link @endif" style="display: inline-block;height: 90px;"> 
                    <a class="nav-link uppercase" href="{{ route('marketing.index') }}">
                        <div><i class="fa-solid fa-bullhorn" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> STUDIO </div>
                    </a> 
                </li>
                <li class="nav-item text-center @if(Route::is('purchase.index') ) active-link @endif" style="display: inline-block;height: 90px;"> 
                    <a class="nav-link uppercase" href="{{ route('purchase.index') }}">
                        <div><i class="fa-solid fa-dollar-sign" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> PURCHASE </div>
                    </a>
                </li>
                <li class="nav-item text-center @if(Route::is('sales.index') ) active-link @endif" style="display: inline-block;height: 90px;"> 
                    <a class="nav-link uppercase" href="{{ route('sales.index') }}">
                        <div><i class="fa-solid fa-comments-dollar" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> SALES </div>
                    </a>
                </li>
                <li class="nav-item text-center @if(request()->routeIs('erp.oms.*', 'admin.tools.oms.*')) active-link @endif" style="display: inline-block;height: 90px;">
                    <a class="nav-link uppercase" href="{{ route('erp.oms.simple') }}">
                        <div><i class="fa-solid fa-warehouse" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> ERP </div>
                    </a>
                </li>
                <li class="nav-item text-center @if(Route::is('documentsManager.index') ) active-link @endif" style="display: inline-block;height: 90px;"> 
                    <a class="nav-link uppercase" href="{{ route('documentsManager.index') }}">
                        <div><i class="fa-solid fa-folder" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> DOCUMENTS </div>
                    </a>
                </li>
                <li class="nav-item text-center @if(request()->routeIs('suppliersMap.*', 'purchase.tools.suppliers.map.*', 'backoffice.tools.suppliers.map.*')) active-link @endif" style="display: inline-block;height: 90px;">
                    <a class="nav-link uppercase" href="{{ route('purchase.tools.suppliers.map.index') }}">
                        <div><i class="fa-solid fa-map-location-dot" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> SUPPLIER MAP </div>
                    </a>
                </li>
                <li class="nav-item text-center @if(request()->routeIs('logistics.tools.shipping.*')) active-link @endif" style="display: inline-block;height: 90px;">
                    <a class="nav-link uppercase" href="{{ route('logistics.tools.shipping.index') }}">
                        <div><i class="fa-solid fa-truck-fast" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> SHIP STATS </div>
                    </a>
                </li>
                <li class="nav-item text-center @if(Route::is('afterSales.index')) active-link @endif" style="display: inline-block;height: 90px;">
                    <a class="nav-link uppercase" href="{{ route('afterSales.index') }}">
                        <div><i class="fa-solid fa-headset" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> AFTER SALES </div>
                    </a>
                </li>
                <li class="nav-item text-center @if(Route::is('design.index')) active-link @endif" style="display: inline-block;height: 90px;">
                    <a class="nav-link uppercase" href="{{ route('design.index') }}">
                        <div><i class="fa-solid fa-pen-ruler" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> DESIGN </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
