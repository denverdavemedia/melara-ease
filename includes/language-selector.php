<?php
/* Language selector: echo languageSelector('header' | 'footer' | 'locked'). Lists every folder in lang/; picking one links to ?lang=<code>.
 * Text keys get $k = '_2' on the second selector of a page. Styles: assets/css/language-selector.css */

// Language code -> flag in assets/img/flags/ (no flag: the code is shown)
function languageFlag($code) {
    static $flags = [
        'en' => 'us', 'de' => 'de', 'pl' => 'pl', 'ja' => 'jp', 'pt-br' => 'br', 'pt-pt' => 'pt',
        'zh-hant' => 'tw', 'zh-hans' => 'sg', 'fr' => 'fr', 'es' => 'es', 'mx' => 'mx', 'ko' => 'kr',
        'tl' => 'ph', 'cs' => 'cz', 'da' => 'dk', 'hu' => 'hu', 'it' => 'it', 'fi' => 'fi', 'vi' => 'vn',
        'el' => 'gr', 'sr-latn' => 'rs', 'id' => 'id', 'nl' => 'nl', 'ar' => 'qa', 'ro' => 'ro',
        'tr' => 'tr', 'lb' => 'lu', 'nb' => 'no', 'ur' => 'pk', 'sv' => 'se', 'th' => 'th',
        'ca' => 'es-ct', 'ru' => 'ru',
    ];
    $file = isset($flags[$code]) ? 'assets/img/flags/' . $flags[$code] . '.svg' : null;
    return ($file && is_file(dirname(__DIR__) . '/' . $file)) ? $file : null;
}

// Flag (or code fallback) shown in the button and the list
function languageBadge($code, $active = false, $size = 'w-7 h-5') {
    $flag = languageFlag($code);
    if ($flag) {
        return '<img src="' . $flag . '" alt="" width="28" height="20" class="' . $size . ' shrink-0 rounded-[4px] object-cover ring-1 ring-black/10 shadow-xs">';
    }
    return '<span class="grid place-items-center ' . $size . ' shrink-0 rounded-[4px] text-[9px] font-bold uppercase tracking-wider '
         . ($active ? 'bg-turq text-ink' : 'bg-linen text-muted') . '">' . htmlspecialchars(strtoupper(explode('-', $code)[0])) . '</span>';
}

function languageSelector($variant = 'header') {
    global $OfferApi, $collector;
    static $assetsPrinted = false;
    static $calls = 0;
    $k = ++$calls > 1 ? '_' . $calls : ''; // the 2nd selector on a page (footer) gets its own keys: lang_label_2...

    // Names and the active language come from OfferApi::definedLanguages(), same as shopmelara-apex (language-selector.php there),
    // with the active language's native name on the button at every screen size.
    // Only these languages are listed (the others stay reachable via ?lang=)
    $shown = ['en', 'zh-hans', 'zh-hant', 'ja'];
    $defined = $OfferApi->definedLanguages();
    $languages = array_intersect_key($defined['languages'], array_flip($shown));
    asort($languages);
    $active = isset($languages[$defined['active']]) ? $defined['active'] : $OfferApi->targetLanguage;
    $activeName = $defined['active_lang'] ?? ($languages[$active] ?? 'English');
    $up = $variant === 'footer';

    if ($variant === 'locked') {
        $lock = '<svg viewBox="0 0 24 24" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2.5"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>';
        return '<span class="inline-flex items-center gap-1.5 sm:gap-2 rounded-full border border-sand bg-linen/60 px-2.5 sm:px-3 py-2 text-xs sm:text-sm font-medium text-muted cursor-not-allowed select-none" aria-disabled="true"'
             . ' title="' . $collector->translate('lang_locked' . $k, 'Language is set before checkout') . '" aria-label="' . $collector->translate('lang_label' . $k, 'Language') . ': ' . htmlspecialchars($activeName) . '">'
             . '<span class="whitespace-nowrap">' . htmlspecialchars($activeName) . '</span>' // full name on phones too (not the "EN" code)
             . $lock . '</span>';
    }

    $href = function ($code) {
        $params = $_GET;
        $params['lang'] = $code;
        return '?' . htmlspecialchars(http_build_query($params));
    };
    $globe = '<svg viewBox="0 0 24 24" class="w-4 h-4 sm:w-[18px] sm:h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3z"/></svg>';
    $check = '<svg viewBox="0 0 24 24" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>';

    ob_start();
    if (!$assetsPrinted):
        $assetsPrinted = true; ?>
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
    <details class="lang-sel relative min-w-0" <?= $up ? 'data-up' : '' ?>>
        <summary class="max-w-full inline-flex items-center <?= $up ? 'gap-2 px-3' : 'gap-1 px-2 sm:gap-2 sm:px-3' ?> cursor-pointer select-none rounded-full border border-sand bg-white/70 backdrop-blur-sm py-2 text-sm font-medium text-ink hover:border-turq hover:text-turq-ink focus-visible:outline-hidden focus-visible:ring-4 focus-visible:ring-turq/30 transition"
                 aria-label="<?= $collector->translate('lang_label' . $k, 'Language') ?>: <?= htmlspecialchars($activeName) ?>">
            <span class="shrink-0 text-turq-ink"><?= $globe ?></span>
            <?php // full name on phones too, like apex; on a narrow phone it ends in … instead of pushing the header CTA off screen ?>
            <span class="<?= $up ? '' : 'min-w-0 truncate text-xs sm:text-sm' ?> whitespace-nowrap"><?= htmlspecialchars($activeName) ?></span>
            <svg viewBox="0 0 24 24" class="lang-chev shrink-0 w-3 h-3 sm:w-3.5 sm:h-3.5 text-muted" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
        </summary>
        <div class="lang-panel absolute <?= $up ? 'bottom-full mb-2 left-1/2 -translate-x-1/2 sm:start-auto sm:end-0 sm:translate-x-0' : 'top-full mt-2 end-0' ?> z-50 w-64 rounded-2xl border border-sand bg-white p-2 shadow-[0_24px_60px_-20px_rgba(29,34,38,.35)] text-start">
            <p class="px-3 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-[.18em] text-muted"><?= $collector->translate('lang_choose' . $k, 'Choose your language') ?></p>
            <?php if (count($languages) > 6): ?>
                <div class="relative px-1 py-1">
                    <svg viewBox="0 0 24 24" class="absolute start-4 top-1/2 -translate-y-1/2 w-4 h-4 text-muted pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <input type="search" class="lang-search w-full rounded-xl border border-sand bg-cream ps-9 pe-3 py-2 text-sm focus:outline-hidden focus:border-turq focus:ring-4 focus:ring-turq/20" placeholder="<?= $collector->translate('lang_search' . $k, 'Search languages') ?>" aria-label="<?= $collector->translate('lang_search_label' . $k, 'Search languages') ?>">
                </div>
            <?php endif; ?>
            <ul class="max-h-72 overflow-y-auto overscroll-contain py-1">
                <?php foreach ($languages as $code => $name): $isActive = $code === $active; ?>
                    <li>
                        <a href="<?= $href($code) ?>" hreflang="<?= $code ?>" lang="<?= $code ?>" data-search="<?= htmlspecialchars(mb_strtolower($name . ' ' . $code)) ?>"
                           class="lang-opt flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-turq <?= $isActive ? 'bg-turq-soft text-turq-ink font-semibold' : 'text-ink hover:bg-cream' ?>"
                           <?= $isActive ? 'aria-current="true"' : '' ?>>
                            <span class="flex-1"><?= htmlspecialchars($name) ?></span>
                            <?php if ($isActive) echo $check; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li class="lang-empty px-3 py-3 text-sm text-muted" hidden><?= $collector->translate('lang_no_match' . $k, 'No language found') ?></li>
            </ul>
        </div>
    </details>
    <?php
    return ob_get_clean();
}
