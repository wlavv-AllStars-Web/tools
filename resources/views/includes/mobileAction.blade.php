<div style="text-align: right;padding-right: 5px;margin-top: 5px">
    @if(isset($actions[0]) && (count($actions[0]) > 0) )
        @foreach ($actions as $key => $action)
            @if( isset($action['onclick']))
                <button class="btn {{$action['class']}} btn-dimensions" onclick="{{$action['onclick']}}"> {!! $action['icon'] !!} <span class="f-left" style="margin: 0 5px;"></span> </button>
            @else
                <a class="btn {{$action['class']}} btn-dimensions" href="{{$action['url']}}"> {!! $action['icon'] !!} <span class="f-left m-0-5" style="margin: 0 5px;"></span> </a>
            @endif
        @endforeach
    @endif
</div>