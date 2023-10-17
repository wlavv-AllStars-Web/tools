<div @if(isset($col)) class="col-lg-{{$col}}" @else class="col-lg-1" @endif>
    <div class="navbar navbar-light customPanel">
    <div class="panel panel-default" style="display: flow-root">
            <div class="panel-heading text-center" style="cursor:pointer; text-transform:uppercase;">
            <div onclick="$('.{{$item_id}}').toggle()" id="{{$item_id}}_quantity" style="border-radius: 5px; padding: 5px 0; color: white; @if($counter > 0) background-color: red; @else background-color: dodgerblue; @endif">
                <div style="font-size: 50px">{{$counter}}</div>
                <div style="font-size: 18px">{{$name}}</div>
            </div>
            </div>
            <div class="panel-body {{$item_id}}" style="display: none;">
                <table class="table table-bordered customTable text-center">
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