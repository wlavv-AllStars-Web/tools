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
    
    function setRowAsCheckedFromButton(trigger) {
        setRowAsChecked(
            $(trigger).attr('data-panel') || '',
            $(trigger).attr('data-var-1') || '',
            $(trigger).attr('data-var-2') || '',
            $(trigger).attr('data-var-3') || '',
            $(trigger).attr('data-panel-dom-id') || '',
            trigger
        );
    }

	function setRowAsChecked(panel, var_1, var_2, var_3, panelDomId, trigger){

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
                
                if (trigger) {
                    $(trigger).closest('tr').remove();
                } else {
                    $('table tr#row_exception_' + var_1).remove();
                }

                let quantityTag = $('#' + panelDomId + '_quantity');
                let quantity = parseInt(quantityTag.text(), 10);
                let newQuantity = Math.max((isNaN(quantity) ? 1 : quantity) - 1, 0);

                quantityTag.text(newQuantity);

                if (newQuantity === 0) {
                    let header = $('#' + panelDomId + '_header');

                    header
                        .css('background-color', '#0BDA51')
                        .css('cursor', 'default')
                        .removeAttr('onclick');

                    $('#' + panelDomId).hide().attr('data-open', 0);
                }
                 
            }
        });
        
	}
	
</script>
