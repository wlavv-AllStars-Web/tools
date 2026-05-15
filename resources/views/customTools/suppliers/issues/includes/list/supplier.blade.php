<div class="containerSupplierBlock" id="container-supplier">

    <div style="width: 100%; display: table;">
        <div class="navbar navbar-light customPanel" onclick="$('.newSupplierIssue').toggle()" style="width: calc( 100% - 200px ); float: left;padding: 13px;"> ADD NEW SUPPLIER ISSUE ? </div>
        <div class="navbar navbar-light customPanel" style="width: 180px; float: right;"> 
            <button id="supplierShowClosed" class="btn btn-warning" type="button" onclick="$('.issue_done').toggle();$('#supplierShowClosed').css('display', 'none');$('#supplierHideClosed').css('display', 'block');" style="padding: 2px;width: 100%;">SHOW CLOSED</button>
            <button id="supplierHideClosed" class="btn btn-danger" type="button" onclick="$('.issue_done').toggle();$('#supplierShowClosed').css('display', 'block');$('#supplierHideClosed').css('display', 'none');" style="padding: 2px;width: 100%;display: none;">HIDE CLOSED</button>
        </div>
    </div>
    
    <div class="newSupplierIssue" style="display: none;">
        
        <div class="navbar navbar-light customPanel">

            <form action="{{route('suppliersIssues.newSupplierIssue')}}" method="post">
                <div class="row">
                    <div class="col-lg-12">
                        {!! csrf_field() !!}
                        
                        <table class="table table-striped text-center" style="width: 100;">
                            <tr>
                                <td style="width: 10%;"> SUPPLIER </td>
                                <td style="width: 10%;"> DATE </td>
                                <td style="width: 5%;">  ALERT BY </td>
                                <td style="width: 5%;">  QUANTITY </td>
                                <td style="width: 10%;"> REFERENCE </td>
                                <td style="width: 25%;"> DESCRIPTION </td>
                                <td style="width: 10%;"> STATUS </td>
                                <td style="width: 20%;"> INFO </td>
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
                                <td> <input style="width: 100%;text-align: center;" type="text" name="alert_by"> </td>
                                <td> <input style="width: 100%;text-align: center;" type="number" name="quantity"> </td>
                                <td> <input style="width: 100%;text-align: center;" type="text" name="reference"> </td>
                                <td> <input style="width: 100%;text-align: center;" type="text" name="description"> </td>
                                <td>
                                    <select style="width: 100%;border-radius: 2px;box-shadow: none;border: 1px solid #666;padding: 3px;text-align: center;" name="status">
                                        <option value="IN PROGRESS">IN PROGRESS</option>
                                        <option value="APPROVED">APPROVED</option>
                                        <option value="CLOSED ISSUE">CLOSED ISSUE</option>
                                    </select>
                                </td>
                                <td> <input style="width: 100%;text-align: center;" type="text" name="info"> </td>
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
                    <td style="background-color: #ccc;"> DATE </td>
                    <td style="background-color: #ccc;"> ALERT BY </td>
                    <td style="background-color: #ccc;"> REFERENCE </td>
                    <td style="background-color: #ccc;"> QUANTITY </td>
                    <td style="background-color: #ccc;"> DESCRIPTION </td>
                    <td style="background-color: #ccc;"> STATUS </td>
                    <td style="background-color: #ccc;"> INFO </td>
                    <td style="background-color: #ccc;"> </td>
                </tr>
                @foreach($issues AS $issue)
                    @foreach($issue['issues'] AS $row)
                        <tr @if( $row->status == 'CLOSED ISSUE') style="display: none;" class="issue_done" @else class="issue_undone" @endif>
                            <td @if( $row->status == 'CLOSED ISSUE') class="item_done" @endif> {{$row->supplier->name}} </td>
                            <td @if( $row->status == 'CLOSED ISSUE') class="item_done" @endif> {{$row->date}} </td>
                            <td @if( $row->status == 'CLOSED ISSUE') class="item_done" @endif> {{$row->alert_by}} </td>
                            <td @if( $row->status == 'CLOSED ISSUE') class="item_done" @endif> {{$row->reference}} </td>
                            <td @if( $row->status == 'CLOSED ISSUE') class="item_done" @endif>
                                @if($row->status == 'FROM WARRANTY')
                                    <span style="color: red;font-weight: bolder;">{{$row->quantity}}</span>
                                @else
                                    {{$row->quantity}}
                                @endif
                            </td>
                            <td @if( $row->status == 'CLOSED ISSUE') class="item_done" @endif> 
                                <input class="edit_supplier_{{$row->id}} description_supplier_{{$row->id}}" name="description" type="text" value="{{$row->description}}" style="display:none;width: 100%;text-align: center;">
                                <span  class="show_supplier_{{$row->id}} description_supplier_show_{{$row->id}}">{{$row->description}}</span>
                            </td>
                            <td @if( $row->status == 'CLOSED ISSUE') class="item_done" @endif>
                                <span class="show_supplier_{{$row->id}}   status_supplier_show_{{$row->id}}">
                                    @if($row->status == 'FROM WARRANTY')
                                        <span style="color: red;font-weight: bolder;">{{$row->status}}</span>
                                    @else
                                        {{$row->status}}
                                    @endif
                                </span>
                                <select class="edit_supplier_{{$row->id}} status_supplier_{{$row->id}}" style="width: 100%;border-radius: 2px;box-shadow: none;border: 1px solid #666;padding: 3px;text-align: center;display: none;" name="status">
                                    <option @if($row->status == 'IN PROGRESS') selected="selected" @endif value="IN PROGRESS">IN PROGRESS</option>
                                    <option @if($row->status == 'APPROVED') selected="selected" @endif  value="APPROVED">APPROVED</option>
                                    <option @if($row->status == 'CLOSED ISSUE') selected="selected" @endif  value="CLOSED ISSUE">CLOSED ISSUE</option>
                                </select>
                            </td>
                            <td @if( $row->status == 'CLOSED ISSUE') class="item_done" @endif>
                                <input class="edit_supplier_{{$row->id}} info_supplier_{{$row->id}}" name="info" type="text" value="{{$row->info}}" style="display:none;width: 100%;text-align: center;">
                                <span  class="show_supplier_{{$row->id}} info_supplier_show_{{$row->id}}">{{$row->info}}</span> 
                            </td>
                            <td @if( $row->status == 'CLOSED ISSUE') class="item_done" @endif>
                                @if( $row->status != 'CLOSED ISSUE')
                                    <button onclick="allowEdit({{$row->id}})" class="show_supplier_{{$row->id}} btn btn-light" type="button" style="float: right;padding: 4px;"><i style="color: orange" class="fa-solid fa-pen"></i></button>
                                    <button onclick="updateData({{$row->id}})" class="edit_supplier_{{$row->id}} btn btn-success" type="button" style="float: right;display: none;padding: 4px;"><i class="fa-solid fa-floppy-disk"></i></button>
                                @else
                                
                                @endif
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

        function allowEdit(id){
            
            $('.edit_supplier_' + id).css('display', 'block');
            $('.show_supplier_' + id).css('display', 'none');
        }
        
        function updateData(id){
            
            description = $('.description_supplier_' + id).val();
            info = $('.info_supplier_' + id).val();
            status = $('.status_supplier_' + id).val();
                
            $.ajax({
                type: 'POST',
                url: "{{route('suppliersIssues.updateSupplierIssue')}}",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    description: description,
                    info: info,
                    status: status
                },
                success: function() {
                    Swal.fire('SUPPLIER ISSUE UPDATED!', '', 'info');
            
                    $('.description_supplier_show_' + id).text(description);
                    $('.info_supplier_show_' + id).text(info);
                    $('.status_supplier_show_' + id).text(status);
                    
                    $('.edit_supplier_' + id).css('display', 'none');
                    $('.show_supplier_' + id).css('display', 'block');
                    
                    location.reload();
            
                }       
            });
            
        }
        
    </script>
    
</div>