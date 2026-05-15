<tr>
	<td class="box" style="border:1px solid #D6D4D4;background-color:#f8f8f8;padding:7px 0">
		<table class="table" style="width:100%">
			<tr>
				<td width="10" style="padding:7px 0">&nbsp;</td>
				<td style="padding:7px 0; text-align: center;">
					<font size="3" face="Open-sans, sans-serif" color="#555454" style="color: #666;">
						<p style="border-bottom:1px solid #D6D4D4;margin:3px 0 7px;text-transform:uppercase;font-weight:500;font-size:18px;padding-bottom:10px;text-align:center;">PRODUCT COMPATIBILITY CONFIRMATION</p>
						<p>Hello {{$data->name}},</p>
						<p>Thank you for getting in contact with us.</p>
						<p>Upon analyzing your claim and checking with the manufacturer, we can confirm that this product is compatible with the following car models:</p>
						<style fieldset table { width: 100% !important; }></style>
						<fieldset style="background: #FFF !important;padding: 0;">
						    {!!$data->compatibilities!!}
						</fieldset>
						<p>As indicated in the description of the product, it fits without any modifications on OEM vehicles. However, if the vehicle has been previously modified, most likely adaptations have to be made, new parts can be needed, or modifications might need to take place depending on each particular case.</p>
						<p>- Further specific technical explanation on the particular warranty case -</p>
						<p>Thank you and have a good day,</p>
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