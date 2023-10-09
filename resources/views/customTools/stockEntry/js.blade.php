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
        
        $.ajax({
            type: 'POST',
            url: "{{route('stockEntry.post')}}",
            data: {
                _token: "{{ csrf_token() }}",
                action: type,
                code: $('#search').val(),
            },
            success: function(data) {
                            
                const product = data.product;
                const partials = data.partials;
                const backorders = data.backorders;
                const preparations = data.preparations;
                
                console.log(data.product.product_id);

                if(data.product.product_id > 0){
                    
                    $('#mainContainer').css('display', 'inline-block');
                    $('#mainContainerEmpty').css('display', 'none');
                    
                    let counter_partials = 0;
                    let counter_backorders = 0;
                    let counter_preparations = 0;
                    
                    if ( partials !== null )     counter_partials     = partials.length;
                    if ( backorders !== null )   counter_backorders   = backorders.length;
                    if ( preparations !== null ) counter_preparations = preparations.length;
                    
                    let location = product.housing;
                    let weight   = product.p_weight;
                    let width    = product.width;
                    let depth    = product.depth;
                    let height   = product.height;
                    let measures = Number(width).toFixed(0) + " X " + Number(height).toFixed(0) + " X " + Number(depth).toFixed(0) + " ( cm ) ";
                    if(obj['housing'] == 'ZZ-ZZ-ZZ') location = obj['attr_location'];
                    
                    /** WEIGHT AND MEASURES**/
                    document.getElementById("tag_weight").innerHTML = Number(weight).toFixed(2) + " Kg";
                    document.getElementById("tag_measures").innerHTML = measures;
                    document.getElementById("tag_location").innerHTML = location;
                    
                    /** ORDERS STATUS **/
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
                        $("#tr_partials").css('display', 'none');
                    }
                    
                    if(counter_backorders > 0 ) {
                        backorders.forEach(createBackordersLink);
                        $("#tr_backorders").css('display', 'table-row');
                        document.getElementById('container_backorders').innerHTML = html_backorders;
                    }else{
                        $("#tr_backorders").css('display', 'none');
                    }
                    
                    if(counter_preparations > 0 ) {
                        preparations.forEach(createPreparationsLink);
                        $("#tr_preparations").css('display', 'table-row');
                        document.getElementById('container_preparations').innerHTML = html_preparations;
                    }else{
                        $("#tr_preparations").css('display', 'none');
                    }
                    
                    /** NEWS **/
                    if(product.is_new === "0"){
                        $("#span_new").css('display', 'none');
                        $("#tag_new").css('color', 'red');
                        document.getElementById("tag_new").innerHTML = "NO";
                    }else{
                        $("#span_new").css('display', 'block');
                        $("#tag_new").css('color', 'darkgreen');
                        document.getElementById("tag_new").innerHTML = "YES";
                        
                    }
                    
                    document.getElementById('tag_ordered').innerHTML = product.qty_ordered;
                    document.getElementById('tag_billed').innerHTML = product.qty_wmfaturado;
                    document.getElementById('tag_expecting').innerHTML = product.qty_expected;
                    document.getElementById('tag_received').placeholder = product.qty_received;
                    stock_entry_quantity = 0;
                    $('#stock_entry').focus();
                    
                }else{
                    $('#mainContainer').css('display', 'none');
                    $('#mainContainerEmpty').css('display', 'inline-block');

                }         
            }
        });
        
        function createPartialsLink(item){ html_partials += '<a href="#" style="background-color: #956f55; color: #fff; padding: 5px; border-radius: 5px; text-decoration: none;margin: 5px;">' + item['id_order'] + '</a>'; }
        
        function createBackordersLink(item){ html_backorders += '<a href="#" style="background-color: #f78e1f; color: #fff; padding: 5px; border-radius: 5px; text-decoration: none;margin: 5px;">' + item['id_order'] + '</a>'; }
        
        function createPreparationsLink(item){ html_preparations += '<a href="#" style="background-color: dodgerblue; color: #fff; padding: 5px; border-radius: 5px; text-decoration: none;margin: 5px;">' + item['id_order'] + '</a>'; }

        $("#stock_entry").keyup(function(event) {
            event.stopPropagation();
            
            if( $('#search').val() === $('#stock_entry').val()){
            
                stock_entry_quantity +=1;
                
                if(stock_entry_quantity > 0 ){
                    $('#tag_received').attr('value', stock_entry_quantity);
                    $('#saveEntryContainer').css('display', 'table-row');
                }else{
                    $('#saveEntryContainer').css('display', 'none');
                }
                $('#stock_entry').attr('value', '');
            }
            
            $('#stock_entry').focus();
        });      
        
        function saveStockEntry(){
            alert('SAVE');
        }
        
    }

    function updateEAN(){
        let ean = prompt("Please enter new EAN13");
    }

    function report(){
        alert('Under development'); 
    }
    


</script>