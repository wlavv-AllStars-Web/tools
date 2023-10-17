<div @if(isset($col)) class="col-lg-{{$col}}" @else class="col-lg-3" @endif>
    <div class="navbar navbar-light customPanel">
    <div class="panel panel-default" style="display: flow-root">
            <div class="panel-heading text-center" style="cursor:pointer; text-transform:uppercase;background-color: #dedede">
                <div onclick="$('.{{$item_id}}').toggle()" style="width: calc(100% - 30px); float: left; background-color: #dedede;height: 33px; padding: 5px 0;">{{$name}}</div>
                <div onclick="$('.{{$item_id}}').toggle()" id="{{$item_id}}_quantity" style="pointer-events: none;width: 30px; height: 33px;padding: 5px 0;float: right; color: white; @if($counter > 0) background-color: red; @else background-color: dodgerblue; @endif">{{$counter}}</div>
            </div>
            <div class="panel-body {{$item_id}}" style="display: none;">
                <table class="table table-bordered customTable">
                    <tr style="text-transform: uppercase">
                        @foreach($columns AS $column) <td>{{$column}}</td> @endforeach
                    </tr>
                    @foreach($data AS $item)
                        <tr>
                            @foreach($columns AS $column)
                                <td >{{$item[$column]}}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>