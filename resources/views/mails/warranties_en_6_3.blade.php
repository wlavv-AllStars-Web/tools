<tr>
	<td style="background-color:#FFF;padding:15px; text-align: center;border-bottom:4px solid #dd170e;">
    	<font size="3" face="Open-sans, sans-serif" color="#555454" style="color: #666;">
            <p style="font-weight:500;font-size:20px;text-transform:uppercase;line-height:20px;padding: 0;margin: 0;">Warranty – additional information requested</p>
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
						<p>After submitting your documents to the manufacturer as part of the review of your warranty claim, he has not yet been able to reach a conclusion at this stage due to missing information.</p>
						<p>In order to proceed with your request, we kindly ask you to provide the following additional details:</p>
                        <div style="background-color:#fff; border:1px solid #d8dde6; border-radius:6px; padding:12px 14px; margin:35px; font-style:italic; line-height:22px; color:#555454; ">
                        	<span style="font-size:22px; color:#dd170e; font-weight:bold; line-height:0;">“</span>
                        	<span> {!! $data->supplier_message !!} </span>
                        	<span style="font-size:22px; color:#dd170e; font-weight:bold; line-height:0;">”</span>
                        </div>
						<p>Please upload these documents to your warranty request through your customer account by adding them in the 'Additional Information' section using the link below:</p>
                        <p style="text-align:center; margin:25px 0;">
                        	<a href="{!! $data->href !!}" style=" display:inline-block; min-width:220px; padding:12px 20px; background-color:#dd170e; border:1px solid #b0120a; border-radius:6px; color:#ffffff; font-size:14px; font-weight:600; text-decoration:none; text-transform:uppercase; letter-spacing:0.5px; ">
                        		Warranty Request
                        	</a>
                        </p>
						<p>Once we receive this information, we will forward it immediately to the manufacturer so that we can provide you with a response as quickly as possible.</p>
						<p>In the meantime, we thank you for your cooperation.</p>
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