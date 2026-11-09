<?php

return (object) array(
    'campaignId' => 1471,
    'targetLanguage' => 'en',
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
            'productIds' => [34003], // Melara Sitora Seat Cushion
            'yesBtnTo' => 'knee-pillow',
            'noBtnTo' => 'knee-pillow',
            'baseQtyFromCheckout' =>false, // initial qty is base on the selected qty of checkout page
        ],
        [
            'page' => 'knee-pillow',
            'everflowEventId' => 563, // Knee Pillow Upsell
            'downsellEverflowEventId' => 564, // Knee Pillow Downsell - fired when the no-thanks popup downsell (productIds[1]) is accepted
            'productIds' => [34011, 35551], // Knee Pillows, Knee Pillows Downsell
            'yesBtnTo' => 'melara-apex-pillow',
            'noBtnTo' => 'melara-apex-pillow'
        ],
        [
            'page' => 'melara-apex-pillow',
            'everflowEventId' => 561, // Melara Apex Pillow Upsell
            'downsellEverflowEventId' => 562, // Melara Apex Pillow Downsell - fired when the no-thanks popup downsell is accepted
            'productIds' => [34007, 34009], // Melara Apex Pillow, Melara Apex Pillow Downsell
            'yesBtnTo' => 'thankyou',
            'noBtnTo' => 'thankyou'
        ],
    ]
);

?>
