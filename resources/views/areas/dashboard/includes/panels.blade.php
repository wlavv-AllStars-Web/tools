<div @if(isset($col)) class="col-lg-{{$col}}" @else class="col-lg-3" @endif>
    <div class="navbar navbar-light customPanel">
    <div class="panel panel-default" style="display: flow-root">
            <div class="panel-heading text-center" style="cursor:pointer; text-transform:uppercase;background-color: #dedede">

                @if( isset($info) )
                    <div onclick="@if(!isset($direct_link)) $('.{{$item_id}}').toggle() @else window.open('{{$direct_link}}', '_blank') @endif" id="{{$item_id}}_quantity" style="pointer-events: none;width: 50px; height: 33px;padding: 5px 0;float: right; color: white; background-color: dodgerblue; @if( $counter < 1) cursor: default; @endif">{{$counter}}</div>
                @else
                    <div onclick="@if(!isset($direct_link)) $('.{{$item_id}}').toggle() @else window.open('{{$direct_link}}', '_blank') @endif" id="{{$item_id}}_quantity" style="pointer-events: none;width: 50px; height: 33px;padding: 5px 0;float: right; color: white; @if($counter > 0) background-color: red; @else background-color: dodgerblue; @endif @if( $counter < 1) cursor: default; @endif">{{$counter}}</div>
                @endif
            
            </div>
            <div class="panel-body @if( $counter > 0) {{$item_id}} @endif" style="display: none;">
                <table class="table table-bordered customTable">
                    <tr style="text-transform: uppercase">
                        @foreach($columns AS $column)
                            @if($column != 'other')
                                <td> @if($column == 'clean') @else {{ __('tags.' . $column) }} @endif </td>
                            @endif
                        @endforeach
                    </tr>
                    
                    @foreach($data AS $item)
                        @if(isset($exception_fields))
                        <tr id="row_exception_{{$item[$exception_fields[1]]}}">
                        @else
                        <tr>
                        @endif
                            @foreach($columns AS $column)
                                @if($column == 'extra_action') 
                                    <td> {!! $item[$column] !!} </td>
                                @elseif($column == 'clean') 
                                    <td class="{{$column}}"> 
                                        <img src="/admin/images/check.png" onclick="setRowAsChecked( '{{$exception_fields[0]}}', '{{$item[$exception_fields[1]]}}', '{{$item[$exception_fields[2]]}}', '{{$item[$exception_fields[3]]}}', '{{$item_id}}' )">     
                                    </td>
                                @elseif($column == 'send_email') 
                                    <td id="send_email_{{$item['send_email']}}"> <i class="fa-solid fa-envelope" onclick="requestedProductSendEmail('{{$item['send_email']}}')" style="color: dodgerblue; font-size: 20px; cursor: pointer;"></i> </td>
                                @else
                                    <td class="{{$column}}" @if( isset($prestashop) ) onclick="window.open('{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/admin77500/index.php?controller={{$prestashop['controller']}}&id_product={{$item[$prestashop['element']]}}&token={{$prestashop['token']}}{{$prestashop['extraParameters']}}', '_blank')" style="cursor: pointer;"@endif> 
                                        {!! $item[$column] !!}    
                                    </td>
                                @endif
                                @if($column != 'other')
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                    
                </table>
            </div>
        </div>
    </div>
</div>
