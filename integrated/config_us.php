<?php

return (object) array(
    'campaignId' => 1471,
    'targetLanguage' => 'en',
    'paypalSandboxId' => null, // TODO
    'paypalProductionId' => null, // TODO
    'stripeSandboxId' => null, // TODO
    'stripeProductionId' => null, // TODO
    'corporateName' => 'Melara Pro LLC',
    'customDescriptor' => 'Melara Seat Cushion',
    'companyAddress' => '30 N Gould ST. N Sheridan WY. 82801',
    'companyEmailAddress' => 'support@melarapro.com',
    'companyEmailAddress1' => 'orders@melarapro.com',
    'companyPhoneNumber' => '+1 866 624 5445',
    'returnAddress' => '',
    'everflowOfferId' => null, // TODO
    'checkoutType' => 'onePage', // onePage, twoPage, compact
    'stripePaymentWallet' => "", // e.g. "google_pay, apple_pay, link" once the Stripe IDs are set
    'stripeKeys' =>[
        'sandbox_publicKey' => getenv('SANDBOX_PUBLICKEY'),
        'sandbox_secretKey' => getenv('SANDBOX_SECRETKEY'),
        'live_publicKey' => getenv('LIVE_PUBLICKEY'),
        'live_secretKey' => getenv('LIVE_SECRETKEY')
    ],
    'campaignFunnel' => [
        [
            'page' => 'extra-seat-cushion',
            'everflowEventId' => null, // Melara Seat Cushion Upsell
            'downsellEverflowEventId' => null, // Melara Seat Cushion Downsell - fired when the no-thanks popup downsell (productIds[1]) is accepted
            'productIds' => [34003, 34005],
            'yesBtnTo' => 'knee-pillow',
            'noBtnTo' => 'knee-pillow',
            'baseQtyFromCheckout' =>false, // initial qty is base on the selected qty of checkout page
        ],
        [
            'page' => 'knee-pillow',
            'everflowEventId' => null, // Knee Pillow Upsell
            'productIds' => [34011],
            'yesBtnTo' => 'melara-apex-pillow',
            'noBtnTo' => 'melara-apex-pillow'
        ],
        [
            'page' => 'melara-apex-pillow',
            'everflowEventId' => null, // Melara Apex Pillow Upsell
            'downsellEverflowEventId' => null, // Melara Apex Pillow Downsell - fired when the no-thanks popup downsell is accepted
            'productIds' => [34007, 34009],
            'yesBtnTo' => 'thankyou',
            'noBtnTo' => 'thankyou'
        ],
    ]
);

?>
