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

if (document.readyState === 'complete') {
    hidePageLoader();
} else {
    window.addEventListener('load', hidePageLoader, { once: true });
}

// Fallback if load is delayed by a hanging asset.
window.setTimeout(hidePageLoader, 4000);
