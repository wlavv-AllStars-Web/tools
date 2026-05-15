<tr>
	<td class="box" style="border:1px solid #D6D4D4;background-color:#f8f8f8;padding:7px 0">
		<table class="table" style="width:100%">
			<tr>
				<td width="10" style="padding:7px 0">&nbsp;</td>
				<td style="padding:7px 0; text-align: center;">
					<font size="3" face="Open-sans, sans-serif" color="#555454" style="color: #666;">
						<p style="border-bottom:1px solid #D6D4D4;margin:3px 0 7px;text-transform:uppercase;font-weight:500;font-size:18px;padding-bottom:10px;text-align:center;">CONFIRMATION DE COMPATIBILITÉ DU PRODUIT</p>
						<p>Bonjour {{$data->name}},</p>
                        <p>Après avoir envoyé vos éléments au fabricant dans le cadre de l’analyse de votre demande de garantie, ce dernier est parvenu à émettre une conclusion.</p>
                        <p>Vous pouvez consulter ci-dessous la réponse envoyée par le fabricant :</p>
						<fieldset style="background: #FFF !important;padding: 0;">
						    {!!$data->compatibilities!!}
						</fieldset>
                        <p>Malgré tous nos efforts et l’importance que nous accordons à la satisfaction de nos clients, notre fournisseur n’a pu apporter une réponse favorable à votre demande, les éléments envoyés ne permettant pas de mettre en évidence de manière tangible que le produit présente un défaut ne provenant ni d’une usure anormale ni d’un facteur externe.</p>
                        <p>Vous avez la possibilité, si vous souhaitez avoir des informations complémentaires, de contacter directement le fabricant en vous reportant au formulaire de contact du site de la marque.</p>
                        <p>Nos équipes restent à votre disposition en cas de besoin.</p>
						<br>
						<p>Cordialement,</p>
						<p> All Stars Motorsport </p>
					</font>
				</td>
				<td width="10" style="padding:7px 0">&nbsp;</td>
			</tr>
		</table>
	</td>
</tr>	