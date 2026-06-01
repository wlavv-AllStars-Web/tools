@if( $data->counter > 0)
    <div id="holder_{{$pickingSource}}" class="holders navbar navbar-light specialPanel" @if( !empty($defaultOpen) ) style="display: block;" @else style="display: none;" @endif >
        @foreach($data->data AS $row)
            @include("customTools.picking.order", [ 'row' => $row ])
        @endforeach
    </div>
@else
    <div id="holder_{{$pickingSource}}" class="holders navbar navbar-light specialPanel" @if( !empty($defaultOpen) ) style="display: block;" @else style="display: none;" @endif > <div class="alert alert-warning" role="alert"> NO ORDERS AVAILABLE FOR PICKING! </div> </div>
@endif
