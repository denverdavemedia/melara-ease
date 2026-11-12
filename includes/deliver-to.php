<?php
/* "Deliver to <country>" badge, shown beside the language selector (same data as shopmelara-apex).
 * Country comes from the visitor's IP via OfferApi::getUserCountry() (sessionCountry + countryName).
 * echo deliverTo('header') -> pill in the nav (large screens); echo deliverTo('bar') -> slim row above the nav (phones/tablets).
 * Text is translated once and reused, because JsonCollector exits on a duplicate key. */

function deliverTo($variant = 'header') {
    global $OfferApi, $collector;
    static $t = null;

    if ($t === null) {
        $code = strtolower((string) ($OfferApi->sessionCountry ?: $OfferApi->default_country));
        $name = $OfferApi->countryName ?: 'United States';
        $flag = 'assets/img/flags/' . $code . '.svg';
        $t = [
            'label' => $collector->translate('deliver_to', 'Deliver to'),
            // shared country_name<CODE> keys, same as the checkout country list
            'name'  => $OfferApi->targetLanguage === 'en' ? $name : $collector->translate('country_name' . strtoupper($code), $name, false),
            'flag'  => is_file(dirname(__DIR__) . '/' . $flag) ? $flag . '?v=' . filemtime(dirname(__DIR__) . '/' . $flag) : null,
        ];
    }

    $pin = '<svg viewBox="0 0 24 24" class="w-4 h-4 shrink-0 text-turq-ink" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>';
    $flag = fn($size) => $t['flag']
        ? '<img src="' . $t['flag'] . '" alt="" width="24" height="16" class="' . $size . ' shrink-0 rounded-[4px] object-cover ring-1 ring-black/10">'
        : $pin;
    $label = htmlspecialchars($t['label']);
    $name  = htmlspecialchars($t['name']);

    if ($variant === 'bar') {
        return '<div class="deliver-bar xl:hidden border-b border-sand bg-white/80 py-1.5 px-4 flex items-center justify-center gap-2 text-xs text-muted">'
             . $flag('w-5 h-[14px]')
             . '<span>' . $label . ' <strong class="font-semibold text-ink">' . $name . '</strong></span>'
             . '</div>';
    }

    return '<div class="hidden xl:inline-flex items-center gap-2.5 rounded-full border border-sand bg-white/70 backdrop-blur-sm ps-2.5 pe-4 py-1.5" title="' . $label . ' ' . $name . '">'
         . $flag('w-6 h-4')
         . '<span class="leading-tight">'
         . '<span class="block text-[11px] text-muted">' . $label . '</span>'
         . '<span class="block text-sm font-semibold text-ink whitespace-nowrap">' . $name . '</span>'
         . '</span></div>';
}
