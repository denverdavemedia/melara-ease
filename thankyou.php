<?php
/* ------------------------------------------------------------------
 * Melara Seat Cushion — thank-you page (end of funnel: no upsells yet).
 * Live mode reads the order like shopmelara-apex/thankyou.php; preview
 * mode shows a sample order for the chosen pack (?pack=1-4&ship=0|1).
 * ------------------------------------------------------------------ */
include_once("integrated/setup.php");
include_once(BASEPATH . '/src/JsonTranslateCollector.php');

$collector = new JsonCollector(targetLanguage: $OfferApi->targetLanguage, pageName: "thankyou");
function t($key, $text) {
    static $cache = [];
    global $collector;
    return $cache[$key] ??= $collector->translate($key, $text);
}

$brand   = 'Melara';
$product = t('product_name', 'Melara Seat Cushion');
$logo    = 'assets/img/logo.svg';
$render  = 'assets/img/anatomy-assembled.webp';
function asset($src) { return $src . '?v=' . @filemtime(__DIR__ . '/' . $src); }

if (OFFER_LIVE) {
    $OfferApi->initProduct(orderData: $_POST);
    $order    = $OfferApi->query_customer_order();
    $currency = $OfferApi->currencySymbol ?: '$';
    $orderId  = $order->orderId ?? '';
    $email    = $order->emailAddress ?? '';
    $items    = [];
    foreach ((array) ($order->items ?? []) as $it) {
        $items[] = ['name' => trim(preg_replace('/\s*\bdownsell\b\s*/i', ' ', $it->name ?? '')), 'price' => $it->price ?? null, 'qty' => $it->qty ?? null];
    }
    $total = $order->totalAmount ?? null;
    $ship  = array_filter([
        trim(($order->shipFirstName ?? $order->firstName ?? '') . ' ' . ($order->shipLastName ?? $order->lastName ?? '')),
        $order->shipAddress ?? $order->address ?? '',
        trim(($order->shipCity ?? $order->city ?? '') . ', ' . ($order->shipState ?? $order->state ?? '') . ' ' . ($order->shipPostalCode ?? $order->postalCode ?? ''), ' ,'),
        $order->shipCountry ?? $order->country ?? '',
    ]);
} else {
    // Preview: sample order built from the pack chosen on the checkout (prices match checkout.php preview prices)
    $currency = '$';
    $prices   = [1 => 49, 2 => 89, 3 => 119, 4 => 139];
    $qty      = isset($_GET['pack'], $prices[(int) $_GET['pack']]) ? (int) $_GET['pack'] : 2;
    $withShip = !isset($_GET['ship']) || $_GET['ship'] === '1';
    $orderId  = 'PREVIEW';
    $email    = '';
    $items    = [['name' => $qty . ' × ' . $product, 'price' => $prices[$qty], 'qty' => $qty]];
    if ($withShip) $items[] = ['name' => 'SecureShip', 'price' => 4.99, 'qty' => 1];
    $total = array_sum(array_column($items, 'price'));
    $ship  = [];
}
$fmt = fn($n) => $n === null ? '' : $currency . number_format((float) $n, ((float) $n == floor((float) $n)) ? 0 : 2);

$steps = [
    ['t' => t('next_1_title', 'Order confirmed'), 'd' => t('next_1_text', 'We\'ve received your order and sent a confirmation email.')],
    ['t' => t('next_2_title', 'Ships within 24 hours'), 'd' => t('next_2_text', 'You\'ll get a tracking link as soon as it leaves our warehouse.')],
    ['t' => t('next_3_title', 'Start your 100 nights'), 'd' => t('next_3_text', 'Strap it on, sit back, and feel the difference from day one.')],
];
?>
<!doctype html>
<html lang="<?= $OfferApi->targetLanguage ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= t('title', 'Thank you') ?> — <?= $product ?></title>
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
    <style>
        /* Montserrat throughout; titles bold; emphasis is colour-only, never italic */
        .font-serif { font-weight: 700; letter-spacing: -0.03em; }
        html, body { background: #f3f4f6; }
        .check-pop { animation: pop .6s cubic-bezier(.3,1.6,.5,1) both; }
        @keyframes pop { from { transform: scale(.4); opacity: 0; } to { transform: none; opacity: 1; } }
        @media (prefers-reduced-motion: reduce) { .check-pop { animation: none; } }
    </style>
</head>
<body class="font-sans text-ink antialiased">

<header class="bg-white border-b border-sand">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 h-16 flex items-center justify-between">
        <a href="index.php" class="shrink-0"><img src="<?= $logo ?>" alt="<?= $brand ?>" class="h-9 w-auto" width="190" height="49"></a>
        <span class="text-sm text-muted"><?= t('order_complete', 'Order complete') ?></span>
    </div>
</header>

<main class="mx-auto max-w-5xl px-4 sm:px-6 py-10 sm:py-14">
    <?php if (!OFFER_LIVE): ?>
        <p class="mb-6 rounded-xl border border-dashed border-sand bg-white px-4 py-2 text-center text-xs text-muted"><?= t('preview_note', 'Preview mode: this is a sample confirmation. No order was placed and no payment was taken.') ?></p>
    <?php endif; ?>

    <section class="relative overflow-hidden rounded-[2rem] bg-white border border-sand p-6 sm:p-10 bg-[radial-gradient(ellipse_at_85%_20%,#d6f7ee_0%,#fff_55%)]">
        <div class="grid gap-8 sm:grid-cols-[1fr_auto] items-center">
            <div>
                <span class="check-pop grid place-items-center w-14 h-14 rounded-full bg-turq text-ink shadow-[0_14px_30px_-12px_rgba(29,209,161,.9)]">
                    <svg viewBox="0 0 24 24" class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                </span>
                <h1 class="mt-5 font-serif text-3xl sm:text-5xl leading-tight"><?= t('heading', 'Thank you!') ?> <span class="text-turq-ink"><?= t('heading_accent', 'Your back says thanks too.') ?></span></h1>
                <p class="mt-3 text-muted max-w-xl">
                    <?= t('intro', 'Your order is confirmed. We\'re getting your Melara Seat Cushion ready to ship.') ?>
                    <?php if ($email): ?><?= t('intro_email', 'A confirmation is on its way to') ?> <strong class="text-ink"><?= htmlspecialchars($email) ?></strong>.<?php endif; ?>
                </p>
                <p class="mt-4 inline-flex items-center gap-2 rounded-full bg-cream border border-sand px-4 py-1.5 text-sm">
                    <span class="text-muted"><?= t('order_number', 'Order number') ?></span> <strong>#<?= htmlspecialchars($orderId) ?></strong>
                </p>
            </div>
            <img src="<?= asset($render) ?>" alt="<?= $product ?>" class="hidden sm:block w-56 drop-shadow-2xl" width="224" height="224">
        </div>
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1.2fr_1fr] lg:items-start">
        <!-- Order summary -->
        <section class="rounded-[1.5rem] bg-ink text-white p-6 sm:p-7 bg-[radial-gradient(ellipse_at_top_right,rgba(29,209,161,.35),transparent_60%)]">
            <h2 class="text-lg font-bold"><?= t('summary_title', 'Order summary') ?></h2>
            <ul class="mt-4 divide-y divide-white/10">
                <?php foreach ($items as $it): ?>
                    <li class="flex items-center justify-between gap-4 py-3 text-sm">
                        <span><?= htmlspecialchars($it['name']) ?></span>
                        <span class="font-semibold"><?= $fmt($it['price']) ?></span>
                    </li>
                <?php endforeach; ?>
                <li class="flex items-center justify-between py-3 text-sm text-white/70"><span><?= t('shipping', 'Shipping') ?></span><span class="text-turq"><?= t('free', 'Free') ?></span></li>
            </ul>
            <?php if ($total !== null): ?>
                <div class="mt-2 flex items-center justify-between border-t border-white/15 pt-4 text-base font-semibold"><span><?= t('total', 'Total') ?></span><span><?= $fmt($total) ?></span></div>
            <?php endif; ?>
            <?php if ($ship): ?>
                <div class="mt-5 rounded-xl bg-white/5 p-4 text-sm">
                    <p class="text-white/60"><?= t('ships_to', 'Ships to') ?></p>
                    <p class="mt-1 leading-relaxed"><?= implode('<br>', array_map('htmlspecialchars', $ship)) ?></p>
                </div>
            <?php endif; ?>
        </section>

        <!-- What happens next -->
        <section class="rounded-[1.5rem] bg-white border border-sand p-6 sm:p-7">
            <h2 class="text-lg font-bold"><?= t('next_title', 'What happens next') ?></h2>
            <ol class="mt-5 space-y-5">
                <?php foreach ($steps as $i => $s): ?>
                    <li class="flex gap-4">
                        <span class="grid place-items-center shrink-0 w-9 h-9 rounded-xl <?= $i === 0 ? 'bg-turq text-ink' : 'bg-turq-soft text-turq-ink' ?> font-bold text-sm"><?= $i + 1 ?></span>
                        <span>
                            <span class="block font-semibold"><?= $s['t'] ?></span>
                            <span class="block text-sm text-muted"><?= $s['d'] ?></span>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ol>
        </section>
    </div>

    <!-- Support -->
    <section class="mt-6 rounded-[1.5rem] bg-white border border-sand p-6 sm:p-7 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold"><?= t('help_title', 'Questions about your order?') ?></h2>
            <p class="text-sm text-muted"><?= t('help_text', 'Our team is happy to help, every day.') ?></p>
        </div>
        <div class="flex flex-wrap gap-3 text-sm">
            <a href="mailto:<?= htmlspecialchars($OfferApi->companyEmailAddress) ?>" class="rounded-full border border-sand px-4 py-2 hover:border-turq transition"><?= htmlspecialchars($OfferApi->companyEmailAddress) ?></a>
            <a href="tel:<?= preg_replace('/[^+\d]/', '', $OfferApi->companyPhoneNumber) ?>" class="rounded-full border border-sand px-4 py-2 hover:border-turq transition"><?= htmlspecialchars($OfferApi->companyPhoneNumber) ?></a>
        </div>
    </section>

    <p class="mt-8 text-center"><a href="index.php" class="text-sm font-semibold text-turq-ink hover:underline"><?= t('back_home', '← Back to Melara') ?></a></p>
</main>

<footer class="border-t border-sand bg-white">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 py-6 text-center text-xs text-muted">
        © <?= date('Y') ?> <?= htmlspecialchars($OfferApi->corporateName ?: $brand) ?>. <?= t('rights', 'All rights reserved.') ?>
    </div>
</footer>
</body>
</html>
<?php $collector->saveTranslation(); ?>
