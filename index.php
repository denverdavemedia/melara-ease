<?php
/* Melara Sitora landing page */
include_once("integrated/setup.php");

// ?custom_config=hr serves the hemorrhoid-friendly sitting-comfort version (index-hr.php, copy in lang/<code>/index-hr.json).
// Only the URL decides the version, never the session: the language selector and the checkout links keep
// ?custom_config=hr in the URL, so the session is not needed, and relying on it showed the HR page on plain visits.
if (($_GET['custom_config'] ?? '') === 'hr') {
    include __DIR__ . '/index-hr.php';
    return;
}
// Plain page: drop a leftover 'hr' from an earlier visit so orders placed from here aren't tagged as HR
if (($_SESSION['custom_config'] ?? '') === 'hr') {
    unset($_SESSION['custom_config']);
}
include_once(BASEPATH . '/src/JsonTranslateCollector.php');
include_once(__DIR__ . '/includes/language-selector.php');
include_once(__DIR__ . '/includes/deliver-to.php');

$collector = new JsonCollector(targetLanguage: $OfferApi->targetLanguage, pageName: "index");

$brand    = 'Melara';
$product  = 'Melara Sitora';
$currency = '$';
$logo     = 'assets/img/logo.svg';

$img = [
    'hero'     => 'assets/img/index/hero.webp',   // Margaret at her dining table on the cushion
    'detail'   => 'assets/img/index/detail.webp',    // hand pressing the memory foam
    'cta'      => 'assets/img/index/cta.webp',       // final banner: cushion on a chair
];

/** Render an image if it exists, otherwise a soft labelled placeholder. */
function photo($src, $alt, $class = '', $label = null, $eager = false) {
    if (is_file(__DIR__ . '/' . $src)) {
        return '<img src="' . $src . '?v=' . filemtime(__DIR__ . '/' . $src) . '" alt="' . htmlspecialchars($alt) . '" class="' . $class . ' object-cover" ' . ($eager ? 'fetchpriority="high"' : 'loading="lazy"') . ' decoding="async">';
    }
    $label = $label ?: basename($src);
    return '<div class="' . $class . ' ph grid place-items-center" role="img" aria-label="' . htmlspecialchars($alt) . '">'
         . '<span class="flex flex-col items-center gap-2 px-6 text-center"><span class="text-[11px] tracking-widest uppercase text-ink/40 bg-white/60 rounded-full px-3 py-1">' . htmlspecialchars($label) . '</span>'
         . '<span class="max-w-xs text-xs text-ink/50 leading-snug normal-case tracking-normal">' . htmlspecialchars($alt) . '</span></span></div>';
}

$offers = [
    ['qty' => 1, 'title' => 'Just me',       'tag' => 'Start feeling better', 'price' => 49,  'compare' => 109,  'perks' => ['1 × Melara Sitora', 'Skin-friendly fabric'], 'badge' => null, 'featured' => false],
    ['qty' => 2, 'title' => 'Home & work',   'tag' => 'Home office + work',           'price' => 89,  'compare' => 234, 'perks' => ['2 × Melara Sitora', 'Free spare cover', 'Priority support'], 'badge' => 'Most loved', 'featured' => true],
    ['qty' => 3, 'title' => 'Every chair',    'tag' => 'Office · home · gaming',    'price' => 119, 'compare' => 340, 'perks' => ['3 × Melara Sitora', '2 free spare covers', 'Linen carry bag'], 'badge' => 'Save 65%', 'featured' => false],
    ['qty' => 4, 'title' => 'The team',    'tag' => 'Family or office team',    'price' => 139, 'compare' => 463, 'perks' => ['4 × Melara Sitora', '4 free spare covers', 'Linen carry bag', 'Lifetime foam warranty'], 'badge' => 'Best value', 'featured' => false],
];
// Pack prices from the CRM, retail price from the same discount tiers as checkout.php
$discountTiers = [1 => 0.55, 2 => 0.62, 3 => 0.65, 4 => 0.70];
foreach ((array) $OfferApi->offerProducts as $row) {
    $q = (int) $row->maxOrderQty;
    foreach ($offers as &$o) {
        if ($o['qty'] === $q && stripos($row->productName, 'Seat Cushion') !== false) {
            $o['price']   = (float) $row->price;
            $o['compare'] = round($row->price / (1 - $discountTiers[$q]));
        }
    }
    unset($o);
}
$currency = $OfferApi->currencySymbol ?: $currency;

$pains = [
    ['t' => $collector->translate('pain2_0_t', 'Lower back that aches by afternoon'), 'd' => $collector->translate('pain2_0_d', 'After a while in a chair, your lower back feels stiff and sore, and it lingers long after you stand up.')],
    ['t' => $collector->translate('pain2_1_t', 'A tender tailbone'), 'd' => $collector->translate('pain2_1_d', 'Hard seats press right on your coccyx, so sitting down becomes something you brace for.')],
    ['t' => $collector->translate('pain2_2_t', 'Discomfort that runs down your leg'), 'd' => $collector->translate('pain2_2_d', 'Pressure on your lower back and buttocks can aggravate the sciatic nerve, sending discomfort down the back of your thigh.')],
    ['t' => $collector->translate('pain2_3_t', 'Always shifting to get comfortable'), 'd' => $collector->translate('pain2_3_d', 'Your hips and sit bones carry all your weight, so you can\'t settle into a meal, a book or a film.')],
    ['t' => $collector->translate('pain2_4_t', 'Slumping without realising'), 'd' => $collector->translate('pain2_4_d', 'Without support, your spine rounds forward, and your back and neck pay for it later.')],
];

// The 5 support zones from the supplier spec, each matched to a pain above.
// [zone number, marker x%, y% on anatomy-assembled.webp, pain index, zone title, how it helps]
$zones = [
    [1, 68, 25, 0, $collector->translate('sol2_0_t', 'Lumbar support zone'),         $collector->translate('sol2_0_d', 'The shell-shaped backrest follows the natural inward curve of your lower spine, so your back muscles can relax instead of holding you upright.')],
    [2, 89, 38, 4, $collector->translate('sol2_1_t', 'Upright posture wings'),       $collector->translate('sol2_1_d', 'Raised side wings gently keep your back upright and your spine aligned, so you don\'t slump forward as you tire.')],
    [3, 53, 57, 1, $collector->translate('sol2_2_t', 'Coccyx relief vent'),          $collector->translate('sol2_2_d', 'A hollow sits right beneath your tailbone, so it is suspended instead of pressed into the seat, and air can circulate.')],
    [4, 34, 55, 3, $collector->translate('sol2_3_t', 'Sit-bone pressure zone'),      $collector->translate('sol2_3_d', 'The contoured seat cradles your hips and spreads your weight over a wider area, easing pressure on your sit bones.')],
    [5, 21, 72, 2, $collector->translate('sol2_4_t', 'Thigh and leg support'),       $collector->translate('sol2_4_d', 'Gentle grooves keep your thighs level and side by side, easing pressure under your buttocks and along the back of your thighs, where the sciatic nerve runs.')],
];

// Everyday home scenes. Images are placeholders until the new photos are made (see the alt text for what each shows).
$life = [
    ['img' => 'assets/img/index/life-sofa.jpg',   'alt' => 'Woman in her 60s reading a book on the sofa, sitting on the Melara Sitora cushion',             't' => $collector->translate('life3_0_t', 'On the sofa'),          'd' => $collector->translate('life3_0_d', 'Sink into a film or a good book with your lower back supported.')],
    ['img' => 'assets/img/index/life-dining.jpg', 'alt' => 'Couple laughing over a family dinner, the woman sitting on the Melara Sitora cushion on a wooden dining chair',          't' => $collector->translate('life3_1_t', 'On dining chairs'),     'd' => $collector->translate('life2_0_d', 'Long family meals, puzzles and card games, in comfort.')],
    ['img' => 'assets/img/index/life-car.jpg',    'alt' => 'Woman adjusting the rear-view mirror in the driver\'s seat, sitting on the Melara Sitora cushion',         't' => $collector->translate('life3_2_t', 'In the driver\'s seat'), 'd' => $collector->translate('life3_2_d', 'Support for errands, family visits and longer drives.')],
    ['img' => 'assets/img/index/life-office.jpg', 'alt' => 'Man working at his desk, sitting on the Melara Sitora cushion on an office chair', 't' => $collector->translate('life3_3_t', 'On the office chair'),  'd' => $collector->translate('life3_3_d', 'Hours at the desk, without the afternoon ache.')],
];

$faqs = [
    ['q' => $collector->translate('faq_0_q', 'Will it fit my chair?'), 'a' => $collector->translate('faq3_0_a', 'It measures 44 × 42 × 32 cm (17.3 × 16.5 × 12.6 in, W × D × H) and fits most sofas, dining chairs, car seats and office chairs with a seat at least 44 cm (17.3 in) wide. Just set it on the seat and sit back.')],
    ['q' => $collector->translate('faq2_sci_q', 'Can it help with sciatica?'), 'a' => $collector->translate('faq2_sci_a', 'Many people find that sitting on a flat, hard seat makes sciatic discomfort worse. Melara is designed to take pressure off the tailbone, sit bones and back of the thighs, which can make sitting more comfortable. It is a comfort product, not a medical device, so if you have ongoing or severe pain, please speak to your doctor or physiotherapist.')],
    ['q' => $collector->translate('faq2_firm_q', 'Is it firm or soft?'), 'a' => $collector->translate('faq2_firm_a', 'Both, in the right places. The slow-rebound memory foam is soft enough to sink into and supportive enough to hold its shape. It springs back in about 5 seconds and doesn\'t flatten.')],
    ['q' => $collector->translate('faq_1_q', 'How soon will I feel a difference?'), 'a' => $collector->translate('faq2_1_a', 'Most people notice less pressure the first time they sit down. The memory foam gently adapts to your shape as you use it.')],
    ['q' => $collector->translate('faq_2_q', 'Will it slide around on my chair?'), 'a' => $collector->translate('faq_2_a', 'No. The base is covered in silicone anti-slip dots, so the cushion stays in place even when you shift or lean back.')],
    ['q' => $collector->translate('faq_3_q', 'What if it\'s not right for me?'), 'a' => $collector->translate('faq_3_a', 'We offer refunds within 30 days of purchase. Just contact our support team and we\'ll guide you through the return.')],
];

// Sample reviews: replace with real customer reviews (and photos) before launch.
// Keep them to comfort and experience; no medical results ("cured my sciatica").
$reviews = [
    ['name' => 'Margaret H.', 'photo' => 'review-ang',     'role' => $collector->translate('rev2_0_role', 'Retired teacher'),        'text' => $collector->translate('rev2_0_text', 'I can sit through a whole film again without getting up every twenty minutes. My lower back is so much happier.')],
    ['name' => 'Richard P.',  'photo' => 'review-bert',    'role' => $collector->translate('rev2_1_role', 'Retired engineer'),       'text' => $collector->translate('rev2_1_text', 'Our dining chairs are hard as rock. With this, long family dinners are comfortable again.')],
    ['name' => 'Linda W.',    'photo' => 'review-kim',     'role' => $collector->translate('rev2_2_role', 'Grandmother of five'),    'text' => $collector->translate('rev2_2_text', 'My tailbone used to ache after ten minutes. The little hollow at the back makes a real difference.')],
    ['name' => 'Susan K.',    'photo' => 'review-ping',    'role' => $collector->translate('rev2_3_role', 'Loves quilting'),         'text' => $collector->translate('rev2_3_text', 'I quilt for hours at a time. The backrest keeps me sitting up straight without me even thinking about it.')],
    ['name' => 'Thomas B.',   'photo' => 'review-taichi',  'role' => $collector->translate('rev2_4_role', 'Semi-retired, 61'),       'text' => $collector->translate('rev2_4_text', 'Sitting at my desk used to leave my leg uncomfortable by lunchtime. This cushion takes the pressure off.')],
    ['name' => 'Patricia D.', 'photo' => 'review-yoshiko', 'role' => $collector->translate('rev2_5_role', 'Avid reader'),            'text' => $collector->translate('rev2_5_text', 'Firm enough to support me, soft enough to relax into. I bought a second one for my reading chair.')],
    ['name' => 'George M.',   'photo' => 'review-toshiro', 'role' => $collector->translate('rev2_6_role', 'Retired, loves crosswords'), 'text' => $collector->translate('rev2_6_text', 'It doesn\'t slide around on the chair, which matters to me. Comfortable from the first day.')],
    ['name' => 'Helen R.',    'photo' => 'review-eley',    'role' => $collector->translate('rev2_7_role', 'Video calls with family'), 'text' => $collector->translate('rev2_7_text', 'I chat with my grandchildren for an hour most evenings. Now my back doesn\'t complain afterwards.')],
    ['name' => 'Carol S.',    'photo' => 'review-lau',     'role' => $collector->translate('rev2_8_role', 'Retired nurse'),          'text' => $collector->translate('rev2_8_text', 'Well made and supportive in all the right places. The fabric feels lovely on the skin.')],
];


function money($n) { global $currency; return $currency . number_format($n, $n == floor($n) ? 0 : 2); }
?>
<!doctype html>
<html lang="<?= $OfferApi->targetLanguage ?>" dir="<?= melaraTextDirection($OfferApi->targetLanguage) ?>" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="assets/img/favicon/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon/favicon-16x16.png">
    <title><?= $product ?> — Support for Your Back, Hips &amp; Tailbone</title>
    <meta name="description" content="<?= $product ?>: a supportive memory-foam seat cushion with lumbar support and tailbone relief, for more comfortable sitting at home. 30-day returns.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/index.css?t=<?= $OfferApi->assetVersion('assets/css/index.css') ?>">
    <link rel="stylesheet" href="public/css/app.css?t=<?= $OfferApi->assetVersion('public/css/app.css') ?>">
</head>
<body class="font-sans text-ink antialiased selection:bg-turq-soft overflow-x-hidden">

<div class="mesh text-cream text-xs sm:text-sm text-center py-2.5 px-4" style="--veil: rgba(29,34,38,.45)">
    <?= $collector->translate('idx_launch_offer_up_to', 'Launch offer: up to') ?> <strong class="text-turq-soft"><?= $collector->translate('idx_70_off', '70% off') ?></strong> <?= $collector->translate('idx_bundles_ends_in', '· ends in') ?> <span data-countdown class="tabular-nums font-medium">--:--:--</span>
</div>
<?= deliverTo('bar') ?>

<header id="nav" class="sticky top-0 z-40 transition-all duration-300">
    <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <a href="#" class="shrink-0"><img src="<?= $logo ?>" alt="<?= $brand ?>" class="h-8 sm:h-10 w-auto" width="190" height="49"></a>
        <ul class="hidden md:flex items-center gap-9 text-sm text-muted">
            <li><a href="#solution" class="nav-link"><?= $collector->translate('idx_why_it_helps', 'Why it helps') ?></a></li>
            <li><a href="#life" class="nav-link"><?= $collector->translate('idx_who_it_s_for', 'Who it\'s for') ?></a></li>
            <li><a href="#reviews" class="nav-link"><?= $collector->translate('idx_reviews', 'Reviews') ?></a></li>
            <li><a href="#faq" class="nav-link"><?= $collector->translate('idx_faq', 'FAQ') ?></a></li>
        </ul>
        <div class="flex items-center gap-2 sm:gap-3">
            <?= deliverTo('header') ?>
            <?= languageSelector('header') ?>
            <a href="checkout.php?pack=2" class="rounded-full bg-ink text-cream text-xs sm:text-sm font-medium px-3.5 sm:px-5 py-2.5 whitespace-nowrap hover:bg-turq hover:text-ink transition"><?= $collector->translate('idx_get_70_discount', 'Get 70% Discount') ?></a>
        </div>
    </nav>
</header>

<main>

    <section class="relative overflow-hidden mesh-soft">
        <div data-speed="-0.25" class="absolute -top-32 -end-32 w-[520px] h-[520px] blob bg-turq-soft/80 blur-2xl" aria-hidden="true"></div>
        <div data-speed="0.2" class="absolute bottom-0 -start-40 w-[420px] h-[420px] blob bg-ocean-soft blur-2xl" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-10 pb-5 lg:pt-16 lg:pb-28 grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7">
                <p class="reveal inline-flex items-center gap-2 rounded-full bg-white/70 border border-sand ps-1.5 pe-4 py-1.5 text-xs font-medium text-muted">
                    <span class="flex -space-x-2"><?php foreach (['review-ang', 'review-kim', 'review-yoshiko', 'review-bert'] as $av): ?><img src="assets/img/user/<?= $av ?>.png" alt="" width="24" height="24" class="w-6 h-6 rounded-full object-cover ring-2 ring-white"><?php endforeach; ?></span>
                    <span class="text-amber-500">★★★★★</span> <?= $collector->translate('hero2_loved', 'Loved by 12,400+ customers') ?>
                </p>
                <h1 class="reveal mt-6 font-serif text-[2.5rem] sm:text-6xl lg:text-[clamp(2.75rem,4.6vw,4.5rem)] leading-[1.02] sm:leading-none tracking-tight" data-delay="1">
                    <?= $collector->translate('hero2_h_1', 'Sit in') ?>
                    <span class="scribble text-ink"><?= $collector->translate('hero2_h_2', 'comfort again.') ?>
                        <svg viewBox="0 0 200 20" preserveAspectRatio="none" aria-hidden="true"><path d="M2 14 C 50 4, 120 4, 198 12" fill="none" stroke="#1dd1a1" stroke-width="7" stroke-linecap="round"/></svg>
                    </span><br>
                    <?= $collector->translate('hero2_h_3', 'Support where it aches.') ?>
                </h1>
                <p class="reveal mt-6 max-w-lg text-lg text-muted leading-relaxed" data-delay="2">
                    <?= $collector->translate('idx_the', 'The') ?> <?= $product ?> <?= $collector->translate('hero2_sub', 'supports your lower back, cradles your hips and lifts pressure off your tailbone, so everyday sitting at home feels easier on your body.') ?>
                </p>
                <div class="reveal mt-8 flex flex-col sm:flex-row gap-3" data-delay="3">
                    <a href="checkout.php?pack=2" class="group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-full bg-turq px-7 py-4 font-semibold text-ink shadow-[0_14px_30px_-12px_rgba(29,209,161,.6)] hover:bg-turq-dark transition">
                        <?= $collector->translate('idx_get_70_discount_2', 'Get 70% Discount') ?>
                        <svg class="w-4 h-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                    <a href="#solution" class="inline-flex items-center justify-center whitespace-nowrap rounded-full border border-ink/15 px-7 py-4 font-medium hover:bg-white transition"><?= $collector->translate('idx_how_it_helps', 'How it helps') ?></a>
                </div>
                <?php
                // Hero trust points: [title, sub-line, icon path(s)]
                $heroPerks = [
                    [$collector->translate('perk_0_0', '30-day returns'), $collector->translate('perk_0_1', 'Love it or return it'), '<path d="M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5Z"/><path d="M16 3.5v3M14.5 5h3"/>'],
                    [$collector->translate('perk2_1_0', 'Memory foam'),     $collector->translate('perk2_1_1', 'Springs back in 5 sec'), '<path d="M3 9c3-3 6 3 9 0s6 3 9 0M3 15c3-3 6 3 9 0s6 3 9 0"/>'],
                    [$collector->translate('perk_2_0', 'Anti-slip base'),  $collector->translate('perk_2_1', 'Stays put on any chair'), '<rect x="3.5" y="5" width="17" height="14" rx="3"/><path d="M8 9.5h.01M12 9.5h.01M16 9.5h.01M8 14.5h.01M12 14.5h.01M16 14.5h.01" stroke-width="2.6"/>'],
                ];
                ?>
                <!-- Phone: 3 portrait cards side by side. Tablet and up: horizontal pills -->
                <ul class="reveal mt-9 grid grid-cols-3 gap-2 sm:flex sm:flex-wrap sm:gap-3 lg:flex-nowrap lg:gap-2.5" data-delay="4">
                    <?php foreach ($heroPerks as [$t, $sub, $icon]): ?>
                        <li class="group flex flex-col items-center text-center gap-2 px-2 py-3.5 sm:flex-row sm:text-start sm:gap-3 lg:gap-2.5 lg:flex-1 lg:min-w-0 rounded-2xl bg-white/80 backdrop-blur-sm border border-sand sm:ps-2 sm:pe-4 lg:pe-3 sm:py-2 shadow-[0_8px_24px_-16px_rgba(29,34,38,.35)] hover:-translate-y-0.5 hover:border-turq/40 transition">
                            <span class="grid place-items-center shrink-0 w-9 h-9 rounded-xl bg-turq-soft text-turq-ink group-hover:bg-turq group-hover:text-ink transition">
                                <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $icon ?></svg>
                            </span>
                            <span class="leading-tight min-w-0">
                                <span class="block text-[13px] sm:text-sm lg:text-[13px] font-semibold text-ink text-balance sm:whitespace-nowrap"><?= $t ?></span>
                                <span class="block mt-0.5 sm:mt-0 text-[11px] sm:text-xs lg:text-[11px] text-muted text-balance sm:whitespace-nowrap"><?= $sub ?></span>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="lg:col-span-5 relative reveal order-first lg:order-none pb-28" data-delay="2">
                <div class="relative">
                <div class="relative aspect-[4/5] sm:aspect-[5/5] rounded-[2.5rem] overflow-hidden shadow-[0_40px_80px_-30px_rgba(29,34,38,.35)]">
                    <div class="absolute inset-0 animate-kenburns">
                        <?= photo($img['hero'], 'Woman in her 60s with a cup of tea, sitting upright on the Melara Sitora cushion on a dining chair at home', 'w-full h-full', 'hero.webp', true) ?>
                    </div>
                </div>
                <div class="absolute end-3 sm:-end-6 top-full -mt-6 bg-white rounded-2xl shadow-xl px-5 py-4 max-w-[250px] animate-float">
                    <p class="text-amber-500 text-sm">★★★★★</p>
                    <p class="mt-1 text-sm leading-snug"><?= $collector->translate('hero2_review', '“I can sit through a whole film again without getting up every twenty minutes.”') ?></p>
                    <p class="mt-3 flex items-center gap-2 text-xs text-muted"><img src="assets/img/user/review-ang.png" alt="" width="28" height="28" class="w-7 h-7 rounded-full object-cover"> <?= $collector->translate('hero2_review_by', 'Margaret, retired teacher') ?></p>
                </div>
                </div>
            </div>
        </div>
    </section>

    <?php
    // Press logos: 'h' = display height in px
    $press = [
        ['name' => 'Associated Press', 'logo' => 'assets/img/logos/associated-press-black.svg', 'h' => 26],
        ['name' => 'Yahoo Finance',    'logo' => 'assets/img/logos/yahoo-finance-black.svg',    'h' => 30],
        ['name' => 'USA Today',        'logo' => 'assets/img/logos/usa-today-black.svg',        'h' => 36],
        ['name' => 'NBC',              'logo' => 'assets/img/logos/nbc-black.svg',              'h' => 36],
        ['name' => 'CBS',              'logo' => 'assets/img/logos/cbs-black.svg',              'h' => 20],
        ['name' => 'Fox News',         'logo' => 'assets/img/logos/fox-news-black.svg',         'h' => 34],
        ['name' => 'ABC',              'logo' => 'assets/img/logos/abc-black.svg',              'h' => 34],
        ['name' => 'Benzinga',         'logo' => 'assets/img/logos/benzinga-black.svg',         'h' => 16],
        ['name' => 'GlobeNewswire',    'logo' => 'assets/img/logos/globel-newswire-black.svg',  'h' => 32],
        ['name' => 'Digital Journal',  'logo' => 'assets/img/logos/digital-journal-black.svg',  'h' => 32],
    ];
    ?>
    <section class="border-y border-sand bg-soft-gray py-6 overflow-hidden" aria-label="As seen in">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row md:items-center gap-4 md:gap-10">
            <p class="shrink-0 text-xs font-medium uppercase tracking-[.25em] text-muted text-center md:text-start"><?= $collector->translate('idx_as_seen_in', 'As seen in') ?></p>
            <div class="relative flex-1 overflow-hidden [mask-image:linear-gradient(90deg,transparent,#000_10%,#000_90%,transparent)]">
                <div class="flex w-max animate-marquee items-center gap-14 sm:gap-20 whitespace-nowrap hover:[animation-play-state:paused]" style="animation-duration: 45s;">
                    <?php for ($r = 0; $r < 2; $r++): foreach ($press as $p): ?>
                        <span class="flex items-center h-10 shrink-0" <?= $r ? 'aria-hidden="true"' : '' ?>>
                            <?php if (is_file(__DIR__ . '/' . $p['logo'])): ?>
                                <img src="<?= $p['logo'] ?>" alt="<?= $r ? '' : htmlspecialchars($p['name']) ?>" style="height: <?= $p['h'] ?>px" class="w-auto grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition duration-300" loading="lazy">
                            <?php else: ?>
                                <span class="font-serif text-xl text-ink/45"><?= $p['name'] ?></span>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; endfor; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Where it hurts: three pain areas of the body (images are placeholders until generated) -->
    <?php
    $painAreas = [
        ['img' => 'assets/img/index/pain-tailbone.jpg', 'alt' => 'Woman wincing as she sits down into a car seat, with her tailbone glowing red',  't' => $collector->translate('pa_1_t', 'Tailbone and hips'), 'd' => $collector->translate('pa_1_d', 'Sharp pressure at the base of your spine every time you sit down.')],
        ['img' => 'assets/img/index/pain-back.jpg',     'alt' => 'Man at his office desk holding his lower back, with the lower spine glowing red', 't' => $collector->translate('pa_0_t', 'Lower back'),        'd' => $collector->translate('pa_0_d', 'A dull ache that builds through the day and stiffens when you stand up.')],
        ['img' => 'assets/img/index/pain-leg.jpg',      'alt' => 'Woman on a sofa gripping the back of her thigh, with the thigh glowing red', 't' => $collector->translate('pa_2_t', 'Back of the leg'), 'd' => $collector->translate('pa_2_d', 'Sciatic discomfort that runs from your buttock down the back of your thigh.')],
    ];
    ?>
    <section id="pain-areas" class="py-5 md:py-[30px] lg:py-16 bg-[#faf4f2] scroll-mt-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="reveal text-sm font-medium uppercase tracking-[.2em] text-red-500"><?= $collector->translate('pa_eyebrow', 'Where does it hurt?') ?></p>
                <h2 class="reveal mt-4 font-serif text-4xl sm:text-5xl leading-tight sm:leading-none tracking-tight" data-delay="1"><?= $collector->translate('pa_heading', 'Sitting pain shows up in') ?> <em class="text-red-500"><?= $collector->translate('pa_heading_2', 'three places.') ?></em></h2>
            </div>
            <div class="mt-8 lg:mt-14 grid sm:grid-cols-3 gap-4 lg:gap-6">
                <?php foreach ($painAreas as $i => $pa): ?>
                    <article class="reveal rounded-[1.75rem] overflow-hidden bg-white border border-sand/70 shadow-[0_20px_50px_-35px_rgba(29,34,38,.35)]" data-delay="<?= $i + 1 ?>">
                        <div class="relative aspect-square">
                            <?= photo($pa['img'], $pa['alt'], 'w-full h-full', basename($pa['img'])) ?>
                            <span class="glow-pulse glow-red" style="animation-delay: <?= $i * -.8 ?>s" aria-hidden="true"></span>
                            <span class="badge-ping absolute top-4 start-4 grid place-items-center w-9 h-9 rounded-full bg-red-500 text-white text-sm font-bold shadow-lg"><?= $i + 1 ?></span>
                        </div>
                        <div class="p-5 sm:p-6">
                            <h3 class="flex items-center gap-2 font-semibold text-lg"><span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse" aria-hidden="true"></span><?= $pa['t'] ?></h3>
                            <p class="mt-1 text-[15px] text-muted leading-relaxed"><?= $pa['d'] ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="py-5 md:py-[30px] lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-8 lg:gap-14 items-center">
            <!-- Before / after: the two photos and their labels cross-fade on a loop (assets/js/index.js) -->
            <div class="reveal relative order-2 lg:order-1 mb-6 lg:mb-0" data-ba-loop>
                <div class="relative aspect-[4/5] rounded-[2.5rem] overflow-hidden">
                    <div class="ba-slide is-on absolute inset-0">
                        <?= photo('assets/img/index/pain-before.webp', 'Woman in her 60s holding her lower back in pain as she sits on a hard dining chair', 'w-full h-full', 'pain-before.webp') ?>
                        <span class="glow-pulse glow-red" aria-hidden="true"></span>
                    </div>
                    <div class="ba-slide absolute inset-0">
                        <?= photo('assets/img/index/pain-after.webp', 'The same woman smiling and sitting comfortably on the Melara Sitora cushion on the same dining chair', 'w-full h-full', 'pain-after.webp') ?>
                        <span class="glow-pulse glow-turq" aria-hidden="true"></span>
                    </div>
                    <div class="absolute bottom-4 inset-x-0 flex justify-center gap-2" aria-hidden="true">
                        <span class="ba-dot is-on"></span><span class="ba-dot"></span>
                    </div>
                </div>
                <!-- Labels sit under the photo so they never cover the person or the cushion -->
                <div class="mt-4 grid justify-items-center">
                    <span class="ba-cap is-on col-start-1 row-start-1 inline-flex items-center gap-2 rounded-full bg-red-500 px-4 py-2 text-sm font-semibold text-white shadow-lg"><span aria-hidden="true">✕</span> <?= $collector->translate('ba_before', 'Before: a hard, flat chair') ?></span>
                    <span class="ba-cap col-start-1 row-start-1 inline-flex items-center gap-2 rounded-full bg-turq px-4 py-2 text-sm font-semibold text-ink shadow-lg"><span aria-hidden="true">✓</span> <?= $collector->translate('ba_after', 'After: with Melara Sitora') ?></span>
                </div>
            </div>
            <div class="order-1 lg:order-2">
                <p class="reveal text-sm font-medium uppercase tracking-[.2em] text-turq-ink"><?= $collector->translate('idx_sound_familiar', 'Sound familiar?') ?></p>
                <h2 class="reveal mt-4 font-serif text-4xl sm:text-5xl leading-tight sm:leading-none tracking-tight" data-delay="1"><?= $collector->translate('idx_it_s_not_you', 'It\'s not you.') ?> <em class="text-muted"><?= $collector->translate('pain2_h_2', 'It\'s your chair.') ?></em></h2>
                <div class="mt-10 space-y-4">
                    <?php foreach ($pains as $i => $p): ?>
                        <div class="reveal flex gap-5 rounded-2xl bg-white border border-sand/70 p-5 hover:shadow-lg hover:-translate-y-0.5 transition" data-delay="<?= $i + 1 ?>">
                            <span class="grid place-items-center shrink-0 w-10 h-10 rounded-full bg-red-50 text-red-500 ring-1 ring-red-100 font-bold">✕</span>
                            <div>
                                <h3 class="font-semibold"><?= $p['t'] ?></h3>
                                <p class="mt-1 text-sm text-muted"><?= $p['d'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- How Melara fixes each pain: real product photo with the 5 support zones -->
    <section id="solution" class="py-5 md:py-[30px] lg:py-16 bg-soft-gray scroll-mt-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="reveal text-sm font-medium uppercase tracking-[.2em] text-turq-ink"><?= $collector->translate('sol2_eyebrow', 'How it helps') ?></p>
                <h2 class="reveal mt-4 font-serif text-4xl sm:text-5xl leading-tight sm:leading-none tracking-tight" data-delay="1"><?= $collector->translate('sol2_heading', 'Five support zones.') ?> <em class="text-turq-ink"><?= $collector->translate('sol2_heading_2', 'One comfortable seat.') ?></em></h2>
                <p class="reveal mt-4 text-muted leading-relaxed" data-delay="2"><?= $collector->translate('sol2_intro', 'Each zone is shaped to support a different part of your body, so your weight is spread evenly instead of resting on your lower back, tailbone and sit bones.') ?></p>
            </div>

            <div class="mt-8 lg:mt-14 grid lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                <!-- Phone: the cushion stays pinned under the header while the zone cards scroll past, so the lit marker is always visible -->
                <div class="sticky top-16 z-10 -mx-4 px-4 pt-2 pb-3 bg-soft-gray sm:-mx-6 sm:px-6 lg:mx-0 lg:px-0 lg:pt-0 lg:pb-0 lg:bg-transparent lg:col-span-6 lg:top-24">
                    <div class="reveal relative mx-auto max-w-[250px] sm:max-w-[340px] lg:max-w-[560px] aspect-square rounded-[2.5rem] bg-white border border-sand/70 shadow-[0_30px_80px_-40px_rgba(29,34,38,.3)]">
                        <img src="assets/img/anatomy-assembled.webp?t=<?= $OfferApi->assetVersion('assets/img/anatomy-assembled.webp') ?>" alt="<?= $product ?> with its five support zones numbered" class="absolute inset-[6%] w-[88%] h-[88%] object-contain" loading="lazy" decoding="async">
                        <div class="absolute inset-[6%]">
                            <?php foreach ($zones as [$n, $x, $y]): ?>
                                <span class="zone-dot" data-zone-dot="<?= $n ?>" style="left: <?= $x ?>%; top: <?= $y ?>%;" aria-hidden="true"><?= $n ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <ol class="lg:col-span-6 space-y-3 sm:space-y-4">
                    <?php foreach ($zones as $i => [$n, $x, $y, $painIdx, $zt, $zd]): ?>
                        <li class="reveal zone-card group rounded-2xl bg-white border border-sand/70 p-5 sm:p-6 transition" data-zone="<?= $n ?>" data-delay="<?= $i + 1 ?>">
                            <p class="flex items-center gap-2 text-sm text-muted">
                                <span class="grid place-items-center w-5 h-5 rounded-full bg-rose-50 text-rose-400 text-[10px]" aria-hidden="true">✕</span>
                                <span class="line-through decoration-rose-300"><?= $pains[$painIdx]['t'] ?></span>
                            </p>
                            <div class="mt-3 flex gap-4">
                                <span class="zone-num grid place-items-center shrink-0 w-9 h-9 rounded-full bg-ink text-cream text-sm font-semibold transition"><?= $n ?></span>
                                <div>
                                    <h3 class="font-semibold text-lg text-ink"><?= $zt ?></h3>
                                    <p class="mt-1 text-sm sm:text-[15px] text-muted leading-relaxed"><?= $zd ?></p>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>
                <div class="lg:col-start-7 lg:col-span-6">
                <div class="reveal  flex flex-col items-center sm:items-start text-center sm:text-start gap-3">
                    <a href="checkout.php?pack=2" class="group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-full bg-turq px-7 py-4 font-semibold text-ink shadow-[0_14px_30px_-12px_rgba(29,209,161,.6)] hover:bg-turq-dark hover:-translate-y-0.5 transition">
                        <?= $collector->translate('cta_sol', 'Feel all five zones') ?>
                        <svg class="w-4 h-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                    <p class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-xs text-muted">
                        <span class="inline-flex items-center gap-1"><svg viewBox="0 0 24 24" class="w-3.5 h-3.5 text-turq-ink" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg><?= $collector->translate('cta_sol_n1', '30-day returns') ?></span>
                        <span class="inline-flex items-center gap-1"><svg viewBox="0 0 24 24" class="w-3.5 h-3.5 text-turq-ink" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg><?= $collector->translate('cta_sol_n3', '70% off today') ?></span>
                    </p>
                </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Golden sitting angle + goodbye crossed legs: real side and front photos with drawn guides -->
    <section id="posture" class="py-5 md:py-[30px] lg:py-16 scroll-mt-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="reveal text-sm font-medium uppercase tracking-[.2em] text-turq-ink"><?= $collector->translate('pos_eyebrow', 'Sit the way your spine wants') ?></p>
                <h2 class="reveal mt-4 font-serif text-4xl sm:text-5xl leading-tight sm:leading-none tracking-tight" data-delay="1"><?= $collector->translate('pos_heading', 'The golden') ?> <em class="text-turq-ink"><?= $collector->translate('pos_heading_2', 'sitting angle.') ?></em></h2>
            </div>
            <div class="mt-8 lg:mt-14 grid md:grid-cols-2 gap-4 sm:gap-6">
                <article class="reveal rounded-[2rem] bg-soft-gray border border-sand/70 p-5 sm:p-8 flex flex-col">
                    <div class="relative mx-auto w-full max-w-[520px] my-auto py-6">
                        <img src="assets/img/product/product-side.webp?t=<?= $OfferApi->assetVersion('assets/img/product/product-side.webp') ?>" alt="Side view of the <?= $product ?> showing the curved backrest" class="w-full h-auto" width="1000" height="519" loading="lazy" decoding="async">
                        <svg class="absolute inset-x-0 top-6 w-full h-auto overflow-visible" viewBox="0 0 100 51.9" aria-hidden="true">
                            <path class="draw-line" d="M24,3 C32,10 36.5,20 37.5,29 S40,36 47,35.5" fill="none" stroke="#1dd1a1" stroke-width="1.1" stroke-linecap="round" stroke-dasharray="2.2 1.6"/>
                            <circle cx="34.8" cy="17" r="1.6" fill="#1dd1a1"/><circle cx="34.8" cy="17" r="3.2" fill="#1dd1a1" opacity=".25"/>
                        </svg>
                        <span class="absolute start-[40%] top-[14%] rounded-full bg-white border border-sand shadow-sm px-3 py-1 text-xs font-semibold text-ink whitespace-nowrap"><?= $collector->translate('pos_side_label', 'Follows your lower-back curve') ?></span>
                    </div>
                    <h3 class="mt-4 font-serif text-2xl"><?= $collector->translate('pos_side_t', 'Your spine, naturally straight') ?></h3>
                    <p class="mt-2 text-muted leading-relaxed"><?= $collector->translate('pos_side_d', 'The backrest follows the natural curve of your lower back, helping keep your spine in healthy alignment, so good posture takes less effort.') ?></p>
                </article>
                <article class="reveal rounded-[2rem] bg-soft-gray border border-sand/70 p-5 sm:p-8 flex flex-col" data-delay="1">
                    <div class="relative mx-auto w-[70%] max-w-[340px] my-auto">
                        <img src="assets/img/product/product-front.webp?t=<?= $OfferApi->assetVersion('assets/img/product/product-front.webp') ?>" alt="Front view of the <?= $product ?> showing the U-shaped leg support" class="w-full h-auto" width="1000" height="1120" loading="lazy" decoding="async">
                        <svg class="absolute inset-0 w-full h-full overflow-visible" viewBox="0 0 100 112" aria-hidden="true">
                            <defs><marker id="legArrow" viewBox="0 0 10 10" refX="6" refY="5" markerWidth="4" markerHeight="4" orient="auto-start-reverse"><path d="M0,0 L10,5 L0,10 z" fill="#1dd1a1"/></marker></defs>
                            <!-- arrows: dashes flow toward the seat and the arrows gently push inward (legs drawn together) -->
                            <g class="nudge-in-l"><path class="draw-line" d="M-14,66 Q-6,80 9,80" fill="none" stroke="#1dd1a1" stroke-width="2.4" stroke-linecap="round" stroke-dasharray="4.4 3.2" marker-end="url(#legArrow)"/></g>
                            <g class="nudge-in-r"><path class="draw-line" d="M114,66 Q106,80 91,80" fill="none" stroke="#1dd1a1" stroke-width="2.4" stroke-linecap="round" stroke-dasharray="4.4 3.2" marker-end="url(#legArrow)"/></g>
                            <!-- U-shaped leg support line, flowing like the backrest curve on the left card -->
                            <path class="draw-line" d="M24,99 Q50,78 76,99" fill="none" stroke="#1dd1a1" stroke-width="1.6" stroke-linecap="round" stroke-dasharray="2.2 1.6"/>
                            <circle class="u-pulse" cx="50" cy="88.5" r="1.8" fill="#1dd1a1"/>
                        </svg>
                        <span class="absolute left-1/2 -translate-x-1/2 -bottom-3 rounded-full bg-white border border-sand shadow-sm px-3 py-1 text-xs font-semibold text-ink whitespace-nowrap"><?= $collector->translate('pos_front_label', 'U-shaped leg support') ?></span>
                    </div>
                    <h3 class="mt-8 font-serif text-2xl"><?= $collector->translate('pos_front_t', 'Goodbye, crossed legs') ?></h3>
                    <p class="mt-2 text-muted leading-relaxed"><?= $collector->translate('pos_front_d', 'The seat gently guides your legs together in a U-shape, keeping your knees aligned and your pelvis level, so you are less tempted to cross your legs.') ?></p>
                </article>
            </div>
        </div>
    </section>

    <!-- Materials: fabric, memory foam, anti-slip base -->
    <section id="materials" class="py-5 md:py-[30px] lg:py-16 bg-soft-gray scroll-mt-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="reveal text-sm font-medium uppercase tracking-[.2em] text-turq-ink"><?= $collector->translate('mat_eyebrow', 'What it\'s made of') ?></p>
                <h2 class="reveal mt-4 font-serif text-4xl sm:text-5xl leading-tight sm:leading-none tracking-tight" data-delay="1"><?= $collector->translate('mat2_heading', 'Firm enough to support.') ?> <em class="text-turq-ink"><?= $collector->translate('mat2_heading_2', 'Soft enough to relax.') ?></em></h2>
            </div>
            <div class="mt-8 lg:mt-14 grid sm:grid-cols-3 gap-4 sm:gap-6">
                <article class="reveal rounded-[2rem] bg-white border border-sand/70 overflow-hidden">
                    <div class="aspect-[4/3] overflow-hidden"><?= photo('assets/img/index/fabric.webp', 'Close-up of the soft, skin-friendly fabric of the Melara seat cushion', 'w-full h-full', 'fabric.webp') ?></div>
                    <div class="p-5 sm:p-6">
                        <h3 class="font-semibold text-lg"><?= $collector->translate('mat_0_t', 'Skin-friendly fabric') ?></h3>
                        <p class="mt-1 text-sm text-muted leading-relaxed"><?= $collector->translate('mat_0_d', 'Soft and smooth, with a delicate feel against your skin.') ?></p>
                    </div>
                </article>
                <article class="reveal rounded-[2rem] bg-white border border-sand/70 overflow-hidden" data-delay="1">
                    <div class="aspect-[4/3] overflow-hidden"><?= photo($img['detail'], 'Hand pressing into the slow-rebound memory foam of the Melara seat cushion', 'w-full h-full object-[50%_55%]', 'detail.webp') ?></div>
                    <div class="p-5 sm:p-6">
                        <h3 class="font-semibold text-lg"><?= $collector->translate('mat_1_t', 'Slow-rebound memory foam') ?></h3>
                        <p class="mt-1 text-sm text-muted leading-relaxed"><?= $collector->translate('mat_1_d', 'Shapes to your body, then springs back in about 5 seconds without ever collapsing.') ?></p>
                    </div>
                </article>
                <article class="reveal rounded-[2rem] bg-white border border-sand/70 overflow-hidden" data-delay="2">
                    <div class="aspect-[4/3] overflow-hidden"><?= photo('assets/img/index/antislip.webp', 'Close-up of the silicone anti-slip dots on the base of the Melara seat cushion', 'w-full h-full', 'antislip.webp') ?></div>
                    <div class="p-5 sm:p-6">
                        <h3 class="font-semibold text-lg"><?= $collector->translate('mat_2_t', 'Silicone anti-slip base') ?></h3>
                        <p class="mt-1 text-sm text-muted leading-relaxed"><?= $collector->translate('mat_2_d', 'Anti-slip particles on the base keep the cushion in place, however much you move.') ?></p>
                    </div>
                </article>
            </div>
            <ul class="reveal mt-4 sm:mt-6 grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4" data-delay="3">
                <li class="flex items-center gap-3 rounded-2xl bg-white border border-sand/70 p-4">
                    <span class="grid place-items-center shrink-0 w-11 h-11 rounded-xl bg-turq-soft text-turq-ink"><svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><rect x="7" y="3" width="10" height="18" rx="5"/></svg></span>
                    <span class="text-sm font-medium leading-snug"><?= $collector->translate('mat_icon_0', 'Slow-rebound memory foam') ?></span>
                </li>
                <li class="flex items-center gap-3 rounded-2xl bg-white border border-sand/70 p-4">
                    <span class="grid place-items-center shrink-0 w-11 h-11 rounded-xl bg-turq-soft text-turq-ink"><svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12c3-2.5 6-2.5 9 0s6 2.5 9 0"/><path d="M12 2v6m-2.5-2.5L12 8l2.5-2.5M12 22v-6m-2.5 2.5L12 16l2.5 2.5"/></svg></span>
                    <span class="text-sm font-medium leading-snug"><?= $collector->translate('mat_icon_1', 'Rebounds in 5 seconds, never collapses') ?></span>
                </li>
                <li class="flex items-center gap-3 rounded-2xl bg-white border border-sand/70 p-4">
                    <span class="grid place-items-center shrink-0 w-11 h-11 rounded-xl bg-turq-soft text-turq-ink"><svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20c1.5-5 4.5-8 8-8s6.5 3 8 8"/><path d="M8 9V4m-2 2 2-2 2 2M16 9V4m-2 2 2-2 2 2"/></svg></span>
                    <span class="text-sm font-medium leading-snug"><?= $collector->translate('mat_icon_2', 'Curved fit, soft support') ?></span>
                </li>
                <li class="flex items-center gap-3 rounded-2xl bg-white border border-sand/70 p-4">
                    <span class="grid place-items-center shrink-0 w-11 h-11 rounded-xl bg-turq-soft text-turq-ink"><svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 21c-1.5-2 1.5-3 0-5s1.5-3 0-5M12 21c-1.5-2 1.5-3 0-5s1.5-3 0-5M17 21c-1.5-2 1.5-3 0-5s1.5-3 0-5"/><path d="M7 7V3m-2 2 2-2 2 2M17 7V3m-2 2 2-2 2 2"/></svg></span>
                    <span class="text-sm font-medium leading-snug"><?= $collector->translate('mat_icon_3', 'Moisture-wicking & breathable') ?></span>
                </li>
            </ul>

                <div class="reveal mt-8 lg:mt-12 flex flex-col items-center text-center gap-3">
                    <a href="checkout.php?pack=2" class="group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-full bg-turq px-7 py-4 font-semibold text-ink shadow-[0_14px_30px_-12px_rgba(29,209,161,.6)] hover:bg-turq-dark hover:-translate-y-0.5 transition">
                        <?= $collector->translate('cta_mat', 'Get yours at 70% off') ?>
                        <svg class="w-4 h-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                    <p class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-xs text-muted">
                        <span class="inline-flex items-center gap-1"><svg viewBox="0 0 24 24" class="w-3.5 h-3.5 text-turq-ink" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg><?= $collector->translate('cta_mat_n1', '30-day returns') ?></span>
                        <span class="inline-flex items-center gap-1"><svg viewBox="0 0 24 24" class="w-3.5 h-3.5 text-turq-ink" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg><?= $collector->translate('cta_mat_n3', '70% off today') ?></span>
                    </p>
                </div>
        </div>
    </section>

    <!-- Product parameters: real photo with dimension lines -->
    <section id="specs" class="py-5 md:py-[30px] lg:py-16 scroll-mt-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-8 lg:gap-14 items-center">
            <div class="reveal relative mx-auto w-full max-w-[560px] aspect-square rounded-[2.5rem] bg-soft-gray border border-sand/70">
                <img src="assets/img/anatomy-assembled.webp?t=<?= $OfferApi->assetVersion('assets/img/anatomy-assembled.webp') ?>" alt="<?= $product ?> with its dimensions: 44 cm (17.3 in) wide, 42 cm (16.5 in) deep, 32 cm (12.6 in) high" class="absolute inset-[10%] w-[80%] h-[80%] object-contain" loading="lazy" decoding="async">
                <div class="absolute inset-[10%]">
                    <svg class="absolute inset-0 w-full h-full overflow-visible" viewBox="0 0 100 100" aria-hidden="true">
                        <!-- On scroll-in each line draws itself (one after another), its end ticks appear, then a soft highlight keeps sweeping along it -->
                        <g class="dims" fill="none" stroke-linecap="round">
                            <path class="dim-line" pathLength="100" style="--d: 0s"  d="M1,75 L26,97"/>
                            <path class="dim-tick" style="--d: .7s" d="M-0.4,76.6 L2.4,73.4M24.6,98.6 L27.4,95.4"/>
                            <path class="dim-glow" pathLength="100" style="--d: 0s"  d="M1,75 L26,97"/>
                            <path class="dim-line" pathLength="100" style="--d: .45s" d="M36,96.5 L78,72"/>
                            <path class="dim-tick" style="--d: 1.15s" d="M35.2,95.1 L36.8,97.9M77.2,70.6 L78.8,73.4"/>
                            <path class="dim-glow" pathLength="100" style="--d: .45s" d="M36,96.5 L78,72"/>
                            <path class="dim-line" pathLength="100" style="--d: .9s" d="M102,11 L102,55"/>
                            <path class="dim-tick" style="--d: 1.6s" d="M100.4,11 L103.6,11M100.4,55 L103.6,55"/>
                            <path class="dim-glow" pathLength="100" style="--d: .9s" d="M102,11 L102,55"/>
                        </g>
                    </svg>
                    <span class="dim-pill" style="left: 13.5%; top: 86%; --d: .8s;">44 cm<small>17.3 in</small></span>
                    <span class="dim-pill" style="left: 57%; top: 84.25%; --d: 1.25s;">42 cm<small>16.5 in</small></span>
                    <span class="dim-pill" style="left: 102%; top: 33%; --d: 1.7s;">32 cm<small>12.6 in</small></span>
                </div>
            </div>
            <div>
                <p class="reveal text-sm font-medium uppercase tracking-[.2em] text-turq-ink"><?= $collector->translate('spec_eyebrow', 'Product parameters') ?></p>
                <h2 class="reveal mt-4 font-serif text-4xl sm:text-5xl leading-tight sm:leading-none tracking-tight" data-delay="1"><?= $collector->translate('spec_heading', 'Made to fit') ?> <em class="text-turq-ink"><?= $collector->translate('spec_heading_2', 'your chair.') ?></em></h2>
                <dl class="reveal mt-8 divide-y divide-sand rounded-2xl border border-sand bg-white text-sm sm:text-[15px]" data-delay="2">
                    <div class="flex justify-between gap-6 px-5 py-4"><dt class="text-muted"><?= $collector->translate('spec_size_k', 'Size (W × D × H)') ?></dt><dd class="font-semibold text-end"><?= $collector->translate('spec2_size_v', '44 × 42 × 32 cm<br><span class="font-normal text-muted">17.3 × 16.5 × 12.6 in</span>') ?></dd></div>
                    <div class="flex justify-between gap-6 px-5 py-4"><dt class="text-muted"><?= $collector->translate('spec_fit_k', 'Fits') ?></dt><dd class="font-semibold text-end"><?= $collector->translate('spec2_fit_v', 'Chair seats 44 cm (17.3 in) wide or more') ?></dd></div>
                    <div class="flex justify-between gap-6 px-5 py-4"><dt class="text-muted"><?= $collector->translate('spec_design_k', 'Design') ?></dt><dd class="font-semibold text-end"><?= $collector->translate('spec_design_v', 'One-piece seat and backrest') ?></dd></div>
                    <div class="flex justify-between gap-6 px-5 py-4"><dt class="text-muted"><?= $collector->translate('spec_fabric_k', 'Cover') ?></dt><dd class="font-semibold text-end"><?= $collector->translate('spec_fabric_v', 'Skin-friendly fabric') ?></dd></div>
                    <div class="flex justify-between gap-6 px-5 py-4"><dt class="text-muted"><?= $collector->translate('spec_fill_k', 'Filling') ?></dt><dd class="font-semibold text-end"><?= $collector->translate('spec_fill_v', 'Slow-rebound memory foam') ?></dd></div>
                    <div class="flex justify-between gap-6 px-5 py-4"><dt class="text-muted"><?= $collector->translate('spec_base_k', 'Base') ?></dt><dd class="font-semibold text-end"><?= $collector->translate('spec_base_v', 'Silicone anti-slip dots') ?></dd></div>
                    <div class="flex justify-between gap-6 px-5 py-4"><dt class="text-muted"><?= $collector->translate('spec_vent_k', 'Tailbone') ?></dt><dd class="font-semibold text-end"><?= $collector->translate('spec_vent_v', 'Hollow, breathable vent') ?></dd></div>
                </dl>
                <div class="reveal mt-8 flex justify-center" data-delay="3">
                    <a href="checkout.php?pack=2" class="group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-full bg-turq px-7 py-4 font-semibold text-ink shadow-[0_14px_30px_-12px_rgba(29,209,161,.6)] hover:bg-turq-dark transition"><?= $collector->translate('spec_cta', 'Get 70% Discount') ?> <span class="transition group-hover:translate-x-1" aria-hidden="true">→</span></a>
                </div>
            </div>
        </div>
    </section>

    <section id="why" class="relative py-5 md:py-[30px] lg:py-16 bg-soft-gray scroll-mt-16 overflow-hidden">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="reveal text-sm font-medium uppercase tracking-[.2em] text-turq-ink"><?= $collector->translate('idx_why_it_helps_2', 'Why it helps') ?></p>
                <h2 class="reveal mt-4 font-serif text-4xl sm:text-5xl leading-tight sm:leading-none tracking-tight" data-delay="1"><?= $collector->translate('why2_h_1', 'Everything your chair') ?> <em class="text-turq-ink"><?= $collector->translate('idx_was_missing', 'was missing.') ?></em></h2>
            </div>

            <?php
            // Illustrative comfort score (0–100) through a workday
            $day = [
                ['t' => '9am',  'reg' => [85, 0, 'Feeling fine'],       'mel' => [92, 'Comfortable']],
                ['t' => '11am', 'reg' => [60, 1, 'Shifting around'],    'mel' => [90, 'Still comfortable']],
                ['t' => '2pm',  'reg' => [35, 2, 'Lower back aching'],  'mel' => [88, 'Focused, not fidgeting']],
                ['t' => '5pm',  'reg' => [15, 3, 'Stiff, sore, done'],  'mel' => [90, 'Energy for the evening']],
            ];
            foreach ($day as $i => &$d) { $d['reg'][2] = $collector->translate("day_{$i}_regular", $d['reg'][2]); $d['mel'][1] = $collector->translate("day_{$i}_melara", $d['mel'][1]); }
            unset($d);
            $px = fn($i) => 100 + $i * 200;          // x of each time slot in the 800-wide chart
            $py = fn($v) => 210 - $v * 1.7;           // comfort 0–100 → y
            $curve = function ($vals) use ($px, $py) {  // smooth path through the points
                $pts = array_map(fn($v, $i) => [$px($i), $py($v)], $vals, array_keys($vals));
                $d = 'M' . $pts[0][0] . ',' . $pts[0][1];
                for ($i = 1; $i < count($pts); $i++) {
                    $mx = ($pts[$i - 1][0] + $pts[$i][0]) / 2;
                    $d .= " C$mx," . $pts[$i - 1][1] . " $mx," . $pts[$i][1] . ' ' . $pts[$i][0] . ',' . $pts[$i][1];
                }
                return $d;
            };
            $regVals = array_map(fn($d) => $d['reg'][0], $day);
            $melVals = array_map(fn($d) => $d['mel'][0], $day);
            $regPath = $curve($regVals);
            $melPath = $curve($melVals);
            $melArea = $melPath . ' L700,215 L100,215 Z';

            // simple face icon: mood 0 = content … 3 = exhausted, 'happy' = with Melara
            $face = function ($mood) {
                $mouth = [
                    0 => 'M8.5 14.5c1 .9 2.2 1.3 3.5 1.3s2.5-.4 3.5-1.3',
                    1 => 'M9 15.2h6',
                    2 => 'M8.5 16.2c1-.9 2.2-1.3 3.5-1.3s2.5.4 3.5 1.3',
                    3 => 'M8.5 16.4c1-1.1 2.2-1.6 3.5-1.6s2.5.5 3.5 1.6',
                    'happy' => 'M7.8 13.6c1.1 1.8 2.5 2.7 4.2 2.7s3.1-.9 4.2-2.7',
                ][$mood];
                $eyes = $mood === 3
                    ? '<path d="M7.6 10.2h2.8M13.6 10.2h2.8"/>'
                    : '<circle cx="9" cy="10" r=".9" fill="currentColor"/><circle cx="15" cy="10" r=".9" fill="currentColor"/>';
                return '<svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9.2"/>' . $eyes . '<path d="' . $mouth . '"/></svg>';
            };
            ?>
            <div class="reveal cmp mt-8 lg:mt-14 rounded-[2rem] border border-sand bg-white shadow-[0_30px_80px_-40px_rgba(29,34,38,.25)] overflow-hidden">
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 p-7 sm:p-10 pb-0 sm:pb-0">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-[.2em] text-turq-ink"><?= $collector->translate('idx_your_day_compared', 'Your day, compared') ?></p>
                        <h3 class="mt-2 font-serif text-3xl sm:text-4xl tracking-tight"><?= $collector->translate('idx_same_chair', 'Same chair.') ?> <em class="text-turq-ink"><?= $collector->translate('why2_cmp_2', 'A very different evening.') ?></em></h3>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-sm">
                        <span class="inline-flex items-center gap-2 rounded-full bg-rose-50 border border-rose-100 px-3 py-1.5 text-rose-600"><span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span><?= $collector->translate('idx_regular_chair', 'Regular chair') ?></span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-turq-soft/60 border border-turq/30 px-3 py-1.5 text-turq-ink"><span class="w-2.5 h-2.5 rounded-full bg-turq"></span><?= $collector->translate('idx_with', 'With') ?> <?= $brand ?></span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-ink text-cream px-3 py-1.5 font-medium"><?= $collector->translate('idx_5pm_comfort', '5pm comfort') ?> <strong class="text-turq"><?= end($melVals) ?></strong> <?= $collector->translate('idx_vs', 'vs') ?> <span class="text-rose-300"><?= end($regVals) ?></span></span>
                    </div>
                </div>

                <div class="relative px-3 sm:px-6 mt-6">
                    <svg viewBox="0 0 800 230" class="w-full h-auto overflow-visible" role="img" aria-label="Comfort through the day: a regular chair drops from <?= $regVals[0] ?> to <?= end($regVals) ?>, while <?= $brand ?> stays around <?= $melVals[0] ?>">
                        <defs>
                            <linearGradient id="melFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#1dd1a1" stop-opacity=".28"/>
                                <stop offset="100%" stop-color="#1dd1a1" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        <?php foreach ([0, 25, 50, 75, 100] as $g): ?>
                            <line x1="40" x2="760" y1="<?= $py($g) ?>" y2="<?= $py($g) ?>" stroke="#e1e4e8" stroke-dasharray="3 5"/>
                            <text x="30" y="<?= $py($g) + 4 ?>" text-anchor="end" font-size="11" fill="#9aa1a9"><?= $g ?></text>
                        <?php endforeach; ?>
                        <?php foreach ($day as $i => $d): ?>
                            <line x1="<?= $px($i) ?>" x2="<?= $px($i) ?>" y1="20" y2="215" stroke="#eef0f2"/>
                        <?php endforeach; ?>

                        <path class="cmp-area" d="<?= $melArea ?>" fill="url(#melFill)"/>
                        <path class="cmp-line" pathLength="1" d="<?= $regPath ?>" stroke="#fb7185" stroke-width="3" fill="none" stroke-linecap="round" style="transition-delay: .2s"/>
                        <path class="cmp-line" pathLength="1" d="<?= $melPath ?>" stroke="#1dd1a1" stroke-width="4" fill="none" stroke-linecap="round" style="transition-delay: .5s; filter: drop-shadow(0 4px 8px rgba(29,209,161,.45));"/>

                        <?php foreach ($day as $i => $d): ?>
                            <circle class="cmp-pt" cx="<?= $px($i) ?>" cy="<?= $py($d['reg'][0]) ?>" r="6" fill="#fff" stroke="#fb7185" stroke-width="3" style="transition-delay: <?= .6 + $i * .35 ?>s"/>
                            <circle class="cmp-pt" cx="<?= $px($i) ?>" cy="<?= $py($d['mel'][0]) ?>" r="7" fill="#1dd1a1" stroke="#fff" stroke-width="3" style="transition-delay: <?= .9 + $i * .35 ?>s"/>
                        <?php endforeach; ?>
                    </svg>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 border-t border-sand mt-2">
                    <?php foreach ($day as $i => $d): [$rv, $mood, $rt] = $d['reg']; [$mv, $mt] = $d['mel']; ?>
                        <div class="p-5 sm:p-6 border-sand <?= $i % 2 ? 'border-l' : '' ?> <?= $i === 2 ? 'lg:border-l' : '' ?> <?= $i > 1 ? 'border-t lg:border-t-0' : '' ?>">
                            <p class="font-serif text-2xl"><?= $d['t'] ?></p>

                            <div class="mt-4 flex items-start gap-3">
                                <span class="grid place-items-center shrink-0 w-9 h-9 rounded-xl bg-rose-50 text-rose-500"><?= $face($mood) ?></span>
                                <div class="flex-1 min-w-0">
                                    <p class="min-h-[2.5em] text-sm text-muted leading-tight"><?= $rt ?></p>
                                    <div class="mt-2 h-1.5 rounded-full bg-linen overflow-hidden"><div class="cmp-bar h-full rounded-full bg-rose-400" style="--w: <?= $rv ?>%; transition-delay: <?= .4 + $i * .15 ?>s"></div></div>
                                </div>
                            </div>

                            <div class="mt-4 flex items-start gap-3 rounded-2xl bg-turq-soft/40 -mx-2 px-2 py-2">
                                <span class="grid place-items-center shrink-0 w-9 h-9 rounded-xl bg-turq text-ink shadow-[0_6px_16px_-6px_rgba(29,209,161,.9)]"><?= $face('happy') ?></span>
                                <div class="flex-1 min-w-0">
                                    <p class="min-h-[2.5em] text-sm font-medium leading-tight"><?= $mt ?></p>
                                    <div class="mt-2 h-1.5 rounded-full bg-white overflow-hidden"><div class="cmp-bar h-full rounded-full bg-turq" style="--w: <?= $mv ?>%; transition-delay: <?= .6 + $i * .15 ?>s"></div></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="px-7 sm:px-10 py-4 border-t border-sand text-xs text-muted"><?= $collector->translate('why2_note', 'Illustrative comfort score over a typical day of sitting at home.') ?></p>
            </div>

                <div class="reveal mt-8 lg:mt-12 flex flex-col items-center text-center gap-3">
                    <a href="checkout.php?pack=2" class="group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-full bg-turq px-7 py-4 font-semibold text-ink shadow-[0_14px_30px_-12px_rgba(29,209,161,.6)] hover:bg-turq-dark hover:-translate-y-0.5 transition">
                        <?= $collector->translate('cta_why', 'Make every evening like this') ?>
                        <svg class="w-4 h-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                    <p class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-xs text-muted">
                        <span class="inline-flex items-center gap-1"><svg viewBox="0 0 24 24" class="w-3.5 h-3.5 text-turq-ink" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg><?= $collector->translate('cta_why_n1', '30-day returns') ?></span>
                        <span class="inline-flex items-center gap-1"><svg viewBox="0 0 24 24" class="w-3.5 h-3.5 text-turq-ink" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg><?= $collector->translate('cta_why_n3', '70% off today') ?></span>
                    </p>
                </div>
        </div>
    </section>

    <section id="life" class="py-5 md:py-[30px] lg:py-16 scroll-mt-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
                <div>
                    <p class="reveal text-sm font-medium uppercase tracking-[.2em] text-turq-ink"><?= $collector->translate('idx_made_for_real_life', 'Made for real life') ?></p>
                    <h2 class="reveal mt-4 font-serif text-4xl sm:text-5xl leading-tight sm:leading-none tracking-tight" data-delay="1"><?= $collector->translate('life2_h_1', 'One cushion.') ?><br><em><?= $collector->translate('life3_h_2', 'Every seat in your day.') ?></em></h2>
                </div>
                <p class="reveal max-w-sm text-muted" data-delay="2"><?= $collector->translate('life3_sub', 'Light and easy to carry: it fits on the sofa, dining chairs, the driver\'s seat in your car and your office chair.') ?></p>
            </div>

            <div class="mt-8 lg:mt-14 grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <?php foreach ($life as $i => $l): $span = 'aspect-[4/5]'; ?>
                    <article class="reveal group relative rounded-[1.75rem] overflow-hidden <?= $span ?>" data-delay="<?= ($i % 3) + 1 ?>">
                        <div class="absolute inset-0 transition duration-700 group-hover:scale-105">
                            <?= photo($l['img'], $l['alt'], 'w-full h-full object-[50%_25%]', basename($l['img'])) ?>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-ink/70 via-ink/10 to-transparent"></div>
                        <div class="absolute bottom-0 p-4 sm:p-6 text-cream">
                            <h3 class="font-serif text-xl sm:text-2xl"><?= $l['t'] ?></h3>
                            <p class="mt-1 text-xs sm:text-sm text-cream/85 max-w-xs leading-snug"><?= $l['d'] ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="reviews" class="py-5 md:py-[30px] lg:py-16 bg-soft-gray scroll-mt-16 overflow-hidden">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
            <p class="reveal text-amber-500 text-lg">★★★★★</p>
            <h2 class="reveal mt-3 font-serif text-4xl sm:text-5xl tracking-tight" data-delay="1"><?= $collector->translate('idx_why_didn_t_i', '“Why didn\'t I get this') ?> <em class="text-turq-ink"><?= $collector->translate('idx_years_ago', 'years ago?”') ?></em></h2>
            <p class="reveal mt-4 text-muted" data-delay="2"><?= $collector->translate('idx_4_9_out_of', '4.9 out of 5 from 12,400+ happy sitters') ?></p>
        </div>
        <div class="mt-8 lg:mt-14 relative">
            <div class="pointer-events-none absolute inset-y-0 start-0 w-16 sm:w-40 bg-linear-to-r/srgb from-linen to-transparent z-10"></div>
            <div class="pointer-events-none absolute inset-y-0 end-0 w-16 sm:w-40 bg-linear-to-l/srgb from-linen to-transparent z-10"></div>
            <div class="flex w-max animate-marquee gap-5 hover:[animation-play-state:paused]" style="animation-duration: 55s;">
                <?php $verifiedBuyer = $collector->translate('idx_verified_buyer', 'Verified buyer'); // translated once, used on every review card ?>
                <?php for ($r = 0; $r < 2; $r++): foreach ($reviews as $rv): ?>
                    <figure class="bg-white rounded-3xl p-7 w-[300px] sm:w-[370px] shrink-0 border border-sand/70">
                        <p class="text-amber-500 text-sm">★★★★★</p>
                        <blockquote class="mt-3 font-serif text-lg leading-snug">“<?= $rv['text'] ?>”</blockquote>
                        <figcaption class="mt-5 flex items-center gap-3 text-sm">
                            <img src="assets/img/user/<?= $rv['photo'] ?>.png" alt="<?= $r ? '' : htmlspecialchars($rv['name']) ?>" width="44" height="44" class="w-11 h-11 rounded-full object-cover ring-2 ring-white shadow-md" loading="lazy">
                            <span>
                                <span class="block font-semibold"><?= $rv['name'] ?></span>
                                <span class="block text-xs text-muted"><?= $rv['role'] ?></span>
                            </span>
                            <span class="ms-auto inline-flex items-center gap-1 rounded-full bg-turq-soft/70 border border-turq/30 ps-1 pe-2.5 py-0.5 text-[11px] font-semibold text-turq-ink whitespace-nowrap">
                                <svg viewBox="0 0 24 24" class="w-4 h-4" aria-hidden="true"><path fill="#1dd1a1" d="M12 2 4 5v6.1c0 5 3.4 9.4 8 10.9 4.6-1.5 8-5.9 8-10.9V5l-8-3z"/><path d="M12 2v20c4.6-1.5 8-5.9 8-10.9V5l-8-3z" fill="#14b88c"/><path d="m8.6 12 2.4 2.4 4.4-4.6" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <?= $verifiedBuyer ?>
                            </span>
                        </figcaption>
                    </figure>
                <?php endforeach; endfor; ?>
            </div>
        </div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="reveal mt-8 lg:mt-12 flex flex-col items-center text-center gap-3">
                    <a href="checkout.php?pack=2" class="group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-full bg-turq px-7 py-4 font-semibold text-ink shadow-[0_14px_30px_-12px_rgba(29,209,161,.6)] hover:bg-turq-dark hover:-translate-y-0.5 transition">
                        <?= $collector->translate('cta_rev', 'Join 12,400+ happy customers') ?>
                        <svg class="w-4 h-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                    <p class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-xs text-muted">
                        <span class="inline-flex items-center gap-1"><svg viewBox="0 0 24 24" class="w-3.5 h-3.5 text-turq-ink" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg><?= $collector->translate('cta_rev_n1', '30-day returns') ?></span>
                        <span class="inline-flex items-center gap-1"><svg viewBox="0 0 24 24" class="w-3.5 h-3.5 text-turq-ink" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg><?= $collector->translate('cta_rev_n3', '70% off today') ?></span>
                    </p>
                </div>
        </div>
    </section>

    <section id="faq" class="py-5 md:py-[30px] lg:pt-16 lg:pb-20 scroll-mt-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="reveal text-center font-serif text-4xl sm:text-5xl tracking-tight"><?= $collector->translate('idx_good_questions', 'Good questions') ?></h2>
            <div class="mt-8 lg:mt-12 space-y-3">
                <?php foreach ($faqs as $i => $f): ?>
                    <details class="reveal bg-white rounded-2xl px-6 py-5 border border-sand/70" data-delay="<?= min($i + 1, 5) ?>">
                        <summary class="flex cursor-pointer items-center justify-between gap-6 font-medium">
                            <?= $f['q'] ?>
                            <span class="faq-icon grid place-items-center w-8 h-8 shrink-0 rounded-full bg-linen text-turq-ink transition">+</span>
                        </summary>
                        <p class="mt-3 text-muted leading-relaxed"><?= $f['a'] ?></p>
                    </details>
                <?php endforeach; ?>
            </div>

                <div class="reveal mt-8 lg:mt-12 flex flex-col items-center text-center gap-3">
                    <a href="checkout.php?pack=2" class="group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-full bg-turq px-7 py-4 font-semibold text-ink shadow-[0_14px_30px_-12px_rgba(29,209,161,.6)] hover:bg-turq-dark hover:-translate-y-0.5 transition">
                        <?= $collector->translate('cta_faq', 'Get 70% Discount') ?>
                        <svg class="w-4 h-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                    <p class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-xs text-muted">
                        <span class="inline-flex items-center gap-1"><svg viewBox="0 0 24 24" class="w-3.5 h-3.5 text-turq-ink" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg><?= $collector->translate('cta_faq_n1', '30-day returns') ?></span>
                        <span class="inline-flex items-center gap-1"><svg viewBox="0 0 24 24" class="w-3.5 h-3.5 text-turq-ink" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg><?= $collector->translate('cta_faq_n3', '70% off today') ?></span>
                    </p>
                </div>
        </div>
    </section>

    <section class="px-4 sm:px-6 lg:px-8 py-5 md:py-[30px] lg:pt-0 lg:pb-24">
        <div class="reveal relative mx-auto max-w-7xl rounded-[2.5rem] overflow-hidden min-h-[460px] flex items-end">
            <div class="absolute inset-0 animate-kenburns" style="transform-origin: 70% 40%;">
                <?= photo($img['cta'], 'Chair at home with the Melara seat cushion in warm evening sunlight', 'w-full h-full object-[78%_30%] lg:object-[50%_30%]', 'cta.webp') ?>
            </div>
            <div class="absolute inset-0 bg-gradient-to-r from-ink/80 via-ink/40 to-transparent"></div>
            <div class="relative p-10 sm:p-16 max-w-xl text-cream">
                <h2 class="font-serif text-4xl sm:text-6xl leading-[1.05] sm:leading-none tracking-tight"><?= $collector->translate('idx_end_the_day', 'End the day') ?> <em class="text-turq-soft"><?= $collector->translate('idx_feeling_good', 'feeling good.') ?></em></h2>
                <p class="mt-5 text-lg text-cream/80"><?= $collector->translate('idx_try_it_for_100', 'Not the right fit? Request a refund within 30 days of purchase.') ?></p>
                <a href="checkout.php?pack=2" class="mt-8 inline-flex items-center gap-2 rounded-full bg-cream text-ink px-8 py-4 font-medium hover:bg-turq hover:text-ink transition">
                    <?= $collector->translate('idx_get_70_discount_3', 'Get 70% Discount') ?>
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
        </div>
    </section>
</main>

<footer class="border-t border-sand pb-8 md:pb-10 pt-8 md:pt-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6 text-sm text-muted">
        <img src="<?= $logo ?>" alt="<?= $brand ?>" class="h-10 w-auto" width="190" height="49" loading="lazy">
        <ul class="grid grid-cols-2 gap-x-8 gap-y-3 text-center sm:flex sm:flex-wrap sm:justify-center sm:gap-6"><!-- 2x2 on phones so no link is left alone on a second line -->
            <li><a href="#shipping" data-policy="shipping" class="hover:text-ink transition"><?= $collector->translate('idx_shipping', 'Shipping') ?></a></li>
            <li><a href="#returns" data-policy="returns" class="hover:text-ink transition"><?= $collector->translate('idx_returns', 'Returns') ?></a></li>
            <li><a href="#privacy" data-policy="privacy" class="hover:text-ink transition"><?= $collector->translate('idx_privacy', 'Privacy') ?></a></li>
            <li><a href="#contact" data-policy="contact" class="hover:text-ink transition"><?= $collector->translate('idx_contact', 'Contact') ?></a></li>
        </ul>
        <div class="flex flex-col sm:flex-row items-center gap-4">
            <?= languageSelector('footer') ?>
            <p>© <?= date('Y') ?> <?= $brand ?><?= $collector->translate('idx_all_rights_reserved', '. All rights reserved.') ?></p>
        </div>
    </div>
    <p class="mx-auto max-w-3xl px-4 mt-6 text-center text-[11px] leading-relaxed text-muted/80"><?= $collector->translate('footer_disclaimer', 'The Melara Sitora is a comfort and posture-support product, not a medical device. It is not intended to diagnose, treat, cure or prevent any disease. Individual results vary. If you have ongoing pain, consult your doctor or physiotherapist.') ?></p>
</footer>

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

<div id="buyBar" class="md:hidden fixed inset-x-3 bottom-3 z-50 translate-y-[150%] transition-transform duration-500">
    <a href="checkout.php?pack=2" class="flex items-center justify-center gap-2 rounded-full bg-turq px-6 py-4 font-semibold text-ink shadow-[0_14px_30px_-10px_rgba(29,34,38,.45)]">
        <?= $collector->translate('idx_get_70_discount_4', 'Get 70% Discount') ?>
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </a>
</div>

<script src="public/js/index.js?t=<?= $OfferApi->assetVersion('public/js/index.js') ?>"></script>
<!-- Everflow SDK + funnel events (offer 84) -->
<script src="https://www.b04jdmd.com/scripts/main.js" defer></script>
</body>
</html>

<?php $collector->saveTranslation(); ?>
