<tr>
    <td>
        <table style="padding: 20px;margin: 10px 0; width: 100%;">
            <tr> <td> {!! __('mails.asm.requestedProduct.text_1') !!} </td> </tr>
        </table>
        
        <table style="padding: 10px 20px;width: 100%;text-align: center;">
            <tr> <td> <p style="margin: 10px 0;"> {!! __('mails.asm.requestedProduct.text_2') !!} <b>{{$data['product_info']->lang->name}}</b> {!! __('mails.asm.requestedProduct.text_3') !!} </p> </td> </tr>
            <tr> <td> <p style="margin: 10px 0;"></p> </td> </tr>
            <tr> <td> <p style="margin: 10px 0;"> {!! __('mails.asm.requestedProduct.text_4') !!} <a  style="color: red;text-decoration: none;" href="https://www.all-stars-motorsport.com/en/search?orderby=position&orderway=desc&search_query={{$data['product_info']->reference}}&submit_search="> <b style="color: red;">{{$data['product_info']->reference}}</b> </a>.</p> </td> </tr>
            <tr> <td> <p style="margin: 10px 0;"></p> </td> </tr>
            <tr> <td> <p style="margin: 10px 0;"> {!! __('mails.asm.requestedProduct.text_5') !!} </p> </td> </tr>
            <tr> <td> <p style="margin: 10px 0;"></p> </td> </tr>
            <tr> <td> <p style="margin: 10px 0;"> {!! __('mails.asm.requestedProduct.text_6') !!} </p> </td> </tr>
        </table>
        
        <table style="padding: 10px;margin-top: 10px; margin-bottom: 20px; width: 100%;">
            <tr> <td style="text-align: center;"> {!! __('mails.asm.requestedProduct.text_7') !!} <br> {!! __('mails.asm.requestedProduct.text_8') !!} </td> </tr>
        </table>
    </td>
</tr>
