<div id="invoiceBlock" style="display: none;">
    <div class="input-group" style="border-radius: 0 5px 5px 0; margin-bottom: 10px;">
        <div class="input-group-prepend" style="width: 120px; float: left;"> <div class="input-group-text" style="border-radius: 5px 0 0 5px;text-align: right;">{{ __("messages.# Document")}}</div> </div>
        <input id="number" name="number" type="text" class="form-control" value="" required>
    </div>
    
    <div class="input-group" style="border-radius: 0 5px 5px 0; margin-bottom: 10px;">
        <div class="input-group-prepend" style="width: 120px; float: left;"> <div class="input-group-text" style="border-radius: 5px 0 0 5px;text-align: right;">{{ __("messages.Total ex VAT")}}</div> </div>
        <input id="totalExVat" name="totalExVat" type="text" class="form-control" value="" placeholder="100.00" required onchange="calculateValue()">
    </div>
    
    <div class="input-group" style="border-radius: 0 5px 5px 0; margin-bottom: 10px;">
        <div class="input-group-prepend" style="width: 120px; float: left;"> <div class="input-group-text" style="border-radius: 5px 0 0 5px;text-align: right;">{{ __("messages.VAT value")}}</div> </div>
        <input id="vatValue" name="vatValue" type="text" class="form-control" value="0" placeholder="23.00" required onchange="calculateValue()">
    </div>
    
    <div class="input-group" style="border-radius: 0 5px 5px 0; margin-bottom: 10px;">
        <div class="input-group-prepend" style="width: 120px; float: left;"> <div class="input-group-text" style="border-radius: 5px 0 0 5px;text-align: right;">{{ __("messages.Total")}}</div> </div>
        <input id="total" name="total" type="text" class="form-control" value="" placeholder="123.00" required>
    </div>
</div>