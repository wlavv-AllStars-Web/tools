<tr>
	<td style="background-color:#FFF;padding:15px; text-align: center;border-bottom:4px solid #dd170e;">
    	<font size="3" face="Open-sans, sans-serif" color="#555454" style="color: #666;">
            <p style="font-weight:500;font-size:20px;text-transform:uppercase;line-height:20px;padding: 0;margin: 0;">Retour – approuvé</p>
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
                        <p>Nous avons bien réceptionné votre retour et, après vérification, notre département qualité a été en mesure de valider son acceptation. Notre département financier va donc procéder sous 48h-72h (jours ouvrés) à son remboursement via le mode de paiement utilisé lors de votre commande.</p>
                        <p>Notez que, conformément à nos conditions générales de vente, les frais d’envoi engagés dans l’expédition du colis ne sont pas remboursés car restent à la charge du client dans le cadre d’un changement d’avis.</p>
                        <p>Vous pouvez retrouver tous les détails sur nos conditions de retour en cliquant sur le lien ci-dessous <br>( paragraphe 7 ) :</p>
                        <p><a href="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/fr/content/3-conditions-d-utilization">Conditions d'utilisation</a></p>
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