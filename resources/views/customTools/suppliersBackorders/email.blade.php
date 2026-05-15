<!DOCTYPE html>
<html>
    <head>
        <title>ALL STARS BACK ORDERS OVERVIEW - THANKS FOR YOUR REPLY</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    </head>
    <body>
        
        <style>
            .customPanel{ background-color: #fff;border: 1px solid #ddd;border-radius: 5px;display:block; padding: 10px;margin-top: 10px; }
        </style>
        
        <div style="width: 800px; margin: 0 auto;color: #000;">
            <table style="width: 100%;font-size: 18px">
                <tr>
                    <td style="text-align: center;">
                        <img src="{{ config('allstars.stores.ASD.logo_url') }}" style="padding: 20px;" >
                    </td>
                </tr>
               <tr>
                   <td colspan="8" style="height: 3px;background-color: #666;"> </td>
               </tr>
               <tr>
                   <td colspan="8">
                       <div style="text-align: center;font-size: 20px; font-weight: bolder;padding: 20px 0 25px 0"> 
                            <span style="dodgerblue">{{ $dataView['selected_supplier_name'] }}</span> - MONTHLY BACKORDERS OVERVIEW 
                       </div>               
                   </td>
               </tr>
               <tr>
                   <td colspan="8" style="height: 3px;background-color: #666;"> </td>
               </tr>
               <tr>
                   <td>
                       <div  style="text-align: center; padding: 50px 20px;font-size: 18px; width: 750px; margin: 0 auto;">
                           <p>
                                Please click the buton below to access our updated active backorders overview page. 
                                <br>
                                This 100% secure webpage shows all pending items we ordered from you until the end of last month but still not invoiced.(last 30 days)
                           </p>

                           <div style="padding: 20px;">
                               <a class="btn btn-info" style="border-radius: 2px;color: #FFF;background-color: dodgerblue;border: 2px solid #1773cd; border-radius: 5px;padding: 10px;text-decoration: none;" href="{{route('frontSuppliersBackorders.index', ['id_supplier' => $dataView['selected_supplier_id'], 'token' => $dataView['token'] ])}}">BACKORDERS LIST</a>
                           </div>

                           <p>
                                Please carefully review these datas and let us know if still active or not in your system by selecting either "YES" or "NO" for each item. 
                                <br><br>
                                If needed, you can also provide a brief comment for any specific item to clarify its status or to share additional informations. 
                                <br><br>
                                Click confirm at the bottom of the page to submit the file.                              
                            </p>
                           <br>
                           <p>
                                Your feedback will help us to get both our systems and records accurately matching.
                                <br><br>
                                Please note, this file will be automatically sent to you at the begining of each month for constant update.
                           </p>
                           <br>
                           <p> We greatly appreciate your cooperation and support. Should you have any questions or require further assistance, feel free to reach out. </p>
                           <br>
                           <p>
                               Thank you once again for your help.
                           </p>
                       </div>
                    </td>
               </tr>
               <tr>
                   <td colspan="8" style="height: 3px;background-color: #666;"> </td>
               </tr>
               <tr>
                   <td colspan="8">
                       <div style="text-align: center;font-size: 20px; font-weight: bolder;padding: 20px 0 25px 0"> 
                            ALL STARS DISTRIBUTION
                       </div>               
                   </td>
               </tr>
           </table>
        </div>
    </body>
</html> 
