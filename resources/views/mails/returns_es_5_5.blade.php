<tr>
	<td style="background-color:#FFF;padding:15px; text-align: center;border-bottom:4px solid #dd170e;">
    	<font size="3" face="Open-sans, sans-serif" color="#555454" style="color: #666;">
            <p style="font-weight:500;font-size:20px;text-transform:uppercase;line-height:20px;padding: 0;margin: 0;">Devolución – no aprobada</p>
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
						<p>Hola {{$data->name}},</p>
						<p>Hemos recibido su devolución y, tras su revisión, nuestro departamento de calidad no ha podido aprobar su aceptación. De acuerdo con nuestras Condiciones Generales de Venta, el producto devuelto debe encontrarse en el mismo estado en el que fue enviado desde nuestros almacenes, lo cual no es el caso.</p>
						<p>Adjuntamos fotografías detalladas que muestran el estado del producto tal y como lo hemos recibido. En estas condiciones, no es posible realizar un reembolso, ya que el producto no puede volver a ponerse en stock y ya no cumple con los criterios de un producto nuevo.</p>
						<p>Si desea que el producto le sea reenviado, el envío se realizará a su cargo.</p>

                        <div style="background-color:#fff; border:1px solid #d8dde6; border-radius:6px; padding:12px 14px; margin:35px; font-style:italic; line-height:22px; color:#555454; ">
    						<p>Gasto de reenvío: {{$data->value_to_pay}} €</p>
    						<p>Enlace de pago: <a  href="{{$data->link_for_payment}}"> Haga clic para continuar</a> </p>
    						@if(strlen($data->link_to_pictures) > 0)
    						<p>Enlace de imágenes: <a  href="{{$data->link_to_pictures}}"> Haga clic para ver imágenes</a> </p>
    						@endif
                        </div>
						
						<p>Puede consultar todos los detalles de nuestras condiciones de devolución haciendo clic en el siguiente enlace <br>( párrafo 7 ):</p>
						<p>
							<a href="https://www.all-stars-motorsport.com/es/content/3-condiciones-de-uso">Condiciones de uso</a><br>
						</p>
						<p>Quedamos a su disposición para cualquier otra consulta.</p>
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