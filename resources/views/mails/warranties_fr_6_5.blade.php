<tr>
	<td style="background-color:#FFF;padding:15px; text-align: center;border-bottom:4px solid #dd170e;">
    	<font size="3" face="Open-sans, sans-serif" color="#555454" style="color: #666;">
            <p style="font-weight:500;font-size:20px;text-transform:uppercase;line-height:20px;padding: 0;margin: 0;">Garantie – non approuvée</p>
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
						<p>Bonjour {{$data->name}},</p>
						<p>Après avoir envoyé vos éléments au fabricant dans le cadre de l’analyse de votre demande de garantie, ce dernier est parvenu à émettre une conclusion.</p>
						<p>Vous pouvez consulter ci-dessous la réponse envoyée par le fabricant :</p>
                        <div style="background-color:#fff; border:1px solid #d8dde6; border-radius:6px; padding:12px 14px; margin:35px; font-style:italic; line-height:22px; color:#555454; ">
                        	<span style="font-size:22px; color:#dd170e; font-weight:bold; line-height:0;">“</span>
                        	<span> {!!$data->response_manufacturer!!} </span>
                        	<span style="font-size:22px; color:#dd170e; font-weight:bold; line-height:0;">”</span>
                        </div>
						<p>Malgré tous nos efforts et l’importance que nous accordons à la satisfaction de nos clients, le fabricant n’a pu apporter une réponse favorable à votre demande, les éléments envoyés ne permettant pas de mettre en évidence de manière tangible que le produit présente un défaut ne provenant ni d’une usure anormale ni d’un facteur externe.</p>
						<p>Vous avez la possibilité, si vous souhaitez avoir des informations complémentaires, de contacter directement la marque en vous reportant au formulaire de contact de leur site web.</p>
						<p>Nous restons à votre disposition pour toute autre question.</p>
						<br>
						<p>Cordialement,</p>
						<p>All Stars Motorsport</p>
					</font>
				</td>
				<td width="10" style="padding:7px 0">&nbsp;</td>
			</tr>
		</table>
	</td>
</tr>	