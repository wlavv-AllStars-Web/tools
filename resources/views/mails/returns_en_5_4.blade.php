<tr>
	<td style="background-color:#FFF;padding:15px; text-align: center;border-bottom:4px solid #dd170e;">
    	<font size="3" face="Open-sans, sans-serif" color="#555454" style="color: #666;">
            <p style="font-weight:500;font-size:20px;text-transform:uppercase;line-height:20px;padding: 0;margin: 0;">Return – approved</p>
		</font>
	</td>
</tr>
<tr>
    <td class="box" style="border:1px solid #D6D4D4;background-color:#f8f8f8;padding:7px 0">
        <table class="table" style="width:100%">
            <tr>
                <td width="10" style="padding:7px 0">&nbsp;</td>
                <td style="padding:7px 0; text-align: center;">
                    <font size="3" face="Open-sans, sans-serif" color="#555454" style="color: #666;">
                        <p>Hello {{$data->name}},</p>
                        <p>We have received your return and, after inspection, our quality department has been able to approve it. Our finance department will therefore process your refund within 48–72 business hours using the payment method originally used for your order.</p>
                        <p>Please note that, in accordance with our terms and conditions of sale, the shipping costs incurred for sending the package are not refunded, as they remain the customer’s responsibility in the case of a change of mind.</p>
                        <p>You can find all the details of our return policy by clicking on the link below <br>( section 7 ):</p>
                        <p><a href="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/en/content/3-terms-and-conditions-of-use">Terms and Conditions of Use</a></p>
                        <p>Should you have any further questions, we remain at your disposal.</p>
                		<br>
                        <p>Best regards,</p>
                        <p>All Stars Motorsport</p>
                    </font>
                </td>
                <td width="10" style="padding:7px 0">&nbsp;</td>
            </tr>
        </table>
    </td>
</tr>		