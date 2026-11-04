<?php
/* Melara Seat Cushion thank-you page: shows the order from the CRM. */
include_once("integrated/setup.php");
include_once(BASEPATH . '/src/JsonTranslateCollector.php');
include_once(__DIR__ . '/includes/language-selector.php');

$collector = new JsonCollector(targetLanguage: $OfferApi->targetLanguage, pageName: "thankyou");
$product = $collector->translate('product_name', 'Melara Seat Cushion');

$OfferApi->initProduct(orderData: $_POST);
$order = $OfferApi->query_customer_order();

// Order lines and totals, the same way shopmelara-apex/thankyou.php adds them up
$items = (array) ($order->items ?? []);
$price = $shipping = 0;
foreach ($items as $item) {
    $price    += (float) $item->price;
    $shipping += (float) $item->shipping;
}
$total = $price + $shipping - (float) ($order->totalDiscount ?? 0);

// translated once: used on every order line
$qtyText  = $collector->translate('qty', 'Qty');
$freeItem = $collector->translate('free_item', 'Free');

// Product thumbnail per order line, matched on the item name
function thumb($name) {
    if (stripos($name, 'knee') !== false)    return 'assets/img/upsell/knee-pack.png';
    if (stripos($name, 'pillow') !== false)  return 'assets/img/upsell/pillow-pack.png';
    if (stripos($name, 'cushion') !== false) return 'assets/img/anatomy-assembled.webp?t=' . filemtime(__DIR__ . '/assets/img/anatomy-assembled.webp');
    return null;
}
?>
<!doctype html>
<html lang="<?= $OfferApi->targetLanguage ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $collector->translate('title', 'Thank you') ?> — <?= $product ?></title>
    <meta name="robots" content="noindex">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/thankyou.css?t=<?= $OfferApi->assetVersion('assets/css/thankyou.css') ?>">
    <link rel="stylesheet" href="public/css/app.css?t=<?= $OfferApi->assetVersion('public/css/app.css') ?>">
</head>
<body class="font-sans text-ink antialiased">

<header class="bg-white border-b border-sand">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 h-16 flex items-center justify-between">
        <a href="index.php" class="shrink-0"><img src="assets/img/logo.svg" alt="Melara" class="h-9 w-auto" width="190" height="49"></a>
        <div class="flex items-center gap-3 sm:gap-5">
            <span class="hidden sm:inline text-sm text-muted"><?= $collector->translate('order_complete', 'Order complete') ?></span>
            <?= languageSelector('locked') ?>
        </div>
    </div>
</header>

<main class="mx-auto max-w-5xl px-4 sm:px-6 py-10 sm:py-14">

    <section class="relative overflow-hidden rounded-[2rem] bg-white border border-sand p-6 sm:p-10 bg-[radial-gradient(ellipse_at_85%_20%,#d6f7ee_0%,#fff_55%)]">
        <div class="grid gap-8 sm:grid-cols-[1fr_auto] items-center">
            <div>
                <span class="check-pop grid place-items-center w-14 h-14 rounded-full bg-turq text-ink shadow-[0_14px_30px_-12px_rgba(29,209,161,.9)]">
                    <svg viewBox="0 0 24 24" class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                </span>
                <h1 class="mt-5 font-serif text-3xl sm:text-5xl leading-tight sm:leading-none"><?= $collector->translate('heading', 'Thank you!') ?> <span class="text-turq-ink"><?= $collector->translate('heading_accent', 'Your back says thanks too.') ?></span></h1>
                <p class="mt-3 text-muted max-w-xl">
                    <?= count($items) > 1 ? $collector->translate('intro_multi', 'Your order is confirmed. We\'re packing everything into one box, ready to ship.') : $collector->translate('intro', 'Your order is confirmed. We\'re getting your Melara Seat Cushion ready to ship.') ?>
                    <?php if (!empty($order->emailAddress)): ?><?= $collector->translate('intro_email', 'A confirmation is on its way to') ?> <strong class="text-ink"><?= htmlspecialchars($order->emailAddress) ?></strong>.<?php endif; ?>
                </p>
                <p class="mt-4 inline-flex items-center gap-2 rounded-full bg-cream border border-sand px-4 py-1.5 text-sm">
                    <span class="text-muted"><?= $collector->translate('order_number', 'Order number') ?></span> <strong>#<?= htmlspecialchars($order->orderId ?? '') ?></strong>
                </p>
            </div>
            <img src="assets/img/anatomy-assembled.webp?t=<?= $OfferApi->assetVersion('assets/img/anatomy-assembled.webp') ?>" alt="<?= $product ?>" class="hidden sm:block w-56 drop-shadow-2xl" width="224" height="224">
        </div>
    </section>

    <div class="mt-6 grid gap-6 sm:grid-cols-2">
        <section class="rounded-[1.5rem] bg-white border border-sand p-6 sm:p-7">
            <div class="flex items-center gap-3">
                <span class="grid place-items-center w-10 h-10 rounded-xl bg-turq-soft text-turq-ink">
                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/></svg>
                </span>
                <h2 class="text-lg font-bold"><?= $collector->translate('shipping_info', 'Shipping information') ?></h2>
            </div>
            <p class="mt-4 text-sm leading-relaxed text-muted">
                <strong class="block text-ink font-semibold"><?= htmlspecialchars(trim(($order->shipFirstName ?? '') . ' ' . ($order->shipLastName ?? ''))) ?></strong>
                <?= htmlspecialchars($order->shipAddress1 ?? '') ?><br>
                <?php if (!empty($order->shipAddress2)): ?><?= htmlspecialchars($order->shipAddress2) ?><br><?php endif; ?>
                <?= htmlspecialchars(trim(($order->shipCity ?? '') . ', ' . ($order->shipState ?? '') . ' ' . ($order->shipPostalCode ?? ''), ' ,')) ?><br>
                <?= htmlspecialchars($order->shipCountry ?? '') ?>
            </p>
        </section>

        <section class="rounded-[1.5rem] bg-white border border-sand p-6 sm:p-7">
            <div class="flex items-center gap-3">
                <span class="grid place-items-center w-10 h-10 rounded-xl bg-turq-soft text-turq-ink">
                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5.5" width="18" height="13" rx="2.5"/><path d="M3 10h18M7 15h4"/></svg>
                </span>
                <h2 class="text-lg font-bold"><?= $collector->translate('billing_info', 'Billing information') ?></h2>
            </div>
            <p class="mt-4 text-sm leading-relaxed text-muted">
                <strong class="block text-ink font-semibold"><?= htmlspecialchars(trim(($order->firstName ?? '') . ' ' . ($order->lastName ?? ''))) ?></strong>
                <?= htmlspecialchars($order->address1 ?? '') ?><br>
                <?php if (!empty($order->address2)): ?><?= htmlspecialchars($order->address2) ?><br><?php endif; ?>
                <?= htmlspecialchars(trim(($order->city ?? '') . ', ' . ($order->state ?? '') . ' ' . ($order->postalCode ?? ''), ' ,')) ?><br>
                <?= htmlspecialchars($order->country ?? '') ?>
            </p>
        </section>
    </div>

    <section class="mt-6 rounded-[1.5rem] bg-ink text-white p-6 sm:p-8 bg-[radial-gradient(ellipse_at_top_right,rgba(29,209,161,.35),transparent_60%)]">
        <h2 class="text-lg font-bold"><?= $collector->translate('summary_title', 'Order summary') ?></h2>
        <ul class="mt-4 divide-y divide-white/10">
            <?php foreach ($items as $item): ?>
                <li class="flex items-center justify-between gap-4 py-3.5 text-sm sm:text-base">
                    <span class="flex items-center gap-4 min-w-0">
                        <?php if ($src = thumb($item->name)): ?>
                            <img src="<?= $src ?>" alt="" class="w-14 h-14 shrink-0 rounded-xl bg-white/90 object-contain p-1.5" width="56" height="56" loading="lazy">
                        <?php else: ?>
                            <span class="grid place-items-center w-14 h-14 shrink-0 rounded-xl bg-white/10 text-turq" aria-hidden="true">✓</span>
                        <?php endif; ?>
                        <span class="min-w-0">
                            <span class="block font-medium"><?= htmlspecialchars(trim(preg_replace('/\s*\bdownsell\b\s*/i', ' ', $item->name))) ?></span>
                            <?php if (!empty($item->qty)): ?><span class="block text-xs text-white/50"><?= $qtyText ?> <?= (int) $item->qty ?></span><?php endif; ?>
                        </span>
                    </span>
                    <span class="font-semibold whitespace-nowrap"><?= (float) $item->price == 0 ? $freeItem : $OfferApi->formatNumber($item->price) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <dl class="mt-2 border-t border-white/15 pt-4 space-y-2 text-sm">
            <div class="flex justify-between text-white/70"><dt><?= $collector->translate('subtotal', 'Subtotal') ?></dt><dd><?= $OfferApi->formatNumber($price) ?></dd></div>
            <div class="flex justify-between text-white/70"><dt><?= $collector->translate('shipping', 'Shipping') ?></dt><dd class="<?= $shipping > 0 ? '' : 'text-turq' ?>"><?= $shipping > 0 ? $OfferApi->formatNumber($shipping) : $collector->translate('free', 'Free') ?></dd></div>
            <?php if ((float) ($order->totalDiscount ?? 0) > 0): ?>
                <div class="flex justify-between text-turq"><dt><?= $collector->translate('discount', 'Discount') ?></dt><dd>−<?= $OfferApi->formatNumber($order->totalDiscount) ?></dd></div>
            <?php endif; ?>
            <div class="flex justify-between pt-2 text-lg font-semibold text-white"><dt><?= $collector->translate('total', 'Total') ?></dt><dd><?= $OfferApi->formatNumber($total) ?></dd></div>
        </dl>
        <p class="mt-5 rounded-xl bg-white/5 px-4 py-3 text-xs text-white/60"><?= $collector->translate('charges', 'Charges will appear on your statement as') ?> <strong class="text-white"><?= htmlspecialchars($OfferApi->customDescriptor ?: 'Melara') ?></strong>.</p>
    </section>

    <section class="mt-6 rounded-[1.5rem] bg-white border border-sand p-6 sm:p-8">
        <h2 class="text-lg font-bold"><?= $collector->translate('next_title', 'What happens next') ?></h2>
        <ol class="mt-6 grid gap-6 sm:grid-cols-3 sm:gap-4">
            <li class="relative flex gap-4 sm:block">
                <span class="hidden sm:block absolute top-6 left-14 right-0 h-0.5 bg-gradient-to-r from-turq to-turq-soft" aria-hidden="true"></span>
                <span class="relative grid place-items-center shrink-0 w-12 h-12 rounded-2xl bg-turq text-ink shadow-[0_12px_24px_-12px_rgba(29,209,161,.9)]">
                    <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m4 7 8 6 8-6"/></svg>
                </span>
                <span class="block sm:mt-4">
                    <span class="block text-xs font-semibold uppercase tracking-[.18em] text-turq-ink"><?= $collector->translate('next_1_when', 'Today') ?></span>
                    <span class="mt-1 block font-semibold"><?= $collector->translate('next_1_title', 'Order confirmed') ?></span>
                    <span class="mt-1 block text-sm text-muted"><?= $collector->translate('next_1_text', 'We\'ve received your order and sent a confirmation email.') ?></span>
                </span>
            </li>
            <li class="relative flex gap-4 sm:block">
                <span class="hidden sm:block absolute top-6 left-14 right-0 h-0.5 bg-turq-soft" aria-hidden="true"></span>
                <span class="relative grid place-items-center shrink-0 w-12 h-12 rounded-2xl bg-turq-soft text-turq-ink">
                    <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/></svg>
                </span>
                <span class="block sm:mt-4">
                    <span class="block text-xs font-semibold uppercase tracking-[.18em] text-turq-ink"><?= $collector->translate('next_2_when', 'Within 24 hours') ?></span>
                    <span class="mt-1 block font-semibold"><?= $collector->translate('next_2_title', 'Ships within 24 hours') ?></span>
                    <span class="mt-1 block text-sm text-muted"><?= $collector->translate('next_2_text', 'You\'ll get a tracking link as soon as it leaves our warehouse.') ?></span>
                </span>
            </li>
            <li class="relative flex gap-4 sm:block">
                <span class="relative grid place-items-center shrink-0 w-12 h-12 rounded-2xl bg-turq-soft text-turq-ink">
                    <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19.5 14.6A7.5 7.5 0 0 1 9.4 4.5a7.5 7.5 0 1 0 10.1 10.1z"/><path d="M16.5 3.5v3M15 5h3"/></svg>
                </span>
                <span class="block sm:mt-4">
                    <span class="block text-xs font-semibold uppercase tracking-[.18em] text-turq-ink"><?= $collector->translate('next_3_when', 'Your first 100 nights') ?></span>
                    <span class="mt-1 block font-semibold"><?= $collector->translate('next_3_title', 'Start your 100 nights') ?></span>
                    <span class="mt-1 block text-sm text-muted"><?= $collector->translate('next_3_text', 'Set it on your chair, sit back, and feel the difference from day one.') ?></span>
                </span>
            </li>
        </ol>
    </section>

    <section class="mt-6 rounded-[1.5rem] bg-white border border-sand p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center gap-6">
        <img src="assets/img/agent.png" alt="<?= $collector->translate('agent_alt', 'Melara customer support') ?>" class="w-24 h-24 sm:w-28 sm:h-28 shrink-0 rounded-full object-cover ring-4 ring-turq-soft" width="112" height="112" loading="lazy">
        <div class="flex-1">
            <h2 class="text-lg font-bold"><?= $collector->translate('help_title', 'Questions about your order?') ?></h2>
            <p class="mt-1 text-sm text-muted"><?= $collector->translate('help_text', 'Our team is happy to help, every day.') ?></p>
            <div class="mt-4 flex flex-wrap gap-3 text-sm">
                <a href="mailto:<?= htmlspecialchars($OfferApi->companyEmailAddress) ?>" class="inline-flex items-center gap-2 rounded-full border border-sand px-4 py-2 hover:border-turq transition">
                    <svg viewBox="0 0 24 24" class="w-4 h-4 text-turq-ink" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m4 7 8 6 8-6"/></svg>
                    <?= htmlspecialchars($OfferApi->companyEmailAddress) ?>
                </a>
                <a href="tel:<?= preg_replace('/[^+\d]/', '', $OfferApi->companyPhoneNumber) ?>" class="inline-flex items-center gap-2 rounded-full border border-sand px-4 py-2 hover:border-turq transition">
                    <svg viewBox="0 0 24 24" class="w-4 h-4 text-turq-ink" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 4h3.5l1.7 4.2-2.2 1.4a11 11 0 0 0 6.4 6.4l1.4-2.2L20 15.5V19a1.5 1.5 0 0 1-1.6 1.5C10.6 20 4 13.4 3.5 5.6A1.5 1.5 0 0 1 5 4z"/></svg>
                    <?= htmlspecialchars($OfferApi->companyPhoneNumber) ?>
                </a>
            </div>
        </div>
    </section>

    <p class="mt-8 text-center"><a href="index.php" class="text-sm font-semibold text-turq-ink hover:underline"><?= $collector->translate('back_home', '← Back to Melara') ?></a></p>
</main>

<footer class="border-t border-sand bg-white">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 py-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-muted">
        <p>© <?= date('Y') ?> <?= htmlspecialchars($OfferApi->corporateName ?: 'Melara') ?>. <?= $collector->translate('rights', 'All rights reserved.') ?></p>
        <?= languageSelector('locked') ?>
    </div>
</footer>
</body>
</html>
<?php $collector->saveTranslation(); ?>
