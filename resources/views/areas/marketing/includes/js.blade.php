<script>
    function setProductForNewsletter(id_product, reference, send){

        if(send==0){
            removeFromList(id_product, reference)
        }else{
            setToSend(id_product, reference)
        }
        
    }
    
    function removeFromList(id_product, reference){
        
       Swal.fire({
            title: '{{ __("messages.Remove row from list?")}}',
            text: '{{ __("messages.Please confirm.")}}',
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: '{{ __("messages.Remove")}}'
        }).then((result) => {
    
            if (result.isConfirmed) {
    
                $.ajax({
                    type: 'POST',
                    url: "{{route('marketing.post')}}",
                    dataType: "json",
                    data: {
                        _token: "{{ csrf_token() }}",
                        action: 'removeProductForNewsletter',
                        id_product: id_product,
                        reference : reference
                    },
                    success: function(response) {
                        
                        Swal.fire({
                            title: '{{ __("messages.Operation successful")}}',
                            text: '{{ __("messages.Product removed from list.")}}',
                            icon: "success"
                        });
                        
                        $('#row_exception_' + id_product).remove();

                        let new_quantity = $('#counter_products_for_newsletter_quantity').text() - 1;
                        $('#counter_products_for_newsletter_quantity').text(new_quantity);

                    }       
                });
            
            } else {
                        
                Swal.fire({
                    title: '{{ __("messages.Cancelled")}}',
                    text: '{{ __("messages.Operation cancelled!")}}',
                    icon: "error"
                });
    
            }

        }); 
    }
    
    function setToSend(id_product, reference){

        Swal.fire({
            title: '{{ __("messages.Set newsletter to send?")}}',
            text: '{{ __("messages.Please confirm.")}}',
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: '{{ __("messages.send")}}'
        }).then((result) => {
    
            if (result.isConfirmed) {

                $.ajax({
                    type: 'POST',
                    url: "{{route('marketing.post')}}",
                    dataType: "json",
                    data: {
                        _token: "{{ csrf_token() }}",
                        action: 'productForNewsletter',
                        id_product: id_product,
                        reference: reference
                    },
                    success: function(response) {

                        Swal.fire({
                            title: '{{ __("messages.Add to newsletter list")}}',
                            text: '{{ __("messages.Product added.")}}',
                            icon: "success"
                        });
                        
                        $('#row_exception_' + id_product).remove();

                        let new_quantity = $('#counter_products_for_newsletter_quantity').text() - 1;
                        $('#counter_products_for_newsletter_quantity').text(new_quantity);
                        
                    }       
                });
            
            } else {
                Swal.fire({
                    title: '{{ __("messages.Cancelled")}}',
                    text: '{{ __("messages.Operation cancelled!")}}',
                    icon: "error"
                });
            }
        });
    }
    
</script>