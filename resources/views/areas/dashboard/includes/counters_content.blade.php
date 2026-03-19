<div id="{{$panel->panel}}" data-open="1" class="panel-body" style="overflow-x: scroll;">
    @if($details->name == 'ASD missing images')
        <div style="text-align: center;margin: 5px;">
            <a class="btn btn-info" style="margin: 5px;margin: 0 auto;" href="{{route('marketing.asdMissingImages')}}"> UPDATE ASD MISSING IMAGES</a>
        </div>
    @endif
    
    @if( isset($details->columns) )
        <table class="table table-bordered customTable text-center">
            <tr style="text-transform: uppercase">
                @foreach($details->columns AS $column)
                    @if($column != 'other')
                        @if($column == 'email')
                        @else
                            <td> @if($column == 'clean') @else {{ __('tags.' . $column) }} @endif </td>
                        @endif
                    @endif
                @endforeach
            </tr>
            @foreach($details->data AS $item)
                @if(isset($details->exception_fields))
                <tr id="row_exception_{{$item[$details->exception_fields[1]]}}" @if(isset($item['tr_color'])) class="{{$item['tr_color']}}" @endif >
                @else
                <tr @if(isset($item['tr_color'])) class="{{$item['tr_color']}}" @endif >
                @endif
                    @foreach($details->columns AS $column)
                        @if($column != 'other')

                            @if($column == 'extra_action') 
                                <td @if(isset($details->link)) onclick="window.location.replace('{{$details->link}}');" @endif> {!! $item[$column] !!} </td>
                            @elseif($column == 'clean') 
                                <td class="{{$column}}"> 
                                    <img src="/admin/images/check.png" onclick="setRowAsChecked( '{{$details->exception_fields[0]}}', '{{$item[$details->exception_fields[1]]}}', '{{$item[$details->exception_fields[2]]}}', '{{addslashes($item[$details->exception_fields[3]])}}', '{{$panel->panel}}' )">     
                                </td>
                            @elseif($column == 'delete') 
                                <td id="delete_{{$item['delete']}}"> <i class="fa-solid fa-trash" onclick="deleteItem({{$item['delete']}}, '{{$details->table}}')" style="color: red; font-size: 20px; cursor: pointer;"></i> </td>
                            @elseif($column == 'send_email') 
                                <td id="send_email_{{$item['send_email']}}"> 
                                    <i class="fa-solid fa-envelope" onclick="requestedProductSendEmail('{{$item['send_email']}}')" style="color: dodgerblue; font-size: 20px; cursor: pointer;"></i>
                                </td>
                            @elseif($column == 'send_email_reviewed') 
                                <td id="send_email_reviwed_{{$item['email']}}"> <i class="fa-solid fa-envelope" onclick="requestedProductSendEmailReviewed({{$item['id_order']}},'{{$item['email']}}')" style="color: dodgerblue; font-size: 20px; cursor: pointer;"></i> </td>
                            @else
                                @if($column == 'email')
                                @else
                                <td @if(isset($details->link)) onclick="window.location.replace('{{$details->link}}');" @endif class="{{$column}}"
                                    @if( isset($details->prestashop) && !isset($details->prestashop['store'])) 
                                        onclick="window.open('https://all-stars-motorsport.com/admin77500/index.php?controller={{$details->prestashop['controller']}}&{{$details->prestashop['element']}}={{$item[$details->prestashop['element']]}}&token={{$details->prestashop['token']}}{{$details->prestashop['extraParameters']}}', '_blank')"  style="cursor: pointer;"
                                    @else
                                        @if( isset($details->prestashop) && isset($details->prestashop['store'])) 
                                            onclick="window.open('https://all-stars-distribution.com/admin77500/index.php?controller={{$details->prestashop['controller']}}&{{$details->prestashop['element']}}={{$item[$details->prestashop['element']]}}&token={{$details->prestashop['token']}}{{$details->prestashop['extraParameters']}}', '_blank')"  style="cursor: pointer;"
                                        @endif
                                    @endif
                                    > 
                                    @if( ( $column == 'pack_quantity' ) && ( $item['pack_quantity'] > $item['stock'] ) && ( $item['cache_is_pack'] == 1 ) )
                                        <span style="color: red;">{!! $item[$column] !!}</span>
                                    @else
                                        {!! $item[$column] !!}
                                    @endif
                                </td>
                                @endif
                            @endif
                        @endif
                        
                    @endforeach
                </tr>
            @endforeach
        </table>
    @endif
    
</div>

<style>
    tr.row_red > td{ background-color: red; color: #FFF; }
    tr.row_yellow > td{ background-color: yellow; }
</style>