<?php
/* ------------------------------------------------------------------
 * Melara Seat Cushion landing page (light lifestyle theme, light gray + #1dd1a1 accent)
 * Brand, prices and copy are placeholders until the real product is
 * ready. Drop generated images into assets/img/ using the filenames
 * below — any missing image shows a soft placeholder instead.
 * ------------------------------------------------------------------ */
include_once("integrated/setup.php");
include_once(BASEPATH . '/src/JsonTranslateCollector.php');

// Copy is translated through lang/<language>/index.json (auto-generated on localhost, like shopmelara-apex)
$collector = new JsonCollector(targetLanguage: $OfferApi->targetLanguage, pageName: "index");
function t($key, $text) {
    // Memoised: strings inside loops (e.g. "Verified buyer" on every review) reuse one key without tripping the duplicate-key check
    static $cache = [];
    global $collector;
    return $cache[$key] ??= $collector->translate($key, $text);
}
/** Translate the given string fields of every row in a list (keys: {prefix}_{row}_{field}). */
function tRows(array $rows, string $prefix, array $fields) {
    foreach ($rows as $i => &$row) foreach ($fields as $f) if (isset($row[$f]) && is_string($row[$f])) $row[$f] = t("{$prefix}_{$i}_{$f}", $row[$f]);
    return $rows;
}

$brand    = 'Melara';
$product  = 'Melara Seat Cushion';
$currency = '$';
$logo     = 'assets/img/logo.svg';

$img = [
    'hero'     => 'assets/img/hero.webp',      // woman relaxed at sunny home desk
    'product'  => 'assets/img/product.webp',   // product on cream backdrop
    'pain'     => 'assets/img/pain.webp',      // person rubbing lower back at desk
    'detail'   => 'assets/img/detail.webp',    // close-up of foam / fabric texture
    'wfh'      => 'assets/img/life-wfh.webp',
    'office'   => 'assets/img/life-office.webp',
    'gaming'   => 'assets/img/life-gaming.webp',
    'study'    => 'assets/img/life-study.webp',
    'mom'      => 'assets/img/life-mom.webp',
    'cta'      => 'assets/img/cta.webp',       // calm evening, person stretching happily
];

/** Render an image if it exists, otherwise a soft labelled placeholder. */
function photo($src, $alt, $class = '', $label = null, $eager = false) {
    if (is_file(__DIR__ . '/' . $src)) {
        return '<img src="' . $src . '?v=' . filemtime(__DIR__ . '/' . $src) . '" alt="' . htmlspecialchars($alt) . '" class="' . $class . ' object-cover" ' . ($eager ? 'fetchpriority="high"' : 'loading="lazy"') . ' decoding="async">';
    }
    $label = $label ?: basename($src);
    return '<div class="' . $class . ' ph grid place-items-center" role="img" aria-label="' . htmlspecialchars($alt) . '">'
         . '<span class="text-[11px] tracking-widest uppercase text-ink/40 bg-white/60 rounded-full px-3 py-1">' . htmlspecialchars($label) . '</span></div>';
}

$offers = [
    ['qty' => 1, 'title' => 'Just me',       'tag' => 'Start feeling better', 'price' => 49,  'compare' => 109,  'perks' => ['1 × Melara Seat Cushion', 'Washable soft-knit cover', 'Free shipping'], 'badge' => null, 'featured' => false],
    ['qty' => 2, 'title' => 'Home & work',   'tag' => 'Home office + work',           'price' => 89,  'compare' => 234, 'perks' => ['2 × Melara Seat Cushion', 'Free spare cover', 'Free express shipping', 'Priority support'], 'badge' => 'Most loved', 'featured' => true],
    ['qty' => 3, 'title' => 'Every chair',    'tag' => 'Office · home · gaming',    'price' => 119, 'compare' => 340, 'perks' => ['3 × Melara Seat Cushion', '2 free spare covers', 'Free express shipping', 'Linen carry bag'], 'badge' => 'Save 65%', 'featured' => false],
    ['qty' => 4, 'title' => 'The team',    'tag' => 'Family or office team',    'price' => 139, 'compare' => 463, 'perks' => ['4 × Melara Seat Cushion', '4 free spare covers', 'Free express shipping', 'Linen carry bag', 'Lifetime foam warranty'], 'badge' => 'Best value', 'featured' => false],
];

$pains = [
    ['t' => 'That dull ache by 3pm', 'd' => 'Your lower back starts talking halfway through the day — and doesn\'t stop.'],
    ['t' => 'Tailbone that throbs', 'd' => 'Sitting down feels like a negotiation. Standing up feels like relief.'],
    ['t' => 'Constant shifting', 'd' => 'You can\'t focus on work, a game or the road because you can\'t get comfortable.'],
    ['t' => 'Slouching without noticing', 'd' => 'Hours later your neck and shoulders pay for it too.'],
];

$benefits = [
    ['n' => '01', 'icon' => '<path d="M3 9.5c2.2-1.6 4.3-1.6 6.5 0s4.3 1.6 6.5 0 3.5-1.2 5-.8"/><path d="M3 14.5c2.2-1.6 4.3-1.6 6.5 0s4.3 1.6 6.5 0 3.5-1.2 5-.8"/><path d="M3 19.5h18"/><path d="M12 2.5v3.5m-1.6-1.6L12 6l1.6-1.6"/>', 't' => 'Soft where you sink', 'd' => 'Slow-rebound memory foam gently shapes to you, so your weight is spread evenly instead of pressing on two sore spots.'],
    ['n' => '02', 'icon' => '<path d="M4.5 4v7a7.5 7.5 0 0 0 15 0V4"/><path d="M9.5 4v6.5a2.5 2.5 0 0 0 5 0V4"/><path d="M4.5 4h5M14.5 4h5"/>', 't' => 'Space for your tailbone', 'd' => 'A gentle U-shaped opening lets your tailbone float, so sitting stops hurting.'],
    ['n' => '03', 'icon' => '<path d="M12 2.5c-1.6 2.4 1.6 4.4 0 6.8s1.6 4.4 0 6.8 1.6 3.4 0 5.4"/><path d="M8.5 5.5h2M13.5 9h2M8.5 12.5h2M13.5 16h2"/><path d="M5 4c-1.2 5.5-1.2 10.5 0 16M19 4c1.2 5.5 1.2 10.5 0 16"/>', 't' => 'A backrest that hugs you', 'd' => 'The wave-shaped back cushion curves around your lower back and keeps you sitting tall — no more slouching by lunch.'],
    ['n' => '04', 'icon' => '<path d="M2 12h4.5M17.5 12H22"/><rect x="6.5" y="8" width="11" height="8" rx="2.2"/><path d="M10.5 8v8M13.5 10.5l1.5 1.5-1.5 1.5"/>', 't' => 'Fits the chair you already have', 'd' => 'Two adjustable buckle straps wrap around the chair back, so it stays put on office, gaming and dining chairs. No tools, no new chair.'],
];

$life = [
    ['k' => 'wfh',    't' => 'Home office',      'd' => 'Calls, deadlines, and finally no back pain.'],
    ['k' => 'office', 't' => 'At the office',    'd' => 'Turn a basic work chair into the best seat on the floor.'],
    ['k' => 'gaming', 't' => 'Late-night gaming', 'd' => 'One more round, without the ache.'],
    ['k' => 'study',  't' => 'Study sessions',   'd' => 'Stay focused on the work, not the chair.'],
    ['k' => 'mom',    't' => 'After baby',       'd' => 'Gentle support during recovery and long feeds.'],
];

$faqs = [
    ['q' => 'Will it fit my chair?', 'a' => 'It fits seats 40–55 cm wide — office chairs, gaming chairs, car seats, wheelchairs and dining chairs. The strap keeps it in place.'],
    ['q' => 'How soon will I feel a difference?', 'a' => 'Most people feel less pressure the first time they sit down. The foam fully settles to your shape within about a week.'],
    ['q' => 'Can I wash it?', 'a' => 'Yes. The soft-knit cover unzips and goes in a cold machine wash. Keep the foam core dry.'],
    ['q' => 'What if it\'s not right for me?', 'a' => 'You have 100 nights to try it. If you don\'t love it, send it back for a full refund.'],
    ['q' => 'How fast is shipping?', 'a' => 'Orders ship within 24 hours. Standard delivery is 3–6 business days; express (free on 2+ packs) is 1–3.'],
];

// Sample reviews — replace with real customer reviews before launch
$reviews = [
    ['name' => 'Jordan M.',  'photo' => 'review-ang',     'role' => 'Works from home',     'text' => 'I used to end every day lying on the floor to stretch my back. Two weeks in and I just… don\'t need to anymore.'],
    ['name' => 'Kim R.',     'photo' => 'review-kim',     'role' => 'Streamer',            'text' => 'It looks clean on my chair and I can sit through a six-hour stream without shifting every five minutes.'],
    ['name' => 'Bert D.',    'photo' => 'review-bert',    'role' => 'Sales manager',       'text' => 'The straps actually stay put on my office chair. My tailbone used to hurt by noon. Not anymore.'],
    ['name' => 'Ping K.',    'photo' => 'review-ping',    'role' => 'New mom, works remote', 'text' => 'After giving birth, sitting was painful. This made long workdays and feeding time so much easier.'],
    ['name' => 'Taichi T.',  'photo' => 'review-taichi',  'role' => 'Accountant, 47',      'text' => 'I didn\'t expect much, but my posture is noticeably better. My physio asked where I got it.'],
    ['name' => 'Yoshiko A.', 'photo' => 'review-yoshiko', 'role' => 'Project manager',     'text' => 'Back-to-back meetings used to wreck my lower back. Now I barely think about my chair at all.'],
    ['name' => 'Toshiro N.', 'photo' => 'review-toshiro', 'role' => 'University student',  'text' => 'Exam season on a library chair was brutal. This cushion is the reason I can study for hours.'],
    ['name' => 'Eley S.',    'photo' => 'review-eley',    'role' => 'HR specialist',       'text' => 'Ordered one, then the Home & work pack a week later. The backrest support is the real game changer.'],
    ['name' => 'Lau B.',     'photo' => 'review-lau',     'role' => 'Marketing lead',      'text' => 'Soft but supportive, and the knit cover looks great. No more 3pm back ache before deadlines.'],
];

// Translate the copy in the data lists above
$pains    = tRows($pains, 'pain', ['t', 'd']);
$benefits = tRows($benefits, 'benefit', ['t', 'd']);
$life     = tRows($life, 'life', ['t', 'd']);
$faqs     = tRows($faqs, 'faq', ['q', 'a']);
$reviews  = tRows($reviews, 'review', ['role', 'text']);

function money($n) { global $currency; return $currency . number_format($n, 0); }
?>
<!doctype html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $product ?> — Upgrade Your Office Chair</title>
    <meta name="description" content="<?= $product ?>: a memory-foam seat cushion with tailbone relief and a supportive backrest. Try it for 100 nights.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Montserrat', 'system-ui', 'sans-serif'],
                        serif: ['Montserrat', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        cream: '#f8f9fa',
                        linen: '#eef0f2',
                        sand:  '#e1e4e8',
                        turq:  { DEFAULT: '#1dd1a1', dark: '#14b88c', soft: '#d6f7ee', ink: '#0a8a68' },
                        ocean: { DEFAULT: '#1dd1a1', soft: '#e6f9f3' },
                        ink:   '#1d2226',
                        muted: '#5e656d',
                    },
                    animation: {
                        'marquee': 'marquee 40s linear infinite',
                        'float': 'float 7s ease-in-out infinite',
                        'kenburns': 'kenburns 24s ease-in-out infinite alternate',
                    },
                    keyframes: {
                        marquee: { from: { transform: 'translateX(0)' }, to: { transform: 'translateX(-50%)' } },
                        float: { '0%,100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-10px)' } },
                        kenburns: { from: { transform: 'scale(1)' }, to: { transform: 'scale(1.08)' } },
                    },
                },
            },
        };
    </script>

    <style>
        /* Montserrat throughout; titles bold; emphasis is colour-only, never italic */
        .font-serif { font-weight: 700; letter-spacing: -0.03em; }
        em, .italic { font-style: normal; }
        html, body { background: #f8f9fa; }
        body { background: linear-gradient(180deg, #fbfbfc 0%, #f3f4f6 45%, #fafafb 100%); }
        .bg-soft-gray { background: linear-gradient(180deg, #f1f3f5 0%, #f7f8f9 60%, #eef0f2 100%); }

        /* soft paper grain */
        body::after {
            content: ""; position: fixed; inset: 0; pointer-events: none; z-index: 60; opacity: .035;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        }

        /* image placeholder until real photos are added */
        .ph { background: linear-gradient(135deg, #f3f4f6 0%, #e7eaed 55%, #dff6ef 100%); }

        /* dark charcoal with accent glow */
        .mesh {
            background-color: #1d2226;
            background-image:
                linear-gradient(var(--veil, transparent), var(--veil, transparent)),
                radial-gradient(ellipse 38% 30% at 18% 24%, rgba(29,209,161,.55), transparent 70%),
                radial-gradient(ellipse 26% 34% at 72% 18%, rgba(29,209,161,.18), transparent 70%),
                radial-gradient(ellipse 30% 24% at 48% 62%, rgba(255,255,255,.06), transparent 70%),
                radial-gradient(ellipse 22% 30% at 86% 74%, rgba(29,209,161,.30), transparent 70%),
                radial-gradient(ellipse 34% 22% at 12% 84%, rgba(29,209,161,.12), transparent 70%),
                radial-gradient(ellipse 18% 16% at 60% 36%, rgba(0,0,0,.25), transparent 70%),
                radial-gradient(ellipse 16% 20% at 32% 52%, rgba(0,0,0,.2), transparent 70%);
        }
        .mesh-soft { background-image:
                radial-gradient(ellipse 30% 26% at 20% 30%, rgba(29,209,161,.16), transparent 70%),
                radial-gradient(ellipse 26% 30% at 78% 20%, rgba(200,205,212,.35), transparent 70%),
                radial-gradient(ellipse 28% 22% at 60% 80%, rgba(29,209,161,.12), transparent 70%); }

        /* organic blob shape */
        .blob { border-radius: 58% 42% 46% 54% / 52% 44% 56% 48%; }

        /* scroll reveal */
        .reveal { opacity: 0; transform: translateY(32px); transition: opacity 1s cubic-bezier(.2,.7,.2,1), transform 1s cubic-bezier(.2,.7,.2,1); }
        .reveal.in { opacity: 1; transform: none; }
        .reveal[data-delay="1"] { transition-delay: .1s; }
        .reveal[data-delay="2"] { transition-delay: .2s; }
        .reveal[data-delay="3"] { transition-delay: .3s; }
        .reveal[data-delay="4"] { transition-delay: .4s; }
        .reveal[data-delay="5"] { transition-delay: .5s; }

        /* word-by-word headline reveal */
        .w { display: inline-block; opacity: 0; transform: translateY(.45em); filter: blur(6px);
             transition: opacity .8s cubic-bezier(.2,.7,.2,1), transform .8s cubic-bezier(.2,.7,.2,1), filter .8s ease; }
        .in .w, .w.in { opacity: 1; transform: none; filter: none; }

        /* ---------- Product anatomy (scroll-driven, real renders) ---------- */
        .anat-stage { perspective: 1400px; }
        .anat-tilt { transform-style: preserve-3d; will-change: transform, opacity; }
        .anat-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; will-change: opacity, transform, filter; }
        .anat-glow { background: radial-gradient(circle, rgba(29,209,161,.38) 0%, rgba(29,209,161,.12) 38%, transparent 66%); }
        .anat-ring { position: absolute; left: 50%; top: 50%; border-radius: 9999px; border: 1px solid rgba(29,209,161,.35);
                     transform: translate(-50%, -50%) scale(.55); animation: ringPulse 5s ease-out infinite; }
        @keyframes ringPulse { from { transform: translate(-50%, -50%) scale(.55); opacity: .9; } to { transform: translate(-50%, -50%) scale(1.3); opacity: 0; } }
        .spark { position: absolute; width: 5px; height: 5px; border-radius: 9999px; background: #1dd1a1; box-shadow: 0 0 12px #1dd1a1; opacity: 0; animation: sparkUp linear infinite; }
        @keyframes sparkUp { 0% { transform: translateY(0) scale(.6); opacity: 0; } 15% { opacity: .9; } 100% { transform: translateY(-240px) scale(1); opacity: 0; } }
        .flow path { fill: none; stroke: #1dd1a1; stroke-linecap: round; stroke-dasharray: 18 82; animation: flow 3.2s linear infinite; filter: drop-shadow(0 0 2px rgba(29,209,161,.9)); }
        @keyframes flow { to { stroke-dashoffset: -100; } }

        /* hotspots: dot on the product + dashed leader + label */
        .hot { position: absolute; width: 0; height: 0; opacity: 0; transform: scale(.85); transition: opacity .45s ease, transform .45s ease; pointer-events: none; z-index: 5; }
        .hot.on { opacity: 1; transform: none; }
        .hot .dot { position: absolute; left: -7px; top: -7px; width: 14px; height: 14px; border-radius: 9999px; background: #1dd1a1;
                    box-shadow: 0 0 0 5px rgba(29,209,161,.25), 0 0 18px rgba(29,209,161,.9); }
        .hot .dot::after { content: ""; position: absolute; inset: -9px; border-radius: 9999px; border: 2px solid rgba(29,209,161,.55); animation: dotPulse 2s ease-out infinite; }
        @keyframes dotPulse { from { transform: scale(.6); opacity: 1; } to { transform: scale(1.6); opacity: 0; } }
        .hot .line { position: absolute; top: -1px; height: 2px; width: var(--len, 48px); background: repeating-linear-gradient(90deg, rgba(29,34,38,.45) 0 4px, transparent 4px 8px); }
        .hot .label { position: absolute; top: 0; width: 180px; transform: translateY(-50%); }
        .hot[data-side="r"] .line { left: 10px; }
        .hot[data-side="r"] .label { left: calc(var(--len, 48px) + 14px); }
        .hot[data-side="l"] .line { right: 10px; }
        .hot[data-side="l"] .label { right: calc(var(--len, 48px) + 14px); text-align: right; }
        @media (max-width: 1023px) { .hot .line, .hot .label { display: none; } }

        .chap { opacity: .4; transition: opacity .4s; }
        .chap.active { opacity: 1; }
        .chap .bar { transform: scaleY(0); transform-origin: top; transition: transform .5s; }
        .chap.active .bar { transform: scaleY(1); }

        /* hand-drawn underline */
        .scribble { position: relative; white-space: nowrap; isolation: isolate; }
        .scribble svg { position: absolute; left: 0; right: 0; bottom: -.05em; width: 100%; height: .4em; z-index: -1; }
        .scribble path { stroke-dasharray: 300; stroke-dashoffset: 300; transition: stroke-dashoffset 1.4s .6s ease; }
        .in .scribble path, .scribble.in path { stroke-dashoffset: 0; }

        /* day comparison */
        .cmp .cmp-line { stroke-dasharray: 1; stroke-dashoffset: 1; transition: stroke-dashoffset 2s cubic-bezier(.4,.1,.2,1); }
        .cmp.in .cmp-line { stroke-dashoffset: 0; }
        .cmp .cmp-area { opacity: 0; transition: opacity 1.2s ease 1.4s; }
        .cmp.in .cmp-area { opacity: 1; }
        .cmp .cmp-pt { opacity: 0; transform: scale(0); transform-box: fill-box; transform-origin: center; transition: opacity .4s, transform .5s cubic-bezier(.3,1.6,.5,1); }
        .cmp.in .cmp-pt { opacity: 1; transform: none; }
        .cmp .cmp-bar { width: 0; transition: width 1.4s cubic-bezier(.2,.7,.2,1); }
        .cmp.in .cmp-bar { width: var(--w); }

        /* menu: highlight the section you are in */
        .nav-link { position: relative; padding: .4rem 0; transition: color .3s; }
        .nav-link::after { content: ""; position: absolute; left: 0; right: 0; bottom: -2px; height: 2px; border-radius: 2px; background: #1dd1a1; transform: scaleX(0); transition: transform .35s cubic-bezier(.2,.7,.2,1); }
        .nav-link:hover { color: #1d2226; }
        .nav-link.active { color: #1d2226; font-weight: 500; }
        .nav-link.active::after, .nav-link:hover::after { transform: scaleX(1); }
        #nav a[href^="checkout.php"].active { background: #1dd1a1; color: #1d2226; box-shadow: 0 8px 24px -8px rgba(29,209,161,.8); }

        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
        details[open] .faq-icon { transform: rotate(45deg); }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
            .reveal { opacity: 1; transform: none; }
            .scribble path { stroke-dashoffset: 0; }
        }
    </style>
</head>
<body class="font-sans text-ink antialiased selection:bg-turq-soft overflow-x-hidden">


<!-- Announcement -->
<div class="mesh text-cream text-xs sm:text-sm text-center py-2.5 px-4" style="--veil: rgba(29,34,38,.45)">
    <?= t('idx_launch_offer_up_to', 'Launch offer: up to') ?> <strong class="text-turq-soft"><?= t('idx_70_off', '70% off') ?></strong> <?= t('idx_bundles_ends_in', 'bundles · ends in') ?> <span data-countdown class="tabular-nums font-medium">--:--:--</span>
</div>

<!-- Nav -->
<header id="nav" class="sticky top-0 z-40 transition-all duration-300">
    <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <a href="#" class="shrink-0"><img src="<?= $logo ?>" alt="<?= $brand ?>" class="h-9 sm:h-10 w-auto" width="190" height="49"></a>
        <ul class="hidden md:flex items-center gap-9 text-sm text-muted">
            <li><a href="#why" class="nav-link"><?= t('idx_why_it_helps', 'Why it helps') ?></a></li>
            <li><a href="#life" class="nav-link"><?= t('idx_who_it_s_for', 'Who it\'s for') ?></a></li>
            <li><a href="#reviews" class="nav-link"><?= t('idx_reviews', 'Reviews') ?></a></li>
            <li><a href="#faq" class="nav-link"><?= t('idx_faq', 'FAQ') ?></a></li>
        </ul>
        <a href="checkout.php?pack=2" class="rounded-full bg-ink text-cream text-sm font-medium px-5 py-2.5 hover:bg-turq hover:text-ink transition"><?= t('idx_get_70_discount', 'Get 70% Discount') ?></a>
    </nav>
</header>

<main>

    <!-- ============ HERO ============ -->
    <section class="relative overflow-hidden mesh-soft">
        <div data-speed="-0.25" class="absolute -top-32 -right-32 w-[520px] h-[520px] blob bg-turq-soft/80 blur-2xl" aria-hidden="true"></div>
        <div data-speed="0.2" class="absolute bottom-0 -left-40 w-[420px] h-[420px] blob bg-ocean-soft blur-2xl" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-10 pb-20 lg:pt-16 lg:pb-28 grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7">
                <p class="reveal inline-flex items-center gap-2 rounded-full bg-white/70 border border-sand pl-1.5 pr-4 py-1.5 text-xs font-medium text-muted">
                    <span class="flex -space-x-2"><?php foreach (['review-ang', 'review-kim', 'review-yoshiko', 'review-bert'] as $av): ?><img src="assets/img/user/<?= $av ?>.png" alt="" width="24" height="24" class="w-6 h-6 rounded-full object-cover ring-2 ring-white"><?php endforeach; ?></span>
                    <span class="text-amber-500">★★★★★</span> <?= t('idx_loved_by_12_400', 'Loved by 12,400+ people who work from a desk') ?>
                </p>
                <h1 class="reveal mt-6 font-serif text-[2.5rem] sm:text-6xl lg:text-[clamp(2.75rem,4.6vw,4.5rem)] leading-[1.02] tracking-tight" data-delay="1">
                    <?= t('idx_upgrade_your', 'Upgrade your') ?>
                    <span class="scribble text-ink"><?= t('idx_office_chair', 'office chair.') ?>
                        <svg viewBox="0 0 200 20" preserveAspectRatio="none" aria-hidden="true"><path d="M2 14 C 50 4, 120 4, 198 12" fill="none" stroke="#1dd1a1" stroke-width="7" stroke-linecap="round"/></svg>
                    </span><br>
                    <?= t('idx_not_your_budget', 'Not your budget.') ?>
                </h1>
                <p class="reveal mt-6 max-w-lg text-lg text-muted leading-relaxed" data-delay="2">
                    <?= t('idx_the', 'The') ?> <?= $product ?> <?= t('idx_turns_the_chair_you', 'turns the chair you already own into an ergonomic one. A wave-shaped backrest hugs your lower back, a U-shaped cut-out takes pressure off your tailbone, and two straps lock it in place in seconds.') ?>
                </p>
                <div class="reveal mt-8 flex flex-col sm:flex-row gap-3" data-delay="3">
                    <a href="checkout.php?pack=2" class="group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-full bg-turq px-7 py-4 font-semibold text-ink shadow-[0_14px_30px_-12px_rgba(29,209,161,.6)] hover:bg-turq-dark transition">
                        <?= t('idx_get_70_discount_2', 'Get 70% Discount') ?>
                        <svg class="w-4 h-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                    <a href="#why" class="inline-flex items-center justify-center whitespace-nowrap rounded-full border border-ink/15 px-7 py-4 font-medium hover:bg-white transition"><?= t('idx_how_it_helps', 'How it helps') ?></a>
                </div>
                <?php
                // Hero trust points: [title, sub-line, icon path(s)]
                $heroPerks = [
                    ['100-night trial', 'Love it or return it', '<path d="M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5Z"/><path d="M16 3.5v3M14.5 5h3"/>'],
                    ['Free shipping',   'Ships within 24h',     '<path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/>'],
                    ['Washable cover',  'Machine washable', '<rect x="4" y="3" width="16" height="18" rx="2.5"/><circle cx="12" cy="13" r="4.5"/><path d="M9.6 13.6c.8-.8 1.6-.8 2.4 0s1.6.8 2.4 0M7.5 6.5h.01M10.5 6.5h.01"/>'],
                ];
                $heroPerks = tRows($heroPerks, 'perk', [0, 1]);
                ?>
                <ul class="reveal mt-9 flex flex-wrap gap-3 lg:flex-nowrap lg:gap-2.5" data-delay="4">
                    <?php foreach ($heroPerks as [$t, $sub, $icon]): ?>
                        <li class="group flex items-center gap-3 lg:gap-2.5 lg:flex-1 lg:min-w-0 rounded-2xl bg-white/80 backdrop-blur border border-sand pl-2 pr-4 lg:pr-3 py-2 shadow-[0_8px_24px_-16px_rgba(29,34,38,.35)] hover:-translate-y-0.5 hover:border-turq/40 transition">
                            <span class="grid place-items-center shrink-0 w-9 h-9 rounded-xl bg-turq-soft text-turq-ink group-hover:bg-turq group-hover:text-ink transition">
                                <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $icon ?></svg>
                            </span>
                            <span class="leading-tight min-w-0">
                                <span class="block text-sm lg:text-[13px] font-semibold text-ink whitespace-nowrap"><?= $t ?></span>
                                <span class="block text-xs lg:text-[11px] text-muted whitespace-nowrap"><?= $sub ?></span>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="lg:col-span-5 relative reveal order-first lg:order-none" data-delay="2">
                <div class="relative aspect-[4/5] sm:aspect-[5/5] rounded-[2.5rem] overflow-hidden shadow-[0_40px_80px_-30px_rgba(29,34,38,.35)]">
                    <div class="absolute inset-0 animate-kenburns">
                        <?= photo($img['hero'], 'Woman sitting comfortably on an office chair upgraded with the Melara seat cushion', 'w-full h-full', 'hero.webp', true) ?>
                    </div>
                </div>
                <!-- floating cards -->
                <div class="absolute left-3 sm:-left-8 bottom-6 sm:bottom-10 bg-white rounded-2xl shadow-xl px-5 py-4 max-w-[230px] animate-float">
                    <p class="text-amber-500 text-sm">★★★★★</p>
                    <p class="mt-1 text-sm leading-snug"><?= t('idx_first_time_in_years', '“First time in years I finished a workday without back pain.”') ?></p>
                    <p class="mt-3 flex items-center gap-2 text-xs text-muted"><img src="assets/img/user/review-ang.png" alt="" width="28" height="28" class="w-7 h-7 rounded-full object-cover"> <?= t('idx_jordan_works_from_home', 'Jordan, works from home') ?></p>
                </div>
                <div class="absolute right-3 sm:-right-6 top-4 sm:top-8 bg-white rounded-2xl shadow-xl px-5 py-4 animate-float" style="animation-delay:-3s">
                    <p class="font-serif text-3xl text-turq-ink">62%</p>
                    <p class="text-xs text-muted leading-snug"><?= t('idx_less_pressure', 'less pressure') ?><br><?= t('idx_on_your_tailbone', 'on your tailbone') ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ PRESS LOGOS ============ -->
    <?php
    // Press logos (assets/img/logos). 'h' = display height in px, tuned so the logos look visually balanced.
    // Only keep outlets where Melara has actually been featured.
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
            <p class="shrink-0 text-xs font-medium uppercase tracking-[.25em] text-muted text-center md:text-left"><?= t('idx_as_seen_in', 'As seen in') ?></p>
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

    <!-- ============ PAIN / EMPATHY ============ -->
    <section class="py-24 lg:py-32">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-14 items-center">
            <div class="reveal relative order-2 lg:order-1">
                <div class="aspect-[4/5] rounded-[2.5rem] overflow-hidden">
                    <?= photo($img['pain'], 'Person at a desk rubbing their sore lower back', 'w-full h-full', 'pain.jpg') ?>
                </div>
                <div class="absolute -bottom-6 right-6 sm:-right-6 mesh text-cream shadow-xl rounded-2xl px-6 py-5 max-w-[240px]" style="--veil: rgba(29,34,38,.5)">
                    <p class="font-serif text-4xl"><span data-count="9.5" data-dec="1">0</span> <?= t('idx_hrs', 'hrs') ?></p>
                    <p class="text-sm text-cream/70"><?= t('idx_the_average_adult_spends', 'the average adult spends sitting every day') ?></p>
                </div>
            </div>
            <div class="order-1 lg:order-2">
                <p class="reveal text-sm font-medium uppercase tracking-[.2em] text-turq-ink"><?= t('idx_sound_familiar', 'Sound familiar?') ?></p>
                <h2 class="reveal mt-4 font-serif text-4xl sm:text-5xl leading-tight tracking-tight" data-delay="1"><?= t('idx_it_s_not_you', 'It\'s not you.') ?> <em class="text-muted"><?= t('idx_it_s_your_office', 'It\'s your office chair.') ?></em></h2>
                <div class="mt-10 space-y-4">
                    <?php foreach ($pains as $i => $p): ?>
                        <div class="reveal flex gap-5 rounded-2xl bg-white border border-sand/70 p-5 hover:shadow-lg hover:-translate-y-0.5 transition" data-delay="<?= $i + 1 ?>">
                            <span class="grid place-items-center shrink-0 w-10 h-10 rounded-full bg-turq-soft text-turq-ink font-serif">✕</span>
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

    <!-- ============ PRODUCT ANATOMY (scroll-driven, real renders) ============ -->
    <?php
    $anatImg = [
        'a' => 'assets/img/anatomy-assembled.webp',
        'z' => 'assets/img/anatomy-zones.webp',
        'e' => 'assets/img/anatomy-exploded.webp',
    ];
    $chapters = [
        ['t' => 'Designed for your chair', 'd' => 'One-piece support that straps onto the chair you already own.'],
        ['t' => 'Pressure-relief zones',   'd' => 'See exactly where your body finally gets a break.'],
        ['t' => 'Built in layers',         'd' => 'Four layers working together for 8+ hours of comfort.'],
    ];
    // [image, x%, y%, label side, show from, hide at (scroll progress 0–1), title, text, optional leader length px]
    $hotspots = [
        ['a', 70, 16, 'r', .10, .29, 'Wave-shaped backrest',     'Curves around your lower back so you sit tall without trying.'],
        ['a', 93, 50, 'r', .14, .29, 'Adjustable straps',        'Quick-release buckles fit almost any office or gaming chair.', 22],
        ['a', 17, 64, 'l', .18, .29, 'Soft knit cover',          'Breathable, stretchy and zip-off washable.'],
        ['z', 62, 30, 'r', .38, .55, 'Lumbar support zone',      'Holds the natural curve of your lower back.'],
        ['z', 57, 56, 'r', .42, .55, 'Tailbone relief',          'The U-shaped cut-out takes pressure off your coccyx.'],
        ['z', 22, 64, 'l', .46, .55, 'Thigh support',            'Contoured grooves spread your weight evenly.'],
        ['z', 20, 27, 'l', .50, .55, '360° airflow',             'Breathable knit keeps you cool for hours.'],
        ['e', 76, 38, 'r', .70, .89, 'Zip-off 3D knit cover',    'Soft, cooling and machine washable.'],
        ['e', 23, 52, 'l', .73, .89, 'Slow-rebound memory foam', 'Shapes to you in seconds and spreads pressure.'],
        ['e', 81, 66, 'r', .76, .89, '55D high-resilience core', 'Firm support that never bottoms out.'],
        ['e', 22, 80, 'l', .79, .89, 'Non-slip grip base',       'Silicone dots stop it sliding forward.'],
    ];
    $chapters = tRows($chapters, 'chapter', ['t', 'd']);
    $hotspots = tRows($hotspots, 'hotspot', [6, 7]);
    ?>
    <section id="anatomy" class="relative" style="height: 520vh;" aria-label="Inside the <?= $product ?>">
        <div class="sticky top-0 h-screen overflow-hidden bg-soft-gray">
            <div class="relative mx-auto max-w-7xl h-full px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-6 items-center">
                <!-- Chapters -->
                <div class="lg:col-span-4 pt-20 lg:pt-0 relative z-10">
                    <p class="text-sm font-medium uppercase tracking-[.2em] text-turq-ink"><?= t('idx_inside_the_cushion', 'Inside the cushion') ?></p>
                    <h2 class="mt-3 font-serif text-3xl sm:text-5xl leading-tight tracking-tight"><?= t('idx_engineered_for', 'Engineered for') ?><br><em class="text-turq-ink"><?= t('idx_8_hours_of_comfort', '8+ hours of comfort.') ?></em></h2>
                    <ol class="mt-10 space-y-6 hidden lg:block">
                        <?php foreach ($chapters as $i => $c): ?>
                            <li class="chap relative pl-5">
                                <span class="absolute left-0 top-0 bottom-0 w-[3px] rounded-full bg-sand"><span class="bar block w-full h-full rounded-full bg-turq"></span></span>
                                <p class="text-xs text-muted">0<?= $i + 1 ?></p>
                                <p class="font-semibold text-lg"><?= $c['t'] ?></p>
                                <p class="text-sm text-muted mt-0.5"><?= $c['d'] ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                    <!-- mobile caption -->
                    <div class="lg:hidden mt-3 min-h-[60px]">
                        <p class="font-semibold" data-cap-name><?= t('idx_scroll_to_look_inside', 'Scroll to look inside') ?></p>
                        <p class="text-sm text-muted" data-cap-desc><?= t('idx_see_what_makes_it', 'See what makes it comfortable all day.') ?></p>
                    </div>
                </div>

                <!-- Stage -->
                <div class="lg:col-span-8 relative h-[62vh] lg:h-full grid place-items-center">
                    <div id="anatBox" class="anat-stage relative" style="width: min(560px, 88vw, 66vh); aspect-ratio: 1;">
                        <div id="anatGlow" class="absolute -inset-[18%] rounded-full anat-glow blur-xl" aria-hidden="true"></div>
                        <?php foreach ([[110, 0], [110, 1.6], [110, 3.2]] as [$sz, $dl]): ?>
                            <span class="anat-ring" style="width: <?= $sz ?>%; height: <?= $sz ?>%; animation-delay: <?= $dl ?>s;" aria-hidden="true"></span>
                        <?php endforeach; ?>
                        <?php foreach ([[12, 80, 0], [24, 88, 1.2], [38, 84, 2.6], [52, 90, .6], [66, 86, 1.9], [78, 82, 3.1], [88, 88, .3], [30, 70, 2.2], [60, 74, 3.6], [46, 92, 1.5]] as [$x, $y, $d]): ?>
                            <span class="spark" style="left: <?= $x ?>%; top: <?= $y ?>%; animation-duration: <?= 4 + ($x % 3) ?>s; animation-delay: <?= $d ?>s;" aria-hidden="true"></span>
                        <?php endforeach; ?>

                        <div id="anatTilt" class="anat-tilt absolute inset-0">
                            <div class="absolute inset-0 animate-float">
                                <img id="anatA" src="<?= $anatImg['a'] ?>" alt="<?= $product ?> with integrated backrest and adjustable straps" class="anat-img" loading="lazy" decoding="async">
                                <img id="anatZ" src="<?= $anatImg['z'] ?>" alt="Pressure-relief zones of the <?= $product ?>" class="anat-img" style="opacity: 0;" loading="lazy" decoding="async">
                                <img id="anatE" src="<?= $anatImg['e'] ?>" alt="Exploded view of the cushion layers: knit cover, memory foam, support core and grip base" class="anat-img" style="opacity: 0; transform-origin: 50% 80%;" loading="lazy" decoding="async">

                                <svg id="anatFlow" class="flow absolute inset-0 w-full h-full pointer-events-none" viewBox="0 0 100 100" preserveAspectRatio="none" style="opacity: 0;" aria-hidden="true">
                                    <path pathLength="100" stroke-width=".7" d="M-4,34 C18,24 34,44 52,40 S84,24 104,30"/>
                                    <path pathLength="100" stroke-width=".5" d="M-4,58 C16,50 36,66 56,58 S86,48 104,54" style="animation-delay: -1.1s"/>
                                    <path pathLength="100" stroke-width=".6" d="M-4,76 C22,70 40,82 62,74 S90,66 104,72" style="animation-delay: -2.2s"/>
                                </svg>

                                <?php foreach ($hotspots as $h): [$hsImg, $x, $y, $side, $in, $out, $t, $d] = $h; $len = $h[8] ?? 48; ?>
                                    <div class="hot" data-side="<?= $side ?>" data-in="<?= $in ?>" data-out="<?= $out ?>" style="left: <?= $x ?>%; top: <?= $y ?>%; --len: <?= $len ?>px;">
                                        <span class="dot"></span>
                                        <span class="line"></span>
                                        <div class="label bg-white/90 backdrop-blur rounded-2xl border border-sand shadow-lg px-4 py-3">
                                            <p class="text-sm font-semibold"><?= $t ?></p>
                                            <p class="text-xs text-muted leading-snug mt-0.5"><?= $d ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Finale badge -->
            <div id="assembled" class="absolute bottom-8 left-1/2 -translate-x-1/2 opacity-0 transition-opacity duration-500">
                <p class="rounded-full mesh text-cream px-6 py-3 text-sm font-medium shadow-xl whitespace-nowrap" style="--veil: rgba(29,34,38,.35)"><?= t('idx_all_together_comfort_that', '✓ All together: comfort that lasts all day') ?></p>
            </div>

            <!-- scroll hint -->
            <div id="scrollHint" class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-xs text-muted transition-opacity">
                <span><?= t('idx_scroll_to_explore', 'Scroll to explore') ?></span>
                <span class="w-[22px] h-[34px] rounded-full border-2 border-muted/40 grid justify-center pt-1.5"><span class="w-1 h-2 rounded-full bg-turq animate-bounce"></span></span>
            </div>
        </div>
    </section>

    <!-- ============ WHY IT HELPS ============ -->
    <section id="why" class="relative py-24 lg:py-32 bg-soft-gray scroll-mt-16 overflow-hidden">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="reveal text-sm font-medium uppercase tracking-[.2em] text-turq-ink"><?= t('idx_why_it_helps_2', 'Why it helps') ?></p>
                <h2 class="reveal mt-4 font-serif text-4xl sm:text-5xl leading-tight tracking-tight" data-delay="1"><?= t('idx_everything_your_office_chair', 'Everything your office chair') ?> <em class="text-turq-ink"><?= t('idx_was_missing', 'was missing.') ?></em></h2>
            </div>

            <div class="mt-16 grid lg:grid-cols-12 gap-10 items-center">
                <div class="lg:col-span-5 space-y-10">
                    <?php foreach (array_slice($benefits, 0, 2) as $i => $b): ?>
                        <div class="reveal group flex items-start gap-5 lg:flex-row-reverse lg:text-right" data-delay="<?= $i + 1 ?>">
                            <span class="relative shrink-0 grid place-items-center w-14 h-14 rounded-2xl bg-white border border-sand text-turq-ink shadow-[0_12px_30px_-18px_rgba(29,34,38,.45)] transition duration-300 group-hover:bg-turq group-hover:text-ink group-hover:border-turq group-hover:-translate-y-1 group-hover:shadow-[0_14px_30px_-12px_rgba(29,209,161,.7)]">
                                <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $b['icon'] ?></svg>
                                <span class="absolute -top-2 -right-2 grid place-items-center w-6 h-6 rounded-full bg-ink text-cream text-[10px] font-semibold"><?= $b['n'] ?></span>
                            </span>
                            <div>
                                <h3 class="font-serif text-2xl"><?= $b['t'] ?></h3>
                                <p class="mt-2 text-muted leading-relaxed"><?= $b['d'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="lg:col-span-2 reveal hidden lg:block" data-delay="2">
                    <div class="aspect-[1/3] w-full rounded-full overflow-hidden">
                        <?= photo($img['detail'], 'Close-up of soft memory foam and knit fabric', 'w-full h-full', 'detail.jpg') ?>
                    </div>
                </div>
                <div class="lg:col-span-5 space-y-10">
                    <?php foreach (array_slice($benefits, 2) as $i => $b): ?>
                        <div class="reveal group flex items-start gap-5 " data-delay="<?= $i + 3 ?>">
                            <span class="relative shrink-0 grid place-items-center w-14 h-14 rounded-2xl bg-white border border-sand text-turq-ink shadow-[0_12px_30px_-18px_rgba(29,34,38,.45)] transition duration-300 group-hover:bg-turq group-hover:text-ink group-hover:border-turq group-hover:-translate-y-1 group-hover:shadow-[0_14px_30px_-12px_rgba(29,209,161,.7)]">
                                <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $b['icon'] ?></svg>
                                <span class="absolute -top-2 -right-2 grid place-items-center w-6 h-6 rounded-full bg-ink text-cream text-[10px] font-semibold"><?= $b['n'] ?></span>
                            </span>
                            <div>
                                <h3 class="font-serif text-2xl"><?= $b['t'] ?></h3>
                                <p class="mt-2 text-muted leading-relaxed"><?= $b['d'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Your day, compared: comfort chart + matrix -->
            <?php
            // Illustrative comfort score (0–100) through a workday
            $day = [
                ['t' => '9am',  'reg' => [85, 0, 'Feeling fine'],       'mel' => [92, 'Comfortable']],
                ['t' => '11am', 'reg' => [60, 1, 'Shifting around'],    'mel' => [90, 'Still comfortable']],
                ['t' => '2pm',  'reg' => [35, 2, 'Lower back aching'],  'mel' => [88, 'Focused, not fidgeting']],
                ['t' => '5pm',  'reg' => [15, 3, 'Stiff, sore, done'],  'mel' => [90, 'Energy for the evening']],
            ];
            foreach ($day as $i => &$d) { $d['reg'][2] = t("day_{$i}_regular", $d['reg'][2]); $d['mel'][1] = t("day_{$i}_melara", $d['mel'][1]); }
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
            <div class="reveal cmp mt-20 rounded-[2rem] border border-sand bg-white shadow-[0_30px_80px_-40px_rgba(29,34,38,.25)] overflow-hidden">
                <!-- header -->
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 p-7 sm:p-10 pb-0 sm:pb-0">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-[.2em] text-turq-ink"><?= t('idx_your_day_compared', 'Your day, compared') ?></p>
                        <h3 class="mt-2 font-serif text-3xl sm:text-4xl tracking-tight"><?= t('idx_same_chair', 'Same chair.') ?> <em class="text-turq-ink"><?= t('idx_very_different_5pm', 'Very different 5pm.') ?></em></h3>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-sm">
                        <span class="inline-flex items-center gap-2 rounded-full bg-rose-50 border border-rose-100 px-3 py-1.5 text-rose-600"><span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span><?= t('idx_regular_chair', 'Regular chair') ?></span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-turq-soft/60 border border-turq/30 px-3 py-1.5 text-turq-ink"><span class="w-2.5 h-2.5 rounded-full bg-turq"></span><?= t('idx_with', 'With') ?> <?= $brand ?></span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-ink text-cream px-3 py-1.5 font-medium"><?= t('idx_5pm_comfort', '5pm comfort') ?> <strong class="text-turq"><?= end($melVals) ?></strong> <?= t('idx_vs', 'vs') ?> <span class="text-rose-300"><?= end($regVals) ?></span></span>
                    </div>
                </div>

                <!-- chart -->
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

                <!-- matrix -->
                <div class="grid grid-cols-2 lg:grid-cols-4 border-t border-sand mt-2">
                    <?php foreach ($day as $i => $d): [$rv, $mood, $rt] = $d['reg']; [$mv, $mt] = $d['mel']; ?>
                        <div class="p-5 sm:p-6 border-sand <?= $i % 2 ? 'border-l' : '' ?> <?= $i === 2 ? 'lg:border-l' : '' ?> <?= $i > 1 ? 'border-t lg:border-t-0' : '' ?>">
                            <p class="font-serif text-2xl"><?= $d['t'] ?></p>

                            <div class="mt-4 flex items-start gap-3">
                                <span class="grid place-items-center shrink-0 w-9 h-9 rounded-xl bg-rose-50 text-rose-500"><?= $face($mood) ?></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-muted leading-tight"><?= $rt ?></p>
                                    <div class="mt-2 h-1.5 rounded-full bg-linen overflow-hidden"><div class="cmp-bar h-full rounded-full bg-rose-400" style="--w: <?= $rv ?>%; transition-delay: <?= .4 + $i * .15 ?>s"></div></div>
                                </div>
                            </div>

                            <div class="mt-4 flex items-start gap-3 rounded-2xl bg-turq-soft/40 -mx-2 px-2 py-2">
                                <span class="grid place-items-center shrink-0 w-9 h-9 rounded-xl bg-turq text-ink shadow-[0_6px_16px_-6px_rgba(29,209,161,.9)]"><?= $face('happy') ?></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium leading-tight"><?= $mt ?></p>
                                    <div class="mt-2 h-1.5 rounded-full bg-white overflow-hidden"><div class="cmp-bar h-full rounded-full bg-turq" style="--w: <?= $mv ?>%; transition-delay: <?= .6 + $i * .15 ?>s"></div></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="px-7 sm:px-10 py-4 border-t border-sand text-xs text-muted"><?= t('idx_illustrative_comfort_score_based', 'Illustrative comfort score based on a typical 8-hour workday.') ?></p>
            </div>
        </div>
    </section>

    <!-- ============ EVERYDAY LIFE ============ -->
    <section id="life" class="py-24 lg:py-32 scroll-mt-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
                <div>
                    <p class="reveal text-sm font-medium uppercase tracking-[.2em] text-turq-ink"><?= t('idx_made_for_real_life', 'Made for real life') ?></p>
                    <h2 class="reveal mt-4 font-serif text-4xl sm:text-5xl leading-tight tracking-tight" data-delay="1"><?= t('idx_one_upgrade', 'One upgrade.') ?><br><em><?= t('idx_every_desk_you_sit', 'Every desk you sit at.') ?></em></h2>
                </div>
                <p class="reveal max-w-sm text-muted" data-delay="2"><?= t('idx_light_enough_to_bring', 'Light enough to bring to work, with straps that fit almost any office or gaming chair.') ?></p>
            </div>

            <div class="mt-14 grid grid-cols-2 lg:grid-cols-6 gap-4">
                <?php foreach ($life as $i => $l):
                    $span = $i < 2 ? 'lg:col-span-3 aspect-[4/3]' : 'lg:col-span-2 aspect-[4/5]';
                    if ($i === 4) $span .= ' col-span-2 lg:col-span-2 aspect-[16/10] lg:aspect-[4/5]';
                ?>
                    <article class="reveal group relative rounded-[1.75rem] overflow-hidden <?= $span ?>" data-delay="<?= ($i % 3) + 1 ?>">
                        <div class="absolute inset-0 transition duration-700 group-hover:scale-105">
                            <?= photo($img[$l['k']], $l['t'], 'w-full h-full object-[50%_25%]', basename($img[$l['k']])) ?>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-ink/70 via-ink/10 to-transparent"></div>
                        <div class="absolute bottom-0 p-5 sm:p-7 text-cream">
                            <h3 class="font-serif text-xl sm:text-2xl"><?= $l['t'] ?></h3>
                            <p class="mt-1 text-sm text-cream/80 max-w-xs hidden sm:block"><?= $l['d'] ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ REVIEWS ============ -->
    <section id="reviews" class="py-24 lg:py-32 bg-soft-gray scroll-mt-16 overflow-hidden">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
            <p class="reveal text-amber-500 text-lg">★★★★★</p>
            <h2 class="reveal mt-3 font-serif text-4xl sm:text-5xl tracking-tight" data-delay="1"><?= t('idx_why_didn_t_i', '“Why didn\'t I get this') ?> <em class="text-turq-ink"><?= t('idx_years_ago', 'years ago?”') ?></em></h2>
            <p class="reveal mt-4 text-muted" data-delay="2"><?= t('idx_4_9_out_of', '4.9 out of 5 from 12,400+ happy sitters') ?></p>
        </div>
        <div class="mt-14 relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 w-16 sm:w-40 bg-gradient-to-r from-linen to-transparent z-10"></div>
            <div class="pointer-events-none absolute inset-y-0 right-0 w-16 sm:w-40 bg-gradient-to-l from-linen to-transparent z-10"></div>
            <div class="flex w-max animate-marquee gap-5 hover:[animation-play-state:paused]" style="animation-duration: 55s;">
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
                            <span class="ml-auto inline-flex items-center gap-1 rounded-full bg-turq-soft/70 border border-turq/30 pl-1 pr-2.5 py-0.5 text-[11px] font-semibold text-turq-ink whitespace-nowrap">
                                <svg viewBox="0 0 24 24" class="w-4 h-4" aria-hidden="true"><path fill="#1dd1a1" d="M12 2 4 5v6.1c0 5 3.4 9.4 8 10.9 4.6-1.5 8-5.9 8-10.9V5l-8-3z"/><path d="M12 2v20c4.6-1.5 8-5.9 8-10.9V5l-8-3z" fill="#14b88c"/><path d="m8.6 12 2.4 2.4 4.4-4.6" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <?= t('idx_verified_buyer', 'Verified buyer') ?>
                            </span>
                        </figcaption>
                    </figure>
                <?php endforeach; endfor; ?>
            </div>
        </div>
    </section>

    <!-- ============ FAQ ============ -->
    <section id="faq" class="py-24 lg:py-32 scroll-mt-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="reveal text-center font-serif text-4xl sm:text-5xl tracking-tight"><?= t('idx_good_questions', 'Good questions') ?></h2>
            <div class="mt-12 space-y-3">
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
        </div>
    </section>

    <!-- ============ FINAL CTA ============ -->
    <section class="px-4 sm:px-6 lg:px-8 py-24">
        <div class="reveal relative mx-auto max-w-7xl rounded-[2.5rem] overflow-hidden min-h-[460px] flex items-end">
            <div class="absolute inset-0 animate-kenburns" style="transform-origin: 70% 40%;">
                <?= photo($img['cta'], 'Office chair with the Melara seat cushion in warm evening sunlight', 'w-full h-full object-[78%_30%] lg:object-[50%_30%]', 'cta.webp') ?>
            </div>
            <div class="absolute inset-0 bg-gradient-to-r from-ink/80 via-ink/40 to-transparent"></div>
            <div class="relative p-10 sm:p-16 max-w-xl text-cream">
                <h2 class="font-serif text-4xl sm:text-6xl leading-[1.05] tracking-tight"><?= t('idx_end_the_day', 'End the day') ?> <em class="text-turq-soft"><?= t('idx_feeling_good', 'feeling good.') ?></em></h2>
                <p class="mt-5 text-lg text-cream/80"><?= t('idx_try_it_for_100', 'Try it for 100 nights. If your back doesn\'t thank you, we\'ll refund every cent.') ?></p>
                <a href="checkout.php?pack=2" class="mt-8 inline-flex items-center gap-2 rounded-full bg-cream text-ink px-8 py-4 font-medium hover:bg-turq hover:text-ink transition">
                    <?= t('idx_get_70_discount_3', 'Get 70% Discount') ?>
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
        </div>
    </section>
</main>

<footer class="border-t border-sand pb-28 md:pb-10 pt-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6 text-sm text-muted">
        <img src="<?= $logo ?>" alt="<?= $brand ?>" class="h-10 w-auto" width="190" height="49" loading="lazy">
        <ul class="flex flex-wrap justify-center gap-6">
            <li><a href="#" class="hover:text-ink transition"><?= t('idx_shipping', 'Shipping') ?></a></li>
            <li><a href="#" class="hover:text-ink transition"><?= t('idx_returns', 'Returns') ?></a></li>
            <li><a href="#" class="hover:text-ink transition"><?= t('idx_privacy', 'Privacy') ?></a></li>
            <li><a href="#" class="hover:text-ink transition"><?= t('idx_contact', 'Contact') ?></a></li>
        </ul>
        <p>© <?= date('Y') ?> <?= $brand ?><?= t('idx_all_rights_reserved', '. All rights reserved.') ?></p>
    </div>
</footer>

<!-- Mobile sticky buy bar -->
<div id="buyBar" class="md:hidden fixed inset-x-3 bottom-3 z-50 translate-y-[150%] transition-transform duration-500">
    <a href="checkout.php?pack=2" class="flex items-center justify-between rounded-2xl bg-white/90 backdrop-blur border border-sand px-5 py-3 shadow-2xl">
        <span>
            <span class="block text-xs text-muted"><?= t('idx_from', 'From') ?></span>
            <span class="font-serif text-xl"><?= money($offers[0]['price']) ?> <span class="text-sm text-muted line-through"><?= money($offers[0]['compare']) ?></span></span>
        </span>
        <span class="rounded-full bg-turq px-5 py-2.5 text-sm font-semibold text-ink"><?= t('idx_get_70_discount_4', 'Get 70% Discount') ?></span>
    </a>
</div>

<script>
(() => {
    const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Nav background + mobile buy bar
    const nav = document.getElementById('nav'), buyBar = document.getElementById('buyBar');
    const onScroll = () => {
        const scrolled = scrollY > 30;
        nav.classList.toggle('bg-cream/85', scrolled);
        nav.classList.toggle('backdrop-blur', scrolled);
        nav.classList.toggle('shadow-sm', scrolled);
        buyBar.style.transform = scrollY > 600 ? 'translateY(0)' : '';
    };
    addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // Reveal, counters, stock bar
    const countUp = el => {
        const target = parseFloat(el.dataset.count), dec = +el.dataset.dec || 0;
        if (reduce) { el.textContent = target.toFixed(dec); return; }
        const t0 = performance.now();
        const step = t => {
            const p = Math.min((t - t0) / 1600, 1);
            el.textContent = (target * (1 - Math.pow(1 - p, 4))).toFixed(dec);
            if (p < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    };
    const io = new IntersectionObserver(entries => entries.forEach(en => {
        if (!en.isIntersecting) return;
        const el = en.target;
        el.classList.add('in');
        el.querySelectorAll('[data-count]').forEach(countUp);
        el.querySelectorAll('.stock-bar').forEach(b => b.style.width = b.dataset.width);
        io.unobserve(el);
    }), { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.reveal').forEach(el => io.observe(el));

    // Countdown (restarts when it runs out)
    const cds = document.querySelectorAll('[data-countdown]');
    let deadline;
    try { deadline = +localStorage.getItem('melara_deadline'); } catch (_) {}
    if (!deadline || deadline < Date.now()) {
        deadline = Date.now() + (5 * 3600 + 47 * 60) * 1000;
        try { localStorage.setItem('melara_deadline', deadline); } catch (_) {}
    }
    const pad = n => String(n).padStart(2, '0');
    const tick = () => {
        const s = Math.max(0, Math.floor((deadline - Date.now()) / 1000));
        cds.forEach(c => c.textContent = `${pad(Math.floor(s / 3600))}:${pad(Math.floor(s % 3600 / 60))}:${pad(s % 60)}`);
    };
    tick(); setInterval(tick, 1000);

    // ---------- Product anatomy: real renders, driven by scroll ----------
    const anatomy = document.getElementById('anatomy');
    const anatA = document.getElementById('anatA'), anatZ = document.getElementById('anatZ'), anatE = document.getElementById('anatE');
    const anatTilt = document.getElementById('anatTilt'), anatGlow = document.getElementById('anatGlow'), anatFlow = document.getElementById('anatFlow');
    const hots = [...anatomy.querySelectorAll('.hot')];
    const chaps = [...anatomy.querySelectorAll('.chap')];
    const capName = anatomy.querySelector('[data-cap-name]'), capDesc = anatomy.querySelector('[data-cap-desc]');
    const assembled = document.getElementById('assembled'), hint = document.getElementById('scrollHint');
    const clamp = (v, a = 0, b = 1) => Math.min(b, Math.max(a, v));
    const ease = t => t < .5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
    const ramp = (p, a, b) => ease(clamp((p - a) / (b - a)));
    // load the renders before the section is reached so the cross-fades never pop
    [anatA, anatZ, anatE].forEach(i => i.loading = 'eager');
    let lastHot = null;

    const renderAnatomy = () => {
        const r = anatomy.getBoundingClientRect();
        if (r.bottom < 0 || r.top > innerHeight) return;
        const p = clamp(-r.top / (anatomy.offsetHeight - innerHeight));

        const enter = ramp(p, 0, .08);
        const back  = ramp(p, .90, .96);                     // return to the assembled view
        const zIn = ramp(p, .30, .36), zOut = ramp(p, .56, .62);
        const spread = ramp(p, .56, .70);                    // layers pulling apart
        const oA = Math.max(1 - zIn, back);
        const oZ = zIn * (1 - zOut);
        const oE = ramp(p, .56, .64) * (1 - back);

        anatA.style.opacity = oA;
        anatA.style.filter = `blur(${((1 - oA) * 6).toFixed(2)}px)`;
        anatZ.style.opacity = oZ;
        anatZ.style.transform = `scale(${(1 + .05 * (1 - oZ)).toFixed(3)})`;
        anatE.style.opacity = oE;
        anatE.style.transform = `scaleY(${(.6 + .4 * spread).toFixed(3)}) scaleX(${(.92 + .08 * spread).toFixed(3)})`;
        anatE.style.filter = `blur(${((1 - spread) * 8).toFixed(2)}px)`;
        anatFlow.style.opacity = oZ;
        anatGlow.style.opacity = (.45 + .55 * Math.max(oZ, oE * .6)).toFixed(3);
        anatGlow.style.transform = `scale(${(.9 + .25 * oZ).toFixed(3)})`;

        const ry = -14 + 28 * p, rx = 6 - 10 * p;
        anatTilt.style.opacity = (.2 + .8 * enter).toFixed(3);
        anatTilt.style.transform = `scale(${(.82 + .18 * enter).toFixed(3)}) rotateX(${rx.toFixed(2)}deg) rotateY(${ry.toFixed(2)}deg)`;

        let latest = null;
        hots.forEach(h => {
            const on = p >= +h.dataset.in && p < +h.dataset.out;
            h.classList.toggle('on', on);
            if (on) latest = h;
        });
        if (latest !== lastHot) {
            lastHot = latest;
            if (latest && capName) {
                capName.textContent = latest.querySelector('.label p').textContent;
                capDesc.textContent = latest.querySelectorAll('.label p')[1].textContent;
            }
        }

        const chapter = p < .30 ? 0 : p < .58 ? 1 : 2;
        chaps.forEach((c, i) => c.classList.toggle('active', p < .92 && i === chapter));
        assembled.style.opacity = ramp(p, .93, .98);
        hint.style.opacity = 1 - ramp(p, .02, .08);
    };

    // ---------- Site-wide scroll effects ----------
    const navLinks = [...document.querySelectorAll('#nav a.nav-link')];
    const navTargets = navLinks.map(a => document.querySelector(a.getAttribute('href')));
    const parallax = [...document.querySelectorAll('[data-speed]')];
    const renderScroll = () => {
        // highlight the menu item for the section in view
        const line = innerHeight * .35;
        let current = -1;
        navTargets.forEach((t, i) => { const r = t.getBoundingClientRect(); if (r.top <= line && r.bottom > line) current = i; });
        navLinks.forEach((a, i) => { a.classList.toggle('active', i === current); i === current ? a.setAttribute('aria-current', 'true') : a.removeAttribute('aria-current'); });
        if (!reduce) parallax.forEach(el => {
            const r = el.getBoundingClientRect();
            const d = (r.top + r.height / 2 - innerHeight / 2) * +el.dataset.speed;
            el.style.transform = `translate3d(0, ${d.toFixed(1)}px, 0)`;
        });
        renderAnatomy();
    };
    let ticking = false;
    const onFrame = () => { if (!ticking) { ticking = true; requestAnimationFrame(() => { renderScroll(); ticking = false; }); } };
    addEventListener('scroll', onFrame, { passive: true });
    addEventListener('resize', onFrame);
    renderScroll();

    // Word-by-word reveal for section headlines
    document.querySelectorAll('h2.reveal').forEach(h => {
        let i = 0;
        const walk = node => [...node.childNodes].forEach(n => {
            if (n.nodeType === 3) {
                const frag = document.createDocumentFragment();
                n.textContent.split(/(\s+)/).forEach(part => {
                    if (!part) return;
                    if (/^\s+$/.test(part)) { frag.append(part); return; }
                    const s = document.createElement('span');
                    s.className = 'w'; s.textContent = part; s.style.transitionDelay = (i++ * 0.06) + 's';
                    frag.append(s);
                });
                n.replaceWith(frag);
            } else if (n.nodeType === 1 && !/^(svg|br)$/i.test(n.tagName)) walk(n);
        });
        walk(h);
    });

})();
</script>
</body>
</html>

<?php $collector->saveTranslation(); ?>
