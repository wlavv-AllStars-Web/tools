<div>
    <select id="store" style="width: 100%; text-align: center;font-size: 18px;">
        @foreach($options AS $key => $option)
            <option value="{{$key}}">{{$option}}</option>
        @endforeach
    </select>
</div>
