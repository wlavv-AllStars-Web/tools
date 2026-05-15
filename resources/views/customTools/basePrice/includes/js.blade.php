<script>
    
    function executeBasePriceUpdate(id_manufacturer){
        
        Swal.fire({
            title: "Do you wan't to apply base prices?",
            text: "This action will update purchase prices based on currency convertion set on currency!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, apply!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                
                $.ajax({
                    url: "{{ route('basePrice.execute') }}",
                    type: "POST",
                    data: {
                        id_manufacturer: id_manufacturer,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Swal.fire('Sucesso!', response.message, 'success');
                    },
                    error: function(xhr) {
                        Swal.fire('Erro!', 'Algo correu mal.', 'error');
                    }
                });
            
            }
        })
        
    }
</script>