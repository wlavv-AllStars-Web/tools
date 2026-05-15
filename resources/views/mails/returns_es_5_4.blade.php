<tr>
	<td style="background-color:#FFF;padding:15px; text-align: center;border-bottom:4px solid #dd170e;">
    	<font size="3" face="Open-sans, sans-serif" color="#555454" style="color: #666;">
            <p style="font-weight:500;font-size:20px;text-transform:uppercase;line-height:20px;padding: 0;margin: 0;">Devolución – aprobada</p>
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
                        <p>Hemos recibido su devolución y, tras la verificación correspondiente, nuestro departamento de calidad ha podido validar su aceptación. Por lo tanto, nuestro departamento financiero procesará su reembolso dentro de 48 a 72 horas (días hábiles) a través del método de pago utilizado para su pedido.</p>
                        <p>Tenga en cuenta que, de acuerdo con nuestras condiciones generales de venta, los gastos de envío generados para la devolución del paquete no son reembolsables, ya que siguen siendo a cargo del cliente en caso de cambio de opinión.</p>
                        <p>Puede consultar todos los detalles de nuestras condiciones de devolución haciendo clic en el siguiente enlace ( apartado 7 ):</p>
                        <p><a href="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/es/content/3-condiciones-de-uso">Condiciones de uso</a></p>
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