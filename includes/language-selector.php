<?php
/* ------------------------------------------------------------------
 * Melara language selector (same mechanism as shopmelara-apex/language-selector.php)
 *  - Lists every language folder in lang/ ($OfferApi->languages()), so adding lang/<code>/
 *    makes the language appear here automatically.
 *  - Switching links to ?lang=<code> (other query params kept); integrated/setup.php's
 *    assignLanguage() stores the choice in the session for the next pages.
 *
 * Usage: include_once once per page, then <?= languageSelector('header') ?> / <?= languageSelector('footer') ?>
 * 'header' opens downwards and shows the code on small screens; 'footer' opens upwards.
 * ------------------------------------------------------------------ */

// Language code → flag file in assets/img/flags/ (copied from shopmelara-apex/assets/images/flags-svg).
// Languages without a flag here show their code instead.
function languageFlag($code) {
    static $flags = [
        'en' => 'us', 'de' => 'de', 'pl' => 'pl', 'ja' => 'jp', 'pt-br' => 'br', 'pt-pt' => 'pt',
        'zh-hant' => 'tw', 'zh-hans' => 'sg', 'fr' => 'fr', 'es' => 'es', 'mx' => 'mx', 'ko' => 'kr',
        'tl' => 'ph', 'cs' => 'cz', 'da' => 'dk', 'hu' => 'hu', 'it' => 'it', 'fi' => 'fi', 'vi' => 'vn',
        'el' => 'gr', 'sr-latn' => 'rs', 'id' => 'id', 'nl' => 'nl', 'ar' => 'qa', 'ro' => 'ro',
        'tr' => 'tr', 'lb' => 'lu', 'nb' => 'no', 'ur' => 'pk', 'sv' => 'se', 'th' => 'th',
    ];
    $file = isset($flags[$code]) ? 'assets/img/flags/' . $flags[$code] . '.svg' : null;
    return ($file && is_file(dirname(__DIR__) . '/' . $file)) ? $file : null;
}

// Flag (or code fallback) shown in the button and the list
function languageBadge($code, $active = false, $size = 'w-7 h-5') {
    $flag = languageFlag($code);
    if ($flag) {
        return '<img src="' . $flag . '" alt="" width="28" height="20" class="' . $size . ' shrink-0 rounded-[4px] object-cover ring-1 ring-black/10 shadow-sm">';
    }
    return '<span class="grid place-items-center ' . $size . ' shrink-0 rounded-[4px] text-[9px] font-bold uppercase tracking-wider '
         . ($active ? 'bg-turq text-ink' : 'bg-linen text-muted') . '">' . htmlspecialchars(strtoupper(explode('-', $code)[0])) . '</span>';
}

function languageSelector($variant = 'header') {
    global $OfferApi;
    static $assetsPrinted = false;

    $languages = $OfferApi->languages();
    asort($languages);
    $active = isset($languages[$OfferApi->targetLanguage]) ? $OfferApi->targetLanguage : (array_key_first($languages) ?? 'en');
    $activeName = $languages[$active] ?? 'English';
    $up = $variant === 'footer';

    $href = function ($code) {
        $params = $_GET;
        $params['lang'] = $code;
        return '?' . htmlspecialchars(http_build_query($params));
    };
    $globe = '<svg viewBox="0 0 24 24" class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3z"/></svg>';
    $check = '<svg viewBox="0 0 24 24" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>';

    ob_start();
    if (!$assetsPrinted):
        $assetsPrinted = true; ?>
        <style>
            .lang-sel > summary { list-style: none; }
            .lang-sel > summary::-webkit-details-marker { display: none; }
            .lang-sel .lang-chev { transition: transform .2s; }
            .lang-sel[open] .lang-chev { transform: rotate(180deg); }
            .lang-sel[open] .lang-panel { animation: langIn .18s ease-out; }
            .lang-sel[data-up][open] .lang-panel { animation-name: langInUp; }
            @keyframes langIn { from { opacity: 0; transform: translateY(-6px) scale(.98); } to { opacity: 1; transform: none; } }
            @keyframes langInUp { from { opacity: 0; transform: translateY(6px) scale(.98); } to { opacity: 1; transform: none; } }
            @media (prefers-reduced-motion: reduce) { .lang-sel .lang-panel { animation: none !important; } }
        </style>
        <script>
            // One open selector at a time; close on outside click / Escape; live filter when there are many languages
            document.addEventListener('click', e => document.querySelectorAll('.lang-sel[open]').forEach(d => { if (!d.contains(e.target)) d.open = false; }));
            document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.lang-sel[open]').forEach(d => { d.open = false; d.querySelector('summary').focus(); }); });
            document.addEventListener('toggle', e => {
                const d = e.target;
                if (!d.classList || !d.classList.contains('lang-sel') || !d.open) return;
                document.querySelectorAll('.lang-sel[open]').forEach(o => { if (o !== d) o.open = false; });
                // keep the panel inside the screen (12px gutter) wherever the selector sits in the bar
                const p = d.querySelector('.lang-panel');
                p.style.translate = '';
                const r = p.getBoundingClientRect(), vw = document.documentElement.clientWidth;
                const shift = Math.max(12 - r.left, 0) - Math.max(r.right - (vw - 12), 0);
                if (shift) p.style.translate = shift + 'px 0';
                const q = d.querySelector('.lang-search');
                (q || d.querySelector('[aria-current]') || d.querySelector('.lang-opt'))?.focus({ preventScroll: true });
            }, true);
            // build the link from the current URL at click time (the page may have changed ?pack= since load)
            document.addEventListener('click', e => {
                const a = e.target.closest && e.target.closest('.lang-opt');
                if (!a) return;
                const qs = new URLSearchParams(location.search);
                qs.set('lang', a.hreflang);
                a.href = '?' + qs;
            }, true);
            document.addEventListener('input', e => {
                if (!e.target.classList.contains('lang-search')) return;
                const d = e.target.closest('.lang-sel'), q = e.target.value.trim().toLowerCase();
                let shown = 0;
                d.querySelectorAll('.lang-opt').forEach(o => { const hit = o.dataset.search.includes(q); o.hidden = !hit; shown += hit; });
                d.querySelector('.lang-empty').hidden = shown > 0;
            });
        </script>
    <?php endif; ?>
    <details class="lang-sel relative" <?= $up ? 'data-up' : '' ?>>
        <summary class="inline-flex items-center <?= $up ? 'gap-2 px-3' : 'gap-1.5 px-2.5 sm:gap-2 sm:px-3' ?> cursor-pointer select-none rounded-full border border-sand bg-white/70 backdrop-blur py-2 text-sm font-medium text-ink hover:border-turq hover:text-turq-ink focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-turq/30 transition"
                 aria-label="<?= t('lang_label', 'Language') ?>: <?= htmlspecialchars($activeName) ?>">
            <?= languageFlag($active) ? languageBadge($active, true, 'w-6 h-4') : '<span class="text-turq-ink">' . $globe . '</span>' ?>
            <span class="<?= $up ? '' : 'hidden sm:inline' ?>"><?= htmlspecialchars($activeName) ?></span>
            <span class="<?= $up ? 'hidden' : 'sm:hidden' ?> text-xs font-semibold tracking-wider uppercase"><?= htmlspecialchars($active) ?></span>
            <svg viewBox="0 0 24 24" class="lang-chev w-3.5 h-3.5 text-muted <?= $up ? '' : 'hidden sm:block' ?>" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
        </summary>
        <div class="lang-panel absolute <?= $up ? 'bottom-full mb-2 left-1/2 -translate-x-1/2 sm:left-auto sm:right-0 sm:translate-x-0' : 'top-full mt-2 right-0' ?> z-50 w-64 rounded-2xl border border-sand bg-white p-2 shadow-[0_24px_60px_-20px_rgba(29,34,38,.35)] text-left">
            <p class="px-3 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-[.18em] text-muted"><?= t('lang_choose', 'Choose your language') ?></p>
            <?php if (count($languages) > 6): ?>
                <div class="relative px-1 py-1">
                    <svg viewBox="0 0 24 24" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-muted pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <input type="search" class="lang-search w-full rounded-xl border border-sand bg-cream pl-9 pr-3 py-2 text-sm focus:outline-none focus:border-turq focus:ring-4 focus:ring-turq/20" placeholder="<?= t('lang_search', 'Search languages') ?>" aria-label="<?= t('lang_search', 'Search languages') ?>">
                </div>
            <?php endif; ?>
            <ul class="max-h-72 overflow-y-auto overscroll-contain py-1">
                <?php foreach ($languages as $code => $name): $isActive = $code === $active; ?>
                    <li>
                        <a href="<?= $href($code) ?>" hreflang="<?= $code ?>" lang="<?= $code ?>" data-search="<?= htmlspecialchars(mb_strtolower($name . ' ' . $code)) ?>"
                           class="lang-opt flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-turq <?= $isActive ? 'bg-turq-soft text-turq-ink font-semibold' : 'text-ink hover:bg-cream' ?>"
                           <?= $isActive ? 'aria-current="true"' : '' ?>>
                            <?= languageBadge($code, $isActive) ?>
                            <span class="flex-1"><?= htmlspecialchars($name) ?></span>
                            <?php if ($isActive) echo $check; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li class="lang-empty px-3 py-3 text-sm text-muted" hidden><?= t('lang_no_match', 'No language found') ?></li>
            </ul>
        </div>
    </details>
    <?php
    return ob_get_clean();
}
