<div class="navbar navbar-light customPanel categorList {{$listType}}">
    <table class="table table-striped text-center">
        <tr>
            <td style="width: 60px;">ID</td>
            <td>ORDER</td>
            <td>DATE</td>
            <td>CARRIER</td>
            <td>TRACKING</td>
            <td>ISSUES</td>
            <td>NOTES</td>
            <td style="width: 30px;"></td>
            <td style="width: 30px;"></td>
        </tr>
        @if( count($carrierReturn) > 0)
            @foreach($carrierReturn AS $issue)
                <tr id="carrierReturnRow_{{$issue->id}}" onclick="showContent({{$issue->id}})">
                    <td>{{$issue->id}}</td>
                    <td> <span class="showReturnIssue_{{$issue->id}}">{{$issue->id_order}}</span>   <input style="display: none;" type="text" id="edit_id_order_{{$issue->id}}"   name="edit_idOrder_{{$issue->id}}"  value="{{$issue->id_order}}" class="editReturnIssue_{{$issue->id}}"> </td>
                    <td> <span class="showReturnIssue_{{$issue->id}}">{{$issue->date}}</span>       <input style="display: none;" type="date" id="edit_date_{{$issue->id}}"       name="edit_date_{{$issue->id}}"     value="{{$issue->date}}"     class="editReturnIssue_{{$issue->id}}"> </td>
                    <td> <span class="showReturnIssue_{{$issue->id}}">{{$issue->carrier}}</span>    <input style="display: none;" type="text" id="edit_carrier_{{$issue->id}}"    name="edit_carrier_{{$issue->id}}"  value="{{$issue->carrier}}" class="editReturnIssue_{{$issue->id}}"> </td>
                    <td> <span class="showReturnIssue_{{$issue->id}}">{{$issue->tracking}}</span>   <input style="display: none;" type="text" id="edit_tracking_{{$issue->id}}"   name="edit_tracking_{{$issue->id}}" value="{{$issue->tracking}}" class="editReturnIssue_{{$issue->id}}"> </td>
                    <td> <span class="showReturnIssue_{{$issue->id}}">{{$issue->issue}}</span>      <input style="display: none;" type="text" id="edit_issue_{{$issue->id}}"      name="edit_issue_{{$issue->id}}"    value="{{$issue->issue}}"    class="editReturnIssue_{{$issue->id}}"> </td>
                    <td> <span class="showReturnIssue_{{$issue->id}}">{{$issue->notes}}</span>      <input style="display: none;" type="text" id="edit_notes_{{$issue->id}}"      name="edit_notes_{{$issue->id}}"    value="{{$issue->notes}}"    class="editReturnIssue_{{$issue->id}}"> </td>
                    @if($listType == 'carrierReturnActive')
                        <td> 
                            <button class="btn btn-light editReturnIssue_{{$issue->id}}" style="padding: 1px 6px; color: orange;" onclick="editIssue({{$issue->id}})"> <i class="fa-xl fa-solid fa-pencil"></i> </button> 
                            <button class="btn btn-light showReturnIssue_{{$issue->id}}" style="padding: 1px 6px; color: dodgerblue;display: none;" onclick="updateIssue({{$issue->id}})"> <i class="fa-xl fa-solid fa-floppy-disk"></i> </button> 
                        </td>
                        <td> <button class="btn btn-light" style="padding: 1px 6px; color: green;" onclick="archiveIssue({{$issue->id}})"> <i class="fa-xl fa-solid fa-folder-open"></i> </button> </td>
                    @else
                        <td> </td>
                        <td> <button class="btn btn-light" style="padding: 1px 6px; color: red;" onclick="archiveIssue({{$issue->id}})"> <i class="fa-xl fa-solid fa-folder"></i> </button> </td>
                    @endif
                </tr>
            @endforeach
        @else
            <tr> <td colspan="7"> NO DATA TO SHOW!</td> </tr>
        @endif
    </table>
</div>