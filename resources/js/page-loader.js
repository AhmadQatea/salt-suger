/**
 * Branded page loader.
 * Filter/category navigations set sessionStorage ss-fast-nav to skip the loader.
 */
const LOADER_MAX_MS = 3500;

function hidePageLoader() {
    const loader = document.getElementById('ss-page-loader');

    if (! loader || loader.dataset.hidden === 'true') {
        return;
    }

    loader.dataset.hidden = 'true';
    loader.classList.add('is-hidden');

    window.setTimeout(() => {
        loader.remove();
    }, 180);
}

document.addEventListener('click', (event) => {
    const link = event.target instanceof Element
        ? event.target.closest('a[data-fast-nav]')
        : null;

    if (! link) {
        return;
    }

    try {
        sessionStorage.setItem('ss-fast-nav', '1');
    } catch (e) {
        // Ignore private-mode storage failures.
    }
}, { capture: true, passive: true });

if (window.__ssFastNav) {
    const loader = document.getElementById('ss-page-loader');
    loader?.remove();
} else if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', hidePageLoader, { once: true });
    window.setTimeout(hidePageLoader, LOADER_MAX_MS);
} else {
    hidePageLoader();
}
