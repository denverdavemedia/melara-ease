<?php
/* Upsell 3 — Melara Apex Pillow (step 3 of 3). Self-contained: copy it with assets/css/upsell.css, assets/js/upsell.js and assets/img/upsell/. */
include_once('integrated/setup.php');
include_once(BASEPATH . '/src/JsonTranslateCollector.php');
include_once('includes/language-selector.php');
$collector = new JsonCollector(targetLanguage: $OfferApi->targetLanguage, pageName: $OfferApi->funnelPage());

// The upsell product (productIds[0] in the funnel config): price and how many can be added
$selectedProd = $OfferApi->initProduct(orderData: $_POST);
$currency = $OfferApi->currencySymbol ?: '$';
$price    = (float) $selectedProd->price;
$retail   = round($price / (1 - 0.7), 2); // crossed-out price: 70% off
$maxQty   = max(1, (int) $selectedProd->maxOrderQty);

// The downsell offered in the "No thanks" popup (productIds[1] in the funnel config), qty 1
$dsId = (int) ($OfferApi->nextFunnelPage()['productIds'][1] ?? 0);
$dsPrice = $dsShipping = 0;
$dsVariant = ""; // downsell SKU: first variant of the downsell product, read from the CRM (same as OfferApi::initProduct())
foreach ($OfferApi->upsaleProducts as $product) {
    if ((int) $product->campaignProductId === $dsId) {
        $dsPrice    = (float) $product->price;
        $dsShipping = (float) $product->shippingPrice;
        $dsVariant  = $product->variants[0]->variantDetailId ?? "";
    }
}
?>
<!doctype html>
<html lang="<?= $OfferApi->targetLanguage ?>" dir="<?= melaraTextDirection($OfferApi->targetLanguage) ?>" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="assets/img/favicon/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon/favicon-16x16.png">
    <title><?= $collector->translate('title', 'Wait! A one-time offer for you') ?> — Melara Memory Foam Pillow</title>
    <meta name="robots" content="noindex, nofollow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/upsell.css?t=<?= $OfferApi->assetVersion('assets/css/upsell.css') ?>">
    <link rel="stylesheet" href="public/css/app.css?t=<?= $OfferApi->assetVersion('public/css/app.css') ?>">
</head>

<body class="font-sans text-ink antialiased selection:bg-turq-soft overflow-x-hidden">
<?= $selectedProd->hiddenInputs // CRM hidden inputs (product, quantity, Everflow ids) ?>

<div class="mesh text-cream text-xs sm:text-sm text-center py-2.5 px-4" style="--veil: rgba(29,34,38,.45)">
    <strong class="hidden sm:inline text-turq-soft"><?= $collector->translate('bar_wait', 'Wait — your order isn\'t complete yet.') ?></strong>
    <span class="hidden sm:inline"><?= $collector->translate('bar_offer', 'This one-time offer expires in') ?></span>
    <span class="sm:hidden"><?= $collector->translate('bar_offer_short', 'One-time offer expires in') ?></span>
    <span data-countdown class="tabular-nums font-semibold">10:00</span>
</div>

<header class="border-b border-sand/70 bg-white/70 backdrop-blur-sm">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
        <img src="assets/img/logo.svg" alt="Melara" class="h-8 sm:h-9 w-auto shrink-0" width="190" height="49">

        <?php
        // Progress bar built from the funnel config (campaignFunnel in integrated/config_xx.php): a page that isn't in the funnel isn't shown.
        // Pages in the same group share one step (both pillowcase upsells = "Pillowcases"). Labels are translated once, before the loop.
        $stepNames = [
            'extra-seat-cushion' => $collector->translate('step_extra-seat-cushion', 'Extra cushion'),
            'melara-apex-pillow' => $collector->translate('step_melara-apex-pillow', 'Apex pillow'),
            'pillowcases'        => $collector->translate('step_pillowcases', 'Pillowcases'),
            'knee-pillow'        => $collector->translate('step_knee-pillow', 'Knee pillow'),
        ];
        $stepGroups = ['cloudair-pillowcase' => 'pillowcases', 'anti-dust-pillowcase' => 'pillowcases'];
        $steps = [];
        foreach ($OfferApi->upsells ?? [] as $funnelStep) {
            $group = $stepGroups[$funnelStep['page']] ?? $funnelStep['page'];
            if (!in_array($group, $steps, true)) $steps[] = $group;
        }
        $here = array_search($stepGroups[$OfferApi->funnelPage()] ?? $OfferApi->funnelPage(), $steps, true);
        ?>
        <ol class="hidden md:flex items-center gap-2 text-xs font-medium text-muted whitespace-nowrap" aria-label="<?= $collector->translate('progress_label', 'Order progress') ?>">
            <li class="flex items-center gap-2">
                <span class="grid place-items-center w-5 h-5 rounded-full bg-turq text-ink text-[10px]">✓</span>
                <span class="hidden xl:inline"><?= $collector->translate('progress_order', 'Order placed') ?></span>
            </li>
            <?php foreach ($steps as $i => $group):
                $label = $stepNames[$group] ?? ucfirst(str_replace('-', ' ', $group)); ?>
                <li class="w-4 xl:w-8 h-px <?= $i <= $here ? 'bg-turq' : 'bg-sand' ?>" aria-hidden="true"></li>
                <?php if ($i < $here): ?>
                    <li class="flex items-center gap-2">
                        <span class="grid place-items-center w-5 h-5 rounded-full bg-turq text-ink text-[10px]">✓</span>
                        <span class="hidden xl:inline"><?= $label ?></span>
                    </li>
                <?php elseif ($i === $here): ?>
                    <li class="flex items-center gap-2 text-ink" aria-current="step">
                        <span class="grid place-items-center w-6 h-6 rounded-full bg-ink text-cream text-[11px] shadow-[0_0_0_4px_rgba(29,209,161,.25)]"><?= $i + 1 ?></span>
                        <span><?= $label ?></span>
                    </li>
                <?php else: ?>
                    <li class="flex items-center gap-2">
                        <span class="grid place-items-center w-5 h-5 rounded-full border border-sand text-[10px]"><?= $i + 1 ?></span>
                        <span class="hidden xl:inline"><?= $label ?></span>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
            <li class="w-4 xl:w-8 h-px bg-sand" aria-hidden="true"></li>
            <li class="flex items-center gap-2">
                <span class="grid place-items-center w-5 h-5 rounded-full border border-sand" aria-hidden="true">
                    <svg viewBox="0 0 24 24" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l4 4v14H7z"/><path d="M14 3v4h4"/><path d="m9.5 14 2 2 3.5-4"/></svg>
                </span>
                <span class="hidden xl:inline"><?= $collector->translate('progress_confirmation', 'Confirmation') ?></span>
            </li>
        </ol>

        <?= languageSelector('locked') ?>
    </div>

    <div class="md:hidden px-4 pb-3">
        <p class="text-[11px] font-medium text-muted">
            <?= $collector->translate('progress_offer', 'Special offer') ?> 2/4 ·
            <span class="text-ink"><?= $collector->translate('step_melara-apex-pillow_2', 'Apex pillow') ?></span>
        </p>
        <div class="mt-1.5 flex gap-1" aria-hidden="true">
            <span class="h-1 flex-1 rounded-full bg-turq"></span>
            <span class="h-1 flex-1 rounded-full bg-turq"></span>
            <span class="h-1 flex-1 rounded-full bg-turq"></span>
            <span class="h-1 flex-1 rounded-full bg-sand"></span>
            <span class="h-1 flex-1 rounded-full bg-sand"></span>
            <span class="h-1 flex-1 rounded-full bg-sand"></span>
        </div>
    </div>
</header>

<main>
    <section class="relative overflow-hidden mesh-soft">
        <div class="absolute -top-32 -end-32 w-[520px] h-[520px] blob bg-turq-soft/80 blur-2xl" aria-hidden="true"></div>
        <div class="absolute bottom-0 -start-40 w-[420px] h-[420px] blob bg-[#e6f9f3] blur-2xl" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-5 sm:pt-8 pb-5 lg:pt-12 lg:pb-24">

            <div class="mx-auto max-w-4xl text-center">
                <p class="reveal hidden sm:inline-flex items-center gap-2 rounded-full bg-white/80 border border-sand px-4 py-1.5 text-xs font-semibold uppercase tracking-[.18em] text-turq-ink">
                    <span class="w-2 h-2 rounded-full bg-turq animate-pulse"></span><?= $collector->translate('eyebrow', 'One-time offer · only on this page') ?>
                </p>
                <h1 class="reveal sm:mt-5 font-serif text-[1.95rem] sm:text-5xl lg:text-[3.6rem] leading-[1.04] sm:leading-none tracking-tight" data-delay="1">
                    <?= $collector->translate('h1', 'Support your neck') ?> <em class="text-turq-ink"><?= $collector->translate('h1_accent', 'the way you support your back.') ?></em>
                </h1>
                <p class="reveal mt-3 sm:mt-5 text-[15px] sm:text-lg text-muted leading-relaxed sm:leading-7 max-w-2xl mx-auto" data-delay="2"><?= $collector->translate('lead', 'We know you probably have more than one go-to pillow. As part of this one-time offer, add the Melara Memory Foam Pillow to your order at a special price you won\'t see again.') ?></p>

                <ul class="reveal mt-4 sm:mt-7 mx-auto max-w-xl lg:max-w-none grid grid-cols-2 gap-x-3 gap-y-2.5 sm:gap-y-3 rounded-2xl bg-white/80 backdrop-blur-sm border border-sand p-3 sm:p-4 text-start shadow-[0_20px_40px_-30px_rgba(29,34,38,.45)]
                           lg:inline-flex lg:flex-nowrap lg:justify-center lg:items-center lg:gap-0 lg:rounded-full lg:px-3 lg:py-2.5" data-delay="3">
                    <li class="flex items-center gap-2 text-[13px] sm:text-sm font-medium leading-tight sm:leading-5 lg:px-4 lg:whitespace-nowrap">
                        <span class="shrink-0 grid place-items-center w-5 h-5 rounded-full bg-turq text-ink shadow-[0_0_0_3px_rgba(29,209,161,.18)]" aria-hidden="true">
                            <svg viewBox="0 0 24 24" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>
                        </span>
                        <?= $collector->translate('chip_0_t', 'Contoured memory foam') ?>
                    </li>
                    <li class="flex items-center gap-2 text-[13px] sm:text-sm font-medium leading-tight sm:leading-5 lg:px-4 lg:whitespace-nowrap lg:border-s lg:border-sand">
                        <span class="shrink-0 grid place-items-center w-5 h-5 rounded-full bg-turq text-ink shadow-[0_0_0_3px_rgba(29,209,161,.18)]" aria-hidden="true">
                            <svg viewBox="0 0 24 24" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>
                        </span>
                        <?= $collector->translate('chip_1_t', 'Neck & shoulder support') ?>
                    </li>
                    <li class="flex items-center gap-2 text-[13px] sm:text-sm font-medium leading-tight sm:leading-5 lg:px-4 lg:whitespace-nowrap lg:border-s lg:border-sand">
                        <span class="shrink-0 grid place-items-center w-5 h-5 rounded-full bg-turq text-ink shadow-[0_0_0_3px_rgba(29,209,161,.18)]" aria-hidden="true">
                            <svg viewBox="0 0 24 24" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>
                        </span>
                        <?= $collector->translate('chip_2_t', 'Removable, washable cover') ?>
                    </li>
                    <li class="flex items-center gap-2 text-[13px] sm:text-sm font-medium leading-tight sm:leading-5 lg:px-4 lg:whitespace-nowrap lg:border-s lg:border-sand">
                        <span class="shrink-0 grid place-items-center w-5 h-5 rounded-full bg-turq text-ink shadow-[0_0_0_3px_rgba(29,209,161,.18)]" aria-hidden="true">
                            <svg viewBox="0 0 24 24" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>
                        </span>
                        <?= $collector->translate('chip_3_t', 'Keeps its shape all night') ?>
                    </li>
                </ul>
            </div>

            <div class="mt-5 sm:mt-8 lg:mt-12 grid lg:grid-cols-12 gap-10 lg:gap-14 items-center">

                <div class="lg:col-span-6 order-2 lg:order-1 reveal">
                    <div id="stage" class="stage relative mx-auto" style="width: min(560px, 100%); aspect-ratio: 1;">
                        <div class="absolute -inset-[12%] rounded-full stage-glow blur-xl" aria-hidden="true"></div>
                        <span class="stage-ring" aria-hidden="true"></span>
                        <span class="stage-ring" style="animation-delay: 1.6s" aria-hidden="true"></span>
                        <span class="stage-ring" style="animation-delay: 3.2s" aria-hidden="true"></span>
                        <span class="spark" style="left: 14%; top: 82%; animation-duration: 6s; animation-delay: 0s" aria-hidden="true"></span>
                        <span class="spark" style="left: 28%; top: 90%; animation-duration: 5s; animation-delay: 1.2s" aria-hidden="true"></span>
                        <span class="spark" style="left: 44%; top: 86%; animation-duration: 6s; animation-delay: 2.6s" aria-hidden="true"></span>
                        <span class="spark" style="left: 58%; top: 92%; animation-duration: 5s; animation-delay: 0.6s" aria-hidden="true"></span>
                        <span class="spark" style="left: 72%; top: 84%; animation-duration: 4s; animation-delay: 1.9s" aria-hidden="true"></span>
                        <span class="spark" style="left: 86%; top: 88%; animation-duration: 6s; animation-delay: 3.1s" aria-hidden="true"></span>
                        <span class="spark" style="left: 36%; top: 74%; animation-duration: 4s; animation-delay: 2.2s" aria-hidden="true"></span>
                        <span class="spark" style="left: 64%; top: 76%; animation-duration: 5s; animation-delay: 3.6s" aria-hidden="true"></span>
                        <div id="stageTilt" class="stage-tilt absolute inset-[6%]">
                            <div class="relative w-full h-full rounded-[2.5rem] overflow-hidden shadow-[0_40px_80px_-30px_rgba(29,34,38,.4)] animate-float">
                                <img src="assets/img/upsell/pillow-hero.webp" alt="Melara Memory Foam Pillow" class="w-full h-full object-cover" fetchpriority="high" decoding="async">                            <span class="sheen" aria-hidden="true"></span>
                            </div>
                        </div>
                        <div class="absolute start-0 sm:-start-4 top-6 mesh text-cream rounded-2xl px-5 py-3 shadow-xl animate-float" style="--veil: rgba(29,34,38,.35); animation-delay: -2s">
                            <p class="font-serif text-3xl leading-none">70%</p>
                            <p class="text-[11px] uppercase tracking-[.2em] text-turq-soft mt-1"><?= $collector->translate('badge_off', 'Off · today only') ?></p>
                        </div>
                        <div class="absolute end-0 sm:-end-4 bottom-8 bg-white rounded-2xl shadow-xl px-5 py-4 max-w-[220px] animate-float" style="animation-delay: -4s">
                            <p class="font-serif text-2xl text-turq-ink">1,781</p>
                            <p class="text-xs text-muted leading-snug"><?= $collector->translate('stat_text', 'verified customer reviews') ?></p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-6 order-1 lg:order-2 mx-auto w-full max-w-2xl lg:max-w-none">
                    <div class="reveal rounded-[1.75rem] bg-white/90 backdrop-blur-sm border border-sand p-3.5 sm:p-5 shadow-[0_30px_60px_-40px_rgba(29,34,38,.5)]" data-delay="3">

                        <div id="qtyPicker" data-unit="<?= $price ?>" data-off="0.7" data-max="<?= $maxQty ?>" data-currency="<?= $currency ?>"
                                   data-one="<?= $collector->translate('unit', 'Melara Pillow') ?>" data-many="<?= $collector->translate('unit_plural', 'Melara Pillows') ?>">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold"><?= $collector->translate('choose_qty', 'How many would you like?') ?></p>
                                <p class="text-[11px] text-muted"><?= $collector->translate('qty_max', 'Up to') ?> <?= $maxQty ?> <?= $collector->translate('qty_per_order', 'per order') ?></p>
                            </div>

                            <div class="mt-3 flex items-stretch gap-3 sm:gap-4 rounded-2xl border-2 border-turq/60 bg-white p-3 shadow-[0_0_0_4px_rgba(29,209,161,.12)]">
                                <div class="relative w-[38%] sm:w-40 shrink-0 aspect-[4/3] self-center rounded-xl bg-linen overflow-hidden" aria-hidden="true">
                                    <img src="assets/img/upsell/pillow-pack.png" alt="" decoding="async" data-slot="3"
                                               class="stack-img w-[60%] drop-shadow-[0_8px_10px_rgba(29,34,38,.28)]">
                                    <img src="assets/img/upsell/pillow-pack.png" alt="" decoding="async" data-slot="2"
                                               class="stack-img w-[60%] drop-shadow-[0_8px_10px_rgba(29,34,38,.28)]">
                                    <img src="assets/img/upsell/pillow-pack.png" alt="" decoding="async" data-slot="1"
                                               class="stack-img w-[60%] drop-shadow-[0_8px_10px_rgba(29,34,38,.28)]">
                                    <img src="assets/img/upsell/pillow-pack.png" alt="" decoding="async" data-slot="0"
                                               class="stack-img w-[60%] drop-shadow-[0_8px_10px_rgba(29,34,38,.28)]">
                                    <span class="qty-badge absolute z-10 top-1.5 end-1.5 hidden rounded-full bg-ink text-cream text-[11px] font-bold px-2 py-0.5 tabular-nums">×1</span>
                                </div>

                                <div class="flex-1 min-w-0 flex flex-col justify-between gap-2">
                                    <p class="text-sm font-semibold leading-tight" data-qty-label>1 × <?= $collector->translate('unit_2', 'Melara Pillow') ?></p>
                                    <div class="inline-flex self-start items-center rounded-full border border-sand bg-cream p-1" role="group" aria-label="<?= $collector->translate('qty_label', 'Quantity') ?>">
                                        <button type="button" class="qty-minus step-btn grid place-items-center w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white text-ink shadow-xs hover:bg-turq-soft" aria-label="<?= $collector->translate('qty_less', 'One less') ?>" disabled>
                                            <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/></svg>
                                        </button>
                                        <output class="qty-value inline-block w-10 sm:w-12 text-center font-serif text-xl sm:text-2xl tabular-nums" aria-live="polite">1</output>
                                        <button type="button" class="qty-plus step-btn grid place-items-center w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-turq text-ink shadow-xs hover:bg-turq-dark" aria-label="<?= $collector->translate('qty_more', 'One more') ?>">
                                            <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                                        </button>
                                    </div>
                                    <p class="flex items-baseline flex-wrap gap-x-2">
                                        <span class="font-serif text-2xl leading-none" data-cta-total><?= $OfferApi->formatNumber($price) ?></span>
                                        <s class="text-sm text-muted" data-cta-retail><?= $OfferApi->formatNumber($retail) ?></s>
                                        <span class="text-xs font-semibold text-turq-ink"><?= $collector->translate('qty_save', 'You save') ?> <span data-cta-save><?= $OfferApi->formatNumber($retail - $price) ?></span></span>
                                    </p>
                                </div>
                            </div>

                            <div class="qty-meter mt-3 flex items-center gap-3" aria-hidden="true">
                                <div class="flex-1 h-2.5 rounded-full bg-sand/70 overflow-hidden shadow-[inset_0_1px_2px_rgba(29,34,38,.08)]">
                                    <span class="qty-meter-fill block h-full rounded-full bg-gradient-to-r from-turq to-turq-dark transition-[width] duration-300 ease-out" style="width: <?= round(100 / $maxQty) ?>%"></span>
                                </div>
                                <span class="qty-meter-pct min-w-[3rem] rounded-full bg-turq-soft px-2 py-0.5 text-center text-xs font-bold tabular-nums text-turq-ink"><?= round(100 / $maxQty) ?>%</span>
                            </div>
                        </div>

                        <div id="heroCta" class="mt-5 shine rounded-full">
                            <button type="button" class="yes-upsell-link inline-flex w-full items-center justify-center gap-2 rounded-full bg-turq px-5 sm:px-7 py-4 text-[15px] sm:text-lg font-bold text-ink shadow-[0_18px_40px_-14px_rgba(29,209,161,.75)] hover:bg-turq-dark hover:-translate-y-0.5 transition nudge"><span><?= $collector->translate('yes', 'Yes, add to my order') ?> · <span data-cta-total><?= $OfferApi->formatNumber($price) ?></span></span><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>                    </div>
                        <p class="mt-3 flex items-center justify-center gap-2 text-xs text-muted">
                            <svg viewBox="0 0 24 24" class="w-4 h-4 text-turq-ink" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2.5"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                            <?= $collector->translate('one_click', 'One click — added to the order you just paid for. No card details needed.') ?>
                        </p>
                        <div class="mt-4"><button type="button" class="js-open-no-thanks-popup block w-full text-center text-xs sm:text-sm text-muted underline underline-offset-4 hover:text-ink transition"><?= $collector->translate('no_thanks', 'No thanks, I don\'t want this one-time offer') ?></button></div>
                    </div>

                    <ul class="reveal mt-6 grid grid-cols-3 gap-2 text-center text-[11px] sm:text-xs text-muted" data-delay="4">
                        <li class="group rounded-2xl bg-white/70 border border-sand px-2 py-3 hover:border-turq/50 hover:-translate-y-0.5 transition">
                            <span class="mx-auto mb-1.5 grid place-items-center w-9 h-9 rounded-full bg-turq-soft text-turq-ink group-hover:bg-turq group-hover:text-ink transition" aria-hidden="true">
                                <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7.5h10.5v8.5H3z"/><path d="M13.5 10.5h3.8l2.7 3v2.5h-6.5"/><circle cx="7" cy="17.5" r="1.7"/><circle cx="16.8" cy="17.5" r="1.7"/></svg>
                            </span>
                            <span class="block font-semibold text-ink text-sm"><?= $collector->translate('perk_ship_t', 'Ships free') ?></span>
                            <?= $collector->translate('perk_ship_d', 'in the same box') ?>
                        </li>
                        <li class="group rounded-2xl bg-white/70 border border-sand px-2 py-3 hover:border-turq/50 hover:-translate-y-0.5 transition">
                            <span class="mx-auto mb-1.5 grid place-items-center w-9 h-9 rounded-full bg-turq-soft text-turq-ink group-hover:bg-turq group-hover:text-ink transition" aria-hidden="true">
                                <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19.5 14.6A7.5 7.5 0 0 1 9.4 4.5a7.5 7.5 0 1 0 10.1 10.1z"/><path d="M16.5 3.5v3M15 5h3"/></svg>
                            </span>
                            <span class="block font-semibold text-ink text-sm"><?= $collector->translate('perk_trial_t', '30 days') ?></span>
                            <?= $collector->translate('perk_trial_d', 'love it or return it') ?>
                        </li>
                        <li class="group rounded-2xl bg-white/70 border border-sand px-2 py-3 hover:border-turq/50 hover:-translate-y-0.5 transition">
                            <span class="mx-auto mb-1.5 grid place-items-center w-9 h-9 rounded-full bg-turq-soft text-turq-ink group-hover:bg-turq group-hover:text-ink transition" aria-hidden="true">
                                <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 12.6V4.5a1 1 0 0 1 1-1h8.1l7.9 7.9a1.4 1.4 0 0 1 0 2l-6.1 6.1a1.4 1.4 0 0 1-2 0z"/><circle cx="8.3" cy="8.3" r="1.5"/></svg>
                            </span>
                            <span class="block font-semibold text-ink text-sm"><?= $collector->translate('perk_price_t', 'Lowest price') ?></span>
                            <?= $collector->translate('perk_price_d', 'only on this page') ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="relative mesh text-cream py-5 md:py-[30px] lg:py-28 overflow-hidden" style="--veil: rgba(29,34,38,.55)">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="reveal text-sm font-medium uppercase tracking-[.2em] text-turq"><?= $collector->translate('why_eyebrow', 'Why you\'ll love it') ?></p>
                <h2 class="reveal mt-4 font-serif text-4xl sm:text-5xl leading-tight sm:leading-none tracking-tight" data-delay="1">
                    <?= $collector->translate('why_title', 'Better nights start') ?> <em class="text-turq"><?= $collector->translate('why_accent', 'at your neck.') ?></em>
                </h2>
            </div>

            <div class="mt-14 grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="reveal group rounded-[1.75rem] border border-white/10 bg-white/[.04] backdrop-blur-sm p-6 hover:bg-white/[.08] hover:-translate-y-1 transition" data-delay="1">
                    <span class="grid place-items-center w-12 h-12 rounded-2xl bg-turq/15 text-turq group-hover:bg-turq group-hover:text-ink transition">
                        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 13c2-3 5-4.5 9-4.5s7 1.5 9 4.5"/><path d="M3 13c2 2 5 3 9 3s7-1 9-3"/><path d="M9 8.6c.6-1.6 1.7-2.6 3-2.6s2.4 1 3 2.6"/></svg>
                    </span>
                    <h3 class="mt-5 font-serif text-xl"><?= $collector->translate('benefit_0_t', 'Cradles your neck') ?></h3>
                    <p class="mt-2 text-sm text-cream/70 leading-relaxed"><?= $collector->translate('ben2_0_d', 'The contoured neck roll fills the gap under your neck, supporting the natural curve of your cervical spine.') ?></p>
                </div>
                <div class="reveal group rounded-[1.75rem] border border-white/10 bg-white/[.04] backdrop-blur-sm p-6 hover:bg-white/[.08] hover:-translate-y-1 transition" data-delay="2">
                    <span class="grid place-items-center w-12 h-12 rounded-2xl bg-turq/15 text-turq group-hover:bg-turq group-hover:text-ink transition">
                        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9.5c2.2-1.6 4.3-1.6 6.5 0s4.3 1.6 6.5 0 3.5-1.2 5-.8"/><path d="M3 14.5c2.2-1.6 4.3-1.6 6.5 0s4.3 1.6 6.5 0 3.5-1.2 5-.8"/><path d="M3 19.5h18"/></svg>
                    </span>
                    <h3 class="mt-5 font-serif text-xl"><?= $collector->translate('benefit_1_t', 'Slow-rebound memory foam') ?></h3>
                    <p class="mt-2 text-sm text-cream/70 leading-relaxed"><?= $collector->translate('benefit_1_d', 'Shapes to you in seconds and keeps its form night after night — no more folding a flat pillow in half.') ?></p>
                </div>
                <div class="reveal group rounded-[1.75rem] border border-white/10 bg-white/[.04] backdrop-blur-sm p-6 hover:bg-white/[.08] hover:-translate-y-1 transition" data-delay="3">
                    <span class="grid place-items-center w-12 h-12 rounded-2xl bg-turq/15 text-turq group-hover:bg-turq group-hover:text-ink transition">
                        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 4c-2 3-2 6 0 8s2 5 0 8"/><path d="M12 4c-2 3-2 6 0 8s2 5 0 8"/><path d="M18 4c-2 3-2 6 0 8s2 5 0 8"/></svg>
                    </span>
                    <h3 class="mt-5 font-serif text-xl"><?= $collector->translate('benefit_2_t', 'Cool, breathable cover') ?></h3>
                    <p class="mt-2 text-sm text-cream/70 leading-relaxed"><?= $collector->translate('benefit_2_d', 'A soft, airy cover keeps you comfortable all night, and zips off for the wash.') ?></p>
                </div>
                <div class="reveal group rounded-[1.75rem] border border-white/10 bg-white/[.04] backdrop-blur-sm p-6 hover:bg-white/[.08] hover:-translate-y-1 transition" data-delay="4">
                    <span class="grid place-items-center w-12 h-12 rounded-2xl bg-turq/15 text-turq group-hover:bg-turq group-hover:text-ink transition">
                        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 16c1.5-5 4.5-8 8-8s6.5 3 8 8"/><path d="M4 16h16"/><circle cx="12" cy="5" r="1.6"/></svg>
                    </span>
                    <h3 class="mt-5 font-serif text-xl"><?= $collector->translate('benefit_3_t', 'Room for your shoulders') ?></h3>
                    <p class="mt-2 text-sm text-cream/70 leading-relaxed"><?= $collector->translate('benefit_3_d', 'Curved cut-outs give your shoulders and arms space when you turn on your side.') ?></p>
                </div>
            </div>

            <div class="reveal hidden lg:flex mt-14 items-center justify-between gap-8 rounded-[1.75rem] border px-8 py-6 border-white/10 bg-white/[.05] backdrop-blur-sm">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[.2em] text-turq"><?= $collector->translate('sec_cta_eyebrow', 'One-time offer · 70% off') ?></p>
                    <p class="mt-1.5 font-serif text-2xl">
                        <?= $collector->translate('sec_cta_add', 'Add') ?> <span data-cta-label>1 × <?= $collector->translate('unit_3', 'Melara Pillow') ?></span> <?= $collector->translate('sec_cta_for', 'for') ?>            <span class="text-turq" data-cta-total><?= $OfferApi->formatNumber($price) ?></span>
                        <s class="text-base text-cream/60" data-cta-retail><?= $OfferApi->formatNumber($retail) ?></s>
                    </p>
                </div>
                <div class="w-[340px] shrink-0">
                    <div class="shine rounded-full"><button type="button" class="yes-upsell-link inline-flex w-full items-center justify-center gap-2 rounded-full bg-turq px-5 sm:px-7 py-4 text-[15px] sm:text-lg font-bold text-ink shadow-[0_18px_40px_-14px_rgba(29,209,161,.75)] hover:bg-turq-dark hover:-translate-y-0.5 transition nudge"><span><?= $collector->translate('yes_3', 'Yes, add to my order') ?></span><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button></div>
                    <div class="mt-3"><button type="button" class="js-open-no-thanks-popup block w-full text-center text-xs underline underline-offset-4 transition text-cream/60 hover:text-cream"><?= $collector->translate('no_thanks_2', 'No thanks, I don\'t want this one-time offer') ?></button></div>
                </div>
            </div>
        </div>
    </section>

    <section id="road" class="road-section relative overflow-hidden py-5 md:py-[30px] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto text-center">
                <p class="reveal text-sm font-medium uppercase tracking-[.2em] text-turq-ink"><?= $collector->translate('road_eyebrow', 'A quick ride through your night') ?></p>
                <h2 class="reveal mt-4 font-serif text-4xl sm:text-5xl leading-tight sm:leading-none tracking-tight" data-delay="1">
                    <?= $collector->translate('road_title', 'Your neck works the night shift') ?> <em class="text-turq-ink"><?= $collector->translate('road_accent', 'whether you notice or not.') ?></em>
                </h2>
                <p class="reveal mt-4 text-muted leading-relaxed" data-delay="2"><?= $collector->translate('road_lead', 'Eight hours, one pillow, one position after another. Here\'s what your neck goes through — and the one thing that changes it.') ?></p>
            </div>

            <div class="road-track relative mt-14 lg:mt-20 pt-10">
                <svg class="road-svg absolute inset-0 w-full h-full overflow-visible pointer-events-none" aria-hidden="true">
                    <defs><filter id="roadGlow" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="8"/></filter></defs>
                    <path class="road-rim-glow" fill="none" stroke="#1dd1a1" stroke-opacity=".45" stroke-linecap="round" filter="url(#roadGlow)" pathLength="1" stroke-dasharray="1 1" stroke-dashoffset="1"/>
                    <path class="road-rim" fill="none" stroke="#1dd1a1" stroke-linecap="round" pathLength="1" stroke-dasharray="1 1" stroke-dashoffset="1"/>
                    <path class="road-base" fill="none" stroke="#1d2226" stroke-linecap="round"/>
                    <path class="road-dash" fill="none" stroke="#fff" stroke-opacity=".9" stroke-width="2"/>
                    <g class="road-head" opacity="0">
                        <circle r="16" fill="#1dd1a1" opacity=".35" filter="url(#roadGlow)"/>
                        <circle r="7" fill="#1dd1a1"/>
                        <circle r="2.6" fill="#fff"/>
                    </g>
                </svg>

                <ol class="relative space-y-12 lg:space-y-20">
                    <li class="road-stop relative lg:grid lg:grid-cols-2 lg:gap-x-48" data-side="left">
                        <span class="road-node z-10 grid place-items-center w-14 h-14 lg:w-[76px] lg:h-[76px] rounded-full bg-white text-ink border border-sand shadow-lg"
                                   style="--x-mobile: 28px; --x-desktop: calc(50% + 48px)">
                            <svg viewBox="0 0 24 24" class="w-7 h-7 lg:w-9 lg:h-9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z"/></svg>
                        </span>
                        <div class="road-card ms-[76px] lg:ms-0 lg:col-start-1 lg:text-end rounded-[1.75rem] bg-white border border-sand p-5 sm:p-6 shadow-[0_30px_60px_-45px_rgba(29,34,38,.55)]">
                            <p class="text-xs font-semibold uppercase tracking-[.2em] text-turq-ink"><?= $collector->translate('road_stop_label', 'Stop') ?> 01</p>
                            <h3 class="mt-2 font-serif text-xl sm:text-2xl leading-snug sm:leading-8"><?= $collector->translate('road_stop_0_t', 'A third of your life on a pillow') ?></h3>
                            <p class="mt-2 text-sm sm:text-base text-muted leading-relaxed sm:leading-6"><?= $collector->translate('road_stop_0_d', 'Every night your head rests in the same spot for hours. Whatever that pillow does, your neck lives with.') ?></p>
                        </div>
                    </li>
                    <li class="road-stop relative lg:grid lg:grid-cols-2 lg:gap-x-48" data-side="right">
                        <span class="road-node z-10 grid place-items-center w-14 h-14 lg:w-[76px] lg:h-[76px] rounded-full bg-white text-ink border border-sand shadow-lg"
                                   style="--x-mobile: 36px; --x-desktop: calc(50% - 48px)">
                            <svg viewBox="0 0 24 24" class="w-7 h-7 lg:w-9 lg:h-9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 16h18"/><path d="M5 16c0-2 3-3 7-3s7 1 7 3"/><path d="M8 9 6 7M16 9l2-2M12 8V5.5"/></svg>
                        </span>
                        <div class="road-card ms-[76px] lg:ms-0 lg:col-start-2 rounded-[1.75rem] bg-white border border-sand p-5 sm:p-6 shadow-[0_30px_60px_-45px_rgba(29,34,38,.55)]">
                            <p class="text-xs font-semibold uppercase tracking-[.2em] text-turq-ink"><?= $collector->translate('road_stop_label_2', 'Stop') ?> 02</p>
                            <h3 class="mt-2 font-serif text-xl sm:text-2xl leading-snug sm:leading-8"><?= $collector->translate('road_stop_1_t', 'Flat or overstuffed, it bends you') ?></h3>
                            <p class="mt-2 text-sm sm:text-base text-muted leading-relaxed sm:leading-6"><?= $collector->translate('road_stop_1_d', 'Too low and your head drops; too high and it tips forward. Either way your neck sits out of line until morning.') ?></p>
                        </div>
                    </li>
                    <li class="road-stop relative lg:grid lg:grid-cols-2 lg:gap-x-48" data-side="left">
                        <span class="road-node z-10 grid place-items-center w-14 h-14 lg:w-[76px] lg:h-[76px] rounded-full bg-white text-ink border border-sand shadow-lg"
                                   style="--x-mobile: 28px; --x-desktop: calc(50% + 48px)">
                            <svg viewBox="0 0 24 24" class="w-7 h-7 lg:w-9 lg:h-9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 13c2-3 5-4.5 9-4.5s7 1.5 9 4.5"/><path d="M3 13c2 2 5 3 9 3s7-1 9-3"/><path d="M9 8.6c.6-1.6 1.7-2.6 3-2.6s2.4 1 3 2.6"/></svg>
                        </span>
                        <div class="road-card ms-[76px] lg:ms-0 lg:col-start-1 lg:text-end rounded-[1.75rem] bg-white border border-sand p-5 sm:p-6 shadow-[0_30px_60px_-45px_rgba(29,34,38,.55)]">
                            <p class="text-xs font-semibold uppercase tracking-[.2em] text-turq-ink"><?= $collector->translate('road_stop_label_3', 'Stop') ?> 03</p>
                            <h3 class="mt-2 font-serif text-xl sm:text-2xl leading-snug sm:leading-8"><?= $collector->translate('road_stop_2_t', 'Shape changes everything') ?></h3>
                            <p class="mt-2 text-sm sm:text-base text-muted leading-relaxed sm:leading-6"><?= $collector->translate('road_stop_2_d', 'A contoured neck roll fills the gap under your neck, and memory foam holds your head at the right height all night.') ?></p>
                        </div>
                    </li>
                    <li class="road-stop relative lg:grid lg:grid-cols-2 lg:gap-x-48" data-side="right">
                        <span class="road-node z-10 grid place-items-center w-14 h-14 lg:w-[76px] lg:h-[76px] rounded-full bg-white text-ink border border-sand shadow-lg"
                                   style="--x-mobile: 36px; --x-desktop: calc(50% - 48px)">
                            <svg viewBox="0 0 24 24" class="w-7 h-7 lg:w-9 lg:h-9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 12a8 8 0 1 1-2.3-5.7"/><path d="M20 4v4h-4"/></svg>
                        </span>
                        <div class="road-card ms-[76px] lg:ms-0 lg:col-start-2 rounded-[1.75rem] bg-white border border-sand p-5 sm:p-6 shadow-[0_30px_60px_-45px_rgba(29,34,38,.55)]">
                            <p class="text-xs font-semibold uppercase tracking-[.2em] text-turq-ink"><?= $collector->translate('road_stop_label_4', 'Stop') ?> 04</p>
                            <h3 class="mt-2 font-serif text-xl sm:text-2xl leading-snug sm:leading-8"><?= $collector->translate('road_stop_3_t', 'Even when you turn over') ?></h3>
                            <p class="mt-2 text-sm sm:text-base text-muted leading-relaxed sm:leading-6"><?= $collector->translate('road_stop_3_d', 'Back to side and back again — curved cut-outs leave room for your shoulders, so the support goes where you go.') ?></p>
                        </div>
                    </li>
                </ol>

                <div class="road-reveal relative mt-16 lg:mt-24 pt-12">
                    <span class="road-node z-10 grid place-items-center w-20 h-20 rounded-full bg-ink text-cream shadow-xl" style="--x-mobile: 50%; --x-desktop: 50%" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M5.6 18.4l2.1-2.1M16.3 7.7l2.1-2.1"/></svg>
                    </span>
                    <div class="mesh rounded-[2.5rem] text-cream overflow-hidden px-5 py-10 sm:p-12 lg:p-14" style="--veil: rgba(29,34,38,.35)">
                        <div class="grid lg:grid-cols-2 gap-10 lg:gap-14 items-center">

                            <div class="relative mx-auto w-full max-w-sm aspect-square">
                                <span class="reveal-burst"></span><span class="reveal-burst"></span>
                                <div class="absolute inset-0 rounded-[2rem] overflow-hidden ring-1 ring-white/15 shadow-[0_0_80px_rgba(29,209,161,.35)]">
                                    <img src="assets/img/upsell/pillow-hero.webp" alt="Melara Memory Foam Pillow" class="reveal-img w-full h-full object-cover" loading="lazy" decoding="async">                                                                <span class="reveal-q absolute inset-0 grid place-items-center font-serif text-8xl text-white/90" aria-hidden="true">?</span>
                                </div>
                            </div>

                            <div class="reveal-copy text-center lg:text-start">
                                <p class="text-xs font-semibold uppercase tracking-[.2em] text-turq-soft"><?= $collector->translate('road_reveal_eyebrow', 'Waiting at the end of the night') ?></p>
                                <h2 class="mt-3 font-serif text-3xl sm:text-4xl leading-tight sm:leading-10 tracking-tight">
                                    <?= $collector->translate('road_reveal_title', 'The Melara Memory Foam Pillow') ?> <em class="text-turq"><?= $collector->translate('road_reveal_accent', '— 70% off, today only.') ?></em>
                                </h2>
                                <p class="mt-3 text-cream/75 leading-relaxed"><?= $collector->translate('road_reveal_text', 'Rest your head on it tonight and wake up without the stiff neck.') ?></p>
                                <p class="mt-5 text-cream/75">
                                    <span class="font-semibold text-cream" data-cta-label>1 × <?= $collector->translate('unit_4', 'Melara Pillow') ?></span> ·
                                    <span class="font-serif text-2xl font-semibold text-turq" data-cta-total><?= $OfferApi->formatNumber($price) ?></span>
                                    <s class="text-sm text-cream/50" data-cta-retail><?= $OfferApi->formatNumber($retail) ?></s>
                                </p>
                                <div class="mt-6">
                                    <div class="shine rounded-full"><button type="button" class="yes-upsell-link inline-flex w-full items-center justify-center gap-2 rounded-full bg-turq px-5 sm:px-7 py-4 text-[15px] sm:text-lg font-bold text-ink shadow-[0_18px_40px_-14px_rgba(29,209,161,.75)] hover:bg-turq-dark hover:-translate-y-0.5 transition nudge"><span><?= $collector->translate('yes_4', 'Yes, add to my order') ?></span><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button></div>
                                    <div class="mt-3"><button type="button" class="js-open-no-thanks-popup block w-full text-center lg:text-start text-xs sm:text-sm text-cream/60 underline underline-offset-4 hover:text-cream transition"><?= $collector->translate('no_thanks_3', 'No thanks, I don\'t want this one-time offer') ?></button></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 md:py-[30px] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div class="reveal relative">
                <div class="aspect-[4/5] rounded-[2.5rem] overflow-hidden shadow-[0_40px_80px_-40px_rgba(29,34,38,.45)]">
                    <div class="w-full h-full animate-kenburns"><img src="assets/img/upsell/pillow-life.webp" alt="Person sleeping peacefully on the Melara memory foam pillow" class="w-full h-full object-cover" loading="lazy" decoding="async"></div>
                </div>
                <div class="absolute -bottom-6 start-6 sm:-start-6 bg-white rounded-2xl shadow-xl px-5 py-4 max-w-[260px]">
                    <p class="text-amber-500 text-sm">★★★★★</p>
                    <p class="mt-1 text-sm leading-snug">“<?= $collector->translate('review_0_text', 'My neck used to be stiff every morning. Two nights on this pillow and I noticed the difference.') ?>”</p>
                    <p class="mt-2 text-xs text-muted">Yoshiko A.</p>
                </div>
            </div>

            <div>
                <p class="reveal text-sm font-medium uppercase tracking-[.2em] text-turq-ink"><?= $collector->translate('story_eyebrow', 'The complete Melara routine') ?></p>
                <h2 class="reveal mt-4 font-serif text-4xl sm:text-5xl leading-tight sm:leading-none tracking-tight" data-delay="1">
                    <?= $collector->translate('story_title', 'Day and night,') ?> <em class="text-turq-ink"><?= $collector->translate('story_accent', 'fully supported.') ?></em>
                </h2>
                <ol class="mt-10 space-y-4">
                    <li class="reveal flex gap-5 rounded-2xl bg-white border border-sand/70 p-5 hover:shadow-lg hover:-translate-y-0.5 transition" data-delay="1">
                        <span class="grid place-items-center shrink-0 w-10 h-10 rounded-full bg-turq-soft text-turq-ink font-bold text-sm">01</span>
                        <div>
                            <h3 class="font-semibold"><?= $collector->translate('story_0_t', 'Sit well all day') ?></h3>
                            <p class="mt-1 text-sm text-muted leading-relaxed"><?= $collector->translate('story2_0_d', 'Your Melara Sitora keeps your lower back and tailbone comfortable through every meal, book and film.') ?></p>
                        </div>
                    </li>
                    <li class="reveal flex gap-5 rounded-2xl bg-white border border-sand/70 p-5 hover:shadow-lg hover:-translate-y-0.5 transition" data-delay="2">
                        <span class="grid place-items-center shrink-0 w-10 h-10 rounded-full bg-turq-soft text-turq-ink font-bold text-sm">02</span>
                        <div>
                            <h3 class="font-semibold"><?= $collector->translate('story_1_t', 'Sleep well all night') ?></h3>
                            <p class="mt-1 text-sm text-muted leading-relaxed"><?= $collector->translate('story_1_d', 'The Melara Pillow keeps your neck and shoulders relaxed, so your body actually recovers.') ?></p>
                        </div>
                    </li>
                    <li class="reveal flex gap-5 rounded-2xl bg-white border border-sand/70 p-5 hover:shadow-lg hover:-translate-y-0.5 transition" data-delay="3">
                        <span class="grid place-items-center shrink-0 w-10 h-10 rounded-full bg-turq-soft text-turq-ink font-bold text-sm">03</span>
                        <div>
                            <h3 class="font-semibold"><?= $collector->translate('story_2_t', 'Wake up ready') ?></h3>
                            <p class="mt-1 text-sm text-muted leading-relaxed"><?= $collector->translate('story_2_d', 'Less stiffness in the morning means you start the day feeling like yourself.') ?></p>
                        </div>
                    </li>
                </ol>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="reveal hidden lg:flex mt-14 items-center justify-between gap-8 rounded-[1.75rem] border px-8 py-6 border-sand bg-white shadow-[0_30px_60px_-45px_rgba(29,34,38,.5)]">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[.2em] text-turq-ink"><?= $collector->translate('sec_cta_eyebrow_2', 'One-time offer · 70% off') ?></p>
                    <p class="mt-1.5 font-serif text-2xl">
                        <?= $collector->translate('sec_cta_add_2', 'Add') ?> <span data-cta-label>1 × <?= $collector->translate('unit_5', 'Melara Pillow') ?></span> <?= $collector->translate('sec_cta_for_2', 'for') ?>            <span class="text-turq-ink" data-cta-total><?= $OfferApi->formatNumber($price) ?></span>
                        <s class="text-base text-muted" data-cta-retail><?= $OfferApi->formatNumber($retail) ?></s>
                    </p>
                </div>
                <div class="w-[340px] shrink-0">
                    <div class="shine rounded-full"><button type="button" class="yes-upsell-link inline-flex w-full items-center justify-center gap-2 rounded-full bg-turq px-5 sm:px-7 py-4 text-[15px] sm:text-lg font-bold text-ink shadow-[0_18px_40px_-14px_rgba(29,209,161,.75)] hover:bg-turq-dark hover:-translate-y-0.5 transition nudge"><span><?= $collector->translate('yes_5', 'Yes, add to my order') ?></span><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button></div>
                    <div class="mt-3"><button type="button" class="js-open-no-thanks-popup block w-full text-center text-xs underline underline-offset-4 transition text-muted hover:text-ink"><?= $collector->translate('no_thanks_4', 'No thanks, I don\'t want this one-time offer') ?></button></div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-soft-gray py-5 md:py-[30px] lg:py-28 border-y border-sand">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="reveal text-amber-500 text-lg">★★★★★</p>
                <h2 class="reveal mt-2 font-serif text-4xl sm:text-5xl tracking-tight" data-delay="1"><?= $collector->translate('reviews_title', '1,781 verified reviews') ?></h2>
            </div>

            <div class="mt-12 grid md:grid-cols-3 gap-5">
                <figure class="reveal bg-white rounded-3xl p-7 border border-sand/70 hover:-translate-y-1 hover:shadow-xl transition" data-delay="1">
                    <p class="text-amber-500 text-sm">★★★★★</p>
                    <blockquote class="mt-3 font-serif text-lg leading-snug">“<?= $collector->translate('review_0_text_2', 'My neck used to be stiff every morning. Two nights on this pillow and I noticed the difference.') ?>”</blockquote>
                    <figcaption class="mt-5 flex items-center gap-3 text-sm">
                        <img src="assets/img/user/review-yoshiko.png" alt="Yoshiko A." width="44" height="44" class="w-11 h-11 rounded-full object-cover ring-2 ring-white shadow-md" loading="lazy">
                        <span>
                            <span class="block font-semibold">Yoshiko A.</span>
                            <span class="block text-xs text-muted"><?= $collector->translate('review_0_role', 'Back sleeper') ?></span>
                        </span>
                        <span class="ms-auto rounded-full bg-turq-soft/70 border border-turq/30 px-2.5 py-0.5 text-[11px] font-semibold text-turq-ink whitespace-nowrap"><?= $collector->translate('verified', 'Verified buyer') ?></span>
                    </figcaption>
                </figure>
                <figure class="reveal bg-white rounded-3xl p-7 border border-sand/70 hover:-translate-y-1 hover:shadow-xl transition" data-delay="2">
                    <p class="text-amber-500 text-sm">★★★★★</p>
                    <blockquote class="mt-3 font-serif text-lg leading-snug">“<?= $collector->translate('review_1_text', 'Finally a pillow that doesn\'t go flat by 2am. My shoulders thank me.') ?>”</blockquote>
                    <figcaption class="mt-5 flex items-center gap-3 text-sm">
                        <img src="assets/img/user/review-taichi.png" alt="Taichi T." width="44" height="44" class="w-11 h-11 rounded-full object-cover ring-2 ring-white shadow-md" loading="lazy">
                        <span>
                            <span class="block font-semibold">Taichi T.</span>
                            <span class="block text-xs text-muted"><?= $collector->translate('review_1_role', 'Side sleeper') ?></span>
                        </span>
                        <span class="ms-auto rounded-full bg-turq-soft/70 border border-turq/30 px-2.5 py-0.5 text-[11px] font-semibold text-turq-ink whitespace-nowrap"><?= $collector->translate('verified_2', 'Verified buyer') ?></span>
                    </figcaption>
                </figure>
                <figure class="reveal bg-white rounded-3xl p-7 border border-sand/70 hover:-translate-y-1 hover:shadow-xl transition" data-delay="3">
                    <p class="text-amber-500 text-sm">★★★★★</p>
                    <blockquote class="mt-3 font-serif text-lg leading-snug">“<?= $collector->translate('review_2_text', 'Soft but supportive, and it stays cool. I bought a second one for the guest room.') ?>”</blockquote>
                    <figcaption class="mt-5 flex items-center gap-3 text-sm">
                        <img src="assets/img/user/review-lau.png" alt="Lau B." width="44" height="44" class="w-11 h-11 rounded-full object-cover ring-2 ring-white shadow-md" loading="lazy">
                        <span>
                            <span class="block font-semibold">Lau B.</span>
                            <span class="block text-xs text-muted"><?= $collector->translate('review_2_role', 'Light sleeper') ?></span>
                        </span>
                        <span class="ms-auto rounded-full bg-turq-soft/70 border border-turq/30 px-2.5 py-0.5 text-[11px] font-semibold text-turq-ink whitespace-nowrap"><?= $collector->translate('verified_3', 'Verified buyer') ?></span>
                    </figcaption>
                </figure>
            </div>

            <div class="reveal hidden lg:flex mt-14 items-center justify-between gap-8 rounded-[1.75rem] border px-8 py-6 border-sand bg-white shadow-[0_30px_60px_-45px_rgba(29,34,38,.5)]">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[.2em] text-turq-ink"><?= $collector->translate('sec_cta_eyebrow_3', 'One-time offer · 70% off') ?></p>
                    <p class="mt-1.5 font-serif text-2xl">
                        <?= $collector->translate('sec_cta_add_3', 'Add') ?> <span data-cta-label>1 × <?= $collector->translate('unit_6', 'Melara Pillow') ?></span> <?= $collector->translate('sec_cta_for_3', 'for') ?>            <span class="text-turq-ink" data-cta-total><?= $OfferApi->formatNumber($price) ?></span>
                        <s class="text-base text-muted" data-cta-retail><?= $OfferApi->formatNumber($retail) ?></s>
                    </p>
                </div>
                <div class="w-[340px] shrink-0">
                    <div class="shine rounded-full"><button type="button" class="yes-upsell-link inline-flex w-full items-center justify-center gap-2 rounded-full bg-turq px-5 sm:px-7 py-4 text-[15px] sm:text-lg font-bold text-ink shadow-[0_18px_40px_-14px_rgba(29,209,161,.75)] hover:bg-turq-dark hover:-translate-y-0.5 transition nudge"><span><?= $collector->translate('yes_6', 'Yes, add to my order') ?></span><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button></div>
                    <div class="mt-3"><button type="button" class="js-open-no-thanks-popup block w-full text-center text-xs underline underline-offset-4 transition text-muted hover:text-ink"><?= $collector->translate('no_thanks_5', 'No thanks, I don\'t want this one-time offer') ?></button></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Before you decide: price recap (follows the quantity picker) + the questions people have at this point -->
    <section id="decide" class="px-4 sm:px-6 lg:px-8 py-5 md:py-[30px] lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="text-center max-w-2xl mx-auto">
                <p class="reveal text-sm font-medium uppercase tracking-[.2em] text-turq-ink"><?= $collector->translate('dec_eyebrow', 'Before you decide') ?></p>
                <h2 class="reveal mt-3 font-serif text-4xl sm:text-5xl leading-tight tracking-tight" data-delay="1"><?= $collector->translate('dec_title', 'Here\'s exactly') ?> <em class="text-turq-ink"><?= $collector->translate('dec_accent', 'what you get today.') ?></em></h2>
            </div>
            <div class="mt-8 lg:mt-12 grid lg:grid-cols-12 gap-6 lg:gap-10 items-start">
                <div class="reveal lg:col-span-5 rounded-[2rem] bg-white border border-sand shadow-[0_30px_80px_-40px_rgba(29,34,38,.35)] overflow-hidden">
                    <div class="flex items-center justify-between gap-3 bg-ink text-cream px-6 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[.2em]"><?= $collector->translate('rcpt_title', 'Your one-time price') ?></p>
                        <span class="rounded-full bg-turq px-3 py-1 text-xs font-bold text-ink"><?= $collector->translate('rcpt_badge', '70% off') ?></span>
                    </div>
                    <dl class="px-6 py-2 text-sm">
                        <div class="flex justify-between gap-4 py-3 border-b border-dashed border-sand"><dt class="text-muted"><?= $collector->translate('rcpt_item', 'Item') ?></dt><dd class="font-semibold text-end" data-cta-label>1 × <?= $collector->translate('rcpt_unit', 'Melara Pillow') ?></dd></div>
                        <div class="flex justify-between gap-4 py-3 border-b border-dashed border-sand"><dt class="text-muted"><?= $collector->translate('rcpt_regular', 'Regular price') ?></dt><dd class="text-end"><s class="text-muted" data-cta-retail><?= $OfferApi->formatNumber($retail) ?></s></dd></div>
                        <div class="flex justify-between gap-4 py-3 border-b border-dashed border-sand"><dt class="text-muted"><?= $collector->translate('rcpt_shipping', 'Shipping') ?></dt><dd class="font-semibold text-turq-ink text-end"><?= $collector->translate('rcpt_shipping_v', 'Free, in the same box') ?></dd></div>
                        <div class="flex justify-between gap-4 py-3 border-b border-dashed border-sand"><dt class="text-muted"><?= $collector->translate('rcpt_card', 'Card details') ?></dt><dd class="font-semibold text-end"><?= $collector->translate('rcpt_card_v', 'Not needed, one click') ?></dd></div>
                        <div class="flex justify-between gap-4 py-3 border-b border-dashed border-sand"><dt class="text-muted"><?= $collector->translate('rcpt_refund', 'Refunds') ?></dt><dd class="font-semibold text-end"><?= $collector->translate('rcpt_refund_v', '30-day policy') ?></dd></div>
                        <div class="flex items-end justify-between gap-4 pt-4 pb-2"><dt class="font-semibold"><?= $collector->translate('rcpt_today', 'You pay today') ?></dt><dd class="font-serif text-4xl text-ink" data-cta-total><?= $OfferApi->formatNumber($price) ?></dd></div>
                    </dl>
                    <div class="mx-6 mb-5 flex items-center justify-center gap-2 rounded-xl bg-turq-soft px-4 py-2.5 text-sm font-semibold text-turq-ink">
                        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 12V7H4v5M4 12v7h16v-7M2 12h20M12 7v14M12 7c-1.5-3-5-3-5-.5S12 7 12 7Zm0 0c1.5-3 5-3 5-.5S12 7 12 7Z"/></svg>
                        <span><?= $collector->translate('rcpt_save', 'You save') ?> <span data-cta-save><?= $OfferApi->formatNumber($retail - $price) ?></span></span>
                    </div>
                    <div class="px-6 pb-6">
                        <div class="shine rounded-full"><button type="button" class="yes-upsell-link inline-flex w-full items-center justify-center gap-2 rounded-full bg-turq px-5 sm:px-7 py-4 text-[15px] sm:text-lg font-bold text-ink shadow-[0_18px_40px_-14px_rgba(29,209,161,.75)] hover:bg-turq-dark hover:-translate-y-0.5 transition nudge"><span><?= $collector->translate('rcpt_yes', 'Yes, add to my order') ?></span><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button></div>
                        <div class="mt-3"><button type="button" class="js-open-no-thanks-popup block w-full text-center text-xs underline underline-offset-4 transition text-muted hover:text-ink"><?= $collector->translate('rcpt_no', 'No thanks, I don\'t want this one-time offer') ?></button></div>
                    </div>
                </div>
                <div class="lg:col-span-7 space-y-3">
                    <details class="reveal group rounded-2xl bg-white border border-sand/70 open:border-turq/50 open:shadow-[0_18px_40px_-24px_rgba(29,34,38,.35)] transition" open>
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 font-semibold [&::-webkit-details-marker]:hidden"><?= $collector->translate('dec_q0', 'Will I pay for shipping again?') ?><span class="grid place-items-center shrink-0 w-7 h-7 rounded-full bg-linen text-lg leading-none transition group-open:rotate-45 group-open:bg-turq-soft" aria-hidden="true">+</span></summary>
                        <p class="px-5 pb-5 -mt-1 text-sm text-muted leading-relaxed"><?= $collector->translate('dec_a0', 'No. It ships free, in the same box as the order you just placed.') ?></p>
                    </details>
                    <details class="reveal group rounded-2xl bg-white border border-sand/70 open:border-turq/50 open:shadow-[0_18px_40px_-24px_rgba(29,34,38,.35)] transition" data-delay="1">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 font-semibold [&::-webkit-details-marker]:hidden"><?= $collector->translate('dec_q1', 'Do I need to enter my card details again?') ?><span class="grid place-items-center shrink-0 w-7 h-7 rounded-full bg-linen text-lg leading-none transition group-open:rotate-45 group-open:bg-turq-soft" aria-hidden="true">+</span></summary>
                        <p class="px-5 pb-5 -mt-1 text-sm text-muted leading-relaxed"><?= $collector->translate('dec_a1', 'No. One click adds it to the order you just paid for, using the same secure payment.') ?></p>
                    </details>
                    <details class="reveal group rounded-2xl bg-white border border-sand/70 open:border-turq/50 open:shadow-[0_18px_40px_-24px_rgba(29,34,38,.35)] transition" data-delay="2">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 font-semibold [&::-webkit-details-marker]:hidden"><?= $collector->translate('dec_q2', 'Will it suit the way I sleep?') ?><span class="grid place-items-center shrink-0 w-7 h-7 rounded-full bg-linen text-lg leading-none transition group-open:rotate-45 group-open:bg-turq-soft" aria-hidden="true">+</span></summary>
                        <p class="px-5 pb-5 -mt-1 text-sm text-muted leading-relaxed"><?= $collector->translate('dec_a2', 'It is designed for back and side sleepers. The contoured neck roll supports the natural curve of your neck, and the shoulder cut-outs give you room when you turn onto your side.') ?></p>
                    </details>
                    <details class="reveal group rounded-2xl bg-white border border-sand/70 open:border-turq/50 open:shadow-[0_18px_40px_-24px_rgba(29,34,38,.35)] transition" data-delay="3">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 font-semibold [&::-webkit-details-marker]:hidden"><?= $collector->translate('dec_q3', 'Can I get this price later?') ?><span class="grid place-items-center shrink-0 w-7 h-7 rounded-full bg-linen text-lg leading-none transition group-open:rotate-45 group-open:bg-turq-soft" aria-hidden="true">+</span></summary>
                        <p class="px-5 pb-5 -mt-1 text-sm text-muted leading-relaxed"><?= $collector->translate('dec_a3', 'No. This price is offered once, here, right after your order. If you leave this page, it won\'t be shown again.') ?></p>
                    </details>
                    <details class="reveal group rounded-2xl bg-white border border-sand/70 open:border-turq/50 open:shadow-[0_18px_40px_-24px_rgba(29,34,38,.35)] transition" data-delay="4">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 font-semibold [&::-webkit-details-marker]:hidden"><?= $collector->translate('dec_q4', 'What if it\'s not right for me?') ?><span class="grid place-items-center shrink-0 w-7 h-7 rounded-full bg-linen text-lg leading-none transition group-open:rotate-45 group-open:bg-turq-soft" aria-hidden="true">+</span></summary>
                        <p class="px-5 pb-5 -mt-1 text-sm text-muted leading-relaxed"><?= $collector->translate('dec_a4', 'You\'re covered by our 30-day refund policy, just like the rest of your order.') ?></p>
                    </details>
                </div>
            </div>
        </div>
    </section>

    <section id="finalCta" class="px-4 sm:px-6 lg:px-8 py-5 md:py-[30px] lg:py-24">
        <div class="reveal relative mx-auto max-w-5xl mesh rounded-[2.5rem] overflow-hidden text-cream px-6 py-12 sm:p-16 text-center" style="--veil: rgba(29,34,38,.35)">
            <p class="text-xs font-semibold uppercase tracking-[.2em] text-turq-soft"><?= $collector->translate('final_eyebrow', 'Last chance — this page won\'t show again') ?></p>
            <h2 class="mt-4 font-serif text-4xl sm:text-5xl leading-tight sm:leading-none tracking-tight"><?= $collector->translate('final2_title', 'Complete your comfort, from your favourite chair to bed.') ?></h2>
            <p class="mt-4 text-cream/75 max-w-xl mx-auto">
                <?= $collector->translate('final_price_pre', 'Today only') ?>:
                <span class="font-semibold text-cream" data-cta-label>1 × <?= $collector->translate('unit_7', 'Melara Pillow') ?></span> ·
                <span class="font-semibold text-cream" data-cta-total><?= $OfferApi->formatNumber($price) ?></span>
                <span class="line-through text-cream/50" data-cta-retail><?= $OfferApi->formatNumber($retail) ?></span> ·
                <?= $collector->translate('final_price_post', 'ships free with your order.') ?>
            </p>
            <div class="mt-8 max-w-md mx-auto shine rounded-full"><button type="button" class="yes-upsell-link inline-flex w-full items-center justify-center gap-2 rounded-full bg-turq px-5 sm:px-7 py-4 text-[15px] sm:text-lg font-bold text-ink shadow-[0_18px_40px_-14px_rgba(29,209,161,.75)] hover:bg-turq-dark hover:-translate-y-0.5 transition nudge"><span><?= $collector->translate('yes_2', 'Yes, add it to my order') ?></span><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button></div>
            <div class="mt-4 max-w-md mx-auto"><button type="button" class="js-open-no-thanks-popup block w-full text-center text-xs sm:text-sm text-cream/60 underline underline-offset-4 hover:text-cream transition"><?= $collector->translate('no_thanks_6', 'No thanks, I don\'t want this one-time offer') ?></button></div>
        </div>
    </section>

</main>

<div id="mobileCta" class="lg:hidden fixed inset-x-0 bottom-0 z-50" inert>
    <div class="rounded-t-[1.75rem] bg-white/95 backdrop-blur-sm border-t border-sand shadow-[0_-14px_40px_-12px_rgba(29,34,38,.35)] px-4 pt-3" style="padding-bottom: max(.75rem, env(safe-area-inset-bottom))">
        <p class="text-center text-[13px] text-muted leading-tight whitespace-nowrap">
            <span class="font-medium text-ink" data-cta-label>1 × <?= $collector->translate('unit_8', 'Melara Pillow') ?></span> ·
            <span class="font-serif text-lg font-semibold text-ink" data-cta-total><?= $OfferApi->formatNumber($price) ?></span>
            <s class="text-xs" data-cta-retail><?= $OfferApi->formatNumber($retail) ?></s>
        </p>
        <div class="mt-2.5 shine rounded-full"><button type="button" class="yes-upsell-link inline-flex w-full items-center justify-center gap-2 rounded-full bg-turq px-5 sm:px-7 py-4 text-[15px] sm:text-lg font-bold text-ink shadow-[0_18px_40px_-14px_rgba(29,209,161,.75)] hover:bg-turq-dark hover:-translate-y-0.5 transition nudge"><span><?= $collector->translate('yes_7', 'Yes, add to my order') ?></span><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button></div>
        <div class="mt-2"><button type="button" class="js-open-no-thanks-popup block w-full py-1 text-center text-[13px] text-muted underline underline-offset-4"><?= $collector->translate('no_thanks_7', 'No thanks, I don\'t want this one-time offer') ?></button></div>
    </div>
</div>

<footer class="border-t border-sand pt-8 pb-40 lg:pb-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-muted">
        <img src="assets/img/logo.svg" alt="Melara" class="h-8 w-auto" width="190" height="49" loading="lazy">
        <p><?= $collector->translate('taxes', '*Local taxes may apply.') ?></p>
        <div class="flex flex-col sm:flex-row items-center gap-4">
            <?= languageSelector('locked') ?>            <p>© <?= date('Y') ?> <?= $OfferApi->corporateName ?: 'Melara' ?>. <?= $collector->translate('rights', 'All rights reserved.') ?></p>
        </div>
    </div>
    <p class="mx-auto max-w-3xl px-4 mt-6 text-center text-[11px] leading-relaxed text-muted/80"><?= $collector->translate('footer_disclaimer', 'Melara products are comfort and posture-support products, not medical devices. They are not intended to diagnose, treat, cure or prevent any disease. Individual results vary. If you have ongoing pain, consult your doctor or physiotherapist.') ?></p>
</footer>

<div id="no-thanks-popup" class="fixed inset-0 z-[100] items-end sm:items-center justify-center p-3 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="ds-title"
           data-popup-product-id="<?= $dsId ?>" data-popup-unitprice="<?= $dsPrice ?>" data-popup-shipping="<?= $dsShipping ?>" data-popup-variant="<?= $dsVariant ?>">
    <div class="ds-backdrop absolute inset-0 bg-ink/70 backdrop-blur-xs"></div>

    <div class="ds-card relative w-full max-w-lg rounded-[2rem] bg-white overflow-hidden shadow-[0_40px_90px_-30px_rgba(0,0,0,.6)]">
        <div class="mesh relative text-cream text-center px-6 pt-7 pb-6" style="--veil: rgba(29,34,38,.4)">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/15 px-3 py-1 text-[11px] font-semibold uppercase tracking-[.18em] text-turq-soft">
                <span class="w-1.5 h-1.5 rounded-full bg-turq animate-pulse"></span><?= $collector->translate('ds_pill', 'Wait — one last price drop') ?>
            </span>
            <h2 id="ds-title" class="mt-3 font-serif text-2xl sm:text-3xl leading-tight sm:leading-9 tracking-tight">
                <?= $collector->translate('ds_title', 'Get the Melara Memory Foam Pillow for just') ?> <em class="text-turq"><?= $OfferApi->formatNumber($dsPrice) ?></em>
            </h2>
            <p class="mt-2 text-sm text-cream/70"><?= $collector->translate('ds_lead', 'Our lowest price ever — only here, only for one.') ?></p>
        </div>

        <div class="p-5 sm:p-6">
            <div class="flex items-center gap-4">
                <div class="relative w-[36%] shrink-0 aspect-square rounded-2xl bg-linen grid place-items-center overflow-hidden">
                    <img src="assets/img/upsell/pillow-pack.png" alt="Melara Memory Foam Pillow" class="w-[82%] drop-shadow-[0_10px_12px_rgba(29,34,38,.3)]" loading="lazy">
                    <span class="absolute top-2 start-2 rounded-full bg-turq text-ink text-[11px] font-bold px-2 py-0.5">-<?= round(($price - $dsPrice) / $price * 100) ?>%</span>
                </div>
                <div class="flex-1 grid grid-cols-2 gap-2 text-center">
                    <div class="rounded-2xl border border-sand bg-cream px-2 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-muted"><?= $collector->translate('ds_was', 'Was') ?></p>
                        <p class="mt-1 text-xs text-muted">1 × <?= $collector->translate('unit_9', 'Melara Pillow') ?></p>
                        <p class="mt-1 font-serif text-lg text-muted line-through"><?= $OfferApi->formatNumber($price) ?></p>
                    </div>
                    <div class="relative rounded-2xl border-2 border-turq bg-turq-soft/40 px-2 py-3">
                        <span class="absolute -top-2.5 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-ink text-cream text-[10px] font-semibold px-2 py-0.5"><?= $collector->translate('ds_save', 'Save') ?> <?= $OfferApi->formatNumber(round($price - $dsPrice, 2)) ?></span>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-turq-ink"><?= $collector->translate('ds_now', 'Now') ?></p>
                        <p class="mt-1 text-xs text-muted">1 × <?= $collector->translate('unit_10', 'Melara Pillow') ?></p>
                        <p class="mt-1 font-serif text-2xl"><?= $OfferApi->formatNumber($dsPrice) ?></p>
                    </div>
                </div>
            </div>

            <div class="mt-5 shine rounded-full">
                <button type="button" class="popup-yes-btn js-popup-yes inline-flex w-full items-center justify-center gap-2 rounded-full bg-turq px-5 sm:px-7 py-4 text-[15px] sm:text-lg font-bold text-ink shadow-[0_18px_40px_-14px_rgba(29,209,161,.75)] hover:bg-turq-dark hover:-translate-y-0.5 transition nudge"><span><?= $collector->translate('ds_yes', 'Yes, add it for') ?> <?= $OfferApi->formatNumber($dsPrice) ?></span><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>
            </div>
            <p class="mt-3 text-center text-xs text-muted"><?= $collector->translate('one_click_2', 'One click — added to the order you just paid for. No card details needed.') ?></p>
            <div class="mt-3">
                <button type="button" class="no-upsell-link block w-full text-center text-xs sm:text-sm text-muted underline underline-offset-4 hover:text-ink transition"><?= $collector->translate('ds_skip', 'No thanks, I\'ll miss this exclusive offer') ?></button>
            </div>
        </div>
    </div>
</div>

<script src="src/system/<?= $OfferApi->targetLanguage ?>.js?t=<?= $OfferApi->assetVersion('src/system/' . $OfferApi->targetLanguage . '.js') ?>"></script>
<script src="public/js/upsell.js?t=<?= $OfferApi->assetVersion('public/js/upsell.js') ?>"></script>
</body>
</html>
<?php $collector->saveTranslation(); ?>
