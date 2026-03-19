<tr>
	<td style="background-color:#FFF;padding:15px; text-align: center;border-bottom:4px solid #dd170e;">
    	<font size="3" face="Open-sans, sans-serif" color="#555454" style="color: #666;">
            <p style="font-weight:500;font-size:20px;text-transform:uppercase;line-height:20px;padding: 0;margin: 0;">Return – not approved</p>
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
						<p>We have received your return and, after inspection, our quality department was unable to approve it. Indeed, as stated in our General Terms and Conditions of Sale, returned products must be in the same condition as when they were shipped from our warehouse, which is unfortunately not the case here.</p>
						<p>Please find attached detailed photos showing the condition of the product as received. Under these circumstances, a refund cannot be issued, as the product cannot be put back into stock and no longer meets the criteria of a new item.</p>
						<p>If you would like the product to be sent back to you, the return shipping costs will be at your cost.</p>

                        <div style="background-color:#fff; border:1px solid #d8dde6; border-radius:6px; padding:12px 14px; margin:35px; font-style:italic; line-height:22px; color:#555454; ">
    						<p>Return shipping fees: {{$data->value_to_pay}} €</p>
    						<p>Payment link: <a  href="{{$data->link_for_payment}}"> Click to proceed</a> </p>
    						@if(strlen($data->link_to_pictures) > 0)
    						<p>Images link: <a  href="{{$data->link_to_pictures}}"> Click to see images</a> </p>
    						@endif
                        </div>
                        
						<p>You can find full details of our return policy by clicking the link below <br>( paragraph 7 ):</p>
						<p>
							<a href="https://www.all-stars-motorsport.com/en/content/3-terms-and-conditions-of-use">Terms and Conditions of Use</a><br>
						</p>
						<p>Should you have any further questions, please do not hesitate to contact us.</p>
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