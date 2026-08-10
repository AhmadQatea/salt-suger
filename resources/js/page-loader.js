/**
 * Branded page loader.
 * Normal loads stay visible for at least 0.2s (then wait for load on slow networks).
 * Category filter navigations set ss-fast-nav to skip the loader entirely.
 */
const LOADER_MIN_MS = 200;
const LOADER_MAX_MS = 8000;
const startedAt = Date.now();

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

function scheduleHide() {
    const elapsed = Date.now() - startedAt;
    const remaining = Math.max(0, LOADER_MIN_MS - elapsed);

    window.setTimeout(hidePageLoader, remaining);
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
    document.getElementById('ss-page-loader')?.remove();
} else if (document.readyState === 'complete') {
    scheduleHide();
    window.setTimeout(hidePageLoader, LOADER_MAX_MS);
} else {
    window.addEventListener('load', scheduleHide, { once: true });
    window.setTimeout(hidePageLoader, LOADER_MAX_MS);
}
