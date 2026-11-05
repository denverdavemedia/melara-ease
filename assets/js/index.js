/* Melara landing page: nav, scroll reveal, 3D anatomy, parallax, word reveal, countdown. Bundled by gulp → public/js/index.js */
(() => {
    const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Nav background + mobile buy bar
    const nav = document.getElementById('nav'), buyBar = document.getElementById('buyBar');
    const footer = document.querySelector('footer');
    const onScroll = () => {
        const scrolled = scrollY > 30;
        nav.classList.toggle('bg-cream/85', scrolled);
        nav.classList.toggle('backdrop-blur-sm', scrolled);
        nav.classList.toggle('shadow-xs', scrolled);
        // Hide the mobile buy bar once the footer is on screen, so it never covers the footer
        const atFooter = footer && footer.getBoundingClientRect().top < innerHeight;
        // Tailwind v4's translate-y-* uses the CSS `translate` property, so override that (not `transform`)
        buyBar.style.translate = scrollY > 600 && !atFooter ? '0 0' : '';
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

    // ---------- Five zones: highlight the matching marker for the card in view (or hovered) ----------
    const zoneCards = [...document.querySelectorAll('.zone-card')];
    if (zoneCards.length) {
        const setZone = n => {
            zoneCards.forEach(c => c.classList.toggle('is-active', c.dataset.zone === n));
            document.querySelectorAll('[data-zone-dot]').forEach(d => d.classList.toggle('is-active', d.dataset.zoneDot === n));
        };
        const io = new IntersectionObserver(entries => {
            entries.forEach(e => { if (e.isIntersecting) setZone(e.target.dataset.zone); });
        }, { rootMargin: '-45% 0px -45% 0px' });
        zoneCards.forEach(c => {
            io.observe(c);
            c.addEventListener('mouseenter', () => setZone(c.dataset.zone));
        });
    }

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
        // open straight to a policy from a link like index.php#privacy
        const fromHash = location.hash.slice(1);
        if (tabs.some(t => t.dataset.policyTab === fromHash)) open(fromHash);
    }

})();
