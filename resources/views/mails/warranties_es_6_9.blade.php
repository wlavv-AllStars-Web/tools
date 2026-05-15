<tr>
	<td class="box" style="border:1px solid #D6D4D4;background-color:#f8f8f8;padding:7px 0">
		<table class="table" style="width:100%">
			<tr>
				<td width="10" style="padding:7px 0">&nbsp;</td>
				<td style="padding:7px 0; text-align: center;">
					<font size="3" face="Open-sans, sans-serif" color="#555454" style="color: #666;">
						<p style="border-bottom:1px solid #D6D4D4;margin:3px 0 7px;text-transform:uppercase;font-weight:500;font-size:18px;padding-bottom:10px;text-align:center;">CONFIRMACIÓN DE COMPATIBILIDAD DEL PRODUCTO</p>
						<p>Hola {{$data->name}},</p>
                        <p>Hola y gracias por ponerse en contacto con nosotros.</p>
                        <p>Tras analizar su solicitud y consultar con el fabricante, le confirmamos que este artículo es compatible con los siguientes modelos:</p>
						<fieldset style="background: #FFF !important;padding: 0;">
						    {!!$data->compatibilities!!}
						</fieldset>
                        <p>Como se indica en la descripción del producto, este se instala sin necesidad de modificaciones en vehículos de serie.</p>
                        <p>Sin embargo, si el vehículo ha sido previamente modificado, podría ser necesario realizar adaptaciones, instalar componentes adicionales o modificar ciertos elementos, dependiendo de cada caso particular.</p>
                        <p>Información técnica pertinente -</p>
                        <br>
                        <p>Atentamente,</p>
                        <p>All Stars Motorsport</p>
					</font>
				</td>
				<td width="10" style="padding:7px 0">&nbsp;</td>
			</tr>
		</table>
	</td>
</tr>	