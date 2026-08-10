/**
 * Mobile admin "More" bottom sheet open/close.
 */
document.addEventListener('DOMContentLoaded', () => {
    const sheet = document.getElementById('admin-more-sheet');
    const openBtn = document.querySelector('[data-admin-more-open]');

    if (!sheet || !openBtn) {
        return;
    }

    const panel = sheet.querySelector('.admin-more-sheet__panel');
    const closeTriggers = sheet.querySelectorAll('[data-admin-more-close]');

    const setExpanded = (open) => {
        openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    const openSheet = () => {
        sheet.hidden = false;
        sheet.classList.remove('hidden');
        sheet.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        setExpanded(true);
        panel?.classList.remove('is-open');
        window.requestAnimationFrame(() => {
            window.requestAnimationFrame(() => {
                panel?.classList.add('is-open');
            });
        });
    };

    const closeSheet = () => {
        sheet.setAttribute('aria-hidden', 'true');
        setExpanded(false);
        panel?.classList.remove('is-open');
        document.body.classList.remove('overflow-hidden');
        sheet.classList.add('hidden');
        sheet.hidden = true;
    };

    openBtn.addEventListener('click', (event) => {
        event.preventDefault();
        if (sheet.hidden || sheet.classList.contains('hidden')) {
            openSheet();
        } else {
            closeSheet();
        }
    });

    closeTriggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            closeSheet();
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !sheet.hidden) {
            closeSheet();
        }
    });
});
