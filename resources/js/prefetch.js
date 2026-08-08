/**
 * Faster same-origin navigation via Speculation Rules + hover prefetch.
 * Skips checkout POST flows, external links, downloads, and logout.
 */
(() => {
    const SKIP_PREFIXES = ['/checkout', '/admin/logout', '/cart/items', '/admin/login'];

    const shouldSkip = (url) => {
        if (url.origin !== window.location.origin) {
            return true;
        }

        if (url.pathname === window.location.pathname && url.search === window.location.search) {
            return true;
        }

        return SKIP_PREFIXES.some((prefix) => url.pathname === prefix || url.pathname.startsWith(`${prefix}/`));
    };

    const prefetched = new Set();

    const prefetch = (href) => {
        try {
            const url = new URL(href, window.location.href);

            if (shouldSkip(url)) {
                return;
            }

            const key = `${url.pathname}${url.search}`;

            if (prefetched.has(key)) {
                return;
            }

            prefetched.add(key);

            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = key;
            link.as = 'document';
            document.head.appendChild(link);
        } catch (e) {
            // Ignore invalid URLs.
        }
    };

    document.addEventListener('pointerover', (event) => {
        const anchor = event.target instanceof Element
            ? event.target.closest('a[href]')
            : null;

        if (!anchor || anchor.dataset.noPrefetch === 'true') {
            return;
        }

        const href = anchor.getAttribute('href');

        if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('https://wa.me')) {
            return;
        }

        if (anchor.hasAttribute('download') || anchor.getAttribute('target') === '_blank') {
            return;
        }

        prefetch(href);
    }, { capture: true, passive: true });

    // Chromium Speculation Rules: prerender likely next pages on moderate intent.
    if (HTMLScriptElement.supports && HTMLScriptElement.supports('speculationrules')) {
        const script = document.createElement('script');
        script.type = 'speculationrules';
        script.textContent = JSON.stringify({
            prefetch: [{
                where: {
                    and: [
                        { href_matches: '/*' },
                        { not: { href_matches: '/checkout*' } },
                        { not: { href_matches: '/admin/logout' } },
                        { not: { selector_matches: '[data-no-prefetch]' } },
                    ],
                },
                eagerness: 'moderate',
            }],
        });
        document.head.appendChild(script);
    }
})();
