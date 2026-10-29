<?php
/* ------------------------------------------------------------------
 * Melara Seat Cushion — United States offer config
 * Same shape as shopmelara-apex/integrated/config_us.php.
 *
 * PLACEHOLDER: campaignId is null until the seat-cushion campaign exists
 * in Konnektive. While it's null the site runs in "preview" mode: no CRM
 * calls, no click imports, no orders, prices come from the page itself.
 * To go live, fill in the IDs marked TODO and copy .env from shopmelara-apex.
 * ------------------------------------------------------------------ */

return (object) array(
    'campaignId' => null,                 // TODO: Konnektive campaign ID for the seat cushion
    'targetLanguage' => 'en',
    'mainProductName' => 'Melara',        // TODO: CRM product name matched by initProduct()
    'addOnProductName' => 'Secure',       // TODO: CRM product name for SecureShip
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
    'stripePaymentWallet' => "google_pay, apple_pay, link",
    'stripeKeys' => [
        'sandbox_publicKey' => getenv('SANDBOX_PUBLICKEY'),
        'sandbox_secretKey' => getenv('SANDBOX_SECRETKEY'),
        'live_publicKey' => getenv('LIVE_PUBLICKEY'),
        'live_secretKey' => getenv('LIVE_SECRETKEY')
    ],
    // No upsells yet: checkout goes straight to the thank-you page.
    'campaignFunnel' => [],
);

?>
