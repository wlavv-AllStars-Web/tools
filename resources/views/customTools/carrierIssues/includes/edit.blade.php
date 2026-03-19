<tr id="carrierIssue_{{$data['issue']->id_issue}}" class="carrierIssueRow">
    <td colspan="13">
        <div id="editCarrierIssueContainer">
            
            <script> 
                Dropzone.autoDiscover = false; 
            </script>
            
            <form id="myform" action="{{route('carrierIssues.update')}}" method="POST">
                {{ csrf_field() }}
                <input type="hidden", name="id_issue" value="{{$data['issue']->id_issue}}">
                <div class="row" style="text-align: center;">
                    <div class="col-lg-4">
                        <div class="navbar navbar-light customPanel categorList">
                            <h4>PACKAGE ISSUE</h4>
                            <div class="issueBlockContainer">
                                <div> <label>SHOP</label>               
                                    <select name="shop">
                                        <option @if($data['issue']->shop == 'ASM')  selected="selected" @endif value="ASM">ASM</option>
                                        <option @if($data['issue']->shop == 'ASD')  selected="selected" @endif value="ASD">ASD</option>
                                        <option @if($data['issue']->shop == 'EM')   selected="selected" @endif value="EM">EM</option>
                                        <option @if($data['issue']->shop == 'ER')   selected="selected" @endif value="ER">ER</option>
                                    </select>
                                </div>
                                <div> <label>ORDER</label> <input name="id_order" value="{{$data['issue']->id_order}}" type="number"> </div>
                                <div> <label>TRACKING</label> <input name="tracking" value="{{$data['issue']->tracking}}" type="text"> </div>
                                <div> <label>CARRIER</label>               
                                    <select name="carrier">
                                        <option @if($data['issue']->carrier == 'DPD')   selected="selected" @endif value="DPD">DPD</option>
                                        <option @if($data['issue']->carrier == 'Mondial Relay')   selected="selected" @endif value="Mondial Relay">Mondial Relay</option>
                                        <option @if($data['issue']->carrier == 'NACEX') selected="selected" @endif value="NACEX">NACEX</option>
                                        <option @if($data['issue']->carrier == 'TNT')   selected="selected" @endif value="TNT">TNT</option>
                                        <option @if($data['issue']->carrier == 'UPS')   selected="selected" @endif value="UPS">UPS</option>
                                    </select>
                                </div>
                                <div> <label>COUNTRY</label>             
                                    <select name="country">
                                        @foreach($data['countries'] AS $country)
                                            <option @if($data['issue']->country == $country->lang_en->name) selected="selected" @endif value="{{$country->lang_en->name}}">{{$country->lang_en->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div> <label>CUSTOMER CONTACT</label>   <input name="contact_date" value="{{$data['issue']->contact_date}}" type="date"> </div>
                                <div> <label>ISSUE</label>               
                                    <select name="issue">
                                        <option @if($data['issue']->issue == 'DANIFICADO')  selected="selected" @endif value="DANIFICADO">DANIFICADO</option>
                                        <option @if($data['issue']->issue == 'PERDIDO')     selected="selected" @endif value="PERDIDO">PERDIDO</option>
                                    </select></div>
                                <div> <label>DESCRIPTION</label>        <input name="description" value="{{$data['issue']->description}}" type="text"> </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="navbar navbar-light customPanel categorList">
                            <h4>CLAIM DETAILS</h4>
                            <div class="issueBlockContainer">
                                <div> <label>CLAIM DATE</label>         <input name="claim_date"        value="{{$data['issue']->claim_date}}"      type="date"> </div>
                                <div> <label>DOCS SENT</label>          <input name="docs_sent"         value="{{$data['issue']->docs_sent}}"       type="date"> </div>
                                <div> <label>AMOUNT LOST</label>        <input name="amount_lost"       value="{{$data['issue']->amount_lost}}"     type="number" step=".01"> </div>
                                <div> <label>AMOUNT CLAIMED</label>     <input name="amount_claimed"    value="{{$data['issue']->amount_claimed}}"  type="number" step=".01"> </div>
                                <div> <label>SHIP CHARGES</label>       <input name="ship_charges"      value="{{$data['issue']->ship_charges}}"    type="number" step=".01"> </div>
                                <div> <label>FILE SET</label>               
                                    <select name="file_set">
                                        <option @if($data['issue']->file_set == 0) selected="selected" @endif value="0">NO</option>
                                        <option @if($data['issue']->file_set == 1) selected="selected" @endif value="1">YES</option>
                                    </select>
                                </div>
                                <div> <label>CLAIM STATUT</label>               
                                    <select name="claim_status">
                                        <option @if($data['issue']->claim_status == 'PENDENTE')     selected="selected" @endif value="PENDENTE">PENDENTE</option>
                                        <option @if($data['issue']->claim_status == 'RECUSADO' )    selected="selected" @endif value="RECUSADO">RECUSADO</option>
                                        <option @if($data['issue']->claim_status == 'RESOLVIDO NATURALMENTE' )    selected="selected" @endif value="RESOLVIDO NATURALMENTE">RESOLVIDO NATURALMENTE</option>
                                        <option @if($data['issue']->claim_status == 'ACEITE')       selected="selected" @endif value="ACEITE">ACEITE</option>
                                    </select>
                                </div>
                                <div> <label>CREDIT NOTE</label>               <input name="note"          value="{{$data['issue']->note}}"            type="text"> </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="navbar navbar-light customPanel categorList">
                            <h4>RESOLUTION</h4>
                            <div class="issueBlockContainer">
                                <div>
                                    <label>RESOLUTION CUSTOMER</label>               
                                    <select name="resolution">
                                        <option @if($data['issue']->resolution == 'PENDENTE')  selected="selected" @endif value="">WAITING RESOLUTION</option>
                                        <option @if($data['issue']->resolution == 'REEMBOLSO') selected="selected" @endif value="REEMBOLSO">REEMBOLSO</option>
                                        <option @if($data['issue']->resolution == 'REENVIADO') selected="selected" @endif value="REENVIADO">REENVIADO</option>
                                        <option @if($data['issue']->resolution == 'ENTREGUE')  selected="selected" @endif value="ENTREGUE">ENTREGUE  </option>
                                    </select>
                                </div>
                                <div> <label>NEW TRACKING</label>       <input name="new_tracking"  value="{{$data['issue']->new_tracking}}"    type="text"> </div>
                                </form>

                                <div class="input-group" style="border-radius: 0 5px 5px 0;display: grid; width: 80%; margin: 0 auto;">
                                    <div style="text-align: center;">
                                        @include("customTools.uploads.upload_container", [
                                            'title' => '', 
                                            'message' => trans('tags.Drop files here or click to upload'), 
                                            'upload_path' => "carriersIssues/" . $data['issue']->id_issue . "/",
                                            'filename' => "upload.pdf",
                                            'upload_accepted_files' => "*",
                                            'max_files' => 1,
                                            'on_success' => 'executeFunction()',
                                        ])
                                    </div>    
                                </div>
                                
                                @if( $data['issue']->archived == 2)
                                    <div>
                                        <button type="button" class="btn btn-warning" style="width: 400px;margin-top: 20px;" onclick="archiveClaim({{$data['issue']->id_issue}}, 1)">ARCHIVE CLAIM</button>
                                    </div>
                                @else
                                    <div>
                                        <button type="button" class="btn btn-warning" style="width: 200px;margin-top: 20px;float: left;" onclick="archiveClaim({{$data['issue']->id_issue}}, 1)">ARCHIVE CLAIM</button>
                                    </div>
                                    
                                    <div>
                                        <button type="button" class="btn btn-danger" style="width: 200px;margin-top: 20px;margin-left: 20px;float:right;" onclick="archiveClaim({{$data['issue']->id_issue}}, 2)">RETENTION</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4"></div>
                    <div class="col-lg-4"><button type="submit" class="btn btn-success" style="width: 100%;margin: 40px 0 10px 0;">SAVE</button></div>
                </div>
        </div>    
    </td>
</tr>