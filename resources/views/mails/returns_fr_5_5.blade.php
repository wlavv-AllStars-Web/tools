<tr>
	<td style="background-color:#FFF;padding:15px; text-align: center;border-bottom:4px solid #dd170e;">
    	<font size="3" face="Open-sans, sans-serif" color="#555454" style="color: #666;">
            <p style="font-weight:500;font-size:20px;text-transform:uppercase;line-height:20px;padding: 0;margin: 0;">Retour – non approuvé</p>
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
						<p>Nous avons bien réceptionné votre retour et, après vérification, notre département qualité n’a pas été en mesure de valider son acceptation. En effet, le produit retourné doit être dans le même état que lors de son expédition de nos entrepôts, comme l’exigent nos conditions générales de vente, or ce n’est pas le cas. Vous pourrez trouver en pièces jointes des photos détaillées du produit dans l’état tel que nous l’avons réceptionné. Dans ces conditions, un remboursement n’est pas possible car ce produit ne peut en aucun cas être remis en stock, ce dernier ne répondant plus aux critères d’un produit neuf.</p>
						<p>Si vous souhaitez que ce produit vous soit renvoyé, son expédition se fera à vos frais.</p>

                        <div style="background-color:#fff; border:1px solid #d8dde6; border-radius:6px; padding:12px 14px; margin:35px; font-style:italic; line-height:22px; color:#555454; ">
    						<p>Frais de réexpédition: {{$data->value_to_pay}} €</p>
    						<p>Lien de paiement: <a  href="{{$data->link_for_payment}}"> Cliquez pour continuer</a> </p>
    						@if(strlen($data->link_to_pictures) > 0)
    						<p>Lien vers l'image: <a  href="{{$data->link_to_pictures}}"> Cliquez pour voir les images </a> </p>
    						@endif
                        </div>
                        
						<p>Vous pouvez retrouver tous les détails sur nos conditions de retour en cliquant sur le lien ci-dessous <br>( paragraphe 7 ) :</p>
						<p>
							<a href="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/fr/content/3-conditions-generales-de-ventes">Conditions d'utilisation</a><br>
						</p>
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