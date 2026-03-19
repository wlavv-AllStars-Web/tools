<div @if(isset($col)) class="col-lg-{{$col}}" @else class="col-lg-1" @endif>
    <div class="navbar navbar-light customPanel">
    <div class="panel panel-default" style="display: flow-root">
            <div class="panel-heading text-center" style="cursor:pointer; text-transform:uppercase;">
                @if( isset($info) )
                <div onclick=" @if(!isset($direct_link)) $('.{{$item_id}}').toggle() @else window.open('{{$direct_link}}', '_blank') @endif" style="height: 100px; border-radius: 5px; padding: 5px 0; color: white; background-color: dodgerblue; @if( $counter < 1) cursor: default; @endif">
                @else
                <div onclick=" @if(!isset($direct_link)) $('.{{$item_id}}').toggle() @else window.open('{{$direct_link}}', '_blank') @endif" style="height: 100px; border-radius: 5px; padding: 5px 0; color: white; @if($counter > 0) background-color: red; @else background-color: #0BDA51; @endif @if( $counter < 1) cursor: default; @endif">
                @endif
                <div style="font-size: 35px" id="{{$item_id}}_quantity">{{$counter}}</div>
                <div style="font-size: 16px; margin: 0 10px;">{{$name}}</div>
            </div>
            </div>
            <div class="panel-body @if( $counter > 0) {{$item_id}} @endif" style="display: none; overflow-x: scroll;">
                @if($name == 'ASD missing images')
                    <div style="text-align: center;margin: 5px;">
                        <a class="btn btn-info" style="margin: 5px;margin: 0 auto;" href="{{route('marketing.asdMissingImages')}}"> UPDATE ASD MISSING IMAGES</a>
                    </div>
                @endif
                <table class="table table-bordered customTable text-center">
                    <tr style="text-transform: uppercase">
                        @foreach($columns AS $column)
                            <td> @if($column == 'clean') @else {{ __('tags.' . $column) }} @endif </td>
                        @endforeach
                    </tr>
                    @foreach($data AS $item)
                        @if(isset($exception_fields))
                        <tr id="row_exception_{{$item[$exception_fields[1]]}}" @if(isset($item['tr_color'])) class="{{$item['tr_color']}}" @endif>
                        @else
                        <tr @if(isset($item['tr_color'])) class="{{$item['tr_color']}}" @endif>
                        @endif
                            @foreach($columns AS $column)
                                @if($column == 'extra_action') 
                                    <td> {!! $item[$column] !!} </td>
                                @elseif($column == 'clean') 
                                    <td class="{{$column}}"> 
                                        <img src="/admin/images/check.png" onclick="setRowAsChecked( '{{$exception_fields[0]}}', '{{$item[$exception_fields[1]]}}', '{{$item[$exception_fields[2]]}}', '{{addslashes($item[$exception_fields[3]])}}', '{{$item_id}}' )">     
                                    </td>
                                @elseif($column == 'delete') 
                                    <td id="delete_{{$item['delete']}}"> <i class="fa-solid fa-trash" onclick="deleteItem({{$item['delete']}}, '{{$table}}')" style="color: red; font-size: 20px; cursor: pointer;"></i> </td>
                                @elseif($column == 'send_email') 
                                    <td id="send_email_{{$item['send_email']}}"> <i class="fa-solid fa-envelope" onclick="requestedProductSendEmail('{{$item['send_email']}}')" style="color: dodgerblue; font-size: 20px; cursor: pointer;"></i> </td>
                                @else
                                    <td class="{{$column}}" @if( isset($prestashop) ) onclick="window.open('https://all-stars-motorsport.com/admin77500/index.php?controller={{$prestashop['controller']}}&{{$prestashop['element']}}={{$item[$prestashop['element']]}}&token={{$prestashop['token']}}{{$prestashop['extraParameters']}}', '_blank')"  style="cursor: pointer;"@endif> 
                                        {!! $item[$column] !!}
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    tr.row_red > td{ background-color: red; color: #FFF; }
    tr.row_yellow > td{ background-color: yellow; }

</style>