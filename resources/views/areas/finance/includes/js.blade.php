<script>

    function executeFunction(){
        
        /**Swal.fire('{{ __("messages.Please wait until download is done!")}}', '', 'success')***/
        
        setTimeout(function() {        
            download_export();
        }, 1000);

    }

    function download_import(){
        
        $.ajax({
            url: "{{ route('finance.download_intrastat_import') }}",
            type: "POST",
            data: { } ,    
            dataType: "json",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (filename) {

                var hiddenElement = document.createElement('a');
                hiddenElement.href = '{{ request()->getSchemeAndHttpHost() }}' + '/admin/download/' + filename;
                hiddenElement.className = "import";
                hiddenElement.target = '_blank';
                hiddenElement.download = filename;
                hiddenElement.click();

            }, 
            error: function (error) {
                console.log(`Error ${error}`);
            }
        });
    
    } 

    function download_export(){
        
        console.log('Export init!');
        
        $.ajax({
            url: "{{ route('finance.download_intrastat_export') }}",
            type: "POST",
            data: { } ,    
            dataType: "json",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (filename) {
                
                setTimeout(function() {        
                    var hiddenElement = document.createElement('a');
                    hiddenElement.href = '{{ request()->getSchemeAndHttpHost() }}' + '/admin/download/' + filename;
                    hiddenElement.className = "export";
                    hiddenElement.target = '_blank';
                    hiddenElement.download = filename;
                    hiddenElement.click();
                }, 1000);

            }, 
            error: function (error) {
                console.log(`Error ${error}`);
            }
        });
        
        console.log('Export done!');
    } 

    function saveCurrencyRate(){
        
        $.ajax({
            url: "{{ route('currency_variation.store') }}",
            type: "POST",
            data: { 
                yuan: $('#yuan').val(),
                pound: $('#pound').val(),
                usd: $('#dollar').val(),
                yen: $('#yen').val(),
            },    
            dataType: "json",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (filename) {
                
                Swal.fire('{{ __("messages.Data saved successfully!")}}', '', 'success')

            }, 
            error: function (error) {
                console.log(`Error ${error}`);
            }
        });
    }     
    
</script>
