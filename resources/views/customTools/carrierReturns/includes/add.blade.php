<div class="navbar navbar-light customPanel categorList" style="display: inline-table;">
    <div onclick="$('#newCarrierIssueContainer').toggle();" style="cursor: pointer;">ADD NEW CARRIER RETURN ISSUE ?</div>
    <div id="newCarrierIssueContainer" style="display: none;">
        <form id="myform" action="{{route('carrierReturn.store')}}" method="POST">
            {{ csrf_field() }}
            <table class="table table-striped text-center" style="margin-bottom: 0px;">
                <tr>
                    <td style="width: 120px;"> <input class="form-control text-center" placeholder="ID ORDER" type="text" name="id_order"></td>
                    <td style="width: 200px;"> <input class="form-control text-center" placeholder="DATE"     type="date" name="date"></td>
                    <td style="width: 200px;"> <input class="form-control text-center" placeholder="CARRIER" type="text" name="carrier"></td>
                    <td style="width: 200px;"> <input class="form-control text-center" placeholder="TRACKING" type="text" name="tracking"></td>
                    <td> <input class="form-control text-center" placeholder="ISSUE"    type="text" name="issue"></td>
                    <td> <input class="form-control text-center" placeholder="NOTES"    type="text" name="notes"></td>
                    <td style="width: 150px;"> <button style="width: 150px;" class="btn btn-success" type="submit">ADD ISSUE</button></td>
                </tr>
            </table>
        </form>
    </div>
</div>