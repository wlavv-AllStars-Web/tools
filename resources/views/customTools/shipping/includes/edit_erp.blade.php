{{--
<table style="margin: 30px auto 0 auto; width: 100%;">
    <tr>
        <td colspan="2" style="text-align: center;">
            <h2>ERP ORDERS SHIPPED UNDER THIS SHIPMENT</h2>
            <div style="margin: 3px;height: 2px;width: 100%;"></div>
        </td>
    </tr>
    <tr>
        <td>
            <label>ERP ORDER ID</label>
            <div></div>
            @php $key=0; @endphp
            
            @foreach($erp AS $key => $row_erp)
            <span></span><input type="number" step="1" name="erp[{{$key}}]" class="form-control" onblur="addField(this)" style="width: 100px; float: left;margin: 5px;" value="{{$row_erp->id_erp}}">
            @endforeach
                
            <input type="hidden" name="indexField" id="indexField" value="{{$key+1}}">
            <input type="number" step="1" name="erp[{{$key+1}}]" class="form-control" onblur="addField(this)" style="width: 100px; float: left;margin: 5px;">
            <div id="inputERPOrder_holder"></div>
        </td>
    </tr>
</table>

<script>
    function addField(el) {
        
        if(el.value.length < 1) return ;
        
        let indexField = parseInt($('#indexField').val(), 10);
        indexField++;

        $('#indexField').val(indexField);

        const $newInput = $(
            '<input type="number" step="1" name="erp[' + indexField + ']" ' +
            'onblur="addField(this)" class="form-control erp-field" ' +
            'style="width: 100px; float: left; margin: 5px;">'
        );

        $('#inputERPOrder_holder').before($newInput);

        $newInput.focus();
    }
</script>
--}}


<table style="margin: 30px auto 0 auto; width: 100%;">
    <tr>
        <td colspan="2" style="text-align: center;">
            <h2>ERP ORDERS SHIPPED UNDER THIS SHIPMENT</h2>
            <div style="margin: 3px;height: 2px;width: 100%;"></div>
        </td>
    </tr>
    <tr>
        <td>
            <label>ERP ORDER ID</label>
            <div></div>
            @php $key=0; @endphp
            
            @foreach($erp AS $key => $row_erp)
                <input type="number"
                       step="1"
                       name="erp[{{$key}}]"
                       class="form-control"
                       onblur="addField(this)"
                       style="width: 100px; float: left;margin: 5px;"
                       value="{{$row_erp->id_erp}}">
            @endforeach
                
            <input type="hidden" name="indexField" id="indexField" value="{{$key+1}}">
            
            <input type="number"
                   step="1"
                   name="erp[{{$key+1}}]"
                   class="form-control"
                   onblur="addField(this)"
                   style="width: 100px; float: left;margin: 5px;">
                   
            <div id="inputERPOrder_holder"></div>
            
            <div style="margin: 5px; float: left;">
                <button type="submit"
                        formaction="{{ route('shipping.packingList') }}"
                        formmethod="GET"
                        class="btn btn-primary">
                    Generate Packing List
                </button>
            </div>

        </td>
    </tr>
</table>

<hr style="padding: 5px;">

<script>
    function addField(el) {
        
        if(el.value.length < 1) return ;
        
        let indexField = parseInt($('#indexField').val(), 10);
        indexField++;

        $('#indexField').val(indexField);

        const $newInput = $(
            '<input type="number" step="1" name="erp[' + indexField + ']" ' +
            'onblur="addField(this)" class="form-control erp-field" ' +
            'style="width: 100px; float: left; margin: 5px;">'
        );

        $('#inputERPOrder_holder').before($newInput);

        $newInput.focus();
    }
</script>