<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
    <div class="container" style="display: inline-block;padding: 0;">
        <a class="navbar-brand sideMenuLogo" href="{{ route('dashboard.index') }}">
            <img src="/admin/images/allstarsweb.gif" style="width: calc(100% - 20px); margin: 10px; @guest display: none; @endif">
        </a>
        <div class="navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto margin-auto sideMenuUL">

                @if( ( Auth::id() == 2 ) || ( Auth::id() == 43 ) )
                <li class="nav-item text-center @if(Route::is('web.index') ) active-link @endif" style="display: inline-block;height: 90px;"> 
                    <a class="nav-link uppercase" href="{{ route('web.index') }}">
                        <div><i class="fa-solid fa-code" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> {{ __('menu.webmaster') }}</div>
                    </a> 
                </li>
                @endif
                <li class="nav-item text-center @if(Route::is('administration.index') ) active-link @endif" style="display: inline-block;height: 90px;"> 
                    <a class="nav-link uppercase" href="{{ route('administration.index') }}">
                        <div><i class="fa-solid fa-people-roof" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> {{ __('menu.admin') }}</div>
                    </a> 
                </li>
                <li class="nav-item text-center @if(Route::is('finance.index') ) active-link @endif" style="display: inline-block;height: 90px;"> 
                    <a class="nav-link uppercase" href="{{ route('finance.index') }}">
                        <div><i class="fa-solid fa-chart-line" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> {{ __('menu.finance') }}</div>
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
                        <div class="sideMenuText"> {{ __('menu.marketing') }}</div>
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
                <li class="nav-item text-center @if(Route::is('documentsManager.index') ) active-link @endif" style="display: inline-block;height: 90px;"> 
                    <a class="nav-link uppercase" href="{{ route('documentsManager.index') }}">
                        <div><i class="fa-solid fa-folder" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> {{ __('messages.Documents Manager') }}</div>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>