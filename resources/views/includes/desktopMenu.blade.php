<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
    <div class="container" style="display: inline-block;padding: 0;">
        <a class="navbar-brand sideMenuLogo" href="https://www.allstars-web.com/" target="_blank">
            <img src="/admin/images/allstarsweb.gif" style="width: calc(100% - 20px); margin: 10px; @guest display: none; @endif">
        </a>
        <div class="navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto margin-auto sideMenuUL">
                <li class="nav-item text-center @if(Route::is('dashboard.index') ) active-link @endif" style="display: inline-block;height: 90px;"> 
                    <a class="nav-link uppercase" href="{{ route('dashboard.index') }}"> 
                        <div><i class="fa-solid fa-chart-pie" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText">{{ __('menu.dashboard') }}</div>
                    </a> 
                </li>
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
                        <div class="sideMenuText"> BACKOFFICE </div>
                    </a>
                </li>
                <li class="nav-item text-center @if(Route::is('sales.index') ) active-link @endif" style="display: inline-block;height: 90px;"> 
                    <a class="nav-link uppercase" href="{{ route('sales.index') }}">
                        <div><i class="fa-solid fa-comments-dollar" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> FRONTOFFICE </div>
                    </a>
                </li>
                
                <li class="nav-item text-center @if(Route::is('data.index') ) active-link @endif" style="display: inline-block;height: 90px;"> 
                    <a class="nav-link uppercase" href="{{ route('data.index') }}">
                        <div><i class="fa-solid fa-database" style="font-size: 30px;"></i></div>
                        <div class="sideMenuText"> {{ __('menu.data') }}</div>
                    </a> 
                </li>

                <li class="nav-item text-center @if(Route::is('checklist.index') || Route::is('checklist.today')) active-link @endif" style="display: inline-block;height: unset !important;"> 
                    @if(auth()->user()->id == 59 || auth()->user()->id == 94 || auth()->user()->id == 43)
                
                        <a class="nav-link uppercase" data-bs-toggle="collapse" href="#collapseChecklist" role="button" aria-expanded="false" aria-controls="collapseChecklist">
                            <div><i class="fa-solid fa-list" style="font-size:30px;"></i></div>
                            <div class="sideMenuText">{{ __('messages.Checklist') }}</div>
                        </a>
                        <div class="collapse bg-secondary" id="collapseChecklist">
                            <a class="nav-link uppercase text-white" href="{{ route('checklist.index') }}">
                                <div><i class="fa-solid fa-clipboard-list" style="font-size:22px;"></i></div>
                                <div class="sideMenuText"> {{ __('messages.Checklist Manager') }}</div>
                            </a>
                            <a class="nav-link uppercase text-white" href="{{ route('checklist.today') }}">
                                <div><i class="fa-solid fa-list-check" style="font-size: 22px;"></i></div>
                                <div class="sideMenuText"> {{ __('messages.Checklist') }}</div>
                            </a>
                        </div>
                    @else
                        <a class="nav-link uppercase" href="{{ route('checklist.today') }}">
                            <div><i class="fa-solid fa-clipboard-list" style="font-size:30px;"></i></div>
                            <div class="sideMenuText"> {{ __('messages.Checklist') }}</div>
                        </a>
                    @endif
                    
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