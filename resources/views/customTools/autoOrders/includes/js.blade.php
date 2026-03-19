<script>

function setAsOrdered(id, brand, id_brand, index){

    $.ajax({
        url: "{{ route('autoOrders.setAsOrdered') }}",
        type: "POST",
        data: { id: id, quantity: $('#stock_to_order_' + id_brand + '_' + index).val() } ,    
        dataType: "json",
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (success) {
            
            if(success){
                $('#tr_setAsOrdered_'+id).remove();

                brand_quantity = $("#counter_brand_"+id_brand).html();
                quantity = brand_quantity - 1;
                
                $("#counter_brand_"+id_brand).text(quantity);

            }else{
                Swal.fire('{{ __("messages.Fail to set as ordered. Please verify!")}}', '', 'warning')
            }

        }, 
        error: function (error) {
            console.log(`Error ${error}`);
        }
    });
}

function addToOrder(element){

    $.ajax({
        url: "{{ route('autoOrders.addToOrder') }}",
        type: "POST",
        data: { 
            quantity: $('#newQuantity_' + element.attr('id_supplier')).val(),
            reference: element.attr('reference'),
            name: element.attr('title'),
            supplier: element.attr('supplier'),
            id_supplier: element.attr('id_supplier')
         } ,    
        dataType: "json",
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (success) {

            if(success.success){
                Swal.fire(element.attr('reference') + " added to order!", '', 'success')
                $('.search_reference').prop('value', '');
                $('#newQuantity_' + element.attr('id_supplier')).prop('value', '');
                $("#ajax_search_response_" + element.attr('id_supplier')).replaceWith('<div class="ajax_search_response" id="ajax_search_response_'+element.attr('id_supplier')+'"></div>');

                if(success.type=='add') $('#orderNewRow_' + element.attr('id_supplier')).replaceWith(success.html);

                $('#item_counter_' + element.attr('id_supplier')).text(success.quantity_order);

                if(success.type=='edit') location.reload();

            }else{
                Swal.fire('{{ __("messages.Can not add the product to the order!")}}', '', 'error')
            }

        }, 
        error: function (error) {
            console.log(`Error ${error}`);
        }
    });
    
}

function updateOrder(element, id_supplier){

    $.ajax({
        url: "{{ route('autoOrders.updateOrder') }}",
        type: "POST",
        data: { 
            quantity: element.val(),
            reference: element.attr('reference'),
            id_supplier: id_supplier,
         },    
        dataType: "json",
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (success) {
            
            if(success.success){
                Swal.fire(element.attr('reference') + '{{ __("messages.quantity updated!")}}', '', 'success')

                if(success.type == 'remove'){

                    element.closest('tr').remove();
                }
                
                $('#item_counter_' + id_supplier).text(success.quantity_order);
                
            }else{
                Swal.fire('{{ __("messages.Can not add the product to the order!")}}', '', 'error')
            }

        }, 
        error: function (error) {
            console.log(`Error ${error}`);
        }
    });
    
}

function getProductInfo(element, id_supplier){

    if(element.val() != ""){
        $.ajax({
            url: "{{ route('autoOrders.getProductInfo') }}",
            type: "POST",
            data: { 
                reference: element.val(), 
                id_supplier: id_supplier
            },    
            dataType: "json",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (data) {
                $("#ajax_search_response_" + id_supplier).replaceWith(data.items);
            }, 
            error: function (error) {
                console.log(`Error ${error}`);
            }
        });
    }

}

function getProductsInfo(manufacturer, id_manufacturer){

    const waitingPopup = Swal.fire({
        title: '{{ __("messages.Waiting for response!")}}',
        html: '{{ __("messages.Please wait!")}}',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });

    $.ajax({
        url: "{{ route('autoOrders.getProductsInfo') }}",
        type: "POST",
        data: { manufacturer: manufacturer } ,    
        dataType: "json",
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (data) {
            $("#table_" + id_manufacturer).replaceWith(data.html);
            $('#toggle_'+id_manufacturer).toggle();
            Swal.hideLoading(false);
            waitingPopup.close();
        }
    });
    
}

function createAndDownloadZip(manufacturer){

    const waitingPopup = Swal.fire({
        title: '{{ __("messages.Waiting for response!")}}',
        html: '{{ __("messages.Please wait!")}}',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });

    $.ajax({
        url: "{{ route('autoOrders.exportCSV') }}",
        type: "POST",
        data: { manufacturer: manufacturer } ,    
        dataType: "json",
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (data) {

            Swal.hideLoading(false);
            waitingPopup.close();

            window.location = data.path;
        }
    });
    
}

function cleanBrand(manufacturer, key){

    Swal.fire({
        title: '{{ __("messages.Clean")}}' + manufacturer,
        html: '{{ __("messages.Remove products from the auto orders list?")}}',
        showCancelButton: true,
        confirmButtonText: '{{ __("messages.YES")}}',
        denyButtonText: '{{ __("messages.CANCEL")}}'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('autoOrders.cleanBranditems') }}",
                type: "POST",
                data: { manufacturer: manufacturer } ,    
                dataType: "json",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (data) {
                    $('#row_brand_'+key).remove();
                    location.reload();
                }
            });

        } else if (result.isDenied) {
            Swal.fire('{{ __("messages.Cancelled")}}', '{{ __("messages.Request aborted!")}}', "error");
        }
    });

}

function saveOrder(id_supplier){

    Swal.fire({
        title: '{{ __("messages.Add a reference for the order")}}',
        input: "text",
        inputAttributes: {
        autocapitalize: "off"
    },
    showCancelButton: true,
    confirmButtonText: "OK",
    showLoaderOnConfirm: true,
    allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {

        $.ajax({
            url: "{{ route('autoOrders.saveOrder') }}",
            type: "POST",
            data: { 
                id_supplier: id_supplier,
                order_reference: result.value + ""
            } ,    
            dataType: "json",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (success) {
                
                if(success.success){
                    Swal.fire('{{ __("messages.Order")}}' + result.value +'{{ __("messages.created!")}}', '', 'success')

                    $('#panel_'+id_supplier).remove();
                    
                }else{
                    Swal.fire('{{ __("messages.Can not generate the order!")}}', '', 'error')
                }

            }, 
            error: function (error) {
                console.log(`Error ${error}`);
            }
        });
    
    });

}

</script>