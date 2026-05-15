<script>
    
    function getPriceMapOf(id_manufacturer){
        
        Swal.fire({
          title: 'REQUESTING INFORMATION!',
          icon: 'warning',
          allowOutsideClick: false,
          allowEscapeKey: false,
          didOpen: () => Swal.showLoading()   // spinner opcional
        });

        $.ajax({
            url: "{{ route('priceMap.getPriceMapOfBrand') }}",
            type: "POST",
            data: { 
                id_manufacturer: id_manufacturer 
            },    
            dataType: "json",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (data) {
                
                $('.brand_price_map').remove();
                console.log(data.html);
                $("#brandContainer").after(data.html);
                
                $('#priceMapDownload').attr('href', '/uploads/catalogue/ASD_' + id_manufacturer + '.csv?t={{rand()}}');
                
                $('#priceMapDownload').css('display', 'flex');
                $('#priceMapContainer').css('display', 'flex');
                

                Swal.close();
            }, 
            error: function (error) {
                console.log(`Error ${error}`);
            }
            
        });
    }
</script>