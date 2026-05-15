<div id="extraField_services" class="input-group extra_fields" style="border-radius: 0 5px 5px 0; display: none;margin-bottom: 10px;">
    <div class="input-group-prepend" style="width: 120px; float: left;"> <div class="input-group-text" style="border-radius: 5px 0 0 5px;text-align: right;">{{ __("messages.Services")}}</div> </div>
    <select id="services" name="services" class="form-control">
        <option value=""> Please select the service!</option>
        @foreach($services AS $service)
            {!!$service!!}
        @endforeach
    </select>
</div>