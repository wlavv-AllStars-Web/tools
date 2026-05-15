<!-- Texto introductorio -->
<tr>
    <td style="padding: 30px 20px; color: #333;" align="center">
        <h2 style="color: #dd170e; text-align: center; margin-bottom: 15px;">Solicitud de Verificación Técnica</h2>
        <p style="font-size: 14px; line-height: 1.8; color: #333; margin: 10px 0;">
            Esperamos que este mensaje le encuentre bien.
        </p>
        <p style="font-size: 14px; line-height: 1.8; color: #333; margin: 10px 0;">
            Como parte de nuestro compromiso continuo de ofrecer el mejor soporte posible a nuestros clientes, compartimos a continuación la información detallada sobre una situación reportada relacionada con uno de sus productos.
        </p>
        <p style="font-size: 14px; line-height: 1.8; color: #333; margin: 10px 0;">
            Le solicitamos amablemente su <strong>verificación técnica</strong> y confirmación respecto al problema descrito a continuación, para que podamos orientar adecuadamente a nuestro cliente.  
            En caso de confirmarse un <strong>defecto de fabricación o fallo del producto</strong>, deberá tratarse como un <strong>caso en garantía</strong>.
        </p>
    </td>
</tr>

<!-- Datos del cliente -->
<tr>
    <td style="padding: 20px;" align="center">
        <h3 style="color: #dd170e; border-bottom: 1px solid #eee; padding-bottom: 5px;">Información del Cliente</h3>
        <table align="center" style="margin: 10px auto; font-size: 14px; color: #333; text-align: center;">
            <tr>
                <td style="padding: 5px 10px; text-align:right; width: 50%;"><strong>Nombre:</strong></td>
                <td style="text-align:left; width: 50%;">{{ $data['customer_firstname'] }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px; text-align:right;"><strong>Apellidos:</strong></td>
                <td style="text-align:left;">{{ $data['customer_lastname'] }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px; text-align:right;"><strong>Email:</strong></td>
                <td style="text-align:left;">{{ $data['customer_email'] }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px; text-align:right;"><strong>Dirección:</strong></td>
                <td style="text-align:left;">{!! $data['customer_address'] !!}</td>
            </tr>
        </table>
    </td>
</tr>

<!-- Datos del producto -->
<tr>
    <td style="padding: 20px;" align="center">
        <h3 style="color: #dd170e; border-bottom: 1px solid #eee; padding-bottom: 5px;">Información del Producto</h3>
        <table align="center" style="margin: 10px auto; font-size: 14px; color: #333; text-align: center;">
            <tr>
                <td style="padding: 5px 10px; text-align:right; width: 50%;"><strong>Referencia:</strong></td>
                <td style="text-align:left; width: 50%;">{{ $data['product_reference'] }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px; text-align:right;"><strong>Nombre del Producto:</strong></td>
                <td style="text-align:left;">{{ $data['product_name'] }}</td>
            </tr>
        </table>
    </td>
</tr>

<!-- Datos del vehículo -->
<tr>
    <td style="padding: 20px;" align="center">
        <h3 style="color: #dd170e; border-bottom: 1px solid #eee; padding-bottom: 5px;">Información del Vehículo</h3>
        <table align="center" style="margin: 10px auto; font-size: 14px; color: #333; text-align: center;">
            <tr>
                <td style="padding: 5px 10px; text-align:right;"><strong>Marca:</strong></td>
                <td style="text-align:left;">{{ $data['car_brand'] }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px; text-align:right;"><strong>Modelo:</strong></td>
                <td style="text-align:left;">{{ $data['car_model'] }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px; text-align:right;"><strong>VIN:</strong></td>
                <td style="text-align:left;">{{ $data['car_vin'] }}</td>
            </tr>
        </table>
    </td>
</tr>

<!-- Problema reportado -->
<tr>
    <td style="padding: 20px;" align="center">
        <h3 style="color: #dd170e; border-bottom: 1px solid #eee; padding-bottom: 5px;">Problema Reportado</h3>
        <p style="font-size: 14px; color: #333; line-height: 1.6; max-width: 500px; margin: 10px auto;">
            {{ $data['issue_description'] }}
        </p>
    </td>
</tr>

<!-- Fotos -->
@if(!empty($data['issue_photos']))
<tr>
    <td style="padding: 20px;" align="center">
        <h3 style="color: #dd170e; border-bottom: 1px solid #eee; padding-bottom: 5px;">Fotos del Problema</h3>
        <table align="center" style="width: 100%; text-align: center; margin-top: 10px;">
            <tr>
                @foreach($data['issue_photos'] as $photo)
                    <td style="padding: 10px;" align="center">
                        <img src="{{ $photo }}" alt="Foto del problema" style="max-width: 150px; border: 1px solid #ccc; border-radius: 4px;">
                    </td>
                @endforeach
            </tr>
        </table>
    </td>
</tr>
@endif

<!-- Cierre -->
<tr>
    <td style="padding: 30px 20px; color: #333;" align="center">
        <p style="font-size: 14px; line-height: 1.8; max-width: 500px; margin: 10px auto;">
            Agradecemos su atención y colaboración en este asunto.<br>
            Por favor, proporcione su respuesta técnica a la mayor brevedad posible.
        </p>
        <p style="font-size: 14px; line-height: 1.8; margin-top: 20px;">
            Atentamente,<br>
            <strong>All Stars Motorsport</strong><br>
            <a href="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}" style="color: #dd170e; text-decoration: none;">{{ config('allstars.stores.ASM.domain') }}</a>
        </p>
    </td>
</tr>
