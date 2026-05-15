<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
		<title>Message de All Stars Motorsport</title>
		<style>
			body {
			    -webkit-text-size-adjust: none;
    			background-color: #fff;
    			width: 650px;
    			font-family: 'Verdana', 'Open Sans', sans-serif;
    			color: #555454;
    			font-size: 14px;
    			line-height: 18px;
    			margin: auto;
			}
			.table-mail {
    			width: 100%;
    			margin-top: 10px;
    			box-shadow: 0 0 5px #afafaf;
			}
			.space {
    			width: 10px;
			}
			.box {
    			border: 1px solid #D6D4D4;
    			background-color: #f8f8f8;
    			padding: 20px;
    			text-align: center;
			}
			.footer-block a {
    			padding: 0 10px;
    			text-decoration: none;
    			color: #333;
    			text-transform: uppercase;
    			display: inline-block;
			}
			span.title {
    			font-weight: 500;
    			font-size: 18px;
    			text-transform: uppercase;
    			line-height: 33px;
			}
			@media only screen and (max-width: 500px) {
    			body {
        			width: 100% !important;
        			margin: 0 !important;
        			padding: 0 !important;
    			}
    			.table-mail {
        			width: 100% !important;
        			margin: 0 auto !important;
    			}
    			.space {
        			display: none !important;
    			}
    			.box {
        			padding: 15px !important;
    			}
    			span.title {
        			font-size: 18px !important;
        			line-height: 28px !important;
    			}
			}
		</style>
	</head>
	<body>
		<table class="table-mail" align="center" cellpadding="0" cellspacing="0" style="width:100%; max-width:650px;">
			<tr>
				<td class="space">&nbsp;</td>
				<td align="center">
					<!-- Logo -->
					<a href="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}" style="display: block; margin: 20px 0;">
					<img src="{{ config('allstars.stores.ASM.logo_url') }}" alt="All Stars Motorsport" style="max-width:200px; height:auto;">
					</a>
					
					<p style="border-bottom: 4px solid #dd170e;  margin:3px 0 15px; text-transform:uppercase; font-weight:500; font-size:18px; padding-bottom:10px;">
						Votre avis est très important pour nous !
					</p>

					<div class="box">
    					
    					<p>Bonjour {{$data['firstname']}} {{$data['lastname']}},</p>
    					
						<p>Partagez votre expérience et éventuellement des photos / vidéos de votre montage! </p>
						<p style="margin:15px 0;">
							<a href="https://www.yourvoicehub.com?id_lang=5" target="_blank">
							    <img src="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/img/trustpilot.png?t=4" alt="Leave your review on Trustpilot" style="max-width:220px; height:auto; border:0;">
							</a>
						</p>
						<p>Vos commentaires et images contribuent à notre amélioration.</p>
						<p style="margin:25px 0;">
							<a href="https://www.yourvoicehub.com?id_lang=5" target="_blank">
							    <img src="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/img/image.png?t=4" alt="All Stars Motorsport" style="max-width:180px; height:auto; border:0;">
							</a>
						</p>
						<p>Merci d'avance pour votre retour.</p>
						<p>Cordialement,<br>
							All Stars Motorsport
						</p>
					</div>	

					<table class="footer-block" cellpadding="0" cellspacing="0" style="margin-top:20px; border-top:4px solid #dd170e; padding-top:15px; width:100%;">
						<tr>
							<td style="text-align:center;" class="social-icons">
							    <img alt="Social medias" src="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/img/app_icons/full_icons.png?t=2" border="0" style="display:inline-block;width: 300px  !important;"  />
						    </td>
						</tr>
					</table>
				</td>
				<td class="space">&nbsp;</td>
			</tr>
		</table>
	</body>
</html>
