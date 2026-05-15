<div id="parentOptionContainer">
    <select id="parentID" style="width: 100%; text-align: left;">
        @foreach($options AS $option)
            <option value="{{$option->id_option}}">{{$option->name}}</option>
        @endforeach
    </select>
</div>
