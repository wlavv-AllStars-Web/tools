<div id="backorders_suppliers_holder">
    <table style="width: 100%;">
       <tr>
           <td colspan="7">
                <div style="text-align: left;font-size: 22px; font-weight: bolder;padding: 20px 0 25px 0"> 
                    BACK ORDERS OVERVIEW - LAST 30 DAYS EXCLUDED - 
                        <span style="color: dodgerblue;">{{ $dataView['selected_supplier_name'] }}</span>
                        @if( $dataView['quantity_replied'] != $dataView['number_of_rows'])
                            <a onclick="send_email()" class="btn btn-info" href="#" style="float: right;color: #FFF;"><i style="color: #FFF;" class="fa-regular fa-paper-plane"></i> SEND</a>
                        @endif
                </div>               
           </td>
       </tr>
       <tr> <td colspan="7" style="height: 5px;background-color: #666;"> </td> </tr>
   </table>

    <div style="font-weight: bolder; font-size: 22px;text-align: left;"> 
        <span class="navbar navbar-light customPanel" style="margin: 5px; color: black; width: 110px; float: left;text-align: center;"> QTY </span>
        <span class="navbar navbar-light customPanel" style="margin: 5px; width: calc( 100% - 250px); float: left;text-align: center">ORDER ID</span>
        <span class="navbar navbar-light customPanel" style="margin: 5px; width: 110px; float: left;text-align: center;">MONTH</span>
    </div>
    
    <?php $total_pending = 0 ?>

    @foreach($dataView['openOrders'] AS $month => $order)

        <?php $pending = 0 ?>
        <?php $refs = array() ?>
    
        <div id="container_row_{{Key($order)}}">       

            @foreach($order AS $order_rows)       
                @foreach($order_rows AS $row)
                    @if( ( $row->qty_ordered - $row->qty_received ) != 0 )
                       <?php $pending += ($row->qty_ordered - $row->qty_billed ) ?>
                       <?$refs[$row->order_id] = $row->order_reference ?>
                    @else
                       <?php $pending += ($row->qty_ordered - $row->qty_billed ) ?>
                       <?$refs[$row->order_id] = $row->order_reference ?>
                    @endif
                @endforeach
            @endforeach

            <?php $order_reference = '' ?>
            <?php $total_pending += $pending ?>
            <?php $pending_by_order = 0 ?>
            <?php $total_pending_by_order = 0 ?>
            
            <div id="row_{{Key($order)}}" style="cursor: pointer; font-weight: bolder; font-size: 18px;text-align: left;" onclick="$('#row_data_{{Key($order)}}').toggle();"> 
                <span class="navbar navbar-light customPanel" style="margin: 5px; color: red; width: 110px; float: left;text-align: center;"> {{ $pending }} </span>
                <span class="navbar navbar-light customPanel" style="margin: 5px; width: calc( 100% - 250px); float: left;text-align: center;">{!! implode(' <span style="color: red;">|</span> ', $refs) !!}</span>
                <span class="navbar navbar-light customPanel" style="margin: 5px; width: 110px; float: left;text-align: center;">{{ $month }}</span>
            </div> 
            
            <div id="row_data_{{Key($order)}}" style="display: none;">
                <table style="width: calc(100% - 20px);margin: 30px 10px;font-size: 18px" class="table table-striped">
                    <tr> <td colspan="6" style="height: 3px;background-color: #666;padding: 3px;"></td> </tr>
                    @foreach($order AS $k => $order_rows)  
                        <tr> <td colspan="7" style="height: 30px; background-color: #FFF;"></td> </tr>
                        <tr style="text-align: center;">
                           <td style="width: 15%">{{ __('messages.order reference')}}</td>
                           <td style="width: 20%">{{ __('messages.product reference')}}</td>
                           <td style="width: 7%">{{ __('messages.ordered')}}</td>
                           <td style="width: 9%">{{ __('messages.billed')}}</td>
                           <td style="width: 9%">{{ __('messages.pending')}}</td>
                           <td style="width: 40%">COMMENTS</td>
                        </tr>
                        <tr> <td colspan="6" style="height: 3px;background-color: #666;padding: 3px;"></td> </tr>                                 
    
                                    <?php $total_pending_by_order += $pending_by_order ?>
                                    <?php $pending_by_order = 0 ?>
                        <?php $order_reference = $order_rows[0]->order_reference ?>
                        @foreach($order_rows AS $counter => $row)
                            @if( $counter < count($order_rows) )
                                <tr>
                                   <td style="text-align: center; @if($row->in_backorder == 1) background-color: lightgreen !important; @endif @if($row->in_backorder == 0) background-color: #f8d7da !important; @endif">{{ $row->order_reference}}</td>
                                   <td style="text-align: center; @if($row->in_backorder == 1) background-color: lightgreen !important; @endif @if($row->in_backorder == 0) background-color: #f8d7da !important; @endif">{{ $row->product_reference}}</td>
                                   <td style="text-align: center; @if($row->in_backorder == 1) background-color: lightgreen !important; @endif @if($row->in_backorder == 0) background-color: #f8d7da !important; @endif">{{ $row->qty_ordered }}</td>
                                   <td style="text-align: center; @if($row->in_backorder == 1) background-color: lightgreen !important; @endif @if($row->in_backorder == 0) background-color: #f8d7da !important; @endif">{{ $row->qty_billed }}</td>
                                   <td style="text-align: center; color: red;@if($row->in_backorder == 1) background-color: lightgreen !important; @endif @if($row->in_backorder == 0) background-color: #f8d7da !important; @endif">{{ ($row->qty_ordered - $row->qty_billed ) }}</td>
                                   <td style="text-align: center; color: dodgerblue;font-weight: bolder;@if($row->in_backorder == 1) background-color: lightgreen !important; @endif @if($row->in_backorder == 0) background-color: #f8d7da !important; @endif">{{ $row->supplier_comment }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endforeach

                    @if( $pending > 0 )
                        <tr style="text-align: center;">
                           <td colspan="2"></td>
                           <td colspan="2" style="text-align: right;font-weight: bolder; font-size: 18px;">SUBTOTAL: </td>
                           <td style="font-weight: bolder; font-size: 18px;color: red;">{{$pending - $total_pending_by_order}}</td>
                           <td></td>
                        </tr>
                    @else
                    @endif
                </table>
            </div>
        </div>
        
    @endforeach

    <table style="width: 100%;margin-top: 20px;">
        <tr> <td colspan="7" style="height: 5px;background-color: #666;">  </td> </tr>
        <tr> <td colspan="7" style="height: 10px;background-color: #fff;"> </td> </tr>
        <tr> <td colspan="7"> <div style="font-size: 20px; font-weight: bolder;"> TOTAL PENDING PRODUCTS: <span style="color: red;">{{ $total_pending }}</span></div> </td> </tr>
    </table>
</div>

<script>
    function send_email(){
        $.ajax({
            type: 'GET',
            url: "{{route('suppliersBackorders.send_report', ['id_supplier' => $dataView['selected_supplier_id'], 'token' => $dataView['token'] ])}}",
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(response) {
                
                Swal.fire({
                  icon: "success",
                  title: "Backorder's email",
                  text: "Backorder's email sent to supplier!",
                });
            }       
        });
    }
</script>