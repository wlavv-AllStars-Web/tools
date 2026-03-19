<script>
    
    function searchTable(tag, value){
        
        $('.rows_orders').css('display', 'none');
        if(value.length == 0){
            $('.main_rows_orders').css('display', 'table-row');
        }else{
            $('.main_rows_orders').css('display', 'none');
            $('[class*="' + tag + value.toUpperCase() + '"]').css('display', 'table-row');
        }
    }
    
	function setRowAsChecked(panel, var_1, var_2, var_3, var_4){

	    $.ajax({
            type: 'POST',
            async: true,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ route('dashboard.post') }}",
            data: {
                action: 'addException',
                panel: panel,
                var_1: var_1,
                var_2: var_2,
                var_3: var_3,
            },
            success: function(data) {
                
                $('table tr#row_exception_' + var_1).remove();
                
                let quantity = $('#' + var_4 + '_quantity').text()
                $('#' + var_4 + '_quantity').text( parseInt(quantity-1) );
                
            }
        });
        
	}
	
</script>