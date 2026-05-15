<div class="row">
    @foreach($panels AS $panel)
        <div class="col-lg-3" @if( $panel->counter > 0) onclick="alert('{{$panel->panel}}')" @endif>
            <div class="navbar navbar-light customPanel">
                <div class="panel panel-default" style="display: flow-root">
                    <div class="panel-heading text-center" style="cursor:pointer; text-transform:uppercase;background-color: #dedede">
                        <div style="pointer-events: none;width: 50px; height: 33px;padding: 5px 0;float: right; color: white; @if($panel->counter > 0) background-color: red; @else background-color: #0BDA51; @endif @if( $panel->counter < 1) cursor: default; @endif">{{$panel->counter}}</div>
                    </div>
                    <div class="panel-body panel_{{$panel->panel}}" style="display: none;"> </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
