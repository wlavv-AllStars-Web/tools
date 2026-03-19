<div id="option_{{$type}}">
    <select id="option_select_{{$type}}" style="width: 100%; text-align: center;font-size: 18px;" onchange="callAjaxOptions({{$type}})">
        @if( $type == 1 ) <option value="">BRAND</option> @endif
        @if( $type == 2 ) <option value="">MODEL</option> @endif
        @if( $type == 3 ) <option value="">TYPE</option> @endif
        @if( $type == 4 ) <option value="">VERSION</option> @endif

        @foreach($options AS $option)
            <option value="{{$option->id_option}}">{{$option->name}}</option>
        @endforeach
    </select>
</div>
