<script>

    /** ON STOP TYPING **/
    var typingTimer;                
    var doneTypingInterval = 200;
    var input = $('#scan');
    
    input.on('keyup', function () {
      clearTimeout(typingTimer);
      typingTimer = setTimeout(doneTyping, doneTypingInterval);
    });
    
    input.on('keydown', function () {
      clearTimeout(typingTimer);
    });
    
    function doneTyping () {
        let barcode = $('#scan').val();
        if(barcode.length) additionalData();
    }
    
    function openList(list){
        $('.holders').css('display', 'none');
        $('#holder_' + list).css('display', 'block');
    }
    
    function openOrder(id_order, element){
        
        element.addClass('orderHeader_active').removeClass('orderHeader');
        
        $('.order_container').css('display', 'none');
        $('#pickingCounters').css('display', 'none');
        
        $('#order_' + id_order).toggle('slow');
        $('#openOrder').attr('value', id_order);
        
        $('.orderHeader').css('display', 'none');
        
        let pickingBarcode = $('#pickingBarcode_'+id_order).val();
        
        $('#pickingContainer').prop('value', pickingBarcode);
        $('#barcodeScanContainer').css('display', 'block');
        $('#scan').focus();
    }

    function addPickingContainer(id_order){
        
        Swal.fire({
            title: "PICKING CONTAINER!",
            text: "Please scan the picking container!",
            input: 'text',
            showCancelButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            inputValidator: (value) => {
                if (!value) {
                    return 'Please scan the picking container!';
                }
            }
        }).then((result) => {
            
            if (result.value) {
                
                $.ajax({
                    type: 'POST',
                    url: "{{ route('picking.container.save') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_order: id_order,
                        pickingContainer: result.value
                    },
                    success: function(response) {
                        if (response == 1) {
                            $('#pickingContainer').attr('value', result.value);
                            $('#pickingContainer_' + id_order).replaceWith('<span style="color: dodgerblue;" id="pickingContainer_' + id_order + '">' + result.value + '</span>');
                            $('#tr_pickingContainer_' + id_order).css('display', 'contents');
                            $('#pickingBarcode_' + id_order).prop('value', result.value);
                            location.reload();
                        } else {
                            Swal.fire({
                                title: "Can't close picking.",
                                text: "Please confirm all products before scanning the container.",
                                icon: "error"
                            }).then(() => {
                                $('#scan').focus();
                            });
                        }
                    }
                });
            }
        });
        
    }

    function additionalData(){
        
        let id_order = $('#openOrder').val();
        
        if(!id_order.length){
            $('#scan').attr('value', '');
            $('#barcodeToFind').attr('value', '');
        }else{
            sendData();
            /**$('#barcodeToFind').attr('value', $('#scan').val());**/
        }

    }

    function sendData(){
        
        let barcodeToFind = 0;

        $.ajax({
            type: 'POST',
            url: "{{ route('picking.confirmEAN') }}",
            async: false,
            data: {
                _token: "{{ csrf_token() }}",
                code: $('#scan').val()     
            },
            success: function(response) {
                barcodeToFind = response;
            }       
        });
        
        $('#barcodeToFind').attr('value', barcodeToFind);


        let pickingContainer = $('#pickingContainer').val();
        let id_order = $('#openOrder').val();
        
        console.log('BEFORE ' + barcodeToFind);
        barcodeToFind = barcodeToFind.replace(/\s/g, '');
        console.log('AFTER ' + barcodeToFind);
        
        /** CTS-TM-MQB-60D **/
        let toConfirm = $('.optionsToConfirm_' + id_order + '_' + barcodeToFind);

        if(toConfirm.length){

            Swal.fire({
                title: "CONFIRM OPTIONS",
                text: "Please confirm options before proceed!",
                icon: "error"
            });
            
            $('#scan').prop('value', '');
            $('#scan').focus();
            return false;    
        }
        
        if( ( id_order.length > 0 ) && ( barcodeToFind.length > 0 ) ){
            
            let scanned_item = $('.product_' + id_order + '_' + barcodeToFind);
            
            if(scanned_item.length){
                
                let scannedQuantity     = $('.scannedQuantity_' + id_order + '_' + barcodeToFind).val();
                let requestedQuantity   = $('.requestedQuantity_' + id_order + '_' + barcodeToFind).val();
                let requestedBarcode    = $('.requestedBarcode_' + id_order + '_' + barcodeToFind).val();
                let requestedReference  = $('.requestedReference_' + id_order + '_' + barcodeToFind).val();
                
                scannedQuantity = parseInt(scannedQuantity) + parseInt(1);
                requestedQuantity = parseInt(requestedQuantity);

                if( scannedQuantity > requestedQuantity){
                    
                    $('.product_' + id_order + '_' + barcodeToFind).css('background-color', '#f5c6cb').css('color', '#721c24').css('border:', '1px solid #721c24');

                    Swal.fire({
                        title: "EXCESSIVE QUANTITY",
                        text: "The necessary quantity has been exceeded. Please check!",
                        icon: "error"
                    });

                    $('#scan').attr('value', '');
                    $('#scan').prop('value', '');
                    $('#barcodeToFind').attr('value', '');
                    $('#scan').focus();

                    return false;
                }
                
                $('.scannedQuantity_' + id_order + '_' + barcodeToFind).prop('value', scannedQuantity);
                
                $('.productScannedQuantity_' + id_order + '_' + barcodeToFind).replaceWith('<span class="productScannedQuantity_' + id_order + '_' + barcodeToFind + '">' + scannedQuantity + '</span>');
                
                if( scannedQuantity < requestedQuantity) $('.product_' + id_order + '_' + barcodeToFind).css('background-color', '#ffeeba').css('color', '#856404').css('border:', '1px solid #856404');    

                if( scannedQuantity == requestedQuantity){
                    $('.product_' + id_order + '_' + barcodeToFind).css('background-color', '#c3e6cb').css('color', '#155724').css('border:', '1px solid #155724');  
                }

                id_product = $('.requestedProduct_' + id_order + '_' + barcodeToFind).val();  
                id_product_attribute = $('.requestedAttribute_' + id_order + '_' + barcodeToFind).val();  
                
                $.ajax({
                    type: 'POST',
                    url: "{{ route('picking.rowDone') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_order: id_order,
                        barcode: barcodeToFind,
                        id_product: id_product,
                        id_product_attribute: id_product_attribute,
                        quantityRequested: requestedQuantity,
                        scannedQuantity: scannedQuantity,
                        pickingContainer: pickingContainer     
                    },
                    success: function(response) {
                        if( response == 1) addPickingContainer(id_order);
                    }       
                });

                $('#scan').attr('value', '');
                $('#scan').prop('value', '');
                $('#barcodeToFind').attr('value', '');
                
            }else{
                
                Swal.fire({
                    title: "PRODUCT NOT FOUND",
                    text: "The product is not for picking on this order. Please verify!",
                    icon: "error"
                });
                
                $('#scan').prop('value', '');
                $('#barcodeToFind').prop('value', '');
                $('#scan').focus();
            }
            
        }else{

            Swal.fire({
                title: "Can't confirm picking. ",
                text: "Please select again the order!",
                icon: "error"
            });            
    
            $('#openOrder').attr('value', '');
            $('#openOrder').attr('value', '');
            $('#barcodeToFind').attr('value', '');
        
        }
    }
    
    function validateComponents(id_order, barcode){

        barcode = barcode.replace(/\s/g, '');

        $('.product_' + id_order + '_' + barcode).removeClass('blurData');
        $('.optionsToConfirm_' + id_order + '_' + barcode).remove();
                
        $('#scan').prop('value', '');
        $('#barcodeToFind').prop('value', '');
        $('#scan').focus();

    }
</script>
