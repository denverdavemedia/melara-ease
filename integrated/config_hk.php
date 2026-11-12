<?php

return (object) array(
    'campaignId' => 1515, // HK campaign, HKD
    'targetLanguage' => 'en', // same as the shopmelara-apex config_hk.php
    'paypalSandboxId' => 27, // same Melara Pro LLC merchants as shopmelara-apex
    'paypalProductionId' => 23,
    'stripeSandboxId' => 35, // vanotium stripe sandbox id
    'stripeProductionId' => 17,
    'corporateName' => 'Melara Pro LLC',
    'customDescriptor' => 'Melara Seat Cushion',
    'companyAddress' => '30 N Gould ST. N Sheridan WY. 82801',
    'companyEmailAddress' => 'support@melarapro.com',
    'companyEmailAddress1' => 'orders@melarapro.com',
    'companyPhoneNumber' => '+1 866 624 5445',
    'returnAddress' => '',
    'everflowOfferId' => 84, // Everflow offer "Melara Seat Cushion"
    'checkoutType' => 'onePage', // onePage, twoPage, compact
    'stripePaymentWallet' => "google_pay, apple_pay, link", // Stripe express buttons on the checkout
    'stripeKeys' =>[
        'sandbox_publicKey' => getenv('SANDBOX_PUBLICKEY'),
        'sandbox_secretKey' => getenv('SANDBOX_SECRETKEY'),
        'live_publicKey' => getenv('LIVE_PUBLICKEY'),
        'live_secretKey' => getenv('LIVE_SECRETKEY')
    ],
    'campaignFunnel' => [
        [
            'page' => 'extra-seat-cushion',
            'everflowEventId' => 560, // Seat Cushion Upsell (no downsell on this page)
            'productIds' => [35807], // Melara Sitora Seat Cushion
            'yesBtnTo' => 'melara-apex-pillow',
            'noBtnTo' => 'melara-apex-pillow',
            'baseQtyFromCheckout' =>false, // initial qty is base on the selected qty of checkout page
        ],
        [
            // Conditional: buying the Apex pillow (or its popup downsell) leads to the pillowcase upsells.
            // Declining both skips the pillowcases and goes straight to the knee pillow.
            'page' => 'melara-apex-pillow',
            'everflowEventId' => 561, // Melara Apex Pillow Upsell
            'downsellEverflowEventId' => 562, // Melara Apex Pillow Downsell - fired when the no-thanks popup downsell is accepted
            'productIds' => [35809, 35811], // Melara Apex Pillow, Melara Apex Pillow Downsell
            'yesBtnTo' => 'cloudair-pillowcase',
            'noBtnTo' => 'knee-pillow'
        ],
        [
            'page' => 'cloudair-pillowcase',
            'everflowEventId' => 597, // CloudAir Pillowcase Upsell
            'downsellEverflowEventId' => 598, // CloudAir Pillowcase Downsell - fired when the no-thanks popup downsell is accepted
            'productIds' => [35823, 35825], // CloudAir Pillowcase, CloudAir Pillowcase Downsell
            'yesBtnTo' => 'anti-dust-pillowcase',
            'noBtnTo' => 'anti-dust-pillowcase'
        ],
        [
            'page' => 'anti-dust-pillowcase',
            'everflowEventId' => 599, // Anti-Dust Pillowcase Upsell
            'downsellEverflowEventId' => 600, // Anti-Dust Pillowcase Downsell - fired when the no-thanks popup downsell is accepted
            'productIds' => [35819, 35821], // Anti-Dust Pillowcase, Anti-Dust Pillowcase Downsell
            'yesBtnTo' => 'knee-pillow',
            'noBtnTo' => 'knee-pillow'
        ],
        [
            'page' => 'knee-pillow',
            'everflowEventId' => 563, // Knee Pillow Upsell
            'downsellEverflowEventId' => 564, // Knee Pillow Downsell - fired when the no-thanks popup downsell (productIds[1]) is accepted
            'productIds' => [35813, 35815], // Knee Pillows, Knee Pillows Downsell
            'yesBtnTo' => 'thankyou',
            'noBtnTo' => 'thankyou'
        ],
    ]
);

?>
