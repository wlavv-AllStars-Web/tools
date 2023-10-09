<div style="display: inline-block;padding: 0;text-align: left;">
        <div><h3 style="padding-left: 20px;text-transform: uppercase;">{{ __('messages.' . Route::currentRouteName()) }}</h3></div>
    <ul style="list-style: none;padding-left: 20px; margin-bottom: 0; display: inline-flex;">
        <li> <a href="{{route('dashboard')}}" style="text-transform: uppercase;color: #666;text-decoration: none;">{{ __('messages.home') }} </a> </li>
        @if(isset($breadcrumbs[0]))<li> <span> <i class="fa fa-chevron-right" style="color: dodgerblue; margin: 0 10px;"></i> </span> </li> @endif
        @if(isset($breadcrumbs) && (count($breadcrumbs) > 0) )
            @foreach ($breadcrumbs as $key => $breadcrumb)
                <li> <a href="{{$breadcrumb['url']}}" style="text-transform: uppercase;color: #666;text-decoration: none;">{{ __('messages.' . $breadcrumb['name']) }} </a> </li>
                @if(isset($breadcrumbs[$key+1]))<li> <span> <i class="fa fa-chevron-right" style="color: dodgerblue; margin: 0 10px;"></i> </span> </li>@endif
            @endforeach
        @endif
    </ul>
</div>