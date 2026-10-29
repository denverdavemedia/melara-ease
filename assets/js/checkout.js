/* Melara checkout: order summary, countdown, state field, card formatting, order submit. Bundled by gulp → public/js/checkout.js */
/* Page values come from window.MELARA_CHECKOUT (set inline by checkout.php).
 * Live mode (campaign set in integrated/config_us.php): the apex CRMIntegrated library (src/common/js/integrated.js)
 * keeps the cart, validates the form and imports the order; this file only keeps the Melara summary in sync.
 * Preview mode: nothing is sent anywhere, a valid form goes to the sample thank-you page. */
(() => {
    const cfg = window.MELARA_CHECKOUT || { live: false, currency: '$', secureShip: 0 };
    const fmt = n => cfg.currency + (Number.isInteger(n) ? n : n.toFixed(2));
    const secure = cfg.secureShip;
    const $ = id => document.getElementById(id);
    const packs = [...document.querySelectorAll('input[name="pack"]')];
    const ship = $('insuranceAddOn'); // SecureShip, only rendered when there is an add-on product
    const withShip = () => !!ship && ship.checked;

    // Order summary follows the selected pack and add-on
    const update = () => {
        const p = packs.find(r => r.checked);
        const price = +p.dataset.price, compare = +p.dataset.compare, shipping = +p.dataset.shipping || 0;
        const total = price + shipping + (withShip() ? secure : 0);
        $('sumQty').textContent = p.value;
        $('sumLabel').textContent = p.dataset.label;
        $('sumPack').textContent = fmt(price);
        $('sumCompare').textContent = fmt(compare);
        $('sumDiscount').textContent = '−' + fmt(compare - price);
        if (ship) {
            $('sumShip').textContent = ship.checked ? fmt(secure) : fmt(0);
            $('sumShipRow').style.opacity = ship.checked ? 1 : .45;
        }
        const shipText = shipping > 0 ? fmt(shipping) : 'Free';
        $('sumShipping').textContent = shipText;
        $('sumShipping').classList.toggle('text-turq', !(shipping > 0));
        $('sumShippingOpt').textContent = shipText;
        $('sumTotal').textContent = fmt(Math.round(total * 100) / 100);
        // keep lang, affiliate and tracking params; only the pack changes
        const qs = new URLSearchParams(location.search);
        qs.set('pack', p.value);
        history.replaceState(null, '', location.pathname + '?' + qs);
    };
    packs.forEach(r => r.addEventListener('change', update));
    if (ship) ship.addEventListener('change', update);
    update();

    // 10-minute reservation timer (restarts on reload, like the original)
    const cd = document.querySelector('[data-countdown]');
    const end = Date.now() + 10 * 60 * 1000;
    const tick = () => {
        const s = Math.max(0, Math.round((end - Date.now()) / 1000));
        cd.textContent = String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
    };
    tick(); setInterval(tick, 1000);

    const form = $('checkout');
    const note = $('formNote');
    const show = (msg, ok) => { note.textContent = msg; note.className = 'mt-3 rounded-xl px-4 py-3 text-sm text-center ' + (ok ? 'bg-turq-soft text-turq-ink' : 'bg-rose-50 text-rose-600'); };

    if (cfg.live && typeof window.CRMIntegrated === 'function') {
        const CRM = window.CRMIntegrated({
            intlPhone: true,
            intlPhoneInitialCountry: $('userCountry') ? $('userCountry').value : 'US',
            dynamicShippingFields: false,
        });

        // Pack cards are ._prodSelect: integrated.js blocks the label's default click, so check the radio here
        CRM.selectProdToOrderCallback = data => {
            sessionStorage.setItem('main_order_qty', data.target.attr('data-overridequantity'));
            data.item.variationDetailId = data.target.attr('data-variantDetailId');
            CRM.addOrderProd(data.item);
            const radio = data.target[0].querySelector('input[name="pack"]');
            if (radio && !radio.checked) { radio.checked = true; update(); }
            if (ship) window.jQuery(ship).trigger('change');
        };
        CRM.selectProdToOrder({ el: '._prodSelect', callback: CRM.selectProdToOrderCallback });
        if (ship) CRM.addAddOnToOrder({ el: '#insuranceAddOn' });
        CRM.conversionEverflowPerEvent = () => {}; // no add-on Everflow events yet

        CRM.init(() => {
            // put the pack shown on the page (?pack=) in the cart
            setTimeout(() => window.jQuery('._prodSelect.active').trigger('click'), 200);
            // integrated.js fills the states list (codes) for the selected country
            CRM.addStatesProvince('#fields_state');
        });

        // Enter key must not post the page; the order goes through #submitForm
        form.addEventListener('submit', e => e.preventDefault());
        return;
    }

    // ---- Preview mode below ----

    // State: US list, free text elsewhere
    const country = $('fields_country_select'), stSel = $('fields_state'), stTxt = $('fields_state_text');
    country.addEventListener('change', () => {
        const us = country.value === 'US';
        stSel.classList.toggle('hidden', !us); stTxt.classList.toggle('hidden', us);
        stSel.required = us; stTxt.required = !us;
        stSel.name = us ? 'state' : ''; stTxt.name = us ? '' : 'state';
    });

    // Card field formatting
    $('cc_number').addEventListener('input', e => { e.target.value = e.target.value.replace(/\D/g, '').slice(0, 16).replace(/(.{4})(?=.)/g, '$1 '); });
    $('expiry_date').addEventListener('input', e => { const d = e.target.value.replace(/\D/g, '').slice(0, 4); e.target.value = d.length > 2 ? d.slice(0, 2) + ' / ' + d.slice(2) : d; });
    $('cc_cvv').addEventListener('input', e => { e.target.value = e.target.value.replace(/\D/g, '').slice(0, 4); });

    // Static page: validate, then explain that no payment is processed. Nothing is sent anywhere.
    document.querySelectorAll('.express').forEach(b => b.addEventListener('click', () => show('Express checkout is a design placeholder. Connect a payment provider to enable it.', true)));
    form.addEventListener('submit', e => {
        e.preventDefault();
        const fields = [...e.target.querySelectorAll('[required]')].filter(f => !f.closest('.hidden') && !f.classList.contains('hidden'));
        let first = null;
        fields.forEach(f => { const bad = !f.value.trim() || (f.type === 'email' && !/^\S+@\S+\.\S+$/.test(f.value)); f.classList.toggle('invalid', bad); if (bad && !first) first = f; });
        if (first) { first.focus(); show('Please complete the highlighted fields.', false); return; }
        // Preview: go to the sample thank-you page (only the pack + SecureShip choice travel in the URL, never personal data)
        show('All set! Taking you to your confirmation…', true);
        const pack = packs.find(r => r.checked).value;
        setTimeout(() => { location.href = 'thankyou.php?pack=' + pack + '&ship=' + (withShip() ? 1 : 0); }, 700);
    });
    document.querySelectorAll('.field').forEach(f => f.addEventListener('input', () => f.classList.remove('invalid')));
})();
