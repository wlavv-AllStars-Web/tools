<div style="text-align: right;padding-right: 10px;">
    @if(isset($actions[0]) && (count($actions[0]) > 0) )
        @foreach ($actions as $key => $action)

            @if( isset($action['onclick']))
                <button class="btn {{$action['class']}} btn-dimensions" onclick="{{$action['onclick']}}">
                    {{ __('messages.' . $action['name']) }} 
                </button>
            @else
                <a class="btn {{$action['class']}} btn-dimensions" href="{{$action['url']}}">
                    {{ __('messages.' . $action['name']) }} 
                </a>
            @endif
        @endforeach
    @endif
</div>