<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
@php
    $storeCode = $requestItem->store_code ?: 'ASM';
    $storeName = $requestItem->storeName();
    $storeBaseUrl = \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl($storeCode);
    $resourcesBaseUrl = env('RESOURCES_PRODUCTION') ?: config('allstars.services.resources.base_url');
    if (str_contains((string) $resourcesBaseUrl, '.local')) {
        $resourcesBaseUrl = 'https://resources.allstars-group.com';
    }
    if (!preg_match('#^https?://#i', (string) $resourcesBaseUrl)) {
        $resourcesBaseUrl = 'https://' . ltrim((string) $resourcesBaseUrl, '/');
    }
    $emailAssetBaseUrl = preg_replace('#^http://#i', 'https://', rtrim((string) $resourcesBaseUrl, '/'));
    $logoUrl = $emailAssetBaseUrl . '/logos/' . strtolower($storeCode) . '.png';
    $brandColor = config('allstars.payment_links.stores.' . $storeCode . '.payment_link_color', '#dd170e');
    $socialLinks = (array) config('allstars.payment_links.stores.' . $storeCode . '.social_links', []);
    $footerSocialImage = config('allstars.payment_links.stores.' . $storeCode . '.footer_social_image');
    $storeSocialIcons = (array) config('allstars.payment_links.stores.' . $storeCode . '.social_icons', []);
    $socialIcons = [
        'facebook' => ['image' => 'facebook_mail.jpg', 'label' => 'Facebook'],
        'flickr' => ['image' => 'flickr_mail.jpg', 'label' => 'Flickr'],
        'instagram' => ['image' => 'insta_mail.jpg', 'label' => 'Instagram'],
        'youtube' => ['image' => 'youtube_mail.jpg', 'label' => 'Youtube'],
    ];
@endphp
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
        <title>Payment link - {{ $storeName }}</title>
        <style>
            body {
                -webkit-text-size-adjust: none;
                background-color: #fff;
                width: 650px;
                font-family: Verdana, Arial, sans-serif;
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
            .button {
                background-color: {{ $brandColor }};
                color: #ffffff !important;
                display: inline-block;
                font-weight: 700;
                padding: 12px 28px;
                text-decoration: none;
                text-transform: uppercase;
            }
            .summary {
                background-color: #ffffff;
                border: 1px solid #D6D4D4;
                margin: 20px auto;
                width: 85%;
            }
            .summary td {
                border-bottom: 1px solid #D6D4D4;
                padding: 10px;
            }
            .summary tr:last-child td {
                border-bottom: 0;
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
                .summary {
                    width: 100% !important;
                }
            }
        </style>
    </head>
    <body>
        <table class="table-mail" align="center" cellpadding="0" cellspacing="0" style="width:100%; max-width:650px;">
            <tr>
                <td class="space">&nbsp;</td>
                <td align="center">
                    <a href="{{ $storeBaseUrl }}" style="display: block; margin: 20px 0;">
                        <img src="{{ $logoUrl }}" alt="{{ $storeName }}" style="max-width:200px; height:auto; border:0;">
                    </a>

                    <p style="border-bottom: 4px solid {{ $brandColor }}; margin:3px 0 15px; text-transform:uppercase; font-weight:500; font-size:18px; padding-bottom:10px;">
                        Secure payment link
                    </p>

                    <div class="box">
                        <p>Hello,</p>
                        <p>You can complete the payment for your order using the secure payment link below.</p>

                        <table class="summary" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="left"><strong>Store</strong></td>
                                <td align="right">{{ $storeName }}</td>
                            </tr>
                            <tr>
                                <td align="left"><strong>Order</strong></td>
                                <td align="right">{{ $requestItem->order_id }}</td>
                            </tr>
                            <tr>
                                <td align="left"><strong>Description</strong></td>
                                <td align="right">{{ $requestItem->description }}</td>
                            </tr>
                            <tr>
                                <td align="left"><strong>Amount</strong></td>
                                <td align="right">{{ number_format((float) $requestItem->amount, 2, '.', ' ') }} {{ $requestItem->currency }}</td>
                            </tr>
                        </table>

                        <p style="margin:25px 0;">
                            <a class="button" href="{{ $paymentUrl }}" target="_blank">Pay now</a>
                        </p>

                        <p style="font-size:12px; color:#777; margin-bottom:0;">
                            If the button does not open, copy and paste this link into your browser:<br>
                            <a href="{{ $paymentUrl }}" style="color:{{ $brandColor }}; word-break:break-all;">{{ $paymentUrl }}</a>
                        </p>
                    </div>

                    <table cellpadding="0" cellspacing="0" style="margin-top:20px; border-top:4px solid {{ $brandColor }}; padding-top:15px; width:100%;">
                        @if($footerSocialImage)
                            <tr>
                                <td style="text-align:center; padding:8px 0 12px;">
                                    <img alt="{{ $storeName }} social media" src="{{ $emailAssetBaseUrl }}/logos/{{ $footerSocialImage }}" border="0" style="border:0; display:inline-block; max-width:260px; height:auto;">
                                </td>
                            </tr>
                        @elseif(count($socialLinks) > 0)
                            <tr>
                                <td style="text-align:center; padding:8px 0 12px;">
                                    @foreach($socialLinks as $network => $url)
                                        @if(isset($socialIcons[$network]))
                                            <a href="{{ $url }}" target="_blank" style="display:inline-block; margin:0 5px; text-decoration:none;">
                                                <img alt="{{ $socialIcons[$network]['label'] }} {{ $storeName }}" src="{{ $emailAssetBaseUrl }}/logos/{{ $storeSocialIcons[$network] ?? $socialIcons[$network]['image'] }}" width="40" height="40" border="0" style="border:0; display:inline-block;">
                                            </a>
                                        @endif
                                    @endforeach
                                </td>
                            </tr>
                        @endif
                    </table>
                </td>
                <td class="space">&nbsp;</td>
            </tr>
        </table>
    </body>
</html>
