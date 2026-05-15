<tr>
	<td style="background-color:#FFF;padding:15px; text-align: center;border-bottom:4px solid #dd170e;">
    	<font size="3" face="Open-sans, sans-serif" color="#555454" style="color: #666;">
            <p style="font-weight:500;font-size:20px;text-transform:uppercase;line-height:20px;padding: 0;margin: 0;">Retour - demande enregistrée</p>
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
						<p>Votre demande de retour a bien été enregistrée.</p>
						<p>Pour rappel, le retour d'un article est possible dès lors que la demande de retour est effectuée sous un délai de 14 jours après sa livraison.</p>
						<p><b>Les produits retournés doivent être en parfait état (pouvant être considérés comme neufs), complets et dans leur emballage d'origine en excellent état.</b></p>
						<p>A réception, les articles seront vérifiés par nos équipes, et s'ils répondent à nos conditions de retour, le remboursement sera automatiquement effectué via le mode de paiement initialement utilisé. Nous attirons votre attention sur le fait que les frais de port engagés pour l'envoi de la commande ne sont pas remboursés.</p>
						<p>Tout article retourné incomplet, endommagé ou n'étant pas dans l'état dans lequel il avait été initialement envoyé, ne donnera lieu à aucun remboursement, il sera alors retourné à vos frais.</p>
						<p>Si l'emballage du produit n'est pas en parfait état, il sera déduit du montant remboursé des frais de reconditionnement, nous vous recommandons donc de vous assurer que l'article est bien éligible à un retour avant l’expédition dans nos locaux.</p>
						<p>Vous pouvez consulter nos conditions de retour et vidéos d'informations via les liens suivants :</p>
						<p>
							<a href="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/fr/content/3-conditions-d-utilization">Conditions d'utilisation</a><br>
							<a href="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/fr/content/370-service-client-faq">FAQ & Service client</a>
						</p>
						<p>Vous serez informé par email à chaque fois que le statut de votre demande sera actualisé. Vous pouvez également retrouver toutes les informations concernant votre demande depuis votre espace client.</p>
						<p>Nous restons à votre disposition pour toute autre question.</p>
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