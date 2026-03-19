<div class="input-group" style="border-radius: 0 5px 5px 0; margin-bottom: 10px;">
    <div class="input-group-prepend" style="width: 120px; float: left;display: flex;"> <div class="input-group-text" style="border-radius: 5px 0 0 5px;text-align: right;">{{ __("messages.Document type")}}</div> </div>
    <div class="form-control">
        <div style="width: 31%; float: left;text-align: center;">
            {{ __("messages.INVOICE")}}
            <br><input id="document_type" name="document_type" type="radio" value="invoice" onclick="$('#categoryBlock').css('display', 'block');$('#invoiceBlock').css('display', 'block');$('#manifestBlock').css('display', 'none');"> 
        </div>
        <div style="width: 38%; float: left;text-align: center;">
            {{ __("messages.CREDIT NOTE")}}
            <br><input id="document_type" name="document_type" type="radio" value="credit_note" onclick="$('#categoryBlock').css('display', 'block');$('#invoiceBlock').css('display', 'block');$('#manifestBlock').css('display', 'none'); "> 
        </div> 
        <div style="width: 31%; float: left;text-align: center;" id="manifestContainer">
            {{ __("messages.Manifest")}}
            <br><input id="document_type" name="document_type" type="radio" value="manifest" onclick="$('#categoryBlock').css('display', 'none');$('#invoiceBlock').css('display', 'none');$('#manifestBlock').css('display', 'block'); "> 
        </div>                   
    </div>
</div>