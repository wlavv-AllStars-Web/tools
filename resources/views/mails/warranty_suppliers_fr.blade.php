<!-- Texte d’introduction -->
<tr>
    <td style="padding: 30px 20px; color: #333;" align="center">
        <h2 style="color: #dd170e; text-align: center; margin-bottom: 15px;">Demande de vérification technique</h2>
        <p style="font-size: 14px; line-height: 1.8; color: #333; margin: 10px 0;">
            Nous espérons que ce message vous trouve en bonne santé.
        </p>
        <p style="font-size: 14px; line-height: 1.8; color: #333; margin: 10px 0;">
            Dans le cadre de notre engagement à offrir le meilleur service possible à nos clients, nous partageons ci-dessous les informations détaillées concernant un problème signalé impliquant l’un de vos produits.
        </p>
        <p style="font-size: 14px; line-height: 1.8; color: #333; margin: 10px 0;">
            Nous vous prions de bien vouloir procéder à une <strong>vérification technique</strong> et de nous confirmer la situation décrite ci-dessous, afin que nous puissions informer correctement notre client.  
            Si cela est confirmé comme un <strong>défaut de fabrication</strong> ou un <strong>problème de produit</strong>, le cas devra être traité comme une <strong>demande de garantie</strong>.
        </p>
    </td>
</tr>

<!-- Informations du client -->
<tr>
    <td style="padding: 20px;" align="center">
        <h3 style="color: #dd170e; border-bottom: 1px solid #eee; padding-bottom: 5px;">Informations du client</h3>
        <table align="center" style="margin: 10px auto; font-size: 14px; color: #333; text-align: center;">
            <tr>
                <td style="padding: 5px 10px;text-align:right;width: 50%;"><strong>Prénom&nbsp;:</strong></td>
                <td style="text-align:left;width: 50%;">{{ $data['customer_firstname'] }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px;text-align:right;"><strong>Nom&nbsp;:</strong></td>
                <td style="text-align:left;">{{ $data['customer_lastname'] }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px;text-align:right;"><strong>Email&nbsp;:</strong></td>
                <td style="text-align:left;">{{ $data['customer_email'] }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px;text-align:right;"><strong>Adresse&nbsp;:</strong></td>
                <td style="text-align:left;">{!! $data['customer_address'] !!}</td>
            </tr>
        </table>
    </td>
</tr>

<!-- Informations sur le produit -->
<tr>
    <td style="padding: 20px;" align="center">
        <h3 style="color: #dd170e; border-bottom: 1px solid #eee; padding-bottom: 5px;">Informations sur le produit</h3>
        <table align="center" style="margin: 10px auto; font-size: 14px; color: #333; text-align: center;">
            <tr>
                <td style="padding: 5px 10px;text-align:right;width: 50%;"><strong>Référence&nbsp;:</strong></td>
                <td style="text-align:left;width: 50%;">{{ $data['product_reference'] }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px;text-align:right;"><strong>Nom du produit&nbsp;:</strong></td>
                <td style="text-align:left;">{{ $data['product_name'] }}</td>
            </tr>
        </table>
    </td>
</tr>

<!-- Informations sur le véhicule -->
<tr>
    <td style="padding: 20px;" align="center">
        <h3 style="color: #dd170e; border-bottom: 1px solid #eee; padding-bottom: 5px;">Informations sur le véhicule</h3>
        <table align="center" style="margin: 10px auto; font-size: 14px; color: #333; text-align: center;">
            <tr>
                <td style="padding: 5px 10px;text-align:right;"><strong>Marque&nbsp;:</strong></td>
                <td style="text-align:left;">{{ $data['car_brand'] }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px;text-align:right;"><strong>Modèle&nbsp;:</strong></td>
                <td style="text-align:left;">{{ $data['car_model'] }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px;text-align:right;"><strong>VIN&nbsp;:</strong></td>
                <td style="text-align:left;">{{ $data['car_vin'] }}</td>
            </tr>
        </table>
    </td>
</tr>

<!-- Problème signalé -->
<tr>
    <td style="padding: 20px;" align="center">
        <h3 style="color: #dd170e; border-bottom: 1px solid #eee; padding-bottom: 5px;">Problème signalé</h3>
        <p style="font-size: 14px; color: #333; line-height: 1.6; max-width: 500px; margin: 10px auto;">
            {{ $data['issue_description'] }}
        </p>
    </td>
</tr>

<!-- Photos du problème -->
@if(!empty($data['issue_photos']))
<tr>
    <td style="padding: 20px;" align="center">
        <h3 style="color: #dd170e; border-bottom: 1px solid #eee; padding-bottom: 5px;">Photos du problème</h3>
        <table align="center" style="width: 100%; text-align: center; margin-top: 10px;">
            <tr>
                @foreach($data['issue_photos'] as $photo)
                    <td style="padding: 10px;" align="center">
                        <img src="{{ $photo }}" alt="Photo du problème" style="max-width: 150px; border: 1px solid #ccc; border-radius: 4px;">
                    </td>
                @endforeach
            </tr>
        </table>
    </td>
</tr>
@endif

<!-- Clôture -->
<tr>
    <td style="padding: 30px 20px; color: #333;" align="center">
        <p style="font-size: 14px; line-height: 1.8; max-width: 500px; margin: 10px auto;">
            Nous vous remercions pour votre attention et votre collaboration concernant ce dossier.<br>
            Merci de nous faire parvenir votre retour technique dès que possible.
        </p>
        <p style="font-size: 14px; line-height: 1.8; margin-top: 20px;">
            Cordialement,<br>
            <strong>All Stars Motorsport</strong><br>
            <a href="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}" style="color: #dd170e; text-decoration: none;">{{ config('allstars.stores.ASM.domain') }}</a>
        </p>
    </td>
</tr>
