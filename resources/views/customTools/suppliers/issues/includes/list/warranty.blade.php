<div class="containerSupplierBlock" id="container-warranty">

    <div style="width: 100%; display: table;">
        <div class="navbar navbar-light customPanel" onclick="$('.newWarrantyIssue').toggle()" style="width: calc( 100% - 200px ); float: left;padding: 13px;"> ADD NEW WARRANTY ISSUE ? </div>
        <div class="navbar navbar-light customPanel" style="width: 180px; float: right;"> 
            <button id="supplierWarrantyShowClosed" class="btn btn-warning" type="button" onclick="$('.warranty_done').toggle();$('#supplierWarrantyShowClosed').css('display', 'none');$('#supplierWarrantyHideClosed').css('display', 'block');" style="padding: 2px;width: 100%;">SHOW CLOSED</button>
            <button id="supplierWarrantyHideClosed" class="btn btn-danger"  type="button" onclick="$('.warranty_done').toggle();$('#supplierWarrantyShowClosed').css('display', 'block');$('#supplierWarrantyHideClosed').css('display', 'none');" style="padding: 2px;width: 100%;display: none;">HIDE CLOSED</button>
        </div>
    </div>
    
    <div class="newWarrantyIssue">
        <div class="navbar navbar-light customPanel">
            <form action="{{route('suppliersIssues.newWarrantyIssue')}}" method="post">
                <div class="row">
                    <div class="col-lg-12">
                        {!! csrf_field() !!}
                        
                        <table class="table table-striped text-center" style="width: 100;">
                            <tr>
                                <td style="width: 15%;"> SUPPLIER </td>
                                <td style="width: 10%;"> DATE </td>
                                <td style="width: 5%;">  ORDER ID </td>
                                <td style="width: 15%;"> REFERENCE </td>
                                <td style="width: 25%;"> DESCRIPTION </td>
                                <td style="width: 25%;"> ACTION </td>
                                <td style="width: 5%;"> </td>
                            </tr>
                            <tr>
                                <td> 
                                    <select style="width: 100%;border-radius: 2px;box-shadow: none;border: 1px solid #666;padding: 3px;text-align: center;" name="id_supplier">
                                        @foreach($suppliers AS $id_supplier => $supplier)
                                        <option value="{{$id_supplier}}">{{$supplier}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td> <input style="width: 100%;text-align: center;" type="date" name="date"> </td>
                                <td> <input style="width: 100%;" type="text" name="id_order"> </td>
                                <td> <input style="width: 100%;" type="text" name="reference"> </td>
                                <td> <input style="width: 100%;" type="text" name="description"> </td>
                                <td> <input style="width: 100%;" type="text" name="action"> </td>
                                <td> <button type="submit" class="btn btn-success" style="width: 100%;padding: 2px;border-radius: 2px;">SAVE</button> </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="navbar navbar-light customPanel">
        <table class="table text-center" style="width: 100%;">
            @if( count($issues) > 0)
                <tr>
                    <td style="background-color: #ccc;"> SUPPLIER </td>
                    <td style="background-color: #ccc;">  DATE </td>
                    <td style="background-color: #ccc;">  ORDER ID </td>
                    <td style="background-color: #ccc;"> REFERENCE </td>
                    <td style="background-color: #ccc;"> DESCRIPTION </td>
                    <td style="background-color: #ccc;"> ACTION </td>
                    <td style="background-color: #ccc;width: 70px;"> </td>
                </tr>
                @foreach($issues AS $issue)
                    @foreach($issue['issues'] AS $row)
                        <tr id="issue_{{$row->id}}" @if($row->closed == 1) class="warranty_done" style="display: none;" @endif >
                            <td @if( $row->closed == 1 ) class="item_done" @endif> {{$row->supplier->name}} </td>
                            <td @if( $row->closed == 1 ) class="item_done" @endif> {{$row->date}} </td>
                            <td @if( $row->closed == 1 ) class="item_done" @endif> {{$row->id_order}}  </td>
                            <td @if( $row->closed == 1 ) class="item_done" @endif> {{$row->reference}} </td>
                            <td @if( $row->closed == 1 ) class="item_done" @endif> 
                                <input class="edit_{{$row->id}} description_{{$row->id}}" name="description" type="text" value="{{$row->description}}" style="display:none;width: 100%;text-align: center;">
                                <span  class="show_{{$row->id}} description_show_{{$row->id}}">{{$row->description}}</span>
                            </td>
                            <td @if( $row->closed == 1 ) class="item_done" @endif> 
                                <input class="edit_{{$row->id}} action_{{$row->id}}" name="action" type="text" value="{{$row->action}}" style="display:none;width: 100%;text-align: center;">
                                <span  class="show_{{$row->id}} action_show_{{$row->id}}">{{$row->action}}</span>
                            </td>
                            <td @if( $row->closed == 1 ) class="item_done" @endif> 
                                <button onclick="allowEditWarranty({{$row->id}})" class="show_{{$row->id}} btn btn-light" type="button" style="float: right; margin-left: 5px;padding: 4px;"><i style="color: orange" class="fa-solid fa-pen"></i></button>
                                <button onclick="closeIssue({{$row->id}})" class="show_{{$row->id}} btn btn-light" type="button" style="float: right;padding: 4px;"><i style="color: red" class="fa-solid fa-lock"></i></button>
                                <button onclick="updateDataWarranty({{$row->id}})" class="edit_{{$row->id}} btn btn-success" type="button" style="float: right;display: none;padding: 4px;"><i class="fa-solid fa-floppy-disk"></i></button>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            @else
                <tr>
                    <td colspan="8"> WITHOUT DATA TO DISPLAY! </td>
                </tr>
            @endif
        </table>
    </div>
    
    <script>
        
        function allowEditWarranty(id){
            $('.edit_' + id).css('display', 'block');
            $('.show_' + id).css('display', 'none');
        }
        
        function updateDataWarranty(id){
            
            description = $('.description_' + id).val();
            action = $('.action_' + id).val();
                
            $.ajax({
                type: 'POST',
                url: "{{route('suppliersIssues.updateWarrantyIssue')}}",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    description: description,
                    action: action
                },
                success: function() {
                    Swal.fire('WARRANTY ISSUE UPDATED!', '', 'info');
            
                    $('.description_show_' + id).text(description);
                    $('.action_show_' + id).text(action);
                    
                    $('.edit_' + id).css('display', 'none');
                    $('.show_' + id).css('display', 'block');
            
                }       
            });
            
        }
        
        function closeIssue(id){

            Swal.fire({
                title: "PLEASE CONFIRM ISSUE CLOSURE?",
                showDenyButton: true,
                confirmButtonText: 'YES',
                denyButtonText: `NO`,
                
                
                
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: "YES, add to supplier's issue",
                denyButtonText: 'YES',
            }).then((result) => {
                
                if (result.isConfirmed) {
                
                    $.ajax({
                        type: 'POST',
                        url: "{{route('suppliersIssues.closeWarrantyIssue')}}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: id,
                            move: 1
                        },
                        success: function() {
                            $('#issue_' + id).css('display', 'none');
                            Swal.fire('WARRANTY ISSUE CLOSED! CREATED NEW RECORD ON SUPPLIER ISSUES', '', 'info');
                        }       
                    });
                    
                } else if (result.isDenied) {
                
                    $.ajax({
                        type: 'POST',
                        url: "{{route('suppliersIssues.closeWarrantyIssue')}}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: id,
                            move: 0
                        },
                        success: function() {
                            $('#issue_' + id).css('display', 'none');
                            Swal.fire('WARRANTY ISSUE CLOSED!', '', 'info');
                        }       
                    });
                    
                }else{
                    Swal.fire("ISSUE NOT CLOSED!", '', 'info')
                }
    
            });
        }
    </script>
</div>