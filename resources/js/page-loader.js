/**
 * Branded page loader — hides immediately when the page is ready on fast networks.
 * Internal navigations set ss-fast-nav to skip the loader entirely.
 */
const LOADER_MAX_MS = 8000;

function hidePageLoader() {
    const loader = document.getElementById('ss-page-loader');

    if (! loader || loader.dataset.hidden === 'true') {
        return;
    }

    loader.dataset.hidden = 'true';
    loader.classList.add('is-hidden');

    window.setTimeout(() => {
        loader.remove();
    }, 160);
}

function scheduleHide() {
    hidePageLoader();
}

document.addEventListener('click', (event) => {
    const link = event.target instanceof Element
        ? event.target.closest('a[href]')
        : null;

    if (! link || link.target === '_blank' || link.hasAttribute('download')) {
        return;
    }

    const href = link.getAttribute('href');

    if (! href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) {
        return;
    }

    try {
        const url = new URL(href, window.location.href);

        if (url.origin !== window.location.origin) {
            return;
        }

        if (url.pathname === window.location.pathname && url.search === window.location.search) {
            return;
        }

        sessionStorage.setItem('ss-fast-nav', '1');
    } catch (e) {
        // Ignore invalid URLs.
    }
}, { capture: true, passive: true });

if (window.__ssFastNav) {
    document.getElementById('ss-page-loader')?.remove();
} else if (document.readyState === 'complete') {
    scheduleHide();
    window.setTimeout(hidePageLoader, LOADER_MAX_MS);
} else {
    document.addEventListener('DOMContentLoaded', scheduleHide, { once: true });
    window.addEventListener('load', scheduleHide, { once: true });
    window.setTimeout(hidePageLoader, LOADER_MAX_MS);
}
