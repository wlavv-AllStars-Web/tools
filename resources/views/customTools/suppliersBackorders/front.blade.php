<!DOCTYPE html>
<html>
    <head>
        <title>ALL STARS BACK ORDERS OVERVIEW - LAST 30 DAYS NOT INCLUDED - {{ $dataView['selected_supplier_name'] }}</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="https://webtools.all-stars-motorsport.com/admin/css/sweetalert2.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://webtools.all-stars-motorsport.com/admin/js/sweetalert2.min.js"></script>
    </head>
    <body>
        <style> .customPanel{ background-color: #fff;border: 1px solid #ddd;border-radius: 5px;display:block; padding: 10px;margin-top: 10px; } </style>
        <div style="width: 1500px; margin: 0 auto;">
            <table style="width: 100%;">
                <tr>
                    <td style="text-align: center;">
                        <img src="https://www.all-stars-distribution.com/img/all-stars-distribution-logo-1611565520.png" style="padding: 20px;">
                    </td>
                </tr>
                <tr>
                    <td colspan="7">
                       <div style="text-align: center;font-size: 20px; font-weight: bolder;padding: 20px 0 25px 0"> 
                            BACK ORDERS OVERVIEW - LAST 30 DAYS EXCLUDED - 
                            <span style="color: dodgerblue;">{{ $dataView['selected_supplier_name'] }}</span>
                       </div>               
                    </td>
                </tr>
                <tr> <td colspan="7" style="height: 3px;background-color: #666;"> </td> </tr>
           </table>
            
            <div style="margin: 20px 0;">
                <div style="font-weight: bolder; font-size: 22px;text-align: left;display: flex;"> 
                    <span class="navbar navbar-light customPanel" style="margin: 5px; color: black; width: 110px; float: left;text-align: center;"> QTY </span>
                    <span class="navbar navbar-light customPanel" style="margin: 5px; width: calc( 100% - 250px); float: left;text-align: center">ORDER ID</span>
                    <span class="navbar navbar-light customPanel" style="margin: 5px; width: 110px; float: left;text-align: center;">MONTH</span>
                </div>
        
                <?php $total_pending = 0 ?>
            
                @foreach($dataView['openOrders'] AS $month => $order)
            
                    <?php $pending = 0 ?>
                    <?php $refs = array() ?>
                
                    <div id="container_row_{{Key($order)}}" style="display: block;">       
            
                        @foreach($order AS $order_rows)       
                            @foreach($order_rows AS $row)
                                @if( ( $row->qty_ordered - $row->qty_billed ) != 0 )
                                   <?php $pending += ($row->qty_ordered - $row->qty_billed ) ?>
                                   <?$refs[$row->order_id] = $row->order_reference ?>
                                @endif
                            @endforeach
                        @endforeach
            
                        <?php $total_pending += $pending ?>
                        <?php $pending_by_order = 0 ?>
                        <?php $total_pending_by_order = 0 ?>
    
                        <div id="row_{{Key($order)}}" style="cursor: pointer; font-weight: bolder; font-size: 18px;text-align: left;display: flex;" onclick="$('.tabs_orders').css('display', 'none'); $('#row_data_{{Key($order)}}').toggle();"> 
                            <span class="navbar navbar-light customPanel" style="margin: 5px; color: red; width: 110px; float: left;text-align: center;"> {{ $pending }} </span>
                            <span class="navbar navbar-light customPanel" style="margin: 5px; width: calc( 100% - 250px); float: left;text-align: center;">{!! implode(' <span style="color: red;">|</span> ', $refs) !!}</span>
                            <span class="navbar navbar-light customPanel" style="margin: 5px; width: 110px; float: left;text-align: center;">{{ $month }}</span>
                        </div> 
    
                        <div class="tabs_orders" id="row_data_{{Key($order)}}" style="display: none;font-size: 18px;font-weight: bolder;">
                            <table style="width: calc(100% - 30px); margin: 40px 15px;" >
                                <tr>
                                   <td colspan="7" style="height: 3px;background-color: #666;"> </td>
                                </tr>
                                @foreach($order AS $order_rows) 
                                    
                                    <tr> <td colspan="7" style="height: 30px; background-color: #FFF;"></td> </tr>
                                    <tr style="text-align: center;">
                                       <td style="width: 15%">{{ __('messages.order reference')}}</td>
                                       <td style="width: 15%">{{ __('messages.product reference')}}</td>
                                       <td style="width: 5%">{{ __('messages.ordered')}}</td>
                                       <td style="width: 5%">{{ __('messages.billed')}}</td>
                                       <td style="width: 5%">{{ __('messages.pending')}}</td>
                                       <td style="width: 15%;">{{ __('messages.still in backorder?')}}</td>
                                       <td style="width: 35%;">{{ __('messages.comment')}}</td>
                                    </tr>
                                    <tr style="text-align: center;">
                                       <td colspan="7" style="height: 5px; background-color: #666;"></td>
                                    </tr>                                 
    
                                    <?php $total_pending_by_order += $pending_by_order ?>
                                    <?php $pending_by_order = 0 ?>
                                            
                                    @foreach($order_rows AS $row)
                                    
                                        @if( ( $row->qty_ordered - $row->qty_billed ) != 0 )
                                           <tr style="text-align: center;">
                                               <td>{{ $row->order_reference}}</td>
                                               <td>{{ $row->product_reference}}</td>
                                               <td>{{ $row->qty_ordered }}</td>
                                               <td>{{ $row->qty_billed }}</td>
                                               <td>{{ ($row->qty_ordered - $row->qty_billed ) }}</td>
                                               <td>
                                                   @if($row->in_backorder != 2)
                                                        <input id="input_option_{{$row->id}}" class="pending_inputs" type="hidden" value="0">
                                                   @else
                                                        <input id="input_option_{{$row->id}}" class="pending_inputs" type="hidden" value="1">
                                                   @endif
                                                   <div style="width: 50%; float: left;text-align: center;">
                                                       <label>
                                                           <input @if($row->in_backorder == 1 ) checked="checked" @endif type="radio" name="comment_option_{{$row->id}}" onclick="updateBackorder({{$row->id}}, {{$row->id_supplier}}, 1)"> YES
                                                       </label>
                                                   </div>
                                                   <div style="width: 50%; float: left;text-align: center;">
                                                       <label>
                                                           <input @if($row->in_backorder < 1 ) checked="checked" @endif type="radio" name="comment_option_{{$row->id}}" onclick="updateBackorder({{$row->id}}, {{$row->id_supplier}}, 0)"> NO
                                                       </label>
                                                   </div>
                                                </td>
                                                <td>
                                                    <input id="comment_{{$row->id}}" type="text" value="{{$row->supplier_comment}}" name="comment" onfocusout="updateComment({{$row->id}}, {{$row->id_supplier}})" style="width: 100%;">
                                                </td>
                                           </tr>
                                           <?php $pending_by_order += ($row->qty_ordered - $row->qty_billed ) ?>
                                       @endif
                                    @endforeach
                                
                                    <tr style="text-align: center;"> <td colspan="7" style="height: 5px; background-color: #666;"></td> </tr>
                                    <tr style="text-align: center;background-color: #eee;border: 1px solid #ddd;">
                                       <td colspan="4" style="font-weight: bolder; font-size: 20px;text-align: right;" ><span>TOTAL: </span></td>
                                       <td colspan="1" style="font-weight: bolder; font-size: 20px;"><span style="color: red;">{{$pending_by_order}}</span></td>
                                       <td colspan="2"></td>
                                    </tr>
                                @endforeach
                                @if( $pending > 0 )
                                @else
                                    <style> #container_row_{{ Key($order) }}{ display: none; }</style>
                                @endif
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
            <table style="width: 100%;margin-top: 20px; font-size: 20px;">
                <tr> <td colspan="7" style="height: 3px;background-color: #666;">  </td> </tr>
                <tr> <td colspan="7" style="height: 10px;background-color: #fff;"> </td> </tr>
                <tr>
                   <td colspan="2"> <div style="text-align: left;font-size: 20px; font-weight: bolder;"> TOTAL: <span style="color: red;">{{ $total_pending }}</span> PRODUCTS</div> </td>
                   <td colspan="3"> </td>
                   <td colspan="2"> <a class="btn btn-info" style="float: right; width: 250px;border-radius: 2px;color:#fff; background-color: #0273EB;border: 1px solid #025bb9" href="#" onclick="checkForm()"> CONFIRM </a> </td>
                </tr>
            </table>
        </div>
    </body>
    
    <script>
        
        function updateBackorder(id, id_supplier, in_backorder){
            
            $('#input_option_' + id).val(0);
            
            $.ajax({
                type: 'POST',
                url: "{{route('frontSuppliersBackorders.updateBackorders')}}",
                dataType: "json",
                data: {
                    _token: "{{ csrf_token() }}",
                    id, id,
                    id_supplier: id_supplier,
                    token: {{$dataView['token']}},
                    in_backorder: in_backorder
                },
                success: function(response) { }       
            });
            
        }
    
        function updateComment(id, id_supplier){
            
            comment = $('#comment_' + id).val();
            
            $.ajax({
                type: 'POST',
                url: "{{route('frontSuppliersBackorders.updateComment')}}",
                dataType: "json",
                data: {
                    _token: "{{ csrf_token() }}",
                    id, id,
                    id_supplier: id_supplier,
                    token: {{$dataView['token']}},
                    comment: comment
                },
                success: function(response) { }       
            });
            
        }
    
        function checkForm(){
            
            submit = 1;
            
            $('.pending_inputs').each(function(){
                if($(this).val() == 1) submit = 0;
            });
            
            if(submit == 1) 
                window.location.replace("{{route('frontSuppliersBackorders.thanks', [$dataView['selected_supplier_id'], $dataView['token']])}}");
            else{
                
                Swal.fire({
                  icon: "error",
                  title: "Incomplete form response!",
                  text: "Please, for each product in backorder list, confirm the status.",
                });

            }
        }
    </script>
</html> 