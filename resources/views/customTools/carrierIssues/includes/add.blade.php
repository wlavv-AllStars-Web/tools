<div class="navbar navbar-light customPanel categorList">
    <div onclick="$('#newCarrierIssueContainer').toggle();" style="cursor: pointer;">ADD NEW CARRIER ISSUE ?</div>
    <div id="newCarrierIssueContainer" style="display: none;">
        <form id="myform" action="{{route('carrierIssues.store')}}" method="POST">
            {{ csrf_field() }}
            <div class="row" style="text-align: center;">
                <div class="col-lg-4">
                    <div class="navbar navbar-light customPanel categorList">
                        <h4>PACKAGE ISSUE</h4>
                        <div class="issueBlockContainer">
                            <div> <label>SHOP</label>               
                                <select name="shop">
                                    <option value="ASM">ASM</option>
                                    <option value="ASD">ASD</option>
                                    <option value="EM">EM</option>
                                    <option value="ER">ER</option>
                                </select>
                            </div>
                            <div> <label>ORDER</label> <input name="id_order" value="" type="number"> </div>
                            <div> <label>TRACKING</label> <input name="tracking" value="" type="text"> </div>
                            <div> <label>CARRIER</label>               
                                <select name="carrier">
                                    <option value="DPD">DPD</option>
                                    <option value="Mondial Relay">Mondial Relay</option>
                                    <option value="NACEX">NACEX</option>
                                    <option value="TNT">TNT</option>
                                    <option value="UPS">UPS</option>
                                </select>
                            </div>
                            <div> <label>COUNTRY</label>             
                                <select name="country">
                                    @foreach($countries AS $country)
                                        <option value="{{$country->lang_en->name}}">{{$country->lang_en->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div> <label>CUSTOMER CONTACT</label>   <input name="contact_date" value="" type="date"> </div>
                            <div> <label>ISSUE</label>               
                                <select name="issue">
                                    <option value="DANIFICADO">DANIFICADO</option>
                                    <option value="PERDIDO">PERDIDO</option>
                                </select></div>
                            <div> <label>DESCRIPTION</label>        <input name="description" value="" type="text"> </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="navbar navbar-light customPanel categorList">
                        <h4>CLAIM DETAILS</h4>
                        <div class="issueBlockContainer">
                            <div> <label>CLAIM DATE</label>         <input name="claim_date" value="" type="date"> </div>
                            <div> <label>DOCS SENT</label>          <input name="docs_sent" value="" type="date"> </div>
                            <div> <label>AMOUNT LOST</label>        <input name="amount_lost" step=".01" value="" type="number"> </div>
                            <div> <label>AMOUNT CLAIMED</label>     <input name="amount_claimed" step=".01" value="" type="number"> </div>
                            <div> <label>SHIP CHARGES</label>       <input name="ship_charges" step=".01" value="" type="number"> </div>
                            <div> <label>FILE SET</label>               
                                <select name="file_set">
                                    <option value="0">NO</option>
                                    <option value="1">YES</option>
                                </select>
                            </div>
                            <div> <label>CLAIM STATUT</label>               
                                <select name="claim_status">
                                    <option value="PENDENTE">PENDENTE</option>
                                    <option value="RECUSADO">RECUSADO</option>
                                    <option value="RESOLVIDO NATURALMENTE">RESOLVIDO NATURALMENTE</option>
                                    <option value="CONCLUIDO">CONCLUIDO</option>
                                    
                                </select>
                            </div>
                            <div> <label>CREDIT NOTE</label>       <input name="note" step=".01" value="" type="number"> </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="navbar navbar-light customPanel categorList">
                        <h4>RESOLUTION</h4>
                        <div class="issueBlockContainer">
                            <div> <label>RESOLUTION CUSTOMER</label>               
                                <select name="resolution">
                                    <option value="">WAITING RESOLUTION</option>
                                    <option value="REEMBOLSO">REEMBOLSO</option>
                                    <option value="REENVIADO">REENVIADO</option>
                                    <option value="ENTREGUE">ENTREGUE</option>
                                </select>
                            </div>
                            <div> <label>NEW TRACKING</label>       <input name="new_tracking" value="" type="text"> </div>
                            <div> <label>NOTE</label>               <input name="note" value="" type="text"> </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4"></div>
                <div class="col-lg-4"><button type="submit" class="btn btn-success" style="width: 100%;margin: 40px 0 10px 0;">SAVE</button></div>
            </div>
        </form>
    </div>
</div>