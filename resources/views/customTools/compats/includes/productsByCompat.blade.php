<div class="row">
    <div class="col-lg-2"> </div>
    <div class="col-lg-8">
        <div id="searchProductsByCompat" class="navbar navbar-light customPanel" style="display: none;">
            <div class="row">
                <div class="col-lg-1"> </div>
                <div class="col-lg-2"> @include("customTools.compats.includes.stores",  [ 'store' => 5, 'options' => [ '0' => 'All stores', '1' => 'EuroMuscleParts', '2' => 'All Stars Motorsport', '3' => 'All Stars Distribution', '6' => 'Euro-rider' ] ]) </div>
                <div class="col-lg-2"> @include("customTools.compats.includes.options", [ 'type' => 1, 'options' => $options ]) </div>
                <div class="col-lg-2"> @include("customTools.compats.includes.options", [ 'type' => 2, 'options' => [] ]) </div>
                <div class="col-lg-2"> @include("customTools.compats.includes.options", [ 'type' => 3, 'options' => [] ]) </div>
                <div class="col-lg-2"> @include("customTools.compats.includes.options", [ 'type' => 4, 'options' => [] ]) </div>
                <div class="col-lg-1"> </div>
                <div class="col-lg-12"> </div>
                <div class="col-lg-12"> 
        
                    <div style="text-align: center; margin-top: 20px;"> 
                        <div id="products_container"> 
                            <div class="alert alert-warning" role="alert"> SELECT CAR COMPATIBILITY</div> 
                        </div> 
                    </div> 
                    
                    <div style="margin-top: 20px; display: none;" class="createCompat"> 
                        <input type="text" name="products" id="products" value="" placeholder="Add SKU's separated by ';'" style="width: 100%;text-align: center;font-size: 18px;">
                    </div> 
        
                    <div style="text-align: center; margin-top: 20px; display: none;" class="createCompat"> 
                        <button class="btn btn-success" onclick="create_compatibilities()" style="margin: 0 auto;"> CREATE COMPATIBILITIES</button> 
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-2"> </div>
</div>