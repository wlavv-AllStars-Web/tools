<script>
    
   

function requestedSendNewsletter(id) {
    
    Swal.fire({
        title: '{{ __("messages.Set newsletter to send to customers?")}}',
        text: '{{ __("messages.Please confirm.")}}',
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: '{{ __("messages.send")}}'
    }).then((result) => {

        if (result.isConfirmed) {

            let element = $('#send_email_' + id).closest('tr');
            element.remove();
                    
            $.ajax({
                type: 'POST',
                url: "{{route('customerSupport.post')}}",
                dataType: "json",
                data: {
                    _token: "{{ csrf_token() }}",
                    action: 'setToSendNewsletter',
                    id: id,
                },
                success: function(response) { }       
            });
        
        } else {
                    
            Swal.fire({
                title: '{{ __("messages.Cancelled")}}',
                text: '{{ __("messages.Set to send action cancelled.")}}',
                icon: "error"
            });
        }
    });
}

</script>