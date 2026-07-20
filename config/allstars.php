<?php

$normalizeBaseUrl = static function (?string $url, string $fallback): string {
    $url = trim((string) ($url ?: $fallback));

    if (!preg_match('#^https?://#i', $url)) {
        $scheme = str_ends_with($url, '.local') || str_starts_with($url, 'localhost')
            ? 'http://'
            : 'https://';

        $url = $scheme . $url;
    }

    return rtrim($url, '/');
};

$hostFromUrl = static function (string $url): string {
    return (string) parse_url($url, PHP_URL_HOST);
};

$asmBaseUrl = $normalizeBaseUrl(env('ASM_PRODUCTION'), 'https://asm.allstars-group.com');
$asdBaseUrl = $normalizeBaseUrl(env('ASD_PRODUCTION'), 'https://asd.allstars-group.com');
$webtoolsBaseUrl = $normalizeBaseUrl(env('WEBTOOLS_PRODUCTION'), env('APP_URL', 'http://localhost'));
$resourcesBaseUrl = $normalizeBaseUrl(env('RESOURCES_PRODUCTION'), 'https://resources.allstars-group.com');

$asmDomain = env('ASM_DOMAIN', $hostFromUrl($asmBaseUrl));
$asdDomain = env('ASD_DOMAIN', $hostFromUrl($asdBaseUrl));

return [
    'stores' => [
        'ASM' => [
            'id_shop' => (int) env('ASM_SHOP_ID', 2),
            'base_url' => $asmBaseUrl,
            'domain' => $asmDomain,
            'email_domain' => env('ASM_EMAIL_DOMAIN', 'all-stars-motorsport.com'),
            'compat_store_id' => (int) env('ASM_COMPATS_STORE_ID', 2),
            'admin_folder' => env('PRESTASHOP_ASM_ADMIN_FOLDER', 'admineuromus1'),
            'logo_path' => 'uploads/logos/asm.png',
            'logo_url' => $webtoolsBaseUrl . '/uploads/logos/asm.png',
        ],

        'ASD' => [
            'id_shop' => (int) env('ASD_SHOP_ID', 3),
            'base_url' => $asdBaseUrl,
            'domain' => $asdDomain,
            'email_domain' => env('ASD_EMAIL_DOMAIN', 'all-stars-distribution.com'),
            'compat_store_id' => (int) env('ASD_COMPATS_STORE_ID', 3),
            'admin_folder' => env('PRESTASHOP_ASD_ADMIN_FOLDER', 'admineuromus1'),
            'logo_path' => 'uploads/logos/asd.png',
            'logo_url' => $webtoolsBaseUrl . '/uploads/logos/asd.png',
        ],
    ],

    'services' => [
        'webtools' => [
            'base_url' => $webtoolsBaseUrl,
            'domain' => $hostFromUrl($webtoolsBaseUrl),
        ],

        'resources' => [
            'base_url' => $resourcesBaseUrl,
            'domain' => $hostFromUrl($resourcesBaseUrl),
            'homepage_asd_path' => env('RESOURCES_ASD_HOMEPAGE_PATH', 'asd/homepage'),
            'homepage_asd_storage_path' => env('RESOURCES_ASD_HOMEPAGE_STORAGE_PATH', 'uploads/asd/homepage'),
        ],
    ],

    'api' => [
        'tokens' => [
            'compats' => env('ALLSTARS_COMPATS_API_TOKEN'),
            'compats_backoffice' => env('ALLSTARS_COMPATS_BACKOFFICE_TOKEN'),
            'my_garage' => env('ALLSTARS_MY_GARAGE_API_TOKEN', env('ALLSTARS_COMPATS_API_TOKEN')),
            'vat_validation' => env('ALLSTARS_VAT_VALIDATION_TOKEN', env('TOOLS_KEY')),
            'asd_pricing' => env('ASD_PRICING_TOKEN'),
            'purchase_price_sync' => env('ALLSTARS_PURCHASE_PRICE_SYNC_TOKEN'),
            'asd_alerts' => env('ALLSTARS_ASD_ALERTS_API_TOKEN', env('ASD_ALERT_KEY')),
        ],
    ],

    'auto_orders' => [
        'paid_order_states' => array_map(
            'intval',
            array_filter(explode(',', env('AUTO_ORDERS_PAID_ORDER_STATES', '2,3,4,5,15,16,28')))
        ),
        'import_from' => env('AUTO_ORDERS_IMPORT_FROM', '2026-05-12 00:00:00'),
        'shop_codes' => [
            (int) env('ASM_SHOP_ID', 2) => 'ASM',
            (int) env('ASD_SHOP_ID', 3) => 'ASD',
        ],
    ],

    'emails' => [
        'suppliers' => env('ALLSTARS_SUPPLIERS_EMAIL', 'suppliers@' . env('ASD_EMAIL_DOMAIN', 'all-stars-distribution.com')),
        'purchase' => env('ALLSTARS_PURCHASE_EMAIL', 'purchase@' . env('ASD_EMAIL_DOMAIN', 'all-stars-distribution.com')),
        'purchase_price_sync_not_found_to' => env('ALLSTARS_PURCHASE_PRICE_SYNC_NOT_FOUND_TO', 'bruno.fernandes.asm@gmail.com'),
        'sales' => [
            'ASM' => [
                'address' => env('ALLSTARS_ASM_SALES_EMAIL', 'sales@' . env('ASM_EMAIL_DOMAIN', 'all-stars-motorsport.com')),
                'name' => env('ALLSTARS_ASM_SALES_FROM_NAME', 'All Stars Motorsport'),
            ],
            'ASD' => [
                'address' => env('ALLSTARS_ASD_SALES_EMAIL', 'sales@' . env('ASD_EMAIL_DOMAIN', 'all-stars-distribution.com')),
                'name' => env('ALLSTARS_ASD_SALES_FROM_NAME', 'All Stars Distribution'),
            ],
        ],
        'excluded_domains' => [
            env('ASM_EMAIL_DOMAIN', 'all-stars-motorsport.com'),
            env('ASD_EMAIL_DOMAIN', 'all-stars-distribution.com'),
        ],
    ],

    'mailers' => [
        'asm_sales' => [
            'host' => env('ALLSTARS_ASM_SALES_SMTP_HOST', env('MAIL_HOST')),
            'port' => env('ALLSTARS_ASM_SALES_SMTP_PORT', env('MAIL_PORT', 587)),
            'encryption' => env('ALLSTARS_ASM_SALES_SMTP_ENCRYPTION', env('MAIL_ENCRYPTION', 'tls')),
            'username' => env('ALLSTARS_ASM_SALES_SMTP_USERNAME', env('ALLSTARS_ASM_SALES_EMAIL', 'sales@' . env('ASM_EMAIL_DOMAIN', 'all-stars-motorsport.com'))),
            'password' => env('ALLSTARS_ASM_SALES_SMTP_PASSWORD'),
            'from_address' => env('ALLSTARS_ASM_SALES_EMAIL', 'sales@' . env('ASM_EMAIL_DOMAIN', 'all-stars-motorsport.com')),
            'from_name' => env('ALLSTARS_ASM_SALES_FROM_NAME', 'All Stars Motorsport'),
        ],

        'asd_sales' => [
            'host' => env('ALLSTARS_ASD_SALES_SMTP_HOST', env('MAIL_HOST')),
            'port' => env('ALLSTARS_ASD_SALES_SMTP_PORT', env('MAIL_PORT', 587)),
            'encryption' => env('ALLSTARS_ASD_SALES_SMTP_ENCRYPTION', env('MAIL_ENCRYPTION', 'tls')),
            'username' => env('ALLSTARS_ASD_SALES_SMTP_USERNAME', env('ALLSTARS_ASD_SALES_EMAIL', 'sales@' . env('ASD_EMAIL_DOMAIN', 'all-stars-distribution.com'))),
            'password' => env('ALLSTARS_ASD_SALES_SMTP_PASSWORD'),
            'from_address' => env('ALLSTARS_ASD_SALES_EMAIL', 'sales@' . env('ASD_EMAIL_DOMAIN', 'all-stars-distribution.com')),
            'from_name' => env('ALLSTARS_ASD_SALES_FROM_NAME', 'All Stars Distribution'),
        ],

        'asm_media' => [
            'host' => env('ALLSTARS_ASM_MEDIA_SMTP_HOST', env('MAIL_HOST')),
            'port' => env('ALLSTARS_ASM_MEDIA_SMTP_PORT', env('MAIL_PORT', 587)),
            'encryption' => env('ALLSTARS_ASM_MEDIA_SMTP_ENCRYPTION', env('MAIL_ENCRYPTION', 'tls')),
            'username' => env('ALLSTARS_ASM_MEDIA_SMTP_USERNAME', 'media@' . env('ASM_EMAIL_DOMAIN', 'all-stars-motorsport.com')),
            'password' => env('ALLSTARS_ASM_MEDIA_SMTP_PASSWORD'),
            'verify_peer' => env('ALLSTARS_ASM_MEDIA_SMTP_VERIFY_PEER'),
            'from_address' => env('ALLSTARS_ASM_MEDIA_EMAIL', 'media@' . env('ASM_EMAIL_DOMAIN', 'all-stars-motorsport.com')),
            'from_name' => env('ALLSTARS_ASM_MEDIA_FROM_NAME', 'All Stars Motorsport'),
        ],

        'suppliers' => [
            'username' => env('ALLSTARS_SUPPLIERS_SMTP_USERNAME', env('ALLSTARS_SUPPLIERS_EMAIL', 'suppliers@' . env('ASD_EMAIL_DOMAIN', 'all-stars-distribution.com'))),
            'password' => env('ALLSTARS_SUPPLIERS_SMTP_PASSWORD'),
            'from_address' => env('ALLSTARS_SUPPLIERS_EMAIL', 'suppliers@' . env('ASD_EMAIL_DOMAIN', 'all-stars-distribution.com')),
            'from_name' => env('ALLSTARS_SUPPLIERS_FROM_NAME', 'ALL STARS'),
        ],

        'marketing' => [
            'host' => env('ALLSTARS_MARKETING_SMTP_HOST', env('MAIL_HOST')),
            'port' => env('ALLSTARS_MARKETING_SMTP_PORT', env('MAIL_PORT', 465)),
            'encryption' => env('ALLSTARS_MARKETING_SMTP_ENCRYPTION', env('MAIL_ENCRYPTION', 'ssl')),
            'username' => env('ALLSTARS_MARKETING_SMTP_USERNAME'),
            'password' => env('ALLSTARS_MARKETING_SMTP_PASSWORD'),
            'verify_peer' => env('ALLSTARS_MARKETING_SMTP_VERIFY_PEER'),
            'from_address' => env('ALLSTARS_MARKETING_FROM_ADDRESS', env('ALLSTARS_MARKETING_SMTP_USERNAME')),
            'from_name' => env('ALLSTARS_MARKETING_FROM_NAME', 'AS Group'),
        ],
    ],

    'payment_links' => [
        'gateway_url' => env('ALLSTARS_PAYMENT_LINK_GATEWAY_URL', 'https://secure.ogone.com/ncol/PROD/orderstandard.asp'),
        'stores' => [
            'ASM' => [
                'name' => 'All Stars Motorsport',
                'payment_link_color' => '#dd170e',
                'social_links' => [
                    'facebook' => 'https://www.facebook.com/allstarsmotorsport',
                    'flickr' => 'https://www.flickr.com/photos/allstarsmotorsport/',
                    'instagram' => 'https://instagram.com/allstarsmotorsport',
                    'youtube' => 'https://www.youtube.com/user/allstarsmotorsport',
                ],
                'pspid' => env('ALLSTARS_PAYMENT_LINK_ASM_PSPID', env('ALLSTARS_PAYMENT_LINK_PSPID', 'Allstarsmotorsport')),
                'sha_in' => env('ALLSTARS_PAYMENT_LINK_ASM_SHA_IN', env('ALLSTARS_PAYMENT_LINK_SHA_IN', '2f47f9cb-f665-4b76-ba0d-80c0aacee604')),
            ],
            'ASD' => [
                'name' => 'All Stars Distribution',
                'payment_link_color' => 'dodgerblue',
                'social_links' => [
                    'facebook' => 'https://www.facebook.com/allstarsdistribution',
                    'flickr' => 'https://www.flickr.com/photos/allstarsdistribution/',
                    'instagram' => 'https://instagram.com/allstarsdistribution',
                ],
                'footer_social_image' => 'asd_email_logos.png',
                'social_icons' => [
                    'facebook' => 'asd_facebook_mail.png',
                    'flickr' => 'asd_flickr_mail.png',
                    'instagram' => 'asd_insta_mail.png',
                ],
                'pspid' => env('ALLSTARS_PAYMENT_LINK_ASD_PSPID', 'Allstarsdistribution'),
                'sha_in' => env('ALLSTARS_PAYMENT_LINK_ASD_SHA_IN', env('ALLSTARS_PAYMENT_LINK_SHA_IN', '2f47f9cb-f665-4b76-ba0d-80c0aacee604')),
            ],
        ],
    ],
];
