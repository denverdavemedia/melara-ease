<?php
/* Melara Sitora checkout. Pack prices, shipping, currency, SecureShip and countries come from the CRM campaign. */
include_once("integrated/setup.php");
include_once(BASEPATH . '/src/JsonTranslateCollector.php');
include_once(__DIR__ . '/includes/language-selector.php');

$percentage = [0.55, 0.62, 0.65, 0.7]; // discount per pack: 1x 55%, 2x 62%, 3x 65%, 4x 70%
$packLabels = [1 => 'Just me', 2 => 'For two', 3 => 'Every chair', 4 => 'Whole family'];

$collector = new JsonCollector(targetLanguage: $OfferApi->targetLanguage, pageName: "checkout");

$countries = $OfferApi->countries;
// finalizeTransaction POSTs are already handled in integrated/setup.php; don't run initProduct() a second time here
$mainProd = (isset($_POST['finalizeTransaction']) && $_POST['finalizeTransaction'] == 1)
    ? null
    : $OfferApi->initProduct(productName: 'Seat Cushion', orderData: $_POST); // matches the CRM names "1x Melara Sitora Seat Cushion" … (and the old "Melara Seat Cushion")
$insurance = $OfferApi->initProductAddOn(productName: "Secure");
if ($mainProd) usort($mainProd->product, fn($a, $b) => $a->maxOrderQty <=> $b->maxOrderQty); // 1x, 2x, 3x, 4x

$secure      = $insurance->product[0] ?? null; // SecureShip, when the campaign has it
$securePrice = $secure ? (float) $secure->price : 0;
$selected    = in_array((int) ($_GET['pack'] ?? 0), [1, 2, 3, 4]) ? (int) $_GET['pack'] : 2; // ?pack= from the landing page
$maxSave     = round(max($percentage) * 100);
$firstPack   = $mainProd->product[0];

$product  = $collector->translate('product_name', 'Melara Sitora');
$freeText = $collector->translate('co_free', 'Free');
?>
<!doctype html>
<html lang="<?= $OfferApi->targetLanguage ?>" dir="<?= melaraTextDirection($OfferApi->targetLanguage) ?>" translate="no"><!-- same as shopmelara-apex: stops Chrome Translate rewriting the page, e.g. the Chinese address placeholders -->
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="assets/img/favicon/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon/favicon-16x16.png">
    <title>Checkout — <?= $product ?></title>
    <meta name="robots" content="noindex">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="src/common/js/intltelinput/css/intltelInput.css">
    <link rel="stylesheet" href="assets/css/checkout.css?t=<?= $OfferApi->assetVersion('assets/css/checkout.css') ?>">
    <link rel="stylesheet" href="public/css/app.css?t=<?= $OfferApi->assetVersion('public/css/app.css') ?>">
    <script src="https://js.stripe.com/v3/" async></script>
    <style>
        /* address suggestion popups used by the per-country shipping forms, from shopmelara-apex checkout_v1.php */
        .fields-zip-wrapper, .field_address_wrapper { position: relative; width: 100%; }
        #address-suggest { position: absolute; top: calc(100% + 4px); left: 0; width: 100%; display: none; border: 1px solid #ddd; background: #fff; box-shadow: 0 8px 24px rgba(0,0,0,.15); z-index: 99; }
        #close-suggestion { position: absolute; right: -10px; top: -10px; color: #fff; background-color: #333; border: 2px solid #fff; border-radius: 50%; width: 25px; height: 25px; text-align: center; cursor: pointer; text-decoration: none; font-weight: 700; line-height: 22px; padding: 0; margin: 0; z-index: 11; font-size: .8em; }
        #disclaimer { display: none; border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px 14px; color: #4b5563; font-size: .875rem; line-height: 1.6; box-shadow: 0 1px 2px rgba(0,0,0,.05); margin-top: 10px; }
        @media (max-width: 768px) { #address-suggest { top: auto; height: 100%; margin-top: 5px; } }
    </style>
</head>
<body class="font-sans text-ink antialiased">
<?= $mainProd->hiddenInputs ?? '' ?>

<header class="bg-white border-b border-sand">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 h-16 flex items-center justify-between">
        <a href="index.php" class="shrink-0"><img src="assets/img/logo.svg" alt="Melara" class="h-7 sm:h-9 w-auto" width="190" height="49"></a>
        <div class="min-w-0 flex items-center gap-2 sm:gap-5">
            <span class="min-w-0 inline-flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm text-muted"><span class="text-turq-ink"><svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4.5" y="10.5" width="15" height="10" rx="2.2"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3M12 14.5v2.5"/></svg></span> <span class="leading-tight"><?= $collector->translate('co_secure_checkout', 'Secure checkout') ?></span></span>
            <?= languageSelector('locked') ?>
        </div>
    </div>
</header>

<section class="relative overflow-hidden bg-[radial-gradient(ellipse_at_70%_40%,#d6f7ee_0%,#eef0f2_55%,#f3f4f6_100%)]">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 py-8 sm:py-10 flex items-center justify-between gap-6">
        <div>
            <p class="text-xs font-medium uppercase tracking-[.2em] text-turq-ink"><?= $collector->translate('co_almost_there', 'Almost there') ?></p>
            <h1 class="mt-2 font-serif text-3xl sm:text-5xl leading-tight sm:leading-none tracking-tight"><?= $collector->translate('co_treat_your_back_to', 'Treat your back to a') ?><br class="hidden sm:block"> <em class="text-turq-ink"><?= $collector->translate('co2_better_day', 'more comfortable day.') ?></em></h1>
        </div>
        <img src="assets/img/anatomy-assembled.webp?t=<?= $OfferApi->assetVersion('assets/img/anatomy-assembled.webp') ?>" alt="<?= $product ?>" class="hidden sm:block w-48 lg:w-60 drop-shadow-2xl" width="240" height="240">
    </div>
</section>

<main class="mx-auto max-w-6xl px-4 sm:px-6 py-8 sm:py-10">
    <form id="checkout" novalidate class="grid gap-6 lg:grid-cols-[1.1fr_1fr] lg:grid-rows-[auto_1fr] lg:items-start">

        <section class="card p-5 sm:p-7 lg:col-start-1 lg:row-start-1">
            <!-- Phones get only the one-line timer below, like the shopmelara-apex checkout (checkout_v1.php); the discount text is too long there -->
            <div class="hidden sm:flex items-start gap-4">
                <span class="relative shrink-0 w-[76px] h-[76px] grid place-items-center" aria-label="Up to <?= $maxSave ?>% off">
                    <svg viewBox="0 0 100 100" class="absolute inset-0 w-full h-full drop-shadow-[0_10px_18px_rgba(29,209,161,.55)] animate-[spin_24s_linear_infinite]" aria-hidden="true">
                        <path fill="#1dd1a1" stroke="#1dd1a1" stroke-width="3" stroke-linejoin="round" d="M50,2L55.55,7.86L62.42,3.64L66.26,10.74L74,8.43L75.87,16.28L83.94,16.06L83.72,24.13L91.57,26L89.26,33.74L96.36,37.58L92.14,44.45L98,50L92.14,55.55L96.36,62.42L89.26,66.26L91.57,74L83.72,75.87L83.94,83.94L75.87,83.72L74,91.57L66.26,89.26L62.42,96.36L55.55,92.14L50,98L44.45,92.14L37.58,96.36L33.74,89.26L26,91.57L24.13,83.72L16.06,83.94L16.28,75.87L8.43,74L10.74,66.26L3.64,62.42L7.86,55.55L2,50L7.86,44.45L3.64,37.58L10.74,33.74L8.43,26L16.28,24.13L16.06,16.06L24.13,16.28L26,8.43L33.74,10.74L37.58,3.64L44.45,7.86Z"/>
                        <circle cx="50" cy="50" r="35" fill="none" stroke="#fff" stroke-opacity=".7" stroke-width="1.5" stroke-dasharray="2 3"/>
                    </svg>
                    <span class="relative flex flex-col items-center leading-none text-ink">
                        <span class="font-serif text-[18px] tracking-tight"><?= $maxSave ?><span class="text-xs align-top">%</span></span>
                        <span class="mt-0.5 text-[10px] font-bold tracking-[.18em]"><?= $collector->translate('co_off', 'OFF') ?></span>
                    </span>
                </span>
                <div>
                    <p class="font-semibold text-lg text-turq-ink"><?= $collector->translate('co_your_launch_discount_has', 'Your launch discount has been applied') ?></p>
                    <p class="mt-1 text-sm text-muted"><?= $collector->translate('co_limited_time_the', 'Limited time: the') ?> <?= $product ?> <?= $collector->translate('co_is_available_from', 'is available from') ?> <s><?= $OfferApi->formatNumber(round($firstPack->price / (1 - $percentage[0]))) ?></s> <strong class="text-ink"><?= $OfferApi->formatNumber($firstPack->price) ?></strong><?= $collector->translate('co_with_up_to', ', with up to') ?> <?= $maxSave ?><?= $collector->translate('co_off_bundles', '% off when you buy more than one.') ?></p>
                </div>
            </div>
            <div class="sm:mt-5 flex flex-wrap items-center justify-center gap-x-2 sm:gap-x-3 gap-y-1 rounded-2xl border border-dashed border-turq/50 bg-turq-soft/40 px-3 sm:px-4 py-3 text-sm">
                <span class="inline-flex items-center gap-2 font-medium"><span class="text-turq-ink"><svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span> <span class="hidden sm:inline"><?= $collector->translate('co_your_discount_is_reserved', 'Your discount is reserved for:') ?></span><span class="sm:hidden"><?= str_replace('{pct}', $maxSave, $collector->translate('co_discount_ends_in', 'Your {pct}% discount ends in:')) ?></span></span>
                <span data-countdown class="font-semibold tabular-nums text-red-600 sm:text-ink text-base">10:00</span>
            </div>

            <div class="mt-7"><h2 class="flex items-center gap-3 text-lg font-bold"><span class="grid place-items-center w-9 h-9 rounded-xl bg-turq-soft text-turq-ink"><svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 8.5 12 4l8 4.5v7L12 20l-8-4.5z"/><path d="M4 8.5l8 4.5 8-4.5M12 13v7"/></svg></span><span><span class="text-muted font-medium"><?= $collector->translate('step_word', 'Step') ?> 1:</span> <?= $collector->translate('step_1_title', 'Select quantity') ?></span></h2></div>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4" role="radiogroup" aria-label="Pack size">
                <?php foreach ($mainProd->product as $key => $value):
                    $row = $mainProd->product[$key];
                    $qty = (int) $row->maxOrderQty;
                    $retailPrice = round($row->price / (1 - $percentage[$qty - 1]));
                    $discountPercentage = round(($retailPrice - $row->price) / $retailPrice * 100);
                    $shippingText = $row->shippingPrice > 0 ? $OfferApi->formatNumber($row->shippingPrice) : $freeText;
                    $label = $collector->translate('pack_' . $qty . '_label', $packLabels[$qty]);
                    $active = $qty === $selected ? 'active' : '';
                    if ($active) $sel = ['row' => $row, 'retail' => $retailPrice, 'label' => $label, 'shippingText' => $shippingText];
                ?>
                <label class="pack relative block _prodSelect <?= $active ?>"
                       data-productprice="<?= $row->price ?>" data-productprice-text="<?= $OfferApi->formatNumber($row->price) ?>" data-campaignproductid="<?= $row->campaignProductId ?>" data-productname="<?= htmlspecialchars($row->productName) ?>"
                       data-quantity="1" data-shipping="<?= $row->shippingPrice ?>" data-shipping-text="<?= $shippingText ?>" data-unitprice="<?= $row->price ?>" data-originalPrice="<?= $retailPrice ?>"
                       data-variantDetailId="<?= $row->variants[0]->variantDetailId ?? '' ?>" data-overrideQuantity="<?= $qty ?>">
                    <input type="radio" name="pack" value="<?= $qty ?>" class="sr-only" <?= $active ? 'checked' : '' ?>
                           data-price="<?= $row->price ?>" data-compare="<?= $retailPrice ?>" data-shipping="<?= $row->shippingPrice ?>" data-label="<?= htmlspecialchars($label) ?>">
                    <div class="pack-body h-full rounded-2xl border-2 border-sand bg-white p-4 pt-5 flex items-center gap-3">
                        <?php if ($qty == 2): ?>
                            <span class="absolute -top-3 start-4 rounded-full px-3 py-0.5 text-[11px] font-semibold uppercase tracking-wide bg-turq text-ink"><?= $collector->translate('pack_2_badge', 'Most popular') ?></span>
                        <?php elseif ($qty == 4): ?>
                            <span class="absolute -top-3 start-4 rounded-full px-3 py-0.5 text-[11px] font-semibold uppercase tracking-wide bg-ink text-white"><?= $collector->translate('pack_4_badge', 'Best value') ?></span>
                        <?php endif; ?>
                        <div class="stack relative w-[42%] aspect-square shrink-0 rounded-xl bg-linen grid place-items-center overflow-hidden">
                            <?php // one copy per cushion, fanned from top-left (back) to bottom-right (front)
                            $width = [1 => 80, 2 => 68, 3 => 60, 4 => 54][$qty];
                            $step  = [1 => 0, 2 => 28, 3 => 24, 4 => 21][$qty];
                            for ($i = $qty - 1; $i >= 0; $i--):
                                $offset = $i - ($qty - 1) / 2; ?>
                                <img src="assets/img/anatomy-assembled.webp?t=<?= $OfferApi->assetVersion('assets/img/anatomy-assembled.webp') ?>" alt="" class="absolute" style="width: <?= $width ?>%; transform: translate(<?= -$offset * $step ?>%, <?= -$offset * $step * .5 ?>%) scale(<?= 1 - $i * .04 ?>); filter: brightness(<?= 1 - $i * .07 ?>) drop-shadow(0 6px 8px rgba(29,34,38,.22))" loading="lazy">
                            <?php endfor; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-serif text-3xl leading-none"><?= $qty ?><span class="text-xl">×</span></p>
                                <span class="pack-radio shrink-0 w-5 h-5 rounded-full border-2 border-sand transition"></span>
                            </div>
                            <p class="mt-1 text-sm font-medium leading-tight"><?= $product ?><?= $qty > 1 ? 's' : '' ?></p>
                            <span class="mt-2 inline-block rounded-md bg-turq-soft px-2 py-0.5 text-[11px] font-semibold text-turq-ink"><?= $discountPercentage ?><?= $collector->translate('co_off_' . $qty, '% OFF') ?></span>
                            <p class="mt-2 leading-none"><s class="text-xs text-muted"><?= $OfferApi->formatNumber($retailPrice) ?></s> <strong class="text-xl"><?= $OfferApi->formatNumber($row->price) ?></strong></p>
                            <p class="mt-1 text-[11px] text-muted"><?= $OfferApi->formatNumber($row->price / $qty) ?> <?= $collector->translate('co_each_' . $qty, 'each') ?></p>
                        </div>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>

            <?php if ($secure): ?>
            <label class="mt-5 flex items-start gap-4 rounded-2xl border border-sand bg-cream p-4 cursor-pointer hover:border-turq/50 transition">
                <input type="checkbox" id="insuranceAddOn" name="productUpsell" value="1" checked
                       data-campaignproductid="<?= $secure->campaignProductId ?>" data-productname="<?= htmlspecialchars($secure->productName) ?>"
                       data-quantity="<?= $secure->maxOrderQty ?>" data-shipping="<?= $secure->shippingPrice ?>" data-unitprice="<?= $securePrice ?>" class="mt-1 w-5 h-5 accent-[#1dd1a1] shrink-0">
                <!-- same SecureShip picture as the shopmelara-apex checkout (assets/images/silver-ion-v2.png there) -->
                <span class="flex-1">
                    <span class="block font-semibold"><?= $collector->translate('co_yes_add', 'Yes, add') ?> <span class="text-turq-ink"><?= $collector->translate('co_secureship', 'SecureShip') ?></span> <?= $collector->translate('co_to_my_order', 'to my order') ?></span>
                    <span class="block mt-1 text-xs text-muted leading-relaxed"><?= $collector->translate('co_full_protection_against_damage', 'Full protection against damage, loss or porch-pirate theft. We reship or refund, no questions asked.') ?></span>
                    <span class="block mt-2 text-sm font-semibold text-turq-ink"><?= $OfferApi->formatNumber($securePrice) ?></span>
                </span>
            </label>
            <?php endif; ?>
        </section>

        <!-- Right column on desktop: steps 2-3 card, then the order button directly under it.
             On phones the wrapper is display:contents, so its two parts stay separate grid items (button goes last). -->
        <div class="contents lg:flex lg:flex-col lg:gap-6 lg:col-start-2 lg:row-start-1 lg:row-span-2">
        <section class="card p-5 sm:p-7">
            <!-- No order summary box here, to match the shopmelara-apex checkout: pack prices are on the cards, shipping and SecureShip on their own rows -->
            <div><h2 class="flex items-center gap-3 text-lg font-bold"><span class="grid place-items-center w-9 h-9 rounded-xl bg-turq-soft text-turq-ink"><svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4.5 20.5c1.2-3.6 4-5.5 7.5-5.5s6.3 1.9 7.5 5.5"/></svg></span><span><span class="text-muted font-medium"><?= $collector->translate('step_word_2', 'Step') ?> 2:</span> <?= $collector->translate('step_2_title', 'Customer information') ?></span></h2></div>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="label" for="fields_fname"><?= $collector->translate('co_first_name', 'First name') ?></label><div class="has-icon"><span class="field-icon"><svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4.5 20.5c1.2-3.6 4-5.5 7.5-5.5s6.3 1.9 7.5 5.5"/></svg></span><input class="field" id="fields_fname" name="first_name" autocomplete="given-name" placeholder="First name" required></div></div>
                <div><label class="label" for="fields_lname"><?= $collector->translate('co_last_name', 'Last name') ?></label><div class="has-icon"><span class="field-icon"><svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4.5 20.5c1.2-3.6 4-5.5 7.5-5.5s6.3 1.9 7.5 5.5"/></svg></span><input class="field" id="fields_lname" name="last_name" autocomplete="family-name" placeholder="Last name" required></div></div>
                <div class="sm:col-span-2"><label class="label" for="fields_email"><?= $collector->translate('co_email_address', 'Email address') ?></label><div class="has-icon"><span class="field-icon"><svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m4 7 8 6 8-6"/></svg></span><input class="field" id="fields_email" name="email" type="email" autocomplete="email" placeholder="you@example.com" required></div></div>
                <div class="sm:col-span-2"><label class="label" for="fields_phone"><?= $collector->translate('co_phone_number', 'Phone number') ?></label><div><input class="field" id="fields_phone" name="_phone_number" type="tel" autocomplete="tel" placeholder="For delivery updates" required></div></div>
            </div>

            <div class="mt-8"><h2 class="flex items-center gap-3 text-lg font-bold"><span class="grid place-items-center w-9 h-9 rounded-xl bg-turq-soft text-turq-ink"><svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg></span><span><span class="text-muted font-medium"><?= $collector->translate('step_word_3', 'Step') ?> 3:</span> <?= $collector->translate('step_3_title', 'Delivery address') ?></span></h2></div>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2"><label class="label" for="fields_country_select"><?= $collector->translate('co_country', 'Country') ?></label>
                    <div class="has-icon"><span class="field-icon"><svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3z"/></svg></span>
                    <select class="field" id="fields_country_select" name="country" autocomplete="country" required>
                        <?php foreach ($countries as $c): ?><option value="<?= $c->countryCode ?>" <?= $c->countryCode === strtoupper($userCountry ?? 'US') ? 'selected' : '' ?>><?= $collector->translate('country_name' . $c->countryCode, $c->countryName) ?></option><?php endforeach; ?>
                    </select>
                    </div>
                </div>
                <!-- Per-country address form, like shopmelara-apex (checkout_v1.php): the visitor's country is included here,
                     and integrated.js (dynamicShippingFields) reloads this box when the country changes, e.g. Taiwan gets store pickup -->
                <div id="shipping-container" class="sm:col-span-2">
                    <?php
                    $shipFile = __DIR__ . '/src/shipping/shipping-address-' . strtolower($userCountry ?? 'us') . '.php';
                    include is_file($shipFile) ? $shipFile : __DIR__ . '/src/shipping/shipping-address-us.php';
                    ?>
                </div>
            </div>
            <div class="mt-5 rounded-2xl border border-sand p-4">
                <p class="text-sm font-medium"><?= $collector->translate('co_shipping_2', 'Shipping') ?></p>
                <div class="mt-2 flex items-center justify-between text-sm">
                    <span class="inline-flex items-center gap-3"><span class="w-5 h-5 rounded-full border-2 border-turq bg-turq shadow-[inset_0_0_0_3px_#fff]"></span> <?= $collector->translate('co_standard_ship', 'Standard shipping') ?></span>
                    <span class="font-semibold text-turq-ink" id="sumShippingOpt"><?= $sel['shippingText'] ?></span>
                </div>
                <p class="mt-3 text-xs text-muted bg-cream rounded-lg px-3 py-2 inline-flex items-center gap-2"><span class="text-turq-ink"><svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/></svg></span> <?= $collector->translate('co_we_ship_ddp_all', 'We ship DDP: all taxes and duties are paid by us.') ?></p>
            </div>
        </section>

        <!-- Phones: shown last (after billing). Desktop: stacked right under the card above, no gap -->
        <section class="max-lg:order-last">
            <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-muted">
                <span class="inline-flex items-center gap-1.5"><span class="text-turq-ink"><svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 5 6v5.5c0 4.4 3 8.2 7 9.5 4-1.3 7-5.1 7-9.5V6l-7-3z"/><path d="m9 12 2.2 2.2L15.5 10"/></svg></span> <?= $collector->translate('co_100_night_money_back', '30-day money-back') ?></span>
                <span class="inline-flex items-center gap-1.5"><span class="text-turq-ink"><svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12V4h8l9.5 9.5-8 8L3 12z"/><circle cx="7.5" cy="8" r="1.5"/></svg></span> <?= $collector->translate('co_launch_price_locked', 'Launch price locked') ?></span>
            </div>
            <button type="button" id="submitForm" class="group mt-4 w-full rounded-full bg-turq px-6 py-4 text-base sm:text-lg font-semibold text-ink shadow-[0_14px_30px_-12px_rgba(29,209,161,.8)] hover:bg-turq-dark transition">
                <?= $collector->translate('co_get', 'Get') ?> <?= $maxSave ?><?= $collector->translate('co_discount_order_now', '% Discount &amp; Order Now') ?> <span aria-hidden="true" class="inline-block transition group-hover:translate-x-1">→</span>
            </button>
            <div class="mt-4 text-center">
                <p class="inline-flex items-center gap-2 text-sm font-semibold"><span class="text-turq-ink"><svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4.5" y="10.5" width="15" height="10" rx="2.2"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3M12 14.5v2.5"/></svg></span> <span><span class="text-turq-ink"><?= $collector->translate('co_ssl', 'SSL') ?></span> <?= $collector->translate('co_secured_payment', 'secured payment') ?></span></p>
                <p class="mt-1 text-xs text-muted"><?= $collector->translate('co_your_information_is_protected', 'Your information is protected by 256-bit SSL encryption.') ?></p>
            </div>
        </section>
        </div>

        <section class="card p-5 sm:p-7 lg:col-start-1 lg:row-start-2">
            <!-- Express checkout sits right above billing, like shopmelara-apex (_paymentMethod.php): integrated.js mounts the Stripe wallet buttons
                 (Google Pay / Apple Pay / Link) into #express-checkout-stripe and opens PayPal from .paypal-btn -->
            <div>
                <p class="text-center text-sm font-semibold"><?= $collector->translate('express_checkout', 'Express checkout') ?></p>
                <div class="mt-3 space-y-3">
                    <div id="express-checkout-stripe" class="w-full"></div>
                    <?php if ($OfferApi->currencySymbol != 'MOP$'): ?>
                    <button type="button" id="paypal-btn" class="paypal-btn w-full grid place-items-center rounded-lg bg-[#ffc439] hover:brightness-95 transition h-12" aria-label="<?= $collector->translate('pay_with_paypal', 'Pay with PayPal') ?>">
                        <img src="assets/img/paypal-logo.png?t=<?= $OfferApi->assetVersion('assets/img/paypal-logo.png') ?>" alt="PayPal" class="h-6 w-auto" width="353" height="90" loading="lazy">
                    </button>
                    <?php endif; ?>
                </div>
                <div class="mt-5 mb-6 flex items-center gap-3 text-xs font-medium text-muted">
                    <span class="flex-1 border-t border-sand"></span><?= $collector->translate('pay_or_card_2', 'Or pay with credit card') ?><span class="flex-1 border-t border-sand"></span>
                </div>
            </div>

            <h2 class="flex items-center gap-3 text-lg font-bold"><span class="grid place-items-center w-9 h-9 rounded-xl bg-turq-soft text-turq-ink"><svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5.5" width="18" height="13" rx="2.5"/><path d="M3 10h18M7 15h4"/></svg></span><span><span class="text-muted font-medium"><?= $collector->translate('step_word_4', 'Step') ?> 4:</span> <?= $collector->translate('step_4_title', 'Billing information') ?></span></h2>
            <div class="mt-4 flex items-center gap-3 rounded-2xl border-2 border-turq bg-turq-soft/30 px-4 py-3">
                <span class="w-5 h-5 shrink-0 rounded-full border-2 border-turq bg-turq shadow-[inset_0_0_0_3px_#fff]"></span>
                <span class="hidden sm:block text-sm font-medium shrink-0"><?= $collector->translate('co_credit_or_debit_card', 'Credit or debit card') ?></span>
                <!-- Card logos: same images as the shopmelara-apex checkout (_paymentMethod.php); Japanese puts JCB first -->
                <?php $ccImg = $OfferApi->targetLanguage === 'ja' ? 'assets/img/cards/cc-payments-ja.png' : 'assets/img/cards/cc-payments.png'; ?>
                <img src="<?= $ccImg ?>?t=<?= $OfferApi->assetVersion($ccImg) ?>" alt="Visa, Mastercard, American Express, Discover, JCB" width="1295" height="142" class="min-w-0 w-full max-w-[240px] h-auto ms-auto">
            </div>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-5 gap-4">
                <div class="sm:col-span-5"><label class="label" for="cc_number"><?= $collector->translate('co_card_number', 'Card number') ?></label><div><input class="field tracking-wider" id="cc_number" name="number" inputmode="numeric" autocomplete="off" placeholder="1234 5678 9012 3456" maxlength="19" required></div></div>
                <div class="sm:col-span-3"><label class="label" for="expiry_date"><?= $collector->translate('co_expiry_date', 'Expiry date') ?></label><div class="has-icon"><span class="field-icon"><svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/></svg></span><input class="field" id="expiry_date" name="due_date" inputmode="numeric" autocomplete="off" placeholder="MM / YY" maxlength="7" required></div></div>
                <div class="sm:col-span-2"><label class="label" for="cc_cvv"><?= $collector->translate('co_cvv_full', 'CVV/CVC/CSC') ?></label><div class="has-icon"><span class="field-icon"><svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4.5" y="10.5" width="15" height="10" rx="2.2"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3M12 14.5v2.5"/></svg></span><input class="field" id="cc_cvv" name="cvv" inputmode="numeric" autocomplete="off" placeholder="<?= $collector->translate('co_cvv_ph', 'XXX or XXXX') ?>" maxlength="4" required></div></div>
            </div>
        </section>

    </form>
</main>

<footer class="border-t border-sand bg-white">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-muted">
        <img src="assets/img/logo.svg" alt="Melara" class="h-8 w-auto" width="190" height="49" loading="lazy">
        <ul class="grid grid-cols-2 gap-x-8 gap-y-3 text-center sm:flex sm:flex-wrap sm:justify-center sm:gap-5"><!-- 2x2 on phones so no link is left alone on a second line -->
            <li><a href="#shipping" data-policy="shipping" class="hover:text-ink transition"><?= $collector->translate('co_shipping_link', 'Shipping') ?></a></li>
            <li><a href="#returns" data-policy="returns" class="hover:text-ink transition"><?= $collector->translate('co_returns_refunds', 'Returns &amp; refunds') ?></a></li>
            <li><a href="#privacy" data-policy="privacy" class="hover:text-ink transition"><?= $collector->translate('co_privacy_policy', 'Privacy policy') ?></a></li>
            <li><a href="#contact" data-policy="contact" class="hover:text-ink transition"><?= $collector->translate('co_contact_us', 'Contact us') ?></a></li>
        </ul>
        <?= languageSelector('locked') ?>
    </div>
</footer>

<!-- Policy modal: same content as the landing page footer modal -->
<?php
// Policy modal: content matches shopmelara-apex (Terms §6 Shipping, Returns & Refunds, Privacy, Contact). Company details come from the CRM config.
$corp         = $OfferApi->corporateName;
$supportEmail = $OfferApi->companyEmailAddress;
$ordersEmail  = $OfferApi->companyEmailAddress1;
$address      = $OfferApi->companyAddress;
?>
<dialog id="policyModal" class="policy-modal" aria-labelledby="policyTitle">
    <div class="flex flex-col max-h-[inherit]">
        <!-- Header: title, close, tabs -->
        <div class="shrink-0 border-b border-sand bg-white/95 backdrop-blur-sm px-5 sm:px-8 pt-3 sm:pt-6">
            <span class="sm:hidden mx-auto mb-3 block h-1.5 w-10 rounded-full bg-sand" aria-hidden="true"></span>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-[.2em] text-turq-ink"><?= $collector->translate('pol_eyebrow', 'Help & policies') ?></p>
                    <h2 id="policyTitle" class="mt-1 font-serif text-2xl sm:text-3xl tracking-tight"><?= $collector->translate('pol_heading', 'We\'re here to help') ?></h2>
                </div>
                <button type="button" data-policy-close class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-linen text-ink hover:bg-sand transition" aria-label="<?= $collector->translate('pol_close', 'Close') ?>">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>
            <div class="-mx-5 sm:mx-0 mt-5 overflow-x-auto px-5 sm:px-0 [scrollbar-width:none]">
                <div class="inline-flex gap-1 rounded-full bg-linen p-1 mb-4" role="tablist" aria-label="<?= $collector->translate('pol_tabs_label', 'Policies') ?>">
                    <button type="button" role="tab" id="tab-shipping" aria-controls="panel-shipping" data-policy-tab="shipping" class="policy-tab"><?= $collector->translate('pol_tab_shipping', 'Shipping') ?></button>
                    <button type="button" role="tab" id="tab-returns" aria-controls="panel-returns" data-policy-tab="returns" class="policy-tab"><?= $collector->translate('pol_tab_returns', 'Returns') ?></button>
                    <button type="button" role="tab" id="tab-privacy" aria-controls="panel-privacy" data-policy-tab="privacy" class="policy-tab"><?= $collector->translate('pol_tab_privacy', 'Privacy') ?></button>
                    <button type="button" role="tab" id="tab-contact" aria-controls="panel-contact" data-policy-tab="contact" class="policy-tab"><?= $collector->translate('pol_tab_contact', 'Contact') ?></button>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="policy-body flex-1 overflow-y-auto overscroll-contain px-5 sm:px-8 py-6 sm:py-8 text-[15px] leading-relaxed text-muted">

            <!-- Shipping: visitors in Taiwan get 'delivered in 7 days', every other country gets 'within 14 days'.
                 Country comes from the visitor's IP (Cloudflare header, as in OfferApi::getUserCountry), not from sessionCountry,
                 which only holds the allowed checkout countries (US for now). -->
            <?php $shipTw = strtoupper($_SERVER['HTTP_CF_IPCOUNTRY'] ?? $_SERVER['GEOIP_COUNTRY_CODE'] ?? $OfferApi->sessionCountry ?? '') === 'TW'; ?>
            <section id="panel-shipping" role="tabpanel" aria-labelledby="tab-shipping" data-policy-panel="shipping">
                <h3 class="font-serif text-xl text-ink"><?= $collector->translate('pol_ship_title', 'Shipping') ?></h3>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-turq-soft/60 p-4">
                        <p class="font-serif text-2xl text-ink"><?= $shipTw ? $collector->translate('pol_ship_tw_stat', '7 days') : $collector->translate('pol_ship14_stat', '14 days') ?></p>
                        <p class="text-xs text-muted"><?= $shipTw ? $collector->translate('pol_ship_tw_stat_d', 'Delivered in 7 days') : $collector->translate('pol_ship14_stat_d', 'Delivered within 14 days') ?></p>
                    </div>
                    <div class="rounded-2xl bg-linen p-4">
                        <p class="font-serif text-2xl text-ink"><?= $collector->translate('pol_ship_stat_2', 'SecureShip') ?></p>
                        <p class="text-xs text-muted"><?= $collector->translate('pol_ship_stat_2_d', 'Optional cover for lost packages') ?></p>
                    </div>
                </div>
                <p class="mt-5"><?= $shipTw ? $collector->translate('pol_ship_tw_p1', 'Your order will be delivered <b>in 7 days</b>. Shipping charges for your order will be calculated and displayed at checkout.') : $collector->translate('pol_ship14_p1', 'Your order will be delivered <b>within 14 days</b>, although delays may occasionally occur. Shipping charges for your order will be calculated and displayed at checkout.') ?></p>
                <p class="mt-3"><?= $collector->translate('pol_ship_p2', 'We are not responsible for any customs or taxes applied to your order. All fees imposed during or after shipping (tariffs, taxes, etc.) are your responsibility.') ?></p>
                <ul class="policy-list mt-4">
                    <li><?= $collector->translate('pol_ship_li_1', '<b>Lost packages</b> are only covered if you purchased the <b>SecureShip add-on</b> at checkout.') ?></li>
                    <li><?= $collector->translate('pol_ship_li_2', '<b>Damaged items</b> may be exchanged if reported promptly.') ?></li>
                    <li><?= $collector->translate('pol_ship_li_3', 'All other liability for shipping issues is excluded.') ?></li>
                </ul>
                <p class="mt-4 rounded-2xl border border-sand p-4 text-sm"><?= $collector->translate('pol_ship_note', 'We recommend retaining packaging materials and damaged goods until a claim or exchange process is completed.') ?></p>
            </section>

            <!-- Returns -->
            <section id="panel-returns" role="tabpanel" aria-labelledby="tab-returns" data-policy-panel="returns" hidden>
                <h3 class="font-serif text-xl text-ink"><?= $collector->translate('pol_ret_title', 'Returns & Refunds Policy') ?></h3>
                <p class="mt-3"><?= $collector->translate('pol_ret_p1', '<b>We offer refunds within 30 days of purchase</b>. To request a refund, please contact our Customer Service team within 30 days of the original purchase date. After this 30-day period, all sales are final and no refunds will be issued.') ?></p>

                <h4 class="mt-6 font-semibold text-ink"><?= $collector->translate('pol_ret_h_returns', 'Returns') ?></h4>
                <ul class="policy-list mt-2">
                    <li><?= $collector->translate('pol_ret_li_1', 'Our Return Policy lasts <b>30 days</b> from delivery.') ?></li>
                </ul>

                <h4 class="mt-6 font-semibold text-ink"><?= $collector->translate('pol_ret_h_process', 'Process') ?></h4>
                <ol class="policy-steps mt-3">
                    <li><?= $collector->translate('pol_ret_step_1', "Contact us at <a href='mailto:{$supportEmail}'>{$supportEmail}</a> to confirm the return address and provide a tracking number.") ?></li>
                    <li><?= $collector->translate('pol_ret_step_2', 'Once received and inspected, we will notify you of approval or rejection.') ?></li>
                    <li><?= $collector->translate('pol_ret_step_3', 'Approved returns will be processed within <b>30 days</b>.') ?></li>
                </ol>

                <h4 class="mt-6 font-semibold text-ink"><?= $collector->translate('pol_ret_h_refunds', 'Refunds & Exchanges') ?></h4>
                <ul class="policy-list mt-2">
                    <li><?= $collector->translate('pol_ret_ref_1', 'We offer <b>refunds to the original payment method or exchanges for the same item.</b>') ?></li>
                    <li><?= $collector->translate('pol_ret_ref_2', 'Store credits are <b>not offered.</b>') ?></li>
                    <li><?= $collector->translate('pol_ret_ref_3', 'Refunds to credit cards may take up to 5 to 7 business days to appear, depending on your card issuer.') ?></li>
                    <li><?= $collector->translate('pol_ret_ref_4', 'Refunds by bank transfer will be completed within 48 hours after receipt of your bank details, in the original order currency.') ?></li>
                </ul>

                <h4 class="mt-6 font-semibold text-ink"><?= $collector->translate('pol_ret_h_shipping', 'Shipping Costs') ?></h4>
                <ul class="policy-list mt-2">
                    <li><?= $collector->translate('pol_ret_ship_1', 'You are responsible for return shipping costs.') ?></li>
                    <li><?= $collector->translate('pol_ret_ship_2', 'Shipping costs are non-refundable.') ?></li>
                    <li><?= $collector->translate('pol_ret_ship_3', 'For items over US$75, we recommend using a trackable shipping service or purchasing shipping insurance.') ?></li>
                </ul>

                <div class="mt-6 rounded-2xl bg-linen p-4">
                    <p class="text-xs font-medium uppercase tracking-[.18em] text-muted"><?= $collector->translate('pol_ret_h_address', 'Return address') ?></p>
                    <p class="mt-1 font-medium text-ink"><?= htmlspecialchars($address) ?></p>
                </div>
            </section>

            <!-- Privacy -->
            <section id="panel-privacy" role="tabpanel" aria-labelledby="tab-privacy" data-policy-panel="privacy" hidden>
                <h3 class="font-serif text-xl text-ink"><?= $collector->translate('pol_priv_title', 'Privacy Policy') ?></h3>
                <p class="mt-3"><?= $collector->translate('pol_priv_p1', 'By using our Site, you consent to the collection, use, and transfer of your personal information as described in this Privacy Policy.') ?></p>
                <p class="mt-3"><?= $collector->translate('pol_priv_p2', 'For residents of the European Union: In order to comply with the requirements of the General Data Protection Regulation (GDPR), this Privacy Policy explains the legal basis on which we process your personal data and provides other information required under GDPR.') ?></p>

                <div class="mt-5 divide-y divide-sand rounded-2xl border border-sand">
                    <details class="policy-acc" open>
                        <summary><?= $collector->translate('pol_priv_s1', 'Information we collect and how we use it') ?></summary>
                        <div>
                            <p><?= $collector->translate('pol_priv_s1_p1', 'We collect personal data in several ways and for a variety of purposes, including:') ?></p>
                            <ul class="policy-list mt-2">
                                <li><?= $collector->translate('pol_priv_s1_li1', '<b>When you make a purchase:</b> As part of the buying and selling process, we collect the personal information you provide such as your name, billing and shipping address, phone number, and email address.') ?></li>
                                <li><?= $collector->translate('pol_priv_s1_li2', '<b>When you browse our Site:</b> We automatically receive your computer’s internet protocol (IP) address and information about your browser and operating system.') ?></li>
                                <li><?= $collector->translate('pol_priv_s1_li3', "<b>Email marketing (where legally permitted):</b> With your consent, we may send you emails about {$corp}, our products, promotions, and updates. Consent may be established through actions such as subscribing to our newsletter, entering your email during checkout (without opting out), or participating in events, surveys, or contests where you are notified of marketing communications.") ?></li>
                            </ul>
                            <p class="mt-3"><?= $collector->translate('pol_priv_s1_p2', 'All marketing emails will include a clear “unsubscribe” option. Once you opt out, we will stop sending you marketing emails. Transactional and legally required communications (such as receipts, order confirmations, or policy updates) are not affected by marketing preferences.') ?></p>
                            <p class="mt-3"><?= $collector->translate('pol_priv_s1_p3', 'We may use your personal information to:') ?></p>
                            <ul class="policy-list mt-2">
                                <li><?= $collector->translate('pol_priv_use_1', 'Communicate and build a relationship with you') ?></li>
                                <li><?= $collector->translate('pol_priv_use_2', 'Personalize your shopping experience and recommendations') ?></li>
                                <li><?= $collector->translate('pol_priv_use_3', 'Process and fulfill transactions and requests') ?></li>
                                <li><?= $collector->translate('pol_priv_use_4', 'Verify your account or authority to use services') ?></li>
                                <li><?= $collector->translate('pol_priv_use_5', 'Conduct market research and improve our Site and services') ?></li>
                                <li><?= $collector->translate('pol_priv_use_6', 'Send marketing materials and targeted advertising (where permitted)') ?></li>
                                <li><?= $collector->translate('pol_priv_use_7', 'Detect, deter, and prevent fraud or illegal activity') ?></li>
                                <li><?= $collector->translate('pol_priv_use_8', 'Comply with legal requirements') ?></li>
                            </ul>
                            <p class="mt-3"><?= $collector->translate('pol_priv_s1_p4', "If you wish to stop receiving marketing communications, you may opt out at any time by clicking “unsubscribe” in our emails or contacting us at <a href='mailto:{$supportEmail}'>{$supportEmail}</a>.") ?></p>
                            <p class="mt-3"><?= $collector->translate('pol_priv_s1_p5', 'We may release personal data if required by law, or when we believe it is necessary to comply with government requests, enforce our policies, or protect our rights and the safety of others.') ?></p>
                        </div>
                    </details>
                    <details class="policy-acc">
                        <summary><?= $collector->translate('pol_priv_s2', 'Consent as basis') ?></summary>
                        <div>
                            <p><?= $collector->translate('pol_priv_s2_p1', '<b>How do we obtain your consent?</b><br>By providing personal information to complete a transaction, verify a payment, place an order, arrange a delivery, or request a return, you consent to our collecting and using it for that purpose. If we ask for information for any secondary purpose (such as marketing), we will either ask you directly for explicit consent or provide you with an option to decline.') ?></p>
                            <p class="mt-3"><?= $collector->translate('pol_priv_s2_p2', "<b>How do you withdraw your consent?</b><br>If you change your mind, you may withdraw your consent with future effect at any time by contacting us at <a href='mailto:{$supportEmail}'>{$supportEmail}</a> or by using the unsubscribe instructions included in our communications. Withdrawing consent will not affect processing carried out before withdrawal.") ?></p>
                            <p class="mt-3"><?= $collector->translate('pol_priv_s2_p3', 'Certain uses of your data may not require consent, including:') ?></p>
                            <ul class="policy-list mt-2">
                                <li><?= $collector->translate('pol_priv_s2_li1', 'To fulfill our contractual obligations to you') ?></li>
                                <li><?= $collector->translate('pol_priv_s2_li2', 'To comply with legal obligations or investigations') ?></li>
                                <li><?= $collector->translate('pol_priv_s2_li3', 'Where we have a legitimate business interest, except where your rights override such interests') ?></li>
                            </ul>
                            <p class="mt-3"><?= $collector->translate('pol_priv_s2_p4', "You may request to view, update, or delete your personal information at any time by emailing <a href='mailto:{$supportEmail}'>{$supportEmail}</a>. We will not retain your data longer than necessary for the purposes collected, except as required by law.") ?></p>
                        </div>
                    </details>
                    <details class="policy-acc">
                        <summary><?= $collector->translate('pol_priv_s3', 'Disclosure') ?></summary>
                        <div>
                            <p><?= $collector->translate('pol_priv_s3_p1', 'We may share your personal data as follows:') ?></p>
                            <ul class="policy-list mt-2">
                                <li><?= $collector->translate('pol_priv_s3_li1', '<b>Affiliates and partners:</b> For service delivery, product improvement, or marketing.') ?></li>
                                <li><?= $collector->translate('pol_priv_s3_li2', '<b>Vendors and service providers:</b> Including payment processors, shipping providers, and marketing platforms.') ?></li>
                                <li><?= $collector->translate('pol_priv_s3_li3', '<b>Advertising and analytics partners:</b> With your consent, for advertising and measurement purposes.') ?></li>
                                <li><?= $collector->translate('pol_priv_s3_li4', '<b>Legal compliance and public safety:</b> To comply with laws, regulations, investigations, or to protect our rights and property.') ?></li>
                            </ul>
                        </div>
                    </details>
                    <details class="policy-acc">
                        <summary><?= $collector->translate('pol_priv_s4', 'Third-party services') ?></summary>
                        <div>
                            <p><?= $collector->translate('pol_priv_s4_p1', "Third-party providers used by {$corp} will only collect, use, and disclose your information as needed to perform their services. For example, payment processors must comply with PCI-DSS security standards to protect payment data.") ?></p>
                            <p class="mt-3"><?= $collector->translate('pol_priv_s4_p2', 'These providers have their own privacy policies, and your data may be subject to the laws of their jurisdictions. Once you leave our Site or interact with a third-party website or application, our Privacy Policy no longer applies.') ?></p>
                        </div>
                    </details>
                    <details class="policy-acc">
                        <summary><?= $collector->translate('pol_priv_s5', 'Security') ?></summary>
                        <div><p><?= $collector->translate('pol_priv_s5_p1', 'We implement commercially reasonable security measures to protect your personal data from loss, misuse, unauthorized access, disclosure, or alteration. Sensitive information such as credit card details is encrypted using SSL technology and stored with AES-256 encryption. However, no method of online transmission or electronic storage is completely secure, and we cannot guarantee absolute security.') ?></p></div>
                    </details>
                    <details class="policy-acc">
                        <summary><?= $collector->translate('pol_priv_s6', 'Data breach notification') ?></summary>
                        <div><p><?= $collector->translate('pol_priv_s6_p1', 'If we become aware of a personal data breach that may pose a high risk to your rights and freedoms, we will notify you and relevant authorities within 72 hours, where feasible. This notification will include details of the breach, its potential impact, and steps taken to mitigate risks.') ?></p></div>
                    </details>
                    <details class="policy-acc">
                        <summary><?= $collector->translate('pol_priv_s7', 'International data transfers') ?></summary>
                        <div><p><?= $collector->translate('pol_priv_s7_p1', 'If your personal data originates from the European Economic Area and is transferred outside of it, we will ensure adequate protections are in place as required by applicable privacy laws, such as standard contractual clauses or equivalent safeguards.') ?></p></div>
                    </details>
                    <details class="policy-acc">
                        <summary><?= $collector->translate('pol_priv_s8', 'Age of consent') ?></summary>
                        <div><p><?= $collector->translate('pol_priv_s8_p1', 'By using this Site, you confirm that you are at least the age of majority in your state or province of residence, or that you are the age of majority and have given us consent to allow your minor dependents to use this Site.') ?></p></div>
                    </details>
                    <details class="policy-acc">
                        <summary><?= $collector->translate('pol_priv_s9', 'Changes to this Privacy Policy') ?></summary>
                        <div>
                            <p><?= $collector->translate('pol_priv_s9_p1', 'We reserve the right to update this Privacy Policy at any time. Changes take effect immediately once posted on our website. If material changes are made, we will provide notice on our Site. Your continued use of our Site after such changes indicates acceptance of the updated Privacy Policy.') ?></p>
                            <p class="mt-3"><?= $collector->translate('pol_priv_s9_p2', "If {$corp} is acquired or merged with another company, your personal data may be shared with the acquiring or merging entity.") ?></p>
                        </div>
                    </details>
                    <details class="policy-acc">
                        <summary><?= $collector->translate('pol_priv_s10', 'Contact information') ?></summary>
                        <div>
                            <p><?= $collector->translate('pol_priv_s10_p1', 'If you would like to access, correct, amend, or delete any personal information we hold about you, register a complaint, or request more information, please contact us:') ?></p>
                            <p class="mt-3"><?= $collector->translate('pol_priv_s10_phone', "Phone: <b>{$OfferApi->companyPhoneNumber}</b>") ?><br><?= $collector->translate('pol_priv_s10_email', "Email: <b>{$supportEmail}</b>") ?></p>
                            <p class="mt-3"><?= $collector->translate('pol_priv_s10_p2', 'We have a <b>Data Protection Officer</b> responsible for privacy matters. The Data Protection Officer can be reached at the above address and email.') ?></p>
                        </div>
                    </details>
                </div>
            </section>

            <!-- Contact -->
            <section id="panel-contact" role="tabpanel" aria-labelledby="tab-contact" data-policy-panel="contact" hidden>
                <h3 class="font-serif text-xl text-ink"><?= $collector->translate('pol_con_title', 'Need help?') ?></h3>
                <p class="mt-3"><?= $collector->translate('pol_con_p1', "If you are looking for more information about <b>{$corp}</b> or need assistance with your order, our customer support team is here to help.") ?></p>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <a href="mailto:<?= htmlspecialchars($supportEmail) ?>" class="group rounded-2xl border border-sand p-4 hover:border-turq hover:bg-turq-soft/40 transition">
                        <span class="grid h-10 w-10 place-items-center rounded-full bg-turq-soft text-turq-ink"><svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m4 7 8 6 8-6"/></svg></span>
                        <span class="mt-3 block text-xs uppercase tracking-[.18em] text-muted"><?= $collector->translate('pol_con_general', 'General support') ?></span>
                        <span class="block font-semibold text-ink break-all"><?= htmlspecialchars($supportEmail) ?></span>
                    </a>
                    <a href="mailto:<?= htmlspecialchars($ordersEmail) ?>" class="group rounded-2xl border border-sand p-4 hover:border-turq hover:bg-turq-soft/40 transition">
                        <span class="grid h-10 w-10 place-items-center rounded-full bg-turq-soft text-turq-ink"><svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5z"/><path d="m3 7.5 9 4.5 9-4.5M12 12v9"/></svg></span>
                        <span class="mt-3 block text-xs uppercase tracking-[.18em] text-muted"><?= $collector->translate('pol_con_orders', 'Order inquiries') ?></span>
                        <span class="block font-semibold text-ink break-all"><?= htmlspecialchars($ordersEmail) ?></span>
                    </a>
                </div>
                <div class="mt-3 rounded-2xl bg-linen p-4">
                    <p class="text-xs uppercase tracking-[.18em] text-muted"><?= $collector->translate('pol_con_company', 'Company') ?></p>
                    <p class="mt-1 font-semibold text-ink"><?= htmlspecialchars($corp) ?></p>
                    <p class="text-sm"><?= htmlspecialchars($address) ?></p>
                </div>
            </section>
        </div>
    </div>
</dialog>

<script>window.MELARA_CHECKOUT = { currency: '<?= $OfferApi->currencySymbol ?: '$' ?>', secureShip: <?= $securePrice ?>, freeText: '<?= addslashes($freeText) ?>' };</script>
<script src="src/system/<?= $OfferApi->targetLanguage ?>.js?t=<?= $OfferApi->assetVersion('src/system/' . $OfferApi->targetLanguage . '.js') ?>"></script>
<script src="public/js/checkout.js?t=<?= $OfferApi->assetVersion('public/js/checkout.js') ?>"></script>
<?php /* Address helpers for the per-country shipping forms, same as shopmelara-apex (checkout_v1.php) */ ?>
<?php if (in_array($OfferApi->countryName, ["United States", "Greece", "Switzerland", "Portugal"])): ?>
<script src="src/common/js/address.google.autocomplete.min.js?t=<?= $OfferApi->assetVersion('src/common/js/address.google.autocomplete.min.js') ?>" defer></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDxvJBBYYmuMntWjTVCu1hNmnbiJmrC6Xw&callback=initAutocomplete&libraries=places&v=weekly<?= strtolower($userCountry ?? '') === 'pt' ? '&language=pt-PT' : '' ?>" defer></script>
<?php endif; ?>
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js" async></script>
<!-- Everflow SDK + funnel events (offer 84) -->
<script src="https://www.b04jdmd.com/scripts/main.js" defer></script>
</body>
</html>

<?php $collector->saveTranslation(); ?>
