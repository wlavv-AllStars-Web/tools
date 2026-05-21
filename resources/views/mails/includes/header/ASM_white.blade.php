<html>
    <head>
       <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
       <title>{{$data['subject']}}</title>
    </head>
    <body>
        <table class="table table-mail" align="center" style="max-width: 600px;">
            <tbody>
                <tr>
                <td style="background-color: #FFF;padding: 20px;" align="center">
                	<a title="All Stars Motorsport" href="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/en/" style="color:#337ff1" target="_blank">
                        <img src="{{ rtrim(config('allstars.services.webtools.base_url'), '/') }}/img/email_logo_asm.png" alt="All Stars Motorsport" style="width: 200px;"> 
                	</a>
                </td>
            </tr>
