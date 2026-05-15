<tr>
	<td style="background-color:#FFF;padding:15px; text-align: center;border-bottom:4px solid #dd170e;">
    	<font size="3" face="Open-sans, sans-serif" color="#555454" style="color: #666;">
            <p style="font-weight:500;font-size:20px;text-transform:uppercase;line-height:20px;padding: 0;margin: 0;">Garantie – demande d’éléments complémentaires</p>
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
						<p>Après avoir envoyé vos éléments au fabricant dans le cadre de l’analyse de votre demande de garantie, ce dernier n’est pas parvenu à émettre une conclusion pour le moment en raison d’informations manquantes.</p>
						<p>Afin de pouvoir donner suite à vote demande, nous aurions besoin des éléments complémentaires suivants :</p>
                        <div style="background-color:#fff; border:1px solid #d8dde6; border-radius:6px; padding:12px 14px; margin:35px; font-style:italic; line-height:22px; color:#555454; ">
                        	<span style="font-size:22px; color:#dd170e; font-weight:bold; line-height:0;">“</span>
                        	<span> {!! $data->supplier_message !!} </span>
                        	<span style="font-size:22px; color:#dd170e; font-weight:bold; line-height:0;">”</span>
                        </div>
						<p>Merci de joindre ces éléments à votre demande de garantie depuis votre espace client, en les ajoutant dans le champ « Informations complémentaires » en cliquant sur le lien ci-dessous :</p>
                        <p style="text-align:center; margin:25px 0;">
                        	<a href="{!! $data->href !!}" style=" display:inline-block; min-width:220px; padding:12px 20px; background-color:#dd170e;border:1px solid #b0120a; border-radius:6px; color:#ffffff; font-size:14px; font-weight:600; text-decoration:none; text-transform:uppercase; letter-spacing:0.5px;">
                        		DEMANDE DE GARANTIE
                        	</a>
                        </p>
						<p>Dès que nous disposerons de ces informations, nous les ferons suivre immédiatement au fabricant afin de pouvoir vous apporter une réponse dans les plus brefs délais.</p>
						<p>Dans cette attente, nous vous remercions pour votre collaboration.</p>
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