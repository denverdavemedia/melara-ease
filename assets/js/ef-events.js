/* Everflow funnel events for offer 84 (Melara Seat Cushion / Melara Sitora).
 * Same approach as melara-apex assets/js/advertizerEf.js: EF.conversion() through the Everflow SDK
 * (https://www.b04jdmd.com/scripts/main.js) on the DDM tracking domain.
 * Only fires for tracked traffic (an Everflow offer id in the URL or the checkout session).
 * Bundled by gulp → public/js/ef-events.js; loaded on index.php and checkout.php.
 *
 * Event ids (Everflow offer 84 > Revenue & Payout):
 *   556 Product Page        index.php on load
 *   550 Checkout            checkout.php on load
 *   547 Add to Cart         checkout: customer picks a pack (or completes the card fields first)
 *   546 Add Payment Info    checkout: card number, expiry and CVV all filled in
 *   555 Paypal Checkout     checkout: PayPal / wallet button clicked
 *   548 Advertorial Page    not used: this funnel has no presell / advertorial page yet
 * The sale (base), upsell, downsell and Secure Ship events are fired by integrated.js (see integrated/config_us.php
 * and CRM.conversionEverflowPerEvent in assets/js/checkout.js).
 */
(function () {
    var TRACKING_DOMAIN = 'https://www.b04jdmd.com';
    var EVENTS = { productPage: 556, checkout: 550, addToCart: 547, addPaymentInfo: 546, paypalCheckout: 555 };

    var url = new URLSearchParams(location.search);
    // The checkout's CRM instance (assets/js/checkout.js exposes it); not present on index.php
    var crm = function () {
        var c = window.MELARA_CRM;
        return c && typeof c.getDataPropertyValue === 'function' ? c : null;
    };

    // Everflow offer id + transaction id: checkout session data first (integrated.js), then the URL
    var trackingData = function () {
        var c = crm();
        var oid = (c && c.getDataPropertyValue('oid')) || url.get('oid') || url.get('offer_id') || '';
        var tid = (c && c.getDataPropertyValue('transaction_id')) || url.get('transaction_id') || url.get('_ef_transaction_id') || '';
        if (!tid) { var el = document.getElementById('everflow_trans_id'); if (el) tid = el.value; }
        return { oid: oid, tid: tid };
    };

    // Waits for the Everflow SDK (loaded with defer), gives up after ~6s (e.g. blocked by an ad blocker)
    var whenEF = function (cb, tries) {
        tries = tries || 0;
        if (window.EF && typeof window.EF.conversion === 'function') return cb(window.EF);
        if (tries < 20) setTimeout(function () { whenEF(cb, tries + 1); }, 300);
    };

    var fired = {};
    var fire = function (name) {
        if (fired[name]) return;
        var t = trackingData();
        if (!t.oid) return; // untracked visit (no Everflow offer in the link): nothing to report
        fired[name] = true;
        whenEF(function (EF) {
            var params = { tracking_domain: TRACKING_DOMAIN, offer_id: t.oid, event_id: EVENTS[name] };
            if (t.tid) params.transaction_id = t.tid;
            try {
                var p = EF.conversion(params);
                if (p && p.catch) p.catch(function (e) { console.warn('Everflow event ' + name + ' failed', e); });
            } catch (e) { console.warn('Everflow event ' + name + ' failed', e); }
        });
    };

    var page = location.pathname.toLowerCase();
    var isCheckout = page.indexOf('/checkout.php') !== -1;
    var isIndex = !isCheckout && (/\/$/.test(page) || page.indexOf('/index.php') !== -1);

    if (isIndex) {
        fire('productPage');
        return;
    }

    if (!isCheckout) return;

    var onReady = function () {
        fire('checkout');

        // Add to Cart: the customer picks a pack themselves (real click, not the pre-selected pack)
        document.addEventListener('click', function (e) {
            if (e.isTrusted && e.target.closest && e.target.closest('._prodSelect')) fire('addToCart');
        });

        // Add Payment Info: all three card fields filled in (also counts as Add to Cart if they kept the default pack)
        var cardFields = ['cc_number', 'expiry_date', 'cc_cvv'];
        var checkCard = function () {
            var filled = cardFields.every(function (id) { var el = document.getElementById(id); return el && el.value.trim() !== ''; });
            if (filled) { fire('addToCart'); fire('addPaymentInfo'); }
        };
        cardFields.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('blur', checkCard);
        });

        // PayPal / wallet button: integrated.js calls paymentCustomCallback right before opening it
        var c = crm();
        if (c) {
            var prev = c.paymentCustomCallback;
            c.paymentCustomCallback = function () {
                fire('paypalCheckout');
                if (typeof prev === 'function') return prev.apply(this, arguments);
            };
        }
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', onReady);
    else onReady();
})();
