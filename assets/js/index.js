/* Melara landing page: nav, scroll reveal, parallax, word reveal, countdown. Bundled by gulp → public/js/index.js */
(() => {
    const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Nav background + mobile buy bar
    const nav = document.getElementById('nav'), buyBar = document.getElementById('buyBar');
    const footer = document.querySelector('footer');
    // The page's green "buy" buttons; the floating bar only appears when none of them is on screen
    const greenCtas = [...document.querySelectorAll('a.bg-turq')].filter(a => !buyBar.contains(a));
    const ctaInView = () => greenCtas.some(a => {
        const r = a.getBoundingClientRect();
        return r.height > 0 && r.bottom > nav.offsetHeight && r.top < innerHeight;
    });
    const onScroll = () => {
        const scrolled = scrollY > 30;
        nav.classList.toggle('bg-cream/85', scrolled);
        nav.classList.toggle('backdrop-blur-sm', scrolled);
        nav.classList.toggle('shadow-xs', scrolled);
        // Hide the mobile buy bar while a green CTA is visible, and once the footer is on screen
        const atFooter = footer && footer.getBoundingClientRect().top < innerHeight;
        // Tailwind v4's translate-y-* uses the CSS `translate` property, so override that (not `transform`)
        buyBar.style.translate = !ctaInView() && !atFooter ? '0 0' : '';
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

    // ---------- Pain section: before / after photos cross-fade on a loop ----------
    document.querySelectorAll('[data-ba-loop]').forEach(box => {
        const slides = [...box.querySelectorAll('.ba-slide')], dots = [...box.querySelectorAll('.ba-dot')];
        let cur = 0;
        setInterval(() => {
            cur = (cur + 1) % slides.length;
            slides.forEach((s, i) => s.classList.toggle('is-on', i === cur));
            dots.forEach((d, i) => d.classList.toggle('is-on', i === cur));
        }, 3500);
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
