<tr>
	<td style="background-color:#FFF;padding:15px; text-align: center;border-bottom:4px solid #dd170e;">
    	<font size="3" face="Open-sans, sans-serif" color="#555454" style="color: #666;">
            <p style="font-weight:500;font-size:20px;text-transform:uppercase;line-height:20px;padding: 0;margin: 0;">Warranty – not approved</p>
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
    					<p>After sending the information you provided to the manufacturer as part of the analysis of your warranty claim, this one has reached a conclusion.</p>
    					<p>You can find below the response provided by the manufacturer:</p>
                        <div style="background-color:#fff; border:1px solid #d8dde6; border-radius:6px; padding:12px 14px; margin:35px; font-style:italic; line-height:22px; color:#555454; ">
                        	<span style="font-size:22px; color:#dd170e; font-weight:bold; line-height:0;">“</span>
                        	<span> {!!$data->response_manufacturer!!} </span>
                        	<span style="font-size:22px; color:#dd170e; font-weight:bold; line-height:0;">”</span>
                        </div>
                        <p>Despite all our efforts and the importance we attach to the satisfaction of our customers, the manufacturer was unable to provide a favorable response to your claim. The information sent did not provide tangible evidence that the product has a defect unrelated to abnormal wear or an external factor.</p>
    					<p>If you wish to obtain further information, you have the option to contact the brand directly via the contact form available on their website.</p>
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