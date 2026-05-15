<div class="containerSupplierBlock" id="container-delivery">

    <div style="width: 100%; display: table;">
        <div class="navbar navbar-light customPanel" onclick="$('.newDeliveryIssue').toggle()" style="width: 100%;padding: 13px;"> ADD NEW DELIVERY ISSUE ? </div>
    </div>
    
    <div class="newDeliveryIssue">
        <div class="navbar navbar-light customPanel">
            <form action="{{route('suppliersIssues.newDeliveryIssue')}}" method="post">
                <div class="row">
                    <div class="col-lg-12">
                        {!! csrf_field() !!}
                        
                        <table class="table table-striped text-center" style="width: 100;">
                            <tr>
                                <td> SUPPLIER </td>
                                <td> #PO </td>
                                <td> PO REFERENCE </td>
                                <td> PRODUCT REF. </td>
                                <td> QTY ORDERED </td>
                                <td> QTY INVOICED </td>
                                <td> INVOICE DATE </td>
                                <td> QTY RECEIVED </td>
                                <td> COMMENT </td>
                                <td> </td>
                            </tr>
                            <tr>
                                <td> 
                                    <select style="width: 100%;border-radius: 2px;box-shadow: none;border: 1px solid #666;padding: 3px;text-align: center;" name="id_supplier">
                                        @foreach($suppliers AS $id_supplier => $supplier)
                                        <option value="{{$id_supplier}}">{{$supplier}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td> <input style="width: 100%;text-align: center;" type="text" name="po_id"> </td>
                                <td> <input style="width: 100%;" type="text" name="po_reference"> </td>
                                <td> <input style="width: 100%;" type="text" name="reference"> </td>
                                <td> <input style="width: 100%;" type="number" name="qty_ordered"> </td>
                                <td> <input style="width: 100%;" type="number" name="qty_invoived"> </td>
                                <td> <input style="width: 100%;" type="date" name="invoice_date"> </td>
                                <td> <input style="width: 100%;" type="number" name="qty_received"> </td>
                                <td> <input style="width: 100%;" type="text" name="comment"> </td>
                                <td> <button type="submit" class="btn btn-success" style="width: 100%;padding: 2px;border-radius: 2px;">SAVE</button> </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="navbar navbar-light customPanel">
        <div class="navbar navbar-light customPanel triggerOpenIssues" onclick="$('.closedIssues').css('display', 'revert');$('.openIssues').css('display', 'none');   $('.triggerOpenIssues').css('display', 'none'); $('.triggerClosedIssues').css('display', 'block');" style="width: 100%;padding: 13px;display: block;color: orange; font-weight: bolder;"> VIEW ARCHIVED DELIVERY ISSUES? </div>
        <div class="navbar navbar-light customPanel triggerClosedIssues" onclick="$('.openIssues').css('display', 'revert');$('.closedIssues').css('display', 'none'); $('.triggerOpenIssues').css('display', 'block'); $('.triggerClosedIssues').css('display', 'none');" style="width: 100%;padding: 13px;display: none;color: red; font-weight: bolder;"> VIEW OPEN DELIVERY ISSUES? </div>
        <table class="table text-center" style="width: 100%;">
            @if( count($deliveryIssues) > 0)
                @foreach($deliveryIssues AS $issue)
                    <tr @if( $issue['open'] > 0) class="openIssues" @else class="closedIssues" @endif onclick="$('.supplier_container').css('display', 'none'); $('.supplier_{{$issue['id_supplier']}}').css('display', 'table-row'); ">
                        <td colspan="10" style="text-align: left; text-transform: uppercase; background-color: #ddd !important;border-bottom: 1px solid #999;">
                            <div style="float: left; width: calc( 100% - 70px); padding-left: 10px;">{{$issue['name']}}</div>
                        </td>
                    </tr>            
                    <tr class="supplier_container supplier_{{$issue['id_supplier']}}" style="display: none;">
                        <td style="background-color: #ccc;"> # </td>
                        <td style="background-color: #ccc;"> REFERENCE </td>
                        <td style="background-color: #ccc;"> PRODUCT </td>
                        <td style="background-color: #ccc;"> ORDERED </td>
                        <td style="background-color: #ccc;"> INVOICED </td>
                        <td style="background-color: #ccc;"> INVOICE DATE </td>
                        <td style="background-color: #ccc;"> RECEIVED </td>
                        <td style="background-color: #ccc;"> PENDING </td>
                        <td style="background-color: #ccc;"> COMMENT </td>
                        <td style="background-color: #ccc;width: 100px;">
                            <button id="supplierDeliveryShowClosed_{{$issue['id_supplier']}}" class="btn btn-warning" type="button" onclick="showHideDelivery({{$issue['id_supplier']}}, 1)" style="padding: 2px;width: 100%;">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button id="supplierDeliveryHideClosed_{{$issue['id_supplier']}}" class="btn btn-danger"  type="button" onclick="showHideDelivery({{$issue['id_supplier']}}, 0)"  style="padding: 2px;width: 100%;display: none;">
                                <i class="fa-solid fa-eye-low-vision"></i>
                            </button>
                        </td>
                    </tr>
                    
                    @foreach($issue['issues'] AS $row)
                        <tr class="supplier_container supplier_{{$row->id_supplier}} @if($row->closed == 1) delivery_done delivery_done_{{$row->id_supplier}} @endif" style="display: none;">
                            <td  @if( $row->closed == 1 ) class="item_done" @endif> {{$row->po_id}} </td>
                            <td  @if( $row->closed == 1 ) class="item_done" @endif> {{$row->po_reference}} </td>
                            <td  @if( $row->closed == 1 ) class="item_done" @endif> {{$row->reference}} </td>
                            <td  @if( $row->closed == 1 ) class="item_done" @endif> 
                                <input class="edit_delivery_{{$row->id}} action_delivery_{{$row->id}}" id="qty_ordered_{{$row->id}}" name="qty_ordered_{{$row->id}}" type="text" value="{{$row->qty_ordered}}" style="display:none;width: 100%;text-align: center;">
                                <span  id="delivery_issue_qty_ordered_{{$row->id}}"  class="show_delivery_{{$row->id}} action_delivery_show_{{$row->id}}">{{$row->qty_ordered}}</span>
                            </td>
                            <td  @if( $row->closed == 1 ) class="item_done" @endif> 
                                <input class="edit_delivery_{{$row->id}} action_delivery_{{$row->id}}" id="qty_invoiced_{{$row->id}}" name="qty_invoiced_{{$row->id}}" type="text" value="{{$row->qty_invoiced}}" style="display:none;width: 100%;text-align: center;">
                                <span  id="delivery_issue_qty_invoiced_{{$row->id}}"  class="show_delivery_{{$row->id}} action_delivery_show_{{$row->id}}">{{$row->qty_invoiced}}</span>
                            </td>
                            <td  @if( $row->closed == 1 ) class="item_done" @endif> 
                                <span>{{$row->invoice_date}}</span>
                            </td>
                            <td  @if( $row->closed == 1 ) class="item_done" @endif> 
                                <input class="edit_delivery_{{$row->id}} action_delivery_{{$row->id}}" id="qty_received_{{$row->id}}" name="qty_received_{{$row->id}}" type="text" value="{{$row->qty_received}}" style="display:none;width: 100%;text-align: center;">
                                <span  id="delivery_issue_qty_received_{{$row->id}}"  class="show_delivery_{{$row->id}} action_delivery_show_{{$row->id}}">{{$row->qty_received}}</span>
                            </td>
                            <td  @if( $row->closed == 1 ) class="item_done" @endif> 
                                <span  id="delivery_issue_qty_pending_{{$row->id}}"  class="show_delivery_{{$row->id}} action_delivery_show_{{$row->id}}">{{$row->qty_invoiced - $row->qty_received}}</span>
                            </td>
                            <td  @if( $row->closed == 1 ) class="item_done" @endif> 
                                <input class="edit_delivery_{{$row->id}} action_delivery_{{$row->id}}" id="comment_{{$row->id}}" name="comment_{{$row->id}}" type="text" value="{{$row->comment}}" style="display:none;width: 100%;text-align: center;">
                                <span  id="delivery_issue_comment_{{$row->id}}" class="show_delivery_{{$row->id}} action_delivery_show_{{$row->id}}">{{$row->comment}}</span>
                            <td  @if( $row->closed == 1 ) class="item_done" @endif>
                            
                                @if( $row->closed == 0 )
                                    <button onclick="allowEditDelivery({{$row->id}})" class="show_delivery_{{$row->id}}  btn btn-light"   type="button" style="float: right; margin-left: 5px;padding: 4px;"><i style="color: orange" class="fa-solid fa-pen"></i></button>
                                    <button onclick="closeDeliveryIssue({{$row->id}})" class="show_delivery_{{$row->id}} btn btn-light"   type="button" style="float: right;padding: 4px;"><i style="color: red" class="fa-solid fa-lock"></i></button>
                                    <button onclick="updateDataDelivery({{$row->id}})" class="edit_delivery_{{$row->id}} btn btn-success" type="button" style="float: right;display: none;padding: 2px 10px;"><i class="fa-solid fa-floppy-disk"></i></button>
                                @endif
                                
                            </td>
                        </tr>
                        
                    @endforeach
                    @if($issue['open'] == 0)
                        <tr class="supplier_container supplier_{{$issue['id_supplier']}}" style="display: none;"> <td colspan="9"> NO OPEN ISSUES </td> </tr>
                    @endif
                @endforeach
            @else
                <tr>
                    <td colspan="9"> WITHOUT DATA TO DISPLAY! </td>
                </tr>
            @endif
        </table>
    </div>
</div>

<style>
    
    .delivery_done{ display: none !important; }
    .delivery_none{ display: none !important; }
    .delivery_table{ display: table-row !important; }
    
    .openIssues{ display: revert; }
    .closedIssues{ display: none; }
</style>
<script>
    
    function showHideDelivery(id_supplier, status){
        
        if(status == 1){
            
            $('.delivery_done_' + id_supplier).removeClass('delivery_none').addClass('delivery_table');
            
            $('#supplierDeliveryShowClosed_' + id_supplier).removeClass('delivery_table').addClass('delivery_none');
            $('#supplierDeliveryHideClosed_' + id_supplier).removeClass('delivery_none').addClass('delivery_table');

        }else{
            
            console.log('Esconde linha done!');
            
            $('.delivery_done_' + id_supplier).removeClass('delivery_table').addClass('delivery_none');
            
            $('#supplierDeliveryShowClosed_' + id_supplier).removeClass('delivery_none').addClass('delivery_table');
            $('#supplierDeliveryHideClosed_' + id_supplier).removeClass('delivery_table').addClass('delivery_none');
            
        }
        

    }
    
    function updateDataDelivery(id){
        
        qty_ordered  = $('#qty_ordered_' + id).val();
        qty_invoiced = $('#qty_invoiced_' + id).val();
        qty_received = $('#qty_received_' + id).val();
        comment = $('#comment_' + id).val();

        $.ajax({
            type: 'POST',
            url: "{{route('suppliersIssues.updateDeliveryIssue')}}",
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                qty_ordered: qty_ordered,
                qty_invoiced: qty_invoiced,
                qty_received: qty_received,
                comment: comment
            },
            success: function() {
                
                Swal.fire('DELIVERY ISSUE UPDATED!', '', 'info');
                $('#delivery_issue_qty_ordered_' + id).text(qty_ordered);
                $('#delivery_issue_qty_invoiced_' + id).text(qty_invoiced);
                $('#delivery_issue_qty_received_' + id).text(qty_received);
                $('#delivery_issue_comment_' + id).text(comment);
                
                $('.edit_delivery_' + id).css('display', 'none');
                $('.show_delivery_' + id).css('display', 'block');
            }       
        });
        
    }
    
    function allowEditDelivery(id){
        $('.edit_delivery_' + id).css('display', 'block');
        $('.show_delivery_' + id).css('display', 'none');
    }
        
    function closeDeliveryIssue(id){

        Swal.fire({
            title: "PLEASE CONFIRM DELIVERY ISSUE CLOSURE?",
            showDenyButton: true,
            confirmButtonText: 'YES',
            denyButtonText: `NO`,
        }).then((result) => {

            if (result.isConfirmed) {
            
                $.ajax({
                    type: 'POST',
                    url: "{{route('suppliersIssues.closeDeliveryIssue')}}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
                    },
                    success: function() {
                        $('#issue_' + id).css('display', 'none');
                        Swal.fire('DELIVERY ISSUE CLOSED!', '', 'info');
                        location.reload();
                    }       
                });

            } else if (result.isDenied) {
                Swal.fire("DELIVERY ISSUE NOT CLOSED!", '', 'info')
            }

        });
    }
        
</script>