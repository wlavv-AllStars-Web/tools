<tr>
	<td style="background-color:#FFF;padding:15px; text-align: center;border-bottom:4px solid #dd170e;">
    	<font size="3" face="Open-sans, sans-serif" color="#555454" style="color: #666;">
            <p style="font-weight:500;font-size:20px;text-transform:uppercase;line-height:20px;padding: 0;margin: 0;">Return – request registered</p>
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
						<p>Your return request has been successfully registered.</p>
						<p>Please note that an item may be returned only if the return request is submitted within 14 days following its delivery.</p>
						<p><b>The returned products must be in perfect condition (can be considered as new), complete and in their original packaging in excellent condition.</b></p>
						<p>Upon receipt, the items will be checked by our staff, and if they meet our return conditions, the refund will automatically be made via the method of payment initially used. Please note that we do not refund the shipping costs incurred in sending the order.</p>
						<p>Any item returned incomplete, damaged, or not in the condition in which it was originally sent will not be refunded and will be returned at your cost.</p>
						<p>If the product packaging is not in perfect condition, the repackaging costs will be deducted from the refunded amount, so we recommend that you ensure that the item is eligible for return before shipping it to our premises.</p>
						<p>You can consult our return policy and informational videos via the following links:</p>
						<p>
						    <br>
							<a href="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/en/content/3-terms-and-conditions-of-use">Terms and Conditions of Use</a><br>
							<a href="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/en/content/370-customer-support-faq">Customer Support FAQ</a>
						</p>
						<p>You will be notified by email whenever the status of your request is updated.</p>
						<p>You can also find all information regarding your request in your customer account.</p>
						<p>We remain at your disposal should you have any further questions.</p>
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