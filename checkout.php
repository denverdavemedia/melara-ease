<?php
/* Melara Seat Cushion checkout. Pack prices, shipping, currency, SecureShip and countries come from the CRM campaign. */
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
    : $OfferApi->initProduct(productName: 'Melara Seat Cushion', orderData: $_POST);
$insurance = $OfferApi->initProductAddOn(productName: "Secure");
if ($mainProd) usort($mainProd->product, fn($a, $b) => $a->maxOrderQty <=> $b->maxOrderQty); // 1x, 2x, 3x, 4x

$secure      = $insurance->product[0] ?? null; // SecureShip, when the campaign has it
$securePrice = $secure ? (float) $secure->price : 0;
$selected    = in_array((int) ($_GET['pack'] ?? 0), [1, 2, 3, 4]) ? (int) $_GET['pack'] : 2; // ?pack= from the landing page
$maxSave     = round(max($percentage) * 100);
$firstPack   = $mainProd->product[0];

$product  = $collector->translate('product_name', 'Melara Seat Cushion');
$freeText = $collector->translate('co_free', 'Free');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout — <?= $product ?></title>
    <meta name="robots" content="noindex">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="src/common/js/intltelinput/css/intltelInput.css">
    <link rel="stylesheet" href="assets/css/checkout.css?t=<?= $OfferApi->assetVersion('assets/css/checkout.css') ?>">
    <link rel="stylesheet" href="public/css/app.css?t=<?= $OfferApi->assetVersion('public/css/app.css') ?>">
</head>
<body class="font-sans text-ink antialiased">
<?= $mainProd->hiddenInputs ?? '' ?>

<header class="bg-white border-b border-sand">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 h-16 flex items-center justify-between">
        <a href="index.php" class="shrink-0"><img src="assets/img/logo.svg" alt="Melara" class="h-9 w-auto" width="190" height="49"></a>
        <div class="flex items-center gap-3 sm:gap-5">
            <span class="inline-flex items-center gap-2 text-sm text-muted"><span class="text-turq-ink"><svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4.5" y="10.5" width="15" height="10" rx="2.2"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3M12 14.5v2.5"/></svg></span> <span class="hidden sm:inline"><?= $collector->translate('co_secure_checkout', 'Secure checkout') ?></span></span>
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
    <form id="checkout" novalidate class="grid gap-6 lg:grid-cols-[1.1fr_1fr] lg:grid-rows-[auto_1fr_auto] lg:items-start">

        <section class="card p-5 sm:p-7 lg:col-start-1 lg:row-start-1">
            <div class="flex items-start gap-4">
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
            <div class="mt-5 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 rounded-2xl border border-dashed border-turq/50 bg-turq-soft/40 px-4 py-3 text-sm">
                <span class="inline-flex items-center gap-2 font-medium"><span class="text-turq-ink"><svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span> <?= $collector->translate('co_your_discount_is_reserved', 'Your discount is reserved for:') ?></span>
                <span data-countdown class="font-semibold tabular-nums text-ink text-base">10:00</span>
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
                            <span class="absolute -top-3 left-4 rounded-full px-3 py-0.5 text-[11px] font-semibold uppercase tracking-wide bg-turq text-ink"><?= $collector->translate('pack_2_badge', 'Most popular') ?></span>
                        <?php elseif ($qty == 4): ?>
                            <span class="absolute -top-3 left-4 rounded-full px-3 py-0.5 text-[11px] font-semibold uppercase tracking-wide bg-ink text-white"><?= $collector->translate('pack_4_badge', 'Best value') ?></span>
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
                <span class="grid place-items-center shrink-0 w-11 h-11 rounded-xl bg-white border border-sand text-turq-ink"><svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 5 6v5.5c0 4.4 3 8.2 7 9.5 4-1.3 7-5.1 7-9.5V6l-7-3z"/><path d="m9 12 2.2 2.2L15.5 10"/></svg></span>
                <span class="flex-1">
                    <span class="block font-semibold"><?= $collector->translate('co_yes_add', 'Yes, add') ?> <span class="text-turq-ink"><?= $collector->translate('co_secureship', 'SecureShip') ?></span> <?= $collector->translate('co_to_my_order', 'to my order') ?></span>
                    <span class="block mt-1 text-xs text-muted leading-relaxed"><?= $collector->translate('co_full_protection_against_damage', 'Full protection against damage, loss or porch-pirate theft. We reship or refund, no questions asked.') ?></span>
                    <span class="block mt-2 text-sm font-semibold text-turq-ink"><?= $OfferApi->formatNumber($securePrice) ?></span>
                </span>
            </label>
            <?php endif; ?>
        </section>

        <section class="card p-5 sm:p-7 lg:col-start-2 lg:row-start-1 lg:row-span-2">
            <div class="rounded-2xl bg-ink text-white p-5 bg-[radial-gradient(ellipse_at_top_right,rgba(29,209,161,.35),transparent_60%)]">
                <div class="flex items-center gap-4">
                    <div class="relative w-16 h-16 shrink-0 rounded-xl bg-white grid place-items-center">
                        <img src="assets/img/anatomy-assembled.webp?t=<?= $OfferApi->assetVersion('assets/img/anatomy-assembled.webp') ?>" alt="" class="w-14">
                        <span id="sumQty" class="absolute -top-2 -right-2 grid place-items-center w-6 h-6 rounded-full bg-turq text-ink text-xs font-bold"><?= $selected ?></span>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold"><?= $product ?> — <span id="sumLabel"><?= $sel['label'] ?></span></p>
                        <p class="text-xs text-white/60"><?= $collector->translate('co_free_spare_cover_on', 'Free spare cover on 2+ packs') ?></p>
                    </div>
                    <p id="sumPack" class="font-semibold"><?= $OfferApi->formatNumber($sel['row']->price) ?></p>
                </div>
                <dl class="mt-5 space-y-2 text-sm">
                    <div class="flex justify-between text-white/70"><dt><?= $collector->translate('co_retail_price', 'Retail price') ?></dt><dd><s id="sumCompare"><?= $OfferApi->formatNumber($sel['retail']) ?></s></dd></div>
                    <div class="flex justify-between text-white/70"><dt><?= $collector->translate('co_launch_discount', 'Launch discount') ?></dt><dd class="text-turq" id="sumDiscount">−<?= $OfferApi->formatNumber($sel['retail'] - $sel['row']->price) ?></dd></div>
                    <?php if ($secure): ?><div class="flex justify-between text-white/70" id="sumShipRow"><dt><?= $collector->translate('co_secureship_2', 'SecureShip') ?></dt><dd id="sumShip"><?= $OfferApi->formatNumber($securePrice) ?></dd></div><?php endif; ?>
                    <div class="flex justify-between text-white/70"><dt><?= $collector->translate('co_shipping', 'Shipping') ?></dt><dd class="<?= $sel['row']->shippingPrice > 0 ? '' : 'text-turq' ?>" id="sumShipping"><?= $sel['shippingText'] ?></dd></div>
                    <div class="flex justify-between border-t border-white/15 pt-3 text-base font-semibold"><dt><?= $collector->translate('co_total', 'Total') ?></dt><dd id="sumTotal"><?= $OfferApi->formatNumber($sel['row']->price + $sel['row']->shippingPrice + $securePrice) ?></dd></div>
                </dl>
            </div>
            <ul class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-2 text-sm font-medium">
                <li class="flex items-center gap-2"><span class="grid place-items-center w-5 h-5 rounded-full bg-turq text-ink"><svg viewBox="0 0 24 24" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg></span><?= $collector->translate('co2_feature_1', 'Lumbar support') ?></li>
                <li class="flex items-center gap-2"><span class="grid place-items-center w-5 h-5 rounded-full bg-turq text-ink"><svg viewBox="0 0 24 24" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg></span><?= $collector->translate('co_feature_2', 'Premium memory foam') ?></li>
                <li class="flex items-center gap-2"><span class="grid place-items-center w-5 h-5 rounded-full bg-turq text-ink"><svg viewBox="0 0 24 24" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg></span><?= $collector->translate('co2_feature_3', 'Fits most home chairs') ?></li>
            </ul>

            <div class="mt-8"><h2 class="flex items-center gap-3 text-lg font-bold"><span class="grid place-items-center w-9 h-9 rounded-xl bg-turq-soft text-turq-ink"><svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4.5 20.5c1.2-3.6 4-5.5 7.5-5.5s6.3 1.9 7.5 5.5"/></svg></span><span><span class="text-muted font-medium"><?= $collector->translate('step_word_2', 'Step') ?> 2:</span> <?= $collector->translate('step_2_title', 'Customer information') ?></span></h2></div>
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
                        <?php foreach ($countries as $c): ?><option value="<?= $c->countryCode ?>" <?= $c->countryCode === 'US' ? 'selected' : '' ?>><?= $collector->translate('country_name' . $c->countryCode, $c->countryName) ?></option><?php endforeach; ?>
                    </select>
                    </div>
                </div>
                <div class="sm:col-span-2"><label class="label" for="fields_address1"><?= $collector->translate('co_address_1', 'Address 1') ?></label><div class="has-icon"><span class="field-icon"><svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 10.5 12 4l8 6.5V20H4z"/><path d="M10 20v-5.5h4V20"/></svg></span><input class="field" id="fields_address1" name="address 1" autocomplete="address-line1" placeholder="Street address" required></div></div>
                <div class="sm:col-span-2"><label class="label" for="fields_address2"><?= $collector->translate('co_address_2', 'Address 2') ?> <span class="text-muted font-normal"><?= $collector->translate('co_optional', '(optional)') ?></span></label><div class="has-icon"><span class="field-icon"><svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="6" y="3" width="12" height="18" rx="1.5"/><path d="M14.5 12.5h.01"/></svg></span><input class="field" id="fields_address2" name="address 2" autocomplete="address-line2" placeholder="Apt, suite, unit, etc."></div></div>
                <div class="sm:col-span-2"><label class="label" for="fields_city"><?= $collector->translate('co_town_city', 'Town / City') ?></label><div class="has-icon"><span class="field-icon"><svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V9l5-3v15M10 21V4l9 4v13"/><path d="M13 10h3M13 13.5h3M13 17h3"/></svg></span><input class="field" id="fields_city" name="city" autocomplete="address-level2" placeholder="Town / City" required></div></div>
                <div><label class="label" for="state"><?= $collector->translate('co_state_province', 'State / Province') ?></label>
                    <div class="has-icon"><span class="field-icon"><svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 4-6 2.5v13.5l6-2.5 6 2.5 6-2.5V4l-6 2.5z"/><path d="M9 4v13.5M15 6.5V20"/></svg></span>
                    <select class="field" id="fields_state" name="state" autocomplete="address-level1" required>
                        <option value=""><?= $collector->translate('co_select_state', 'Select state') ?></option>
                    </select>
                    </div>
                </div>
                <div><label class="label" for="fields_zip"><?= $collector->translate('co_zip_postal_code', 'Zip / Postal code') ?></label><div class="has-icon"><span class="field-icon"><svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 9h14M5 15h14M10 4 8 20M16 4l-2 16"/></svg></span><input class="field" id="fields_zip" name="zip" autocomplete="postal-code" placeholder="Zip / Postal code" required></div></div>
            </div>
            <div class="mt-5 rounded-2xl border border-sand p-4">
                <p class="text-sm font-medium"><?= $collector->translate('co_shipping_2', 'Shipping') ?></p>
                <div class="mt-2 flex items-center justify-between text-sm">
                    <span class="inline-flex items-center gap-3"><span class="w-5 h-5 rounded-full border-2 border-turq bg-turq shadow-[inset_0_0_0_3px_#fff]"></span> <?= $collector->translate('co_standard_3_6_business', 'Standard (5–7 business days)') ?></span>
                    <span class="font-semibold text-turq-ink" id="sumShippingOpt"><?= $sel['shippingText'] ?></span>
                </div>
                <p class="mt-3 text-xs text-muted bg-cream rounded-lg px-3 py-2 inline-flex items-center gap-2"><span class="text-turq-ink"><svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/></svg></span> <?= $collector->translate('co_we_ship_ddp_all', 'We ship DDP: all taxes and duties are paid by us.') ?></p>
            </div>
        </section>

        <section class="card p-5 sm:p-7 lg:col-start-1 lg:row-start-2">
            <h2 class="flex items-center gap-3 text-lg font-bold"><span class="grid place-items-center w-9 h-9 rounded-xl bg-turq-soft text-turq-ink"><svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5.5" width="18" height="13" rx="2.5"/><path d="M3 10h18M7 15h4"/></svg></span><span><span class="text-muted font-medium"><?= $collector->translate('step_word_4', 'Step') ?> 4:</span> <?= $collector->translate('step_4_title', 'Billing information') ?></span></h2>
            <div class="mt-4 flex items-center gap-3 rounded-2xl border-2 border-turq bg-turq-soft/30 px-4 py-3">
                <span class="w-5 h-5 rounded-full border-2 border-turq bg-turq shadow-[inset_0_0_0_3px_#fff]"></span>
                <span class="text-sm font-medium flex-1"><?= $collector->translate('co_credit_or_debit_card', 'Credit or debit card') ?></span>
                <span class="flex gap-1.5">
                    <span class="grid place-items-center w-11 h-7 rounded-md bg-white border border-sand"><img src="assets/img/cards/visa.svg" alt="Visa" class="h-3 w-auto" width="37" height="12"></span>
                    <span class="grid place-items-center w-11 h-7 rounded-md bg-white border border-sand"><img src="assets/img/cards/mastercard.svg" alt="Mastercard" class="h-5 w-auto" width="32" height="20"></span>
                    <span class="grid place-items-center w-11 h-7 rounded-md bg-white border border-sand"><img src="assets/img/cards/amex.svg" alt="American Express" class="w-[36px] h-auto" width="36" height="10"></span>
                </span>
            </div>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-5 gap-4">
                <div class="sm:col-span-5"><label class="label" for="cc_number"><?= $collector->translate('co_card_number', 'Card number') ?></label><div><input class="field tracking-wider" id="cc_number" name="number" inputmode="numeric" autocomplete="off" placeholder="1234 5678 9012 3456" maxlength="19" required></div></div>
                <div class="sm:col-span-3"><label class="label" for="expiry_date"><?= $collector->translate('co_expiry_date', 'Expiry date') ?></label><div class="has-icon"><span class="field-icon"><svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/></svg></span><input class="field" id="expiry_date" name="due_date" inputmode="numeric" autocomplete="off" placeholder="MM / YY" maxlength="7" required></div></div>
                <div class="sm:col-span-2"><label class="label" for="cc_cvv"><?= $collector->translate('co_cvv', 'CVV') ?></label><div class="has-icon"><span class="field-icon"><svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4.5" y="10.5" width="15" height="10" rx="2.2"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3M12 14.5v2.5"/></svg></span><input class="field" id="cc_cvv" name="cvv" inputmode="numeric" autocomplete="off" placeholder="123" maxlength="4" required></div></div>
            </div>
        </section>

        <section class="lg:col-start-2 lg:row-start-3">
            <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-muted">
                <span class="inline-flex items-center gap-1.5"><span class="text-turq-ink"><svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 5 6v5.5c0 4.4 3 8.2 7 9.5 4-1.3 7-5.1 7-9.5V6l-7-3z"/><path d="m9 12 2.2 2.2L15.5 10"/></svg></span> <?= $collector->translate('co_100_night_money_back', '30-day money-back') ?></span>
                <span class="inline-flex items-center gap-1.5"><span class="text-turq-ink"><svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/></svg></span> <?= $collector->translate('co_ships_in_24_hours', 'Ships in 24 hours') ?></span>
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
    </form>
</main>

<footer class="border-t border-sand bg-white">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-muted">
        <img src="assets/img/logo.svg" alt="Melara" class="h-8 w-auto" width="190" height="49" loading="lazy">
        <ul class="flex flex-wrap justify-center gap-5">
            <li><a href="#" class="hover:text-ink transition"><?= $collector->translate('co_contact_us', 'Contact us') ?></a></li>
            <li><a href="#" class="hover:text-ink transition"><?= $collector->translate('co_terms_of_service', 'Terms of service') ?></a></li>
            <li><a href="#" class="hover:text-ink transition"><?= $collector->translate('co_privacy_policy', 'Privacy policy') ?></a></li>
            <li><a href="#" class="hover:text-ink transition"><?= $collector->translate('co_returns_refunds', 'Returns &amp; refunds') ?></a></li>
        </ul>
        <?= languageSelector('locked') ?>
    </div>
</footer>

<script>window.MELARA_CHECKOUT = { currency: '<?= $OfferApi->currencySymbol ?: '$' ?>', secureShip: <?= $securePrice ?>, freeText: '<?= addslashes($freeText) ?>' };</script>
<script src="src/system/<?= $OfferApi->targetLanguage ?>.js?t=<?= $OfferApi->assetVersion('src/system/' . $OfferApi->targetLanguage . '.js') ?>"></script>
<script src="public/js/checkout.js?t=<?= $OfferApi->assetVersion('public/js/checkout.js') ?>"></script>
</body>
</html>

<?php $collector->saveTranslation(); ?>
