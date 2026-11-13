/* Melara checkout: shipping row, countdown and the CRM order form (integrated.js validates and imports the order). */
(() => {
    const cfg = window.MELARA_CHECKOUT;
    const fmt = n => cfg.currency + (Number.isInteger(n) ? n : n.toFixed(2));
    const $ = id => document.getElementById(id);
    const packs = [...document.querySelectorAll('input[name="pack"]')];
    const ship = $('insuranceAddOn'); // SecureShip, only on the page when the campaign has the add-on

    // The shipping row follows the selected pack (no order summary box, same as shopmelara-apex)
    const update = () => {
        const p = packs.find(r => r.checked);
        const shipping = +p.dataset.shipping || 0;
        $('sumShippingOpt').textContent = shipping > 0 ? fmt(shipping) : cfg.freeText;
        // keep lang, affiliate and tracking params; only the pack changes
        const qs = new URLSearchParams(location.search);
        qs.set('pack', p.value);
        history.replaceState(null, '', location.pathname + '?' + qs);
    };
    packs.forEach(r => r.addEventListener('change', update));
    update();

    // 10-minute reservation timer (restarts on reload)
    const cd = document.querySelector('[data-countdown]');
    const end = Date.now() + 10 * 60 * 1000;
    const tick = () => {
        const s = Math.max(0, Math.round((end - Date.now()) / 1000));
        cd.textContent = String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
    };
    tick(); setInterval(tick, 1000);

    // ---------- Policy modal (footer links: Shipping / Returns / Privacy / Contact) ----------
    const policy = document.getElementById('policyModal');
    if (policy) {
        const tabs = [...policy.querySelectorAll('[data-policy-tab]')];
        const panels = [...policy.querySelectorAll('[data-policy-panel]')];
        const body = policy.querySelector('.policy-body');
        const show = name => {
            tabs.forEach(t => {
                const on = t.dataset.policyTab === name;
                t.setAttribute('aria-selected', on);
                t.tabIndex = on ? 0 : -1;
                if (on) t.scrollIntoView({ block: 'nearest', inline: 'nearest' });
            });
            panels.forEach(p => { p.hidden = p.dataset.policyPanel !== name; });
            body.scrollTop = 0;
        };
        const open = name => { show(name); if (!policy.open) policy.showModal(); };

        document.querySelectorAll('[data-policy]').forEach(a => a.addEventListener('click', e => {
            e.preventDefault();
            open(a.dataset.policy);
        }));
        tabs.forEach((t, i) => {
            t.addEventListener('click', () => show(t.dataset.policyTab));
            // arrow keys move between tabs
            t.addEventListener('keydown', e => {
                if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
                const next = tabs[(i + (e.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];
                show(next.dataset.policyTab); next.focus();
            });
        });
        policy.querySelector('[data-policy-close]').addEventListener('click', () => policy.close());
        // close when clicking the dimmed backdrop: those clicks land on the <dialog> itself, never on its content
        policy.addEventListener('click', e => { if (e.target === policy) policy.close(); });
        // open straight to a policy from a link like checkout.php#privacy
        const fromHash = location.hash.slice(1);
        if (tabs.some(t => t.dataset.policyTab === fromHash)) open(fromHash);
    }

    if (typeof window.CRMIntegrated !== 'function') return;
    // Global CRMIntegrated instance, same as shopmelara-apex (assets/js/advertizerEf.js reads it)
    window.CRMIntegrated = window.CRMIntegrated({
        intlPhone: true,
        intlPhoneInitialCountry: $('userCountry') ? $('userCountry').value : 'US',
        dynamicShippingFields: true, // country change loads src/shipping/shipping-address-{cc}.php into #shipping-container, like shopmelara-apex
    });

    // Pack cards are ._prodSelect: integrated.js blocks the label's default click, so check the radio here
    CRMIntegrated.selectProdToOrderCallback = data => {
        sessionStorage.setItem('main_order_qty', data.target.attr('data-overridequantity'));
        data.item.variationDetailId = data.target.attr('data-variantDetailId');
        CRMIntegrated.addOrderProd(data.item);
        const radio = data.target[0].querySelector('input[name="pack"]');
        if (radio && !radio.checked) { radio.checked = true; update(); }
        if (ship) window.jQuery(ship).trigger('change');
    };
    CRMIntegrated.selectProdToOrder({ el: '._prodSelect', callback: CRMIntegrated.selectProdToOrderCallback });
    if (ship) CRMIntegrated.addAddOnToOrder({ el: '#insuranceAddOn' });
    //conversion event for add-ons
    CRMIntegrated.conversionEverflowPerEvent=function (amount, prodName, paySource) {
        if (prodName.toLowerCase().indexOf('secure') != -1) {
            CRMIntegrated.conversionEverflow({totalAmount:amount}, 559); //secureship event
        }
    }

    // Phone: at most as many digits as a phone number has in the chosen country (US: 10)
    const phone = $('fields_phone');
    const limitPhone = () => {
        const utils = window.intlTelInputUtils, iti = CRMIntegrated.phoneIntelli;
        const example = utils && iti ? utils.getExampleNumber(iti.getSelectedCountryData().iso2, true, utils.numberType.MOBILE) : '';
        const max = example.replace(/\D/g, '').length || 15;
        phone.maxLength = max;
        if (phone.value.length > max) phone.value = phone.value.slice(0, max);
    };
    phone.addEventListener('countrychange', limitPhone);

    CRMIntegrated.init(() => {
        // put the pack shown on the page (?pack=) in the cart
        setTimeout(() => window.jQuery('._prodSelect.active').trigger('click'), 200);
        // integrated.js fills the states list for the selected country
        CRMIntegrated.addStatesProvince('#fields_state');
        limitPhone();
    });

    // Enter key must not post the page; the order goes through #submitForm
    $('checkout').addEventListener('submit', e => e.preventDefault());
})();
