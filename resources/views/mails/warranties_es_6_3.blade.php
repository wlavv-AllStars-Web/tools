<tr>
	<td style="background-color:#FFF;padding:15px; text-align: center;border-bottom:4px solid #dd170e;">
    	<font size="3" face="Open-sans, sans-serif" color="#555454" style="color: #666;">
            <p style="font-weight:500;font-size:20px;text-transform:uppercase;line-height:20px;padding: 0;margin: 0;">Garantía – solicitud de información adicional</p>
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
            			<p>¡Hola {{$data->name}}!</p>
						<p>Tras haber enviado su documentación al fabricante en el marco del análisis de su solicitud de garantía, este no ha podido emitir una conclusión por el momento debido a la falta de cierta información.</p>
						<p>Con el fin de poder continuar con el tratamiento de su solicitud, necesitaríamos que nos facilite la siguiente información adicional:</p>
                        <div style="background-color:#fff; border:1px solid #d8dde6; border-radius:6px; padding:12px 14px; margin:35px; font-style:italic; line-height:22px; color:#555454; ">
                        	<span style="font-size:22px; color:#dd170e; font-weight:bold; line-height:0;">“</span>
                        	<span> {!! $data->supplier_message !!} </span>
                        	<span style="font-size:22px; color:#dd170e; font-weight:bold; line-height:0;">”</span>
                        </div>
						<p>Le rogamos que adjunte estos documentos a su solicitud de garantía desde su cuenta de cliente, añadiéndolos en la sección 'Información adicional' a través del siguiente enlace:</p>
                        <p style="text-align:center; margin:25px 0;">
                        	<a href="{!! $data->href !!}" style=" display:inline-block; min-width:220px; padding:12px 20px; background-color:#dd170e;border:1px solid #b0120a; border-radius:6px; color:#ffffff; font-size:14px; font-weight:600; text-decoration:none; text-transform:uppercase; letter-spacing:0.5px;">
                        		SOLICITUD DE GARANTÍA
                        	</a>
                        </p>
						<p>En cuanto dispongamos de estos datos, los remitiremos inmediatamente al fabricante para poder proporcionar una respuesta a la mayor brevedad posible.</p>
						<p>Agradecemos de antemano su colaboración.</p>
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