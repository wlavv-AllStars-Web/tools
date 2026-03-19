<div id="extraField_carriers" class="input-group extra_fields" style="border-radius: 0 5px 5px 0; display: none;margin-bottom: 10px;">
    <div class="input-group-prepend" style="width: 120px; float: left;"> <div class="input-group-text" style="border-radius: 5px 0 0 5px;text-align: right;">{{ __("messages.Carrier")}}</div> </div>
    <select id="carriers" name="import" class="form-control" onchange="checkExtraFieldsCarriers()">
        <option value=""> Please select the carrier!</option>
        @foreach($carriers AS $carrier)
            <option value="{{$carrier}}"> {{$carrier}}</option>
        @endforeach
    </select>
</div>

<div id="extraField_carriers_movement" class="input-group extraField_carriers_movement" style="border-radius: 0 5px 5px 0; display: none;margin-bottom: 10px;">
    <div class="input-group-prepend" style="width: 120px; float: left;"> <div class="input-group-text" style="border-radius: 5px 0 0 5px;text-align: right;">{{ __("messages.Import / Export")}}</div> </div>
    <select id="carriersMovement" name="carriersMovement" class="form-control" onchange="checkExtraFieldsCarriersMovement()">
        <option value=""> Please select!</option>
        <option value="import"> Import </option>
        <option value="export"> Export </option>
    </select>
</div>
    