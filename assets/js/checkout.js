/* Melara checkout: order summary, countdown and the CRM order form (integrated.js validates and imports the order). */
(() => {
    const cfg = window.MELARA_CHECKOUT;
    const fmt = n => cfg.currency + (Number.isInteger(n) ? n : n.toFixed(2));
    const $ = id => document.getElementById(id);
    const packs = [...document.querySelectorAll('input[name="pack"]')];
    const ship = $('insuranceAddOn'); // SecureShip, only on the page when the campaign has the add-on
    const withShip = () => !!ship && ship.checked;

    // Order summary follows the selected pack and add-on
    const update = () => {
        const p = packs.find(r => r.checked);
        const price = +p.dataset.price, compare = +p.dataset.compare, shipping = +p.dataset.shipping || 0;
        const total = price + shipping + (withShip() ? cfg.secureShip : 0);
        $('sumQty').textContent = p.value;
        $('sumLabel').textContent = p.dataset.label;
        $('sumPack').textContent = fmt(price);
        $('sumCompare').textContent = fmt(compare);
        $('sumDiscount').textContent = '−' + fmt(compare - price);
        if (ship) {
            $('sumShip').textContent = ship.checked ? fmt(cfg.secureShip) : fmt(0);
            $('sumShipRow').style.opacity = ship.checked ? 1 : .45;
        }
        const shipText = shipping > 0 ? fmt(shipping) : cfg.freeText;
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

    // 10-minute reservation timer (restarts on reload)
    const cd = document.querySelector('[data-countdown]');
    const end = Date.now() + 10 * 60 * 1000;
    const tick = () => {
        const s = Math.max(0, Math.round((end - Date.now()) / 1000));
        cd.textContent = String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
    };
    tick(); setInterval(tick, 1000);

    if (typeof window.CRMIntegrated !== 'function') return;
    const CRM = window.CRMIntegrated({
        intlPhone: true,
        intlPhoneInitialCountry: $('userCountry') ? $('userCountry').value : 'US',
        dynamicShippingFields: false,
    });
    window.MELARA_CRM = CRM; // read by ef-events.js (Everflow funnel events)

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
    // Add-ons with their own Everflow event (offer 84): SecureShip -> "Secure Ship" (559)
    CRM.conversionEverflowPerEvent = (amount, prodName) => {
        if (String(prodName).toLowerCase().includes('secure')) CRM.conversionEverflow({ totalAmount: amount }, 559);
    };

    // Phone: at most as many digits as a phone number has in the chosen country (US: 10)
    const phone = $('fields_phone');
    const limitPhone = () => {
        const utils = window.intlTelInputUtils, iti = CRM.phoneIntelli;
        const example = utils && iti ? utils.getExampleNumber(iti.getSelectedCountryData().iso2, true, utils.numberType.MOBILE) : '';
        const max = example.replace(/\D/g, '').length || 15;
        phone.maxLength = max;
        if (phone.value.length > max) phone.value = phone.value.slice(0, max);
    };
    phone.addEventListener('countrychange', limitPhone);

    CRM.init(() => {
        // put the pack shown on the page (?pack=) in the cart
        setTimeout(() => window.jQuery('._prodSelect.active').trigger('click'), 200);
        // integrated.js fills the states list for the selected country
        CRM.addStatesProvince('#fields_state');
        limitPhone();
    });

    // Enter key must not post the page; the order goes through #submitForm
    $('checkout').addEventListener('submit', e => e.preventDefault());
})();
