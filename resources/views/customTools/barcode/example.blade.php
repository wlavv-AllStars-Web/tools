<div style="width: 500px; float: left;">
    <div> <input style="width: 200px;" type="text" id="input_order" placeholder="ID ORDER"> </div>
    <button style="width: 200px;margin: 10px 0;" onclick="generateBarcode('order', 'generate', 'container_order_barcode')"> GENERATE ORDER Barcode </button>
    <div id="container_order_barcode"></div>
    <button style="width: 200px;margin: 10px 0;" onclick="generateBarcode('order', 'print', 'container_order_barcode_html')"> PRINT ORDER Barcode </button>
    <div id="container_order_barcode_html"></div>
</div>

<div style="width: 500px; float: left;">
    <div> <input style="width: 200px;" type="text" id="input_product" placeholder="ID PRODUCT"> </div>
    <div> <input style="width: 200px;margin-top: 10px" type="text" id="input_attribute" placeholder="ID ATTR"> </div>
    <button style="width: 200px;margin: 10px 0;" onclick="generateBarcode('product', 'generate', 'container_product_barcode')"> GENERATE PRODUCT Barcode </button>
    <div id="container_product_barcode"></div>
    <button style="width: 200px;margin: 10px 0;" onclick="generateBarcode('product', 'print', 'container_product_barcode_html')"> PRINT PRODUCT Barcode </button>
    <div id="container_product_barcode_html"></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    
    function generateBarcode(type, action, container){

        let order = $('#input_order').val();
        let product = $('#input_product').val();
        let attribute = $('#input_attribute').val();

        url = '';
        
        if( ( type == 'order' ) && ( action == 'generate') )    url = '{{ config('allstars.services.webtools.base_url') }}/barcode/order/generate/' + order;
        if( ( type == 'order' ) && ( action == 'print') )       url = '{{ config('allstars.services.webtools.base_url') }}/barcode/order/print/' + order;
        
        if( ( type == 'product' ) && ( action == 'generate') )  url = '{{ config('allstars.services.webtools.base_url') }}/barcode/product/generate/' + product + '/' + (parseInt(attribute) + parseInt(0));
        if( ( type == 'product' ) && ( action == 'print') )     url = '{{ config('allstars.services.webtools.base_url') }}/barcode/product/print/' + product + '/' + (parseInt(attribute) + parseInt(0));

        $.ajax({
            type: 'GET',
            url: url,
            dataType: "json",
            data: { },
            success: function(response) {
                
                console.log(container);
                $('#' + container).html(response.html); 
            }       
        });
                
    }
</script>