@php
    $productUrl = __('mails.asm_newsletter.asm_newsletter.base_link') . $data->id_product . '-product.html';
@endphp

<tr>
    <td class="newsletter_content" style="width: 740px;">
        <table style="width: 740px;">
            <tr style="background-color: #ffffff;">
                <td colspan="3" style="text-align: center; background-image: white;">
                    <a href="{{ $productUrl }}" target="_blank" style="display: inline-block; text-decoration: none;">
                        <img id="main_image" src="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/{{ $data->id_image}}-tm_thickbox_default/main_product.jpg" style="width: 600px;margin: 0 auto;">
                    </a>
                </td>
            </tr>
        </table>
        <table style="background-color: #f1f1f1;">
            <tr>
                <td>
                    <table style="width: 740px;">
                        <tr>
                            <td>
                                <a href="{{ $productUrl }}" target="_blank" id="product_name" style="display: block; text-align: center; font-size: 28px; color: #282828; padding: 20px 0; text-decoration: none;">{{ $data->product_name}}</a>
                            </td>
                        </tr> 
                    </table>
                </td>
            </tr>
            <tr style="text-align: center; width: 740px; height: 70px; margin: 0 auto; display: table-cell;padding: 30px 0;">
                <td>
                    <table style="width: 720px;">
                        <tr>                                
                            <td class="left_price_container" style="width: 140px;"></td>
                            <td> 
                                <span style="font-weight: bolder; text-align: center; font-size: 28px; color: #555; padding: 10px;"> {!! __('mails.asm_newsletter.asm_newsletter.Price: ') !!} </span>
                                
                                @if ($data->reduction > 0)
                                    <span id="product_old_price" style="font-weight: bold; text-align: center; font-size: 28px; color: #282828; padding: 10px; text-decoration: line-through red; text-decoration-thickness: 3px;">{{ round($data->main_price, 2)}} €</span>
                                    <span id="product_current_price" style="font-weight: bolder; text-align: center; font-size: 28px; color: #D5050E; padding: 10px;">{{ round( $data->main_price * ( 1-$data->reduction ), 2)}} €</span> 
                                @else   
                                    <span id="product_current_price" style="font-weight: bolder; text-align: center; font-size: 28px; color: #D5050E; padding: 10px;">{{ round($data->main_price, 2)}} €</span>                                   
                                @endif
                                
                                {!! __('mails.asm_newsletter.asm_newsletter.Ex VAT') !!}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="text-align: center;padding: 30px 0;"> 
                    <a href="{{ $productUrl }}" id="see_more" target="_blank" style="background-color: rgb(213, 5, 14); color: white; font-weight: bolder; text-transform: uppercase; font-size: 18px; border: medium none; padding: 20px; margin: 20px auto; text-decoration: none;">{!! __('mails.asm_newsletter.asm_newsletter.see_product') !!}</a>
                </td>
            </tr>
        </table>
    </td>
</tr>
