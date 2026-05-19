<?php

return [
    'stores' => [
        'ASM' => [
            'bridge_token' => env('PRESTASHOP_ASM_BRIDGE_TOKEN'),
            'bridge_token_parameter' => env('PRESTASHOP_ASM_BRIDGE_TOKEN_PARAMETER', 'bridge_key'),
            'bridge_use_hmac' => filter_var(env('PRESTASHOP_ASM_BRIDGE_USE_HMAC', false), FILTER_VALIDATE_BOOLEAN),
            'bridge_hmac_secret' => env('PRESTASHOP_ASM_BRIDGE_HMAC_SECRET'),
            'cookie_key' => env('PRESTASHOP_ASM_COOKIE_KEY'),
        ],

        'ASD' => [
            'bridge_token' => env('PRESTASHOP_ASD_BRIDGE_TOKEN'),
            'bridge_token_parameter' => env('PRESTASHOP_ASD_BRIDGE_TOKEN_PARAMETER', 'bridge_key'),
            'bridge_use_hmac' => filter_var(env('PRESTASHOP_ASD_BRIDGE_USE_HMAC', false), FILTER_VALIDATE_BOOLEAN),
            'bridge_hmac_secret' => env('PRESTASHOP_ASD_BRIDGE_HMAC_SECRET'),
            'cookie_key' => env('PRESTASHOP_ASD_COOKIE_KEY'),
        ],
    ],
];
