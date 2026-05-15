<!-- Introductory Text -->
<tr>
    <td style="padding: 30px 20px; color: #333;" align="center">
        <h2 style="color: #dd170e; text-align: center; margin-bottom: 15px;">Technical Verification Request</h2>
        <p style="font-size: 14px; line-height: 1.8; color: #333; margin: 10px 0;">
            We hope this message finds you well.
        </p>
        <p style="font-size: 14px; line-height: 1.8; color: #333; margin: 10px 0;">
            As part of our ongoing commitment to providing the best possible support to our customers, we are sharing below the detailed information regarding a situation reported involving one of your products.
        </p>
        <p style="font-size: 14px; line-height: 1.8; color: #333; margin: 10px 0;">
            We kindly ask for your <strong>technical verification</strong> and confirmation regarding the issue described below, so that we can properly advise our customer.  
            Should this be confirmed as a <strong>manufacturing defect or product fault</strong>, it should be treated as a <strong>warranty case</strong>.
        </p>
    </td>
</tr>

<!-- Customer Details -->
<tr>
    <td style="padding: 20px;" align="center">
        <h3 style="color: #dd170e; border-bottom: 1px solid #eee; padding-bottom: 5px;">Customer Information</h3>
        <table align="center" style="margin: 10px auto; font-size: 14px; color: #333; text-align: center;">
            <tr>
                <td style="padding: 5px 10px;text-align:right;width: 50%;"><strong>First Name:</strong></td>
                <td style="text-align:left;width: 50%;">{{ $data['customer_firstname'] }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px;text-align:right;"><strong>Last Name:</strong></td>
                <td style="text-align:left;">{{ $data['customer_lastname'] }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px;text-align:right;"><strong>Email:</strong></td>
                <td style="text-align:left;">{{ $data['customer_email'] }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px;text-align:right;"><strong>Address:</strong></td>
                <td style="text-align:left;">{!! $data['customer_address'] !!}</td>
            </tr>
        </table>
    </td>
</tr>


<!-- Product Details -->
<tr>
    <td style="padding: 20px;" align="center">
        <h3 style="color: #dd170e; border-bottom: 1px solid #eee; padding-bottom: 5px;">Product Information</h3>
        <table align="center" style="margin: 10px auto; font-size: 14px; color: #333; text-align: center;">
            <tr>
                <td style="padding: 5px 10px;text-align:right;width: 50%;"><strong>Reference:</strong></td>
                <td style="text-align:left;width: 50%;">{{ $data['product_reference'] }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px;text-align:right;"><strong>Product Name:</strong></td>
                <td style="text-align:left;">{{ $data['product_name'] }}</td>
            </tr>
        </table>
    </td>
</tr>

<!-- Vehicle Details -->
<tr>
    <td style="padding: 20px;" align="center">
        <h3 style="color: #dd170e; border-bottom: 1px solid #eee; padding-bottom: 5px;">Vehicle Information</h3>
        <table align="center" style="margin: 10px auto; font-size: 14px; color: #333; text-align: center;">
            <tr>
                <td style="padding: 5px 10px;text-align:right;"><strong>Brand:</strong></td>
                <td style="text-align:left;">{{ $data['car_brand'] }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px;text-align:right;"><strong>Model:</strong></td>
                <td style="text-align:left;">{{ $data['car_model'] }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px;text-align:right;"><strong>VIN:</strong></td>
                <td style="text-align:left;">{{ $data['car_vin'] }}</td>
            </tr>
        </table>
    </td>
</tr>

<!-- Reported Issue -->
<tr>
    <td style="padding: 20px;" align="center">
        <h3 style="color: #dd170e; border-bottom: 1px solid #eee; padding-bottom: 5px;">Reported Issue</h3>
        <p style="font-size: 14px; color: #333; line-height: 1.6; max-width: 500px; margin: 10px auto;">
            {{ $data['issue_description'] }}
        </p>
    </td>
</tr>

<!-- Photos -->
@if(!empty($data['issue_photos']))
<tr>
    <td style="padding: 20px;" align="center">
        <h3 style="color: #dd170e; border-bottom: 1px solid #eee; padding-bottom: 5px;">Issue Photos</h3>
        @foreach($data['issue_photos'] as $photo)
            @if(Str::endsWith($photo->url, ['.mp4', '.mov', '.webm']))
                <a href="{{ $photo->url }}" target="_blank">
                    <img src="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/upload/return_warranty/video.png"  alt="Video" style="max-width: 80px; border-radius: 4px;margin: 20px;">
                </a>
            @else
                <img src="{{ $photo->url }}" alt="Issue photo" style="max-width:120px;border-radius:4px;margin: 5px;">
            @endif
        @endforeach
    </td>
</tr>
@endif

<!-- Closing -->
<tr>
    <td style="padding: 30px 20px; color: #333;" align="center">
        <p style="font-size: 14px; line-height: 1.8; max-width: 500px; margin: 10px auto;">
            We appreciate your attention and collaboration on this matter.<br>
            Please provide your technical feedback at your earliest convenience.
        </p>
        <p style="font-size: 14px; line-height: 1.8; margin-top: 20px;">
            Kind regards,<br>
            <strong>All Stars Motorsport</strong><br>
            <a href="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}" style="color: #dd170e; text-decoration: none;">{{ config('allstars.stores.ASM.domain') }}</a>
        </p>
    </td>
</tr>
