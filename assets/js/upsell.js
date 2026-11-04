/* Melara upsell pages: reveal, countdown, tilt, quantity, floating bar, road story, downsell popup, CRM. */
$(function () {
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* 1. Scroll reveal: fade sections in (.reveal → .in) as they scroll into view */
    var revealObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('in');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
    $('.reveal').each(function () { revealObserver.observe(this); });

    /* 2. Countdown: 10 minutes, restarts on reload */
    var endTime = Date.now() + 10 * 60 * 1000;
    function updateCountdown() {
        var secondsLeft = Math.max(0, Math.round((endTime - Date.now()) / 1000));
        var minutes = String(Math.floor(secondsLeft / 60)).padStart(2, '0');
        var seconds = String(secondsLeft % 60).padStart(2, '0');
        $('[data-countdown]').text(minutes + ':' + seconds);
    }
    updateCountdown();
    setInterval(updateCountdown, 1000);

    /* 3. Image tilt: the hero image leans towards the mouse (desktop only) */
    if (!reduceMotion && window.matchMedia('(pointer: fine)').matches) {
        $('#stage').on('mousemove', function (e) {
            var box = this.getBoundingClientRect();
            var x = (e.clientX - box.left) / box.width - 0.5;
            var y = (e.clientY - box.top) / box.height - 0.5;
            $('#stageTilt').css('transform', 'rotateY(' + (x * 10) + 'deg) rotateX(' + (-y * 10) + 'deg)');
        }).on('mouseleave', function () {
            $('#stageTilt').css('transform', '');
        });
    }

    /* 4. Quantity +/-: updates every price on the page, the product copies and the CRM quantity */
    var $picker = $('#qtyPicker');
    if ($picker.length) {
        var unitPrice = parseFloat($picker.data('unit'));
        var discount = parseFloat($picker.data('off'));      // e.g. 0.7 = 70% off
        var maxQty = parseInt($picker.data('max'), 10) || 1;
        var currency = $picker.data('currency');
        var qty = 1;

        function formatPrice(amount) {
            amount = Math.round(amount * 100) / 100;
            if (typeof formatNumber === 'function') return formatNumber(amount, currency);   // intlNumberFormat.js
            return currency + (amount % 1 ? amount.toFixed(2) : amount);
        }

        // Replays a one-off CSS animation (.bump / .tick) on an element
        function playAnimation($el, className) {
            $el.removeClass(className);
            void $el[0].offsetWidth;
            $el.addClass(className);
        }

        // Shows qty copies of the product (max 4, fanned out); past 4 the ×N badge shows the number
        function showProductCopies(qty) {
            var copies = Math.min(qty, 4);
            var boxWidth = $picker.find('.stack-img').parent().width();
            var gap = Math.round(boxWidth * (copies > 2 ? .09 : .13));
            // past 4 the ×N badge shows in the top-right corner: move the stack down-left to keep it clear
            var shiftX = qty > 4 ? -Math.round(boxWidth * .08) : 0;
            var shiftY = qty > 4 ? Math.round(boxWidth * .06) : 0;
            $picker.find('.stack-img').each(function () {
                var slot = $(this).data('slot');           // 0 = front copy
                var visible = slot < copies;
                var x = (slot - (copies - 1) / 2) * -gap + shiftX;
                $(this).css({
                    zIndex: copies - slot,
                    opacity: visible ? 1 : 0,
                    transform: visible
                        ? 'translate(calc(-50% + ' + x + 'px), calc(-50% - ' + (slot * 4 - shiftY) + 'px)) scale(' + (1 - slot * 0.07) + ')'
                        : 'translate(-50%, -50%) scale(.6)'
                });
            });
            $picker.find('.qty-badge').text('×' + qty).toggleClass('hidden', qty <= 4);
        }

        function setQty(newQty, animate) {
            qty = Math.min(maxQty, Math.max(1, newQty));
            var total = unitPrice * qty;
            var retail = total / (1 - discount);
            var label = qty + ' × ' + (qty > 1 ? $picker.data('many') : $picker.data('one'));

            $picker.find('.qty-value').text(qty);
            $picker.find('.qty-minus').prop('disabled', qty <= 1);
            $picker.find('.qty-plus').prop('disabled', qty >= maxQty);
            $picker.find('.qty-meter span').css('width', (qty / maxQty * 100) + '%');
            showProductCopies(qty);

            // Every price / label on the page (hero, section CTAs, road reveal, final CTA, floating bar)
            $('[data-qty-label], [data-cta-label]').text(label);
            $('[data-cta-total]').text(formatPrice(total));
            $('[data-cta-retail]').text(formatPrice(retail));
            $('[data-cta-save]').text(formatPrice(retail - total));

            // The CRM reads the quantity from this hidden input
            $('input._product_order').attr('quantity', qty);

            if (animate) {
                playAnimation($picker.find('.qty-value'), 'bump');
                $('[data-cta-total]').each(function () { playAnimation($(this), 'tick'); });
            }
        }

        $picker.find('.qty-plus').on('click', function () { setQty(qty + 1, true); });
        $picker.find('.qty-minus').on('click', function () { setQty(qty - 1, true); });
        setQty(1, false);
    }

    /* 5. Floating Yes / No bar (phones): shown while the hero Yes button is off screen,
          hidden during the road story (keeps the product a surprise) and at the final CTA */
    var $bar = $('#mobileCta');
    if ($bar.length) {
        var heroVisible = true, inRoad = false, atFinal = false;
        function updateBar() {
            var show = !heroVisible && !inRoad && !atFinal;
            $bar.toggleClass('is-shown', show);
            $bar.prop('inert', !show);
        }
        function watch(selector, options, onChange) {
            var el = document.querySelector(selector);
            if (!el) return;
            new IntersectionObserver(function (entries) { onChange(entries[0].isIntersecting); updateBar(); }, options).observe(el);
        }
        watch('#heroCta', { threshold: 0.6 }, function (visible) { heroVisible = visible; });
        watch('#road', { rootMargin: '-15% 0px -15% 0px' }, function (visible) { inRoad = visible; });
        watch('#finalCta', { threshold: 0.25 }, function (visible) { atFinal = visible; });
    }

    /* 6. Road story: draws a road through the stop icons. While it's on screen the lane lines move,
          and a glow travels down the road with the scroll, lighting each stop (.is-lit) and finally the reveal. */
    var $road = $('#road');
    if ($road.length) {
        var track = $road.find('.road-track')[0];
        var roadPath = $road.find('.road-base')[0];
        var $stops = $road.find('.road-stop, .road-reveal');
        var video = $road.find('.reveal-video')[0];
        var roadLength = 0, stopY = [], onScreen = false;

        $road.addClass('road-ready');   // CSS hides the cards until they are lit

        // Build the road: from the top of the track through the centre of every stop icon
        function drawRoad() {
            var trackBox = track.getBoundingClientRect();
            var points = [];
            $road.find('.road-node').each(function () {
                var box = this.getBoundingClientRect();
                points.push({ x: box.left + box.width / 2 - trackBox.left, y: box.top + box.height / 2 - trackBox.top });
            });
            stopY = points.map(function (p) { return p.y; });

            var d = 'M' + points[0].x + ',0';
            var prev = { x: points[0].x, y: 0 };
            points.forEach(function (p) {
                var mid = (prev.y + p.y) / 2;          // S-curve between two stops
                d += ' C' + prev.x + ',' + mid + ' ' + p.x + ',' + mid + ' ' + p.x + ',' + p.y;
                prev = p;
            });
            $road.find('.road-svg').attr('viewBox', '0 0 ' + trackBox.width + ' ' + trackBox.height);
            $road.find('.road-svg path').attr('d', d);
            roadLength = roadPath.getTotalLength();
            updateRoad();
        }

        // Move the glow to the line 60% down the screen and light the stops above it
        function updateRoad() {
            if (!roadLength) return;
            var trackBox = track.getBoundingClientRect();
            var lineY = reduceMotion ? Infinity : window.innerHeight * 0.6 - trackBox.top;
            var roadEndY = stopY[stopY.length - 1];   // the road ends at the reveal icon
            var progress = Math.min(1, Math.max(0, lineY / roadEndY));

            $road.find('.road-rim, .road-rim-glow').css('stroke-dashoffset', 1 - progress);
            var point = roadPath.getPointAtLength(roadLength * progress);
            $road.find('.road-head').attr({ transform: 'translate(' + point.x + ',' + point.y + ')', opacity: progress > 0 && progress < 1 ? 1 : 0 });

            $stops.each(function (i) { $(this).toggleClass('is-lit', lineY >= stopY[i] - 2); });

            // The reveal video plays only while the reveal is lit and on screen
            if (video && !reduceMotion) {
                var revealLit = $stops.last().hasClass('is-lit');
                if (revealLit && onScreen && video.paused) video.play().catch(function () {});
                if ((!revealLit || !onScreen) && !video.paused) video.pause();
            }
        }

        // Every frame while the road is on screen: move the lane lines and the glow
        function animateRoad(time) {
            if (!onScreen) return;
            $road.find('.road-dash').css('stroke-dashoffset', -(time * 0.03 + window.scrollY * 0.45));
            updateRoad();
            requestAnimationFrame(animateRoad);
        }

        drawRoad();
        $(window).on('load resize', drawRoad);   // images and fonts can move the stops

        if (reduceMotion) {
            $stops.addClass('is-lit');
            if (video) video.controls = true;     // no autoplay; the visitor can press play
        } else {
            new IntersectionObserver(function (entries) {
                onScreen = entries[0].isIntersecting;
                if (onScreen) requestAnimationFrame(animateRoad);
                updateRoad();
            }, { rootMargin: '200px 0px' }).observe($road[0]);
        }
    }

    /* 7. "No thanks" downsell popup (same as shopmelara-apex cloud-air-cover-new.js) */
    var $popup = $('#no-thanks-popup');
    $(document).on('click', '.js-open-no-thanks-popup', function (e) {
        e.preventDefault();
        $popup.addClass('is-open');
        $('body').addClass('popup-open');
    });
    $(document).on('click', '.js-popup-yes', function (e) {
        e.preventDefault();
        // Switch the order to the downsell product, qty 1
        $('input._product_order')
            .val($popup.data('popup-product-id'))
            .attr('unitprice', $popup.data('popup-unitprice'))
            .attr('shipping', $popup.data('popup-shipping'))
            .attr('quantity', 1);
        // Fire the downsell Everflow event instead of the upsell one, when set in the config
        var downsellEventId = $('#downsellEverFlowEventId').val();
        if (downsellEventId) $('#everFlowEventId').val(downsellEventId);
        $('.yes-upsell-link:first').trigger('click');
    });

    /* 8. CRM: integrated.js handles the Yes / No buttons */
    if (typeof CRMIntegrated === 'function') {
        CRMIntegrated().init(function () {});
        // Wallet payers (Google / Apple Pay): copy the wallet look onto the popup button, as apex does
        var $wallet = $('.yes-upsell-link.replacedBtn').first();
        if ($wallet.length) $('.popup-yes-btn').html($wallet.html()).attr('style', $wallet.attr('style')).addClass('replacedBtn');
    }
});
