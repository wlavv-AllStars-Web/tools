<script>

    let token_stock_entry;

    $(document).ready(function() {
        $('#search').focus();
    });

    $(document).keyup(function(event) {
        if (event.which === 13) ajaxCall('getProductByEAN');
    });      


    let html_partials = '';
    let html_backorders = '';
    let html_preparations = '';

    let stock_entry_quantity = 0;

    function ajaxCall(type) {
        
        $('#mainContainer').css('display', 'none');

        $.ajax({
            type: 'POST',
            url: "{{route('stockEntry.post')}}",
            dataType: "json",
            data: {
                _token: "{{ csrf_token() }}",
                action: type,
                code: $('#search').val(),
            },
            success: function(response) {

                var data = JSON.parse(JSON.stringify(response));

                const object = data.product;
                const partials = data.partials;
                const backorders = data.backorders;
                const preparations = data.preparations;

                if(data.status == "success"){
                    if(data.product.product_id > 0){
                    
                        $('#mainContainer').css('display', 'block');
                        emptyTag = '<span style="color: #666; padding: 5px; border-radius: 5px; margin: 5px;"> - </span>';
                        $('#mainContainer').css('display', 'inline-block');
                        $('#mainContainerEmpty').css('display', 'none');
                        
                        document.getElementById("tag_product_reference").innerHTML = object.product.reference;

                        
                        let counter_partials = 0;
                        let counter_backorders = 0;
                        let counter_preparations = 0;
                        
                        if ( partials !== null )     counter_partials     = partials.length;
                        if ( backorders !== null )   counter_backorders   = backorders.length;
                        if ( preparations !== null ) counter_preparations = preparations.length;
                        
                        let location = object.product.housing;
                        let weight   = object.product.weight;
                        let width    = object.product.width;
                        let depth    = object.product.depth;
                        let height   = object.product.height;
                        let measures = Number(width).toFixed(0) + " X " + Number(height).toFixed(0) + " X " + Number(depth).toFixed(0) + " ( cm ) ";
                        if(object.product.housing == 'ZZ-ZZ-ZZ') location = object.product.attr_location;
                        
                        if(location.length < 1) location = emptyTag;
                        document.getElementById("tag_weight").innerHTML = Number(weight).toFixed(2) + " Kg";
                        document.getElementById("tag_measures").innerHTML = measures;
                        document.getElementById("tag_location").innerHTML = location;
                        
                        document.getElementById("span_partial").innerHTML = counter_partials;
                        document.getElementById("span_backorder").innerHTML = counter_backorders;
                        document.getElementById("span_preparation").innerHTML = counter_preparations;
                        
                        html_partials = '';
                        html_backorders = '';
                        html_preparations = '';
                        
                        if(counter_partials > 0 ) {
                            partials.forEach(createPartialsLink);
                            $("#tr_partials").css('display', 'table-row');
                            document.getElementById('container_partials').innerHTML = html_partials;
                        }else{
                            document.getElementById('container_partials').innerHTML = emptyTag;
                        }
                        
                        if(counter_backorders > 0 ) {
                            backorders.forEach(createBackordersLink);
                            $("#tr_backorders").css('display', 'table-row');
                            document.getElementById('container_backorders').innerHTML = html_backorders;
                        }else{
                            document.getElementById('container_backorders').innerHTML = emptyTag;
                        }
                        
                        if(counter_preparations > 0 ) {
                            preparations.forEach(createPreparationsLink);
                            $("#tr_preparations").css('display', 'table-row');
                            document.getElementById('container_preparations').innerHTML = html_preparations;
                        }else{
                            document.getElementById('container_preparations').innerHTML = emptyTag;
                        }
                        
                        if(object.is_new === "0"){
                            $("#span_new").css('display', 'none');
                            $("#tag_new").css('color', 'red');
                            document.getElementById("tag_new").innerHTML = "NO";
                        }else{
                            $("#span_new").css('display', 'block');
                            $("#tag_new").css('color', 'darkgreen');
                            document.getElementById("tag_new").innerHTML = "YES";
                        }
                        
                        if(object.product.dim_verify === 1){
                            $("#mearues_set").css('display', 'block');
                            $("#set_measures").css('display', 'none');                        
                        }else{
                            $("#mearues_set").css('display', 'none');
                            $("#set_measures").css('display', 'block'); 

                            $('#measurementsForm').prop('action', data.updateRoute);
                            
                        }

                        $('#idProductMeasurementsForm').prop('value', object.product.id_product);
                        $('#idProductAttributeMeasurementsForm').prop('value', object.product.id_product_attribute);
                        $('#referenceMeasurementsForm').prop('value', object.product.reference);
                        $('#id_bms_procurement_purchase_order_product').prop('value', object.id_bms_procurement_purchase_order_product);
                        
                        
                        document.getElementById('tag_ordered').innerHTML    = object.qty_ordered;
                        document.getElementById('tag_billed').innerHTML     = object.qty_wmfaturado;
                        document.getElementById('tag_expecting').innerHTML  = object.qty_expected;
                        $('#tag_received').prop('value', object.qty_received);

                        $('#stock_entry').focus();
                        
                    }else{
                        $('#mainContainer').css('display', 'none');
                        $('#mainContainerEmpty').css('display', 'inline-block');

                    }   
                }else{
                    Swal.fire(data.message, '', 'warning')
                    $('#search').prop('value', '');
                }      
            }
        });
        
    }

    function createPartialsLink(item){ html_partials += '<a href="#" style="background-color: #956f55; color: #fff; padding: 5px; border-radius: 5px; text-decoration: none;margin: 5px;">' + item['id_order'] + '</a>'; }
    
    function createBackordersLink(item){ html_backorders += '<a href="#" style="background-color: #f78e1f; color: #fff; padding: 5px; border-radius: 5px; text-decoration: none;margin: 5px;">' + item['id_order'] + '</a>'; }
    
    function createPreparationsLink(item){ html_preparations += '<a href="#" style="background-color: dodgerblue; color: #fff; padding: 5px; border-radius: 5px; text-decoration: none;margin: 5px;">' + item['id_order'] + '</a>'; }


    function addQuantity(){
        event.stopPropagation();

        if( $('#search').val() === $('#stock_entry').val()){

            if (event.which === 13){
                stock_entry_quantity = $('#tag_received').val();

                if( (stock_entry_quantity+1) > $('#tag_ordered').text()){
                    Swal.fire("Quantity higher than expected. Please close current invoice first!", '', 'warning')
                }else{
                    stock_entry_quantity= Number(stock_entry_quantity) + Number(1);
                    
                    if(stock_entry_quantity > 0 ){
                        $('#tag_received').prop('value', stock_entry_quantity);
                        $('#saveEntryContainer').css('display', 'table-row');
                    }else{
                        $('#saveEntryContainer').css('display', 'none');
                    }

                    $('#stock_entry').prop('value', '');
                    $('#stock_entry').focus();
                }
            }
        }            

    }   
    















    function saveStockEntry(){
        
        received = $('#tag_received').val();

        $.ajax({
            type: 'PUT',
            url: $('#measurementsForm').attr('action'),
            dataType: "json",
            data: {
                _token: "{{ csrf_token() }}",
                action: 'saveStockEntry',
                id_product: $('#idProductMeasurementsForm').val(),
                id_product_attribute: $('#idProductAttributeMeasurementsForm').val(),
                reference: $('#referenceMeasurementsForm').val(),
                id_bms_procurement_purchase_order_product: $('#id_bms_procurement_purchase_order_product').val(),
                received: received,
            },
            success: function(response) {

                var data = JSON.parse(JSON.stringify(response));
                Swal.fire(data.message, '', 'info')

                $('#mainContainer').css('display', 'none');
                $('#search').prop('value', '');
                $('#search').focus();
            }       
        });
    }   











    function updateEAN(){

        Swal.fire({
            title: "EAN-13 UPDATE",
            text: "Please enter new EAN13",
            input: 'text',
            showCancelButton: true        
        }).then((result) => {

            console.log(result);
            if (result.value) {
                $.ajax({
                    type: 'PUT',
                    url: $('#measurementsForm').attr('action'),
                    dataType: "json",
                    data: {
                        _token: "{{ csrf_token() }}",
                        action: 'updateEAN',
                        ean13: result.value,
                        reference: $('#referenceMeasurementsForm').val(),
                    },
                    success: function(response) {
                        var data = JSON.parse(JSON.stringify(response));
                        Swal.fire(data.message, '', 'info')
                    }       
                });
            }else{
                Swal.fire("You have to enter the new EAN-13, please verify!", '', 'warning')
            }
        });
    }
    
    function saveMeasurementForm(){

        fc = 0;
        multibox = 0;
        if (document.getElementById('multiboxMeasurementsForm').checked) multibox = 1;
        if (document.getElementById('fcMeasurementsForm').checked) fc = 1;

        $.ajax({
            type: 'PUT',
            url: $('#measurementsForm').attr('action'),
            dataType: "json",
            data: {
                _token: "{{ csrf_token() }}",
                action: 'measurementsForm',
                id_product: $('#idProductMeasurementsForm').val(),
                reference: $('#referenceMeasurementsForm').val(),
                multibox: multibox,
                fc: fc,
                width: $('#widthMeasurementsForm').val(),
                height: $('#heightMeasurementsForm').val(),
                depth: $('#depthMeasurementsForm').val(),
                weight: $('#weightMeasurementsForm').val(),
            },
            success: function(response) {

            }       
        });
    }

    function report(){
        $('#reportsContainer').css('display', 'inline-block');
        $('#containerReportButton').css('display', 'inline-block');
        $('#containerReportResponse').css('display', 'none');
    }
    
    function sendReport(title){

        $.ajax({
            type: 'PUT',
            url: $('#measurementsForm').attr('action'),
            dataType: "json",
            data: {
                _token: "{{ csrf_token() }}",
                action: 'sendReport',
                id_product: $('#idProductMeasurementsForm').val(),
                id_product_attribute: $('#idProductAttributeMeasurementsForm').val(),
                reference: $('#referenceMeasurementsForm').val(),
                title: title,
                message: $('#reportMessage').val(),
            },
            success: function(response) {

                var data = JSON.parse(JSON.stringify(response));

                html= '<div class="alert alert-success" role="alert">';
                html+= '<div style="text-align: center;">' + data.response + '</div>';
                html+= '<hr>';
                    html+= '<div><b>TITLE: </b>' + data.title + '</div>';
                    html+= '<div><b>MESSAGE: </b>' + data.message + '</div>';
                    html+= '<div><b>REFERENCE: </b>' + data.reference + '</div>';
                html+= '</div>';

                $('#containerReportButton').css('display', 'none');
                $('#containerReportResponse').css('display', 'inline-block').css('width', '100%');
                document.getElementById('containerReportResponse').innerHTML = html;
            }       
        });
    }
    
    function destroyEntry(id){

        Swal.fire({
            title: "{{ __('messages.Please confirm the removal of the stock entry!')}}",
            showDenyButton: true,
            confirmButtonText: 'Yes',
            denyButtonText: `No`,
        }).then((result) => {

            if (result.isConfirmed) {

                Swal.fire('{{ __("messages.Stock entry removed!")}}', '', 'success')

                $('#destroyEntryForm').prop('action', '/customTools/stockEntry/' + id); 

                setTimeout(function() {
                    $('#destroyEntryForm').submit();
                }, 1000);

            } else if (result.isDenied) {
                Swal.fire("{{ __('messages.The stock entry has not been removed!')}}", '', 'info')
            }

        });
    }

</script>