<?php
/* ------------------------------------------------------------------
 * Melara Seat Cushion — checkout (design copy of shopmelara-apex checkout_v1 flow).
 *
 * Runs on the same backend as shopmelara-apex (integrated/setup.php + src/OfferApi):
 *  - Preview mode (no campaignId in integrated/config_us.php): prices below, nothing is submitted.
 *  - Live mode: packs, prices, currency, SecureShip and countries come from Konnektive via initProduct().
 * Field ids/names and pack data-* attributes match apex so its order script can be attached.
 * ------------------------------------------------------------------ */
include_once("integrated/setup.php");
include_once(BASEPATH . '/src/JsonTranslateCollector.php');
include_once(__DIR__ . '/includes/language-selector.php');

$collector = new JsonCollector(targetLanguage: $OfferApi->targetLanguage, pageName: "checkout");
function t($key, $text) {
    static $cache = [];
    global $collector;
    return $cache[$key] ??= $collector->translate($key, $text);
}

$brand    = 'Melara';
$product  = t('product_name', 'Melara Seat Cushion');
$currency = '$';
$logo     = 'assets/img/logo.svg';
$render   = 'assets/img/anatomy-assembled.webp'; // transparent product render

// Display copy per pack size (labels are ours; prices come from the CRM when live)
$packCopy = [
    1 => ['label' => t('pack_1_label', 'Just me'),     'badge' => null],
    2 => ['label' => t('pack_2_label', 'Home & work'), 'badge' => t('pack_2_badge', 'Most popular')],
    3 => ['label' => t('pack_3_label', 'Every chair'), 'badge' => null],
    4 => ['label' => t('pack_4_label', 'The team'),    'badge' => t('pack_4_badge', 'Best value')],
];
// Discount tiers per pack size, same as shopmelara-apex checkout.php (retail = price / (1 - pct))
$discountTiers = [1 => 0.55, 2 => 0.62, 3 => 0.65, 4 => 0.70];

if (OFFER_LIVE) {
    $mainProd  = (isset($_POST['finalizeTransaction']) && $_POST['finalizeTransaction'] == 1)
        ? null
        : $OfferApi->initProduct(productName: $configs->mainProductName, orderData: $_POST);
    $insurance = $OfferApi->initProductAddOn(productName: $configs->addOnProductName);
    $currency  = $OfferApi->currencySymbol ?: $currency;
    $packs = [];
    foreach ($mainProd->product as $row) {
        $q = (int) $row->maxOrderQty;
        if (!isset($packCopy[$q])) continue;
        $packs[$q] = $packCopy[$q] + [
            'price'    => (float) $row->price,
            'compare'  => round($row->price / (1 - ($discountTiers[$q] ?? 0.5))),
            'shipping' => (float) $row->shippingPrice,
            'crm'      => $row,
        ];
    }
    ksort($packs);
    // SecureShip only shows when the campaign has an add-on product matching addOnProductName
    $secureCrm  = $insurance->product[0] ?? null;
    $secureShip = $secureCrm ? (float) $secureCrm->price : 0;
    $countries  = [];
    foreach ($OfferApi->countries as $c) $countries[$c->countryCode] = t('country_name' . $c->countryCode, $c->countryName);
} else {
    // Preview prices (match index.php)
    $packs = [
        1 => $packCopy[1] + ['price' => 49,  'compare' => 109, 'shipping' => 0, 'crm' => null],
        2 => $packCopy[2] + ['price' => 89,  'compare' => 234, 'shipping' => 0, 'crm' => null],
        3 => $packCopy[3] + ['price' => 119, 'compare' => 340, 'shipping' => 0, 'crm' => null],
        4 => $packCopy[4] + ['price' => 139, 'compare' => 463, 'shipping' => 0, 'crm' => null],
    ];
    $secureShip = 4.99;
    $secureCrm  = null;
    $countries = ['US' => 'United States', 'CA' => 'Canada', 'GB' => 'United Kingdom', 'AU' => 'Australia', 'NZ' => 'New Zealand', 'IE' => 'Ireland', 'SG' => 'Singapore', 'MY' => 'Malaysia', 'PH' => 'Philippines', 'HK' => 'Hong Kong', 'JP' => 'Japan', 'DE' => 'Germany', 'FR' => 'France'];
}
$selected = isset($_GET['pack'], $packs[(int) $_GET['pack']]) ? (int) $_GET['pack'] : (isset($packs[2]) ? 2 : array_key_first($packs));
$maxSave  = max(array_map(fn($pk) => round((1 - $pk['price'] / $pk['compare']) * 100), $packs));
$usStates  = ['Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'];

function money($n) { global $currency; return $currency . number_format($n, $n == floor($n) ? 0 : 2); }
// Shipping line: "Free" or the campaign's shipping price for the pack
function shippingText($n) { return $n > 0 ? money($n) : t('co_free', 'Free'); }
function asset($src) { return $src . '?v=' . @filemtime(__DIR__ . '/' . $src); }

// Line icons (24×24, stroke) reused from the landing page
$icons = [
    'check'  => '<path d="m5 12 5 5 9-10"/>',
    'lock'   => '<rect x="4.5" y="10.5" width="15" height="10" rx="2.2"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3M12 14.5v2.5"/>',
    'user'   => '<circle cx="12" cy="8" r="4"/><path d="M4.5 20.5c1.2-3.6 4-5.5 7.5-5.5s6.3 1.9 7.5 5.5"/>',
    'pin'    => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
    'card'   => '<rect x="3" y="5.5" width="18" height="13" rx="2.5"/><path d="M3 10h18M7 15h4"/>',
    'box'    => '<path d="M4 8.5 12 4l8 4.5v7L12 20l-8-4.5z"/><path d="M4 8.5l8 4.5 8-4.5M12 13v7"/>',
    'shield' => '<path d="M12 3 5 6v5.5c0 4.4 3 8.2 7 9.5 4-1.3 7-5.1 7-9.5V6l-7-3z"/><path d="m9 12 2.2 2.2L15.5 10"/>',
    'truck'  => '<path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/>',
    'tag'    => '<path d="M3 12V4h8l9.5 9.5-8 8L3 12z"/><circle cx="7.5" cy="8" r="1.5"/>',
    'clock'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    'mail'   => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m4 7 8 6 8-6"/>',
    'phone'  => '<path d="M5 4h3.5l1.7 4.2-2.2 1.4a11 11 0 0 0 6.4 6.4l1.4-2.2L20 15.5V19a1.5 1.5 0 0 1-1.6 1.5C10.6 20 4 13.4 3.5 5.6A1.5 1.5 0 0 1 5 4z"/>',
    'globe'  => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3z"/>',
    'home'   => '<path d="M4 10.5 12 4l8 6.5V20H4z"/><path d="M10 20v-5.5h4V20"/>',
    'door'   => '<rect x="6" y="3" width="12" height="18" rx="1.5"/><path d="M14.5 12.5h.01"/>',
    'city'   => '<path d="M3 21h18M5 21V9l5-3v15M10 21V4l9 4v13"/><path d="M13 10h3M13 13.5h3M13 17h3"/>',
    'map'    => '<path d="m9 4-6 2.5v13.5l6-2.5 6 2.5 6-2.5V4l-6 2.5z"/><path d="M9 4v13.5M15 6.5V20"/>',
    'hash'   => '<path d="M5 9h14M5 15h14M10 4 8 20M16 4l-2 16"/>',
    'calendar' => '<rect x="3.5" y="5" width="17" height="15" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
];
function icon($name, $class = 'w-5 h-5') {
    global $icons;
    return '<svg viewBox="0 0 24 24" class="' . $class . '" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icons[$name] . '</svg>';
}
// Leading icon inside an input (placed before the field, inside a .has-icon wrapper)
function fieldIcon($name) {
    return '<span class="field-icon">' . icon($name, 'w-[18px] h-[18px]') . '</span>';
}
function step($n, $iconName, $title) {
    return '<h2 class="flex items-center gap-3 text-lg font-bold">'
         . '<span class="grid place-items-center w-9 h-9 rounded-xl bg-turq-soft text-turq-ink">' . icon($iconName, 'w-[18px] h-[18px]') . '</span>'
         . '<span><span class="text-muted font-medium">' . t('step_word', 'Step') . ' ' . $n . ':</span> ' . t("step_{$n}_title", $title) . '</span></h2>';
}
$p = $packs[$selected];
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

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Montserrat', 'system-ui', 'sans-serif'], serif: ['Montserrat', 'system-ui', 'sans-serif'] },
                    colors: {
                        cream: '#f8f9fa', linen: '#eef0f2', sand: '#e1e4e8',
                        turq: { DEFAULT: '#1dd1a1', dark: '#14b88c', soft: '#d6f7ee', ink: '#0a8a68' },
                        ink: '#1d2226', muted: '#5e656d',
                    },
                },
            },
        };
    </script>
    <?php if (OFFER_LIVE): ?><link rel="stylesheet" href="src/common/js/intltelinput/css/intltelInput.css"><?php endif; ?>
    <style>
        /* Montserrat throughout; titles bold; emphasis is colour-only, never italic */
        .font-serif { font-weight: 700; letter-spacing: -0.03em; }
        em, .italic { font-style: normal; }
        html, body { background: #f3f4f6; }
        .field { width: 100%; border: 1px solid #e1e4e8; background: #fff; border-radius: 12px; padding: 12px 14px; font-size: 15px; color: #1d2226; transition: border-color .2s, box-shadow .2s; }
        .field::placeholder { color: #9aa1a9; }
        .field:focus { outline: none; border-color: #1dd1a1; box-shadow: 0 0 0 4px rgba(29,209,161,.18); }
        .field.invalid { border-color: #fb7185; box-shadow: 0 0 0 4px rgba(251,113,133,.15); }
        select.field { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%235e656d' stroke-width='2' stroke-linecap='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 40px; }
        .has-icon { position: relative; }
        .has-icon .field { padding-left: 44px; }
        .field-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9aa1a9; pointer-events: none; display: flex; transition: color .2s; }
        .has-icon:focus-within .field-icon { color: #0a8a68; }
        .has-icon:has(.invalid) .field-icon { color: #fb7185; }
        .label { display: block; font-size: 13px; font-weight: 500; color: #1d2226; margin-bottom: 6px; }
        .card { background: #fff; border: 1px solid #e1e4e8; border-radius: 24px; }
        .pack { cursor: pointer; transition: border-color .2s, box-shadow .2s, transform .2s; }
        .pack:hover { transform: translateY(-2px); }
        .pack input:checked + .pack-body { border-color: #1dd1a1; box-shadow: 0 0 0 4px rgba(29,209,161,.18), 0 18px 40px -24px rgba(29,209,161,.9); background: #f4fdf9; }
        .pack input:checked + .pack-body .pack-radio { border-color: #1dd1a1; background: #1dd1a1; box-shadow: inset 0 0 0 3px #fff; }
        .pack input:focus-visible + .pack-body { outline: 2px solid #1d2226; outline-offset: 2px; }
        .stack img { mix-blend-mode: multiply; }
        /* Live mode: integrated.js marks bad fields with .error and adds a span.error-message after them */
        .field.error { border-color: #fb7185; box-shadow: 0 0 0 4px rgba(251,113,133,.15); }
        .has-icon:has(.error) .field-icon { color: #fb7185; }
        span.error-message { display: block; margin-top: 6px; font-size: 12px; color: #e11d48; }
        .iti { width: 100%; }
        .iti .field { padding-left: 52px; }
    </style>
</head>
<body class="font-sans text-ink antialiased">
<?php if (OFFER_LIVE) echo $mainProd->hiddenInputs; ?>

<!-- Header: logo + secure badge only (no navigation, keeps buyers focused) -->
<header class="bg-white border-b border-sand">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 h-16 flex items-center justify-between">
        <a href="index.php" class="shrink-0"><img src="<?= $logo ?>" alt="<?= $brand ?>" class="h-9 w-auto" width="190" height="49"></a>
        <div class="flex items-center gap-3 sm:gap-5">
            <span class="inline-flex items-center gap-2 text-sm text-muted"><span class="text-turq-ink"><?= icon('lock', 'w-4 h-4') ?></span> <span class="hidden sm:inline"><?= t('co_secure_checkout', 'Secure checkout') ?></span></span>
            <?= languageSelector('header') ?>
        </div>
    </div>
</header>

<!-- Hero banner -->
<section class="relative overflow-hidden bg-[radial-gradient(ellipse_at_70%_40%,#d6f7ee_0%,#eef0f2_55%,#f3f4f6_100%)]">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 py-8 sm:py-10 flex items-center justify-between gap-6">
        <div>
            <p class="text-xs font-medium uppercase tracking-[.2em] text-turq-ink"><?= t('co_almost_there', 'Almost there') ?></p>
            <h1 class="mt-2 font-serif text-3xl sm:text-5xl leading-tight tracking-tight"><?= t('co_treat_your_back_to', 'Treat your back to a') ?><br class="hidden sm:block"> <em class="text-turq-ink"><?= t('co_better_workday', 'better workday.') ?></em></h1>
        </div>
        <img src="<?= asset($render) ?>" alt="<?= $product ?>" class="hidden sm:block w-48 lg:w-60 drop-shadow-2xl" width="240" height="240">
    </div>
</section>

<main class="mx-auto max-w-6xl px-4 sm:px-6 py-8 sm:py-10">
    <form id="checkout" novalidate class="grid gap-6 lg:grid-cols-[1.1fr_1fr] lg:grid-rows-[auto_1fr_auto] lg:items-start">

        <!-- A: discount + quantity + add-on -->
        <section class="card p-5 sm:p-7 lg:col-start-1 lg:row-start-1">
            <!-- discount applied -->
            <div class="flex items-start gap-4">
                <span class="relative shrink-0 w-[76px] h-[76px] grid place-items-center" aria-label="Up to <?= $maxSave ?>% off">
                    <svg viewBox="0 0 100 100" class="absolute inset-0 w-full h-full drop-shadow-[0_10px_18px_rgba(29,209,161,.55)] animate-[spin_24s_linear_infinite]" aria-hidden="true">
                        <path fill="#1dd1a1" stroke="#1dd1a1" stroke-width="3" stroke-linejoin="round" d="M50,2L55.55,7.86L62.42,3.64L66.26,10.74L74,8.43L75.87,16.28L83.94,16.06L83.72,24.13L91.57,26L89.26,33.74L96.36,37.58L92.14,44.45L98,50L92.14,55.55L96.36,62.42L89.26,66.26L91.57,74L83.72,75.87L83.94,83.94L75.87,83.72L74,91.57L66.26,89.26L62.42,96.36L55.55,92.14L50,98L44.45,92.14L37.58,96.36L33.74,89.26L26,91.57L24.13,83.72L16.06,83.94L16.28,75.87L8.43,74L10.74,66.26L3.64,62.42L7.86,55.55L2,50L7.86,44.45L3.64,37.58L10.74,33.74L8.43,26L16.28,24.13L16.06,16.06L24.13,16.28L26,8.43L33.74,10.74L37.58,3.64L44.45,7.86Z"/>
                        <circle cx="50" cy="50" r="35" fill="none" stroke="#fff" stroke-opacity=".7" stroke-width="1.5" stroke-dasharray="2 3"/>
                    </svg>
                    <span class="relative flex flex-col items-center leading-none text-ink">
                        <span class="font-serif text-[18px] tracking-tight"><?= $maxSave ?><span class="text-xs align-top">%</span></span>
                        <span class="mt-0.5 text-[10px] font-bold tracking-[.18em]"><?= t('co_off', 'OFF') ?></span>
                    </span>
                </span>
                <div>
                    <p class="font-semibold text-lg text-turq-ink"><?= t('co_your_launch_discount_has', 'Your launch discount has been applied') ?></p>
                    <p class="mt-1 text-sm text-muted"><?= t('co_limited_time_the', 'Limited time: the') ?> <?= $product ?> <?= t('co_is_available_from', 'is available from') ?> <s><?= money($packs[1]['compare']) ?></s> <strong class="text-ink"><?= money($packs[1]['price']) ?></strong><?= t('co_with_up_to', ', with up to') ?> <?= $maxSave ?><?= t('co_off_bundles', '% off bundles.') ?></p>
                </div>
            </div>
            <!-- countdown -->
            <div class="mt-5 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 rounded-2xl border border-dashed border-turq/50 bg-turq-soft/40 px-4 py-3 text-sm">
                <span class="inline-flex items-center gap-2 font-medium"><span class="text-turq-ink"><?= icon('clock', 'w-4 h-4') ?></span> <?= t('co_your_discount_is_reserved', 'Your discount is reserved for:') ?></span>
                <span data-countdown class="font-semibold tabular-nums text-ink text-base">10:00</span>
            </div>

            <div class="mt-7"><?= step(1, 'box', 'Select quantity') ?></div>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4" role="radiogroup" aria-label="Pack size">
                <?php foreach ($packs as $qty => $pk):
                    $save = round((1 - $pk['price'] / $pk['compare']) * 100); ?>
                    <label class="pack relative block<?= OFFER_LIVE ? ' _prodSelect' . ($qty === $selected ? ' active' : '') : '' ?>"<?php if (OFFER_LIVE): ?>
                           data-productprice="<?= $pk['price'] ?>" data-productprice-text="<?= money($pk['price']) ?>" data-campaignproductid="<?= $pk['crm']->campaignProductId ?>" data-productname="<?= htmlspecialchars($pk['crm']->productName) ?>"
                           data-quantity="1" data-shipping="<?= $pk['shipping'] ?>" data-shipping-text="<?= shippingText($pk['shipping']) ?>" data-unitprice="<?= $pk['price'] ?>" data-originalPrice="<?= $pk['compare'] ?>"
                           data-variantDetailId="<?= $pk['crm']->variants[0]->variantDetailId ?? '' ?>" data-overrideQuantity="<?= $qty ?>"<?php endif; ?>>
                        <input type="radio" name="pack" value="<?= $qty ?>" class="sr-only" <?= $qty === $selected ? 'checked' : '' ?>
                               data-price="<?= $pk['price'] ?>" data-compare="<?= $pk['compare'] ?>" data-shipping="<?= $pk['shipping'] ?>" data-label="<?= htmlspecialchars($pk['label']) ?>">
                        <div class="pack-body h-full rounded-2xl border-2 border-sand bg-white p-4 pt-5 flex items-center gap-3">
                            <?php if ($pk['badge']): ?>
                                <span class="absolute -top-3 left-4 rounded-full px-3 py-0.5 text-[11px] font-semibold uppercase tracking-wide <?= $qty === 2 ? 'bg-turq text-ink' : 'bg-ink text-white' ?>"><?= $pk['badge'] ?></span>
                            <?php endif; ?>
                            <div class="stack relative w-[42%] aspect-square shrink-0 rounded-xl bg-linen grid place-items-center overflow-hidden">
                                <?php for ($i = $qty - 1; $i >= 0; $i--): ?>
                                    <img src="<?= asset($render) ?>" alt="" class="absolute w-[78%]" style="transform: translate(<?= ($i - ($qty - 1) / 2) * 12 ?>px, <?= -$i * 5 ?>px) scale(<?= 1 - $i * .06 ?>); opacity: <?= 1 - $i * .12 ?>" loading="lazy">
                                <?php endfor; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="font-serif text-3xl leading-none"><?= $qty ?><span class="text-xl">×</span></p>
                                    <span class="pack-radio shrink-0 w-5 h-5 rounded-full border-2 border-sand transition"></span>
                                </div>
                                <p class="mt-1 text-sm font-medium leading-tight"><?= $product ?><?= $qty > 1 ? 's' : '' ?></p>
                                <span class="mt-2 inline-block rounded-md bg-turq-soft px-2 py-0.5 text-[11px] font-semibold text-turq-ink"><?= $save ?><?= t('co_off_2', '% OFF') ?></span>
                                <p class="mt-2 leading-none"><s class="text-xs text-muted"><?= money($pk['compare']) ?></s> <strong class="text-xl"><?= money($pk['price']) ?></strong></p>
                                <p class="mt-1 text-[11px] text-muted"><?= $currency . number_format($pk['price'] / $qty, 2) ?> <?= t('co_each', 'each') ?></p>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <!-- SecureShip add-on -->
            <?php if ($secureShip > 0): ?>
            <label class="mt-5 flex items-start gap-4 rounded-2xl border border-sand bg-cream p-4 cursor-pointer hover:border-turq/50 transition">
                <input type="checkbox" id="insuranceAddOn" name="productUpsell" value="1" checked
                       data-campaignproductid="<?= $secureCrm->campaignProductId ?? '' ?>" data-productname="<?= htmlspecialchars($secureCrm->productName ?? 'SecureShip') ?>"
                       data-quantity="<?= $secureCrm->maxOrderQty ?? 1 ?>" data-shipping="<?= $secureCrm->shippingPrice ?? 0 ?>" data-unitprice="<?= $secureShip ?>" class="mt-1 w-5 h-5 accent-[#1dd1a1] shrink-0">
                <span class="grid place-items-center shrink-0 w-11 h-11 rounded-xl bg-white border border-sand text-turq-ink"><?= icon('shield', 'w-6 h-6') ?></span>
                <span class="flex-1">
                    <span class="block font-semibold"><?= t('co_yes_add', 'Yes, add') ?> <span class="text-turq-ink"><?= t('co_secureship', 'SecureShip') ?></span> <?= t('co_to_my_order', 'to my order') ?></span>
                    <span class="block mt-1 text-xs text-muted leading-relaxed"><?= t('co_full_protection_against_damage', 'Full protection against damage, loss or porch-pirate theft. We reship or refund, no questions asked.') ?></span>
                    <span class="block mt-2 text-sm font-semibold text-turq-ink"><?= money($secureShip) ?></span>
                </span>
            </label>
            <?php endif; ?>
        </section>

        <!-- B: summary + customer + delivery -->
        <section class="card p-5 sm:p-7 lg:col-start-2 lg:row-start-1 lg:row-span-2">
            <!-- order summary -->
            <div class="rounded-2xl bg-ink text-white p-5 bg-[radial-gradient(ellipse_at_top_right,rgba(29,209,161,.35),transparent_60%)]">
                <div class="flex items-center gap-4">
                    <div class="relative w-16 h-16 shrink-0 rounded-xl bg-white grid place-items-center">
                        <img src="<?= asset($render) ?>" alt="" class="w-14">
                        <span id="sumQty" class="absolute -top-2 -right-2 grid place-items-center w-6 h-6 rounded-full bg-turq text-ink text-xs font-bold"><?= $selected ?></span>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold"><?= $product ?> — <span id="sumLabel"><?= $p['label'] ?></span></p>
                        <p class="text-xs text-white/60"><?= t('co_free_spare_cover_on', 'Free spare cover on 2+ packs') ?></p>
                    </div>
                    <p id="sumPack" class="font-semibold"><?= money($p['price']) ?></p>
                </div>
                <dl class="mt-5 space-y-2 text-sm">
                    <div class="flex justify-between text-white/70"><dt><?= t('co_retail_price', 'Retail price') ?></dt><dd><s id="sumCompare"><?= money($p['compare']) ?></s></dd></div>
                    <div class="flex justify-between text-white/70"><dt><?= t('co_launch_discount', 'Launch discount') ?></dt><dd class="text-turq" id="sumDiscount">−<?= money($p['compare'] - $p['price']) ?></dd></div>
                    <?php if ($secureShip > 0): ?><div class="flex justify-between text-white/70" id="sumShipRow"><dt><?= t('co_secureship_2', 'SecureShip') ?></dt><dd id="sumShip"><?= money($secureShip) ?></dd></div><?php endif; ?>
                    <div class="flex justify-between text-white/70"><dt><?= t('co_shipping', 'Shipping') ?></dt><dd class="<?= $p['shipping'] > 0 ? '' : 'text-turq' ?>" id="sumShipping"><?= shippingText($p['shipping']) ?></dd></div>
                    <div class="flex justify-between border-t border-white/15 pt-3 text-base font-semibold"><dt><?= t('co_total', 'Total') ?></dt><dd id="sumTotal"><?= money($p['price'] + $p['shipping'] + $secureShip) ?></dd></div>
                </dl>
            </div>
            <ul class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-2 text-sm font-medium">
                <?php foreach (['Ergonomic design', 'Premium memory foam', 'Fits any office chair'] as $f): ?>
                    <li class="flex items-center gap-2"><span class="grid place-items-center w-5 h-5 rounded-full bg-turq text-ink"><?= icon('check', 'w-3 h-3') ?></span><?= $f ?></li>
                <?php endforeach; ?>
            </ul>

            <!-- Step 2: customer -->
            <div class="mt-8"><?= step(2, 'user', 'Customer information') ?></div>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="label" for="fields_fname"><?= t('co_first_name', 'First name') ?></label><div class="has-icon"><?= fieldIcon('user') ?><input class="field" id="fields_fname" name="first_name" autocomplete="given-name" placeholder="First name" required></div></div>
                <div><label class="label" for="fields_lname"><?= t('co_last_name', 'Last name') ?></label><div class="has-icon"><?= fieldIcon('user') ?><input class="field" id="fields_lname" name="last_name" autocomplete="family-name" placeholder="Last name" required></div></div>
                <div class="sm:col-span-2"><label class="label" for="fields_email"><?= t('co_email_address', 'Email address') ?></label><div class="has-icon"><?= fieldIcon('mail') ?><input class="field" id="fields_email" name="email" type="email" autocomplete="email" placeholder="you@example.com" required></div></div>
                <div class="sm:col-span-2"><label class="label" for="fields_phone"><?= t('co_phone_number', 'Phone number') ?></label><div class="<?= OFFER_LIVE ? '' : 'has-icon' ?>"><?= OFFER_LIVE ? '' : fieldIcon('phone') ?><input class="field" id="fields_phone" name="_phone_number" type="tel" autocomplete="tel" placeholder="For delivery updates" required></div></div>
            </div>

            <!-- Step 3: delivery -->
            <div class="mt-8"><?= step(3, 'pin', 'Delivery address') ?></div>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2"><label class="label" for="fields_country_select"><?= t('co_country', 'Country') ?></label>
                    <div class="has-icon"><?= fieldIcon('globe') ?>
                    <select class="field" id="fields_country_select" name="country" autocomplete="country" required>
                        <?php foreach ($countries as $code => $name): ?><option value="<?= $code ?>" <?= $code === 'US' ? 'selected' : '' ?>><?= $name ?></option><?php endforeach; ?>
                    </select>
                    </div>
                </div>
                <div class="sm:col-span-2"><label class="label" for="fields_address1"><?= t('co_address_1', 'Address 1') ?></label><div class="has-icon"><?= fieldIcon('home') ?><input class="field" id="fields_address1" name="address 1" autocomplete="address-line1" placeholder="Street address" required></div></div>
                <div class="sm:col-span-2"><label class="label" for="fields_address2"><?= t('co_address_2', 'Address 2') ?> <span class="text-muted font-normal"><?= t('co_optional', '(optional)') ?></span></label><div class="has-icon"><?= fieldIcon('door') ?><input class="field" id="fields_address2" name="address 2" autocomplete="address-line2" placeholder="Apt, suite, unit, etc."></div></div>
                <div class="sm:col-span-2"><label class="label" for="fields_city"><?= t('co_town_city', 'Town / City') ?></label><div class="has-icon"><?= fieldIcon('city') ?><input class="field" id="fields_city" name="city" autocomplete="address-level2" placeholder="Town / City" required></div></div>
                <div><label class="label" for="state"><?= t('co_state_province', 'State / Province') ?></label>
                    <div class="has-icon"><?= fieldIcon('map') ?>
                    <select class="field" id="fields_state" name="state" autocomplete="address-level1" required>
                        <option value=""><?= t('co_select_state', 'Select state') ?></option>
                        <?php foreach ($usStates as $s): ?><option><?= $s ?></option><?php endforeach; ?>
                    </select>
                    <input class="field hidden" id="fields_state_text" autocomplete="address-level1" placeholder="State / Province">
                    </div>
                </div>
                <div><label class="label" for="fields_zip"><?= t('co_zip_postal_code', 'Zip / Postal code') ?></label><div class="has-icon"><?= fieldIcon('hash') ?><input class="field" id="fields_zip" name="zip" autocomplete="postal-code" placeholder="Zip / Postal code" required></div></div>
            </div>
            <div class="mt-5 rounded-2xl border border-sand p-4">
                <p class="text-sm font-medium"><?= t('co_shipping_2', 'Shipping') ?></p>
                <div class="mt-2 flex items-center justify-between text-sm">
                    <span class="inline-flex items-center gap-3"><span class="w-5 h-5 rounded-full border-2 border-turq bg-turq shadow-[inset_0_0_0_3px_#fff]"></span> <?= t('co_standard_3_6_business', 'Standard (3–6 business days)') ?></span>
                    <span class="font-semibold text-turq-ink" id="sumShippingOpt"><?= $p['shipping'] > 0 ? money($p['shipping']) : t('co_free_2', 'Free') ?></span>
                </div>
                <p class="mt-3 text-xs text-muted bg-cream rounded-lg px-3 py-2 inline-flex items-center gap-2"><span class="text-turq-ink"><?= icon('truck', 'w-4 h-4') ?></span> <?= t('co_we_ship_ddp_all', 'We ship DDP: all taxes and duties are paid by us.') ?></p>
            </div>
        </section>

        <!-- C: payment -->
        <section class="card p-5 sm:p-7 lg:col-start-1 lg:row-start-2">
            <?php if (!OFFER_LIVE): ?>
            <p class="text-center text-sm font-semibold"><?= t('co_express_checkout', 'Express checkout') ?></p>
            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button type="button" class="express h-12 rounded-xl bg-black text-white font-semibold flex items-center justify-center gap-1.5 hover:opacity-90 transition"><svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor" aria-hidden="true"><path d="M16.4 12.6c0-2.3 1.9-3.4 2-3.5-1.1-1.6-2.8-1.8-3.4-1.8-1.4-.1-2.8.9-3.5.9-.7 0-1.8-.8-3-.8-1.5 0-3 .9-3.8 2.3-1.6 2.8-.4 7 1.2 9.3.8 1.1 1.7 2.4 2.9 2.3 1.2 0 1.6-.7 3-.7s1.8.7 3 .7c1.3 0 2.1-1.1 2.8-2.3.9-1.3 1.3-2.6 1.3-2.6s-2.5-1-2.5-3.8zM14.1 5.7c.6-.8 1.1-1.9 1-3-1 0-2.1.7-2.8 1.4-.6.7-1.1 1.8-1 2.9 1.1.1 2.2-.6 2.8-1.3z"/></svg> <?= t('co_pay', 'Pay') ?></button>
                <button type="button" class="express h-12 rounded-xl bg-[#ffc439] text-[#003087] font-bold italic text-lg flex items-center justify-center hover:brightness-95 transition"><?= t('co_pay_2', 'Pay') ?><span class="text-[#0070e0]"><?= t('co_pal', 'Pal') ?></span></button>
            </div>
            <div class="my-6 flex items-center gap-3 text-sm text-muted"><span class="flex-1 h-px bg-sand"></span><?= t('co_or_pay_with_card', 'Or pay with card') ?><span class="flex-1 h-px bg-sand"></span></div>
            <?php endif; ?>

            <?= step(4, 'card', 'Billing information') ?>
            <div class="mt-4 flex items-center gap-3 rounded-2xl border-2 border-turq bg-turq-soft/30 px-4 py-3">
                <span class="w-5 h-5 rounded-full border-2 border-turq bg-turq shadow-[inset_0_0_0_3px_#fff]"></span>
                <span class="text-sm font-medium flex-1"><?= t('co_credit_or_debit_card', 'Credit or debit card') ?></span>
                <span class="flex gap-1.5 text-[10px] font-bold">
                    <span class="rounded bg-white border border-sand px-1.5 py-0.5 text-[#1a1f71]"><?= t('co_visa', 'VISA') ?></span>
                    <span class="rounded bg-white border border-sand px-1.5 py-0.5 text-[#eb001b]"><?= t('co_mc', 'MC') ?></span>
                    <span class="rounded bg-white border border-sand px-1.5 py-0.5 text-[#2e77bb]"><?= t('co_amex', 'AMEX') ?></span>
                </span>
            </div>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-5 gap-4">
                <div class="sm:col-span-5"><label class="label" for="cc_number"><?= t('co_card_number', 'Card number') ?></label><div class="has-icon"><?= fieldIcon('card') ?><input class="field tracking-wider" id="cc_number" name="number" inputmode="numeric" autocomplete="off" placeholder="1234 5678 9012 3456" maxlength="19" required></div></div>
                <div class="sm:col-span-3"><label class="label" for="expiry_date"><?= t('co_expiry_date', 'Expiry date') ?></label><div class="has-icon"><?= fieldIcon('calendar') ?><input class="field" id="expiry_date" name="due_date" inputmode="numeric" autocomplete="off" placeholder="MM / YY" maxlength="7" required></div></div>
                <div class="sm:col-span-2"><label class="label" for="cc_cvv"><?= t('co_cvv', 'CVV') ?></label><div class="has-icon"><?= fieldIcon('lock') ?><input class="field" id="cc_cvv" name="cvv" inputmode="numeric" autocomplete="off" placeholder="123" maxlength="4" required></div></div>
            </div>
        </section>

        <!-- D: submit -->
        <section class="lg:col-start-2 lg:row-start-3">
            <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-muted">
                <span class="inline-flex items-center gap-1.5"><span class="text-turq-ink"><?= icon('shield', 'w-4 h-4') ?></span> <?= t('co_100_night_money_back', '100-night money-back') ?></span>
                <span class="inline-flex items-center gap-1.5"><span class="text-turq-ink"><?= icon('truck', 'w-4 h-4') ?></span> <?= t('co_ships_in_24_hours', 'Ships in 24 hours') ?></span>
                <span class="inline-flex items-center gap-1.5"><span class="text-turq-ink"><?= icon('tag', 'w-4 h-4') ?></span> <?= t('co_launch_price_locked', 'Launch price locked') ?></span>
            </div>
            <button type="<?= OFFER_LIVE ? 'button' : 'submit' ?>" id="<?= OFFER_LIVE ? 'submitForm' : 'submitBtn' ?>" class="group mt-4 w-full rounded-full bg-turq px-6 py-4 text-base sm:text-lg font-semibold text-ink shadow-[0_14px_30px_-12px_rgba(29,209,161,.8)] hover:bg-turq-dark transition">
                <?= t('co_get', 'Get') ?> <?= $maxSave ?><?= t('co_discount_order_now', '% Discount &amp; Order Now') ?> <span aria-hidden="true" class="inline-block transition group-hover:translate-x-1">→</span>
            </button>
            <p id="formNote" class="hidden mt-3 rounded-xl px-4 py-3 text-sm text-center" role="status"></p>
            <?php if (!OFFER_LIVE): ?>
                <p class="mt-3 rounded-xl border border-dashed border-sand bg-white px-4 py-2 text-center text-xs text-muted"><?= t('co_preview_mode_no_campaign', 'Preview mode: no campaign is connected yet, so no order is placed and no payment is taken.') ?></p>
            <?php endif; ?>
            <div class="mt-4 text-center">
                <p class="inline-flex items-center gap-2 text-sm font-semibold"><span class="text-turq-ink"><?= icon('lock', 'w-4 h-4') ?></span> <span><span class="text-turq-ink"><?= t('co_ssl', 'SSL') ?></span> <?= t('co_secured_payment', 'secured payment') ?></span></p>
                <p class="mt-1 text-xs text-muted"><?= t('co_your_information_is_protected', 'Your information is protected by 256-bit SSL encryption.') ?></p>
            </div>
        </section>
    </form>
</main>

<footer class="border-t border-sand bg-white">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-muted">
        <img src="<?= $logo ?>" alt="<?= $brand ?>" class="h-8 w-auto" width="190" height="49" loading="lazy">
        <ul class="flex flex-wrap justify-center gap-5">
            <li><a href="#" class="hover:text-ink transition"><?= t('co_contact_us', 'Contact us') ?></a></li>
            <li><a href="#" class="hover:text-ink transition"><?= t('co_terms_of_service', 'Terms of service') ?></a></li>
            <li><a href="#" class="hover:text-ink transition"><?= t('co_privacy_policy', 'Privacy policy') ?></a></li>
            <li><a href="#" class="hover:text-ink transition"><?= t('co_returns_refunds', 'Returns &amp; refunds') ?></a></li>
        </ul>
        <?= languageSelector('footer') ?>
    </div>
</footer>

<script>window.MELARA_CHECKOUT = { live: <?= OFFER_LIVE ? 'true' : 'false' ?>, currency: '<?= $currency ?>', secureShip: <?= (float) $secureShip ?> };</script>
<?php if (OFFER_LIVE): ?><script src="src/system/<?= $OfferApi->targetLanguage ?>.js?t=1793290535252"></script><?php endif; ?>
<script src="public/js/checkout.js?t=1793290535252"></script>
</body>
</html>

<?php $collector->saveTranslation(); ?>
