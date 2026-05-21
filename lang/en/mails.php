<?php

return [
    'Requested products notification' => 'Requested products notification',
    
    'asm' => [
        'requestedProduct' => [
            'text_1' => 'Dear Customer,',
            'text_2' => 'We are pleased to inform you the product ',
            'text_3' => ', you tracked the arrival, is now back in stock and available for sale.',
            'text_4' => 'You can now check and order this item by clicking the link below: ',
            'text_5' => 'Please feel free to contact us with any questions.',
            'text_6' => 'Thanks for choosing us.',
            'text_7' => 'Kind regards',
            'text_8' => 'Customer Service Team <br> All Stars Motorsport',
        ]
    ],
    
    'Newsletter - All Stars Motorsport' => 'Newsletter - All Stars Motorsport',
    
    'asm_newsletter' => [
        'asm_newsletter' => [
            'header_link' => rtrim(config('allstars.services.webtools.base_url'), '/') . '/img/newsletter_images/newsletter_header_en.jpg',
            'see_product' => 'See product',
            'Price: ' => 'Price: ',
            'Inc VAT' => 'Inc. VAT',
            'Ex VAT' => 'Ex. VAT',
            'follow_us' => 'Follow us:',
            'unsubscribe_link' => rtrim(config('allstars.stores.ASM.base_url'), '/') . '/en/my-car?getUnsubscribeEmail=1',
            'unsubscribe_message' => 'To unsubscribe from All Stars Motorsport newsletter, please click here',
            'base_link' => rtrim(config('allstars.stores.ASM.base_url'), '/') . '/en/engine/'
        ]
    ],
    
    'Order review' => 'ALL STARS MOTORSPORT - ORDER REVIEW'
];
