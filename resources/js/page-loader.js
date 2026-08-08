const LOADER_MIN_MS = 1000;
const LOADER_MAX_MS = 5000;
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
    }, 280);
}

function scheduleHide() {
    const elapsed = Date.now() - startedAt;
    const remaining = Math.max(0, LOADER_MIN_MS - elapsed);

    window.setTimeout(hidePageLoader, remaining);
}

if (document.readyState === 'complete') {
    scheduleHide();
} else {
    window.addEventListener('load', scheduleHide, { once: true });
}

// Fallback if load is delayed by a hanging asset.
window.setTimeout(hidePageLoader, LOADER_MAX_MS);
