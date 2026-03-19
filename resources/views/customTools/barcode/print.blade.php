<!DOCTYPE html>
<html>
    <head>
        <title>PRINT EAN13</title>
        
        <style>
            html, body {
                margin: 0;
                height: 100%;
            }

        </style>
        <script>
        
            var is_chrome = function () { return Boolean(window.chrome); }

            function print_page(){
                
                if( !is_chrome() ){
                    window.print();
                    window.close();                    
                }else{
                    window.print();
                }

            }
            
            function closeTab(){

                if( !is_chrome() ){
                    window.print();
                    window.close();                    
                }

            }

        </script>
    </head>
    <body onload="print_page()" onmouseover="closeTab()" onclick="window.close();">
        {!!$data->html!!}
    </body>
</html> 