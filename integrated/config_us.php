<?php
/* ------------------------------------------------------------------
 * Melara Seat Cushion — United States offer config
 * Same shape as shopmelara-apex/integrated/config_us.php.
 *
 * Live on Konnektive / CheckoutChamp campaign 1471 (melara-ease.com).
 * Packs come from the campaign: "1x Melara Seat Cushion" … "4x Melara Seat Cushions".
 * Set campaignId to null to go back to preview mode (no CRM calls, page prices).
 * ------------------------------------------------------------------ */

return (object) array(
    'campaignId' => 1471,
    'targetLanguage' => 'en',
    'mainProductName' => 'Melara Seat Cushion', // matched by initProduct() against the campaign product names
    'addOnProductName' => 'Secure',             // SecureShip: not in campaign 1471 yet, the checkout hides it until it is
    'paypalSandboxId' => null,            // TODO
    'paypalProductionId' => null,         // TODO
    'stripeSandboxId' => null,            // TODO
    'stripeProductionId' => null,         // TODO
    'corporateName' => 'Melara Pro LLC',
    'customDescriptor' => 'Melara Seat Cushion',
    'companyAddress' => '30 N Gould ST. N Sheridan WY. 82801',
    'companyEmailAddress' => 'support@melarapro.com',
    'companyEmailAddress1' => 'orders@melarapro.com',
    'companyPhoneNumber' => '+1 866 624 5445',
    'returnAddress' => '',
    'everflowOfferId' => null,            // TODO
    'checkoutType' => 'onePage',          // onePage, twoPage, compact
    // Express wallets (Apple/Google Pay, Link) stay off until the Stripe IDs above are set,
    // e.g. "google_pay, apple_pay, link"
    'stripePaymentWallet' => "",
    'stripeKeys' => [
        'sandbox_publicKey' => getenv('SANDBOX_PUBLICKEY'),
        'sandbox_secretKey' => getenv('SANDBOX_SECRETKEY'),
        'live_publicKey' => getenv('LIVE_PUBLICKEY'),
        'live_secretKey' => getenv('LIVE_SECRETKEY')
    ],
    // No upsells yet: a successful order goes straight to thankyou.php
    // (OfferApi sends the customer to the first funnel page after checkout).
    'campaignFunnel' => [
        [
            'page' => 'thankyou',
            'baseQtyFromCheckout' => false,
        ],
    ],
);

?>
