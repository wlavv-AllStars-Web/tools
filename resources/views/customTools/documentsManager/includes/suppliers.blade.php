<div id="extraField_supplier" class="input-group extra_fields" style="border-radius: 0 5px 5px 0; display: none;margin-bottom: 10px;">
    <div class="input-group-prepend" style="width: 120px; float: left;"> <div class="input-group-text" style="border-radius: 5px 0 0 5px;text-align: right;">{{ __("messages.Supplier")}}</div> </div>
    <select id="supplier" name="supplier" class="form-control">
        <option value=""> Please select the supplier!</option>
        @foreach($suppliers AS $supplier)
            <option value="{{str_replace('č', 'c', $supplier)}}"> {{str_replace('č', 'c', $supplier)}}</option>
        @endforeach
    </select>
</div>