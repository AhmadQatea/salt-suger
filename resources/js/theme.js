/**
 * Theme preference: light | dark
 * Persisted in localStorage. Applied on <html class="dark">.
 */
(() => {
    const STORAGE_KEY = 'ss-theme';

    const resolveTheme = () => {
        const stored = window.localStorage.getItem(STORAGE_KEY);
        if (stored === 'light' || stored === 'dark') {
            return stored;
        }

        return 'light';
    };

    const applyTheme = (theme) => {
        const root = document.documentElement;
        const isDark = theme === 'dark';
        root.classList.toggle('dark', isDark);
        root.dataset.theme = theme;

        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            const icon = button.querySelector('[data-theme-icon]');
            if (icon) {
                icon.textContent = isDark ? 'light_mode' : 'dark_mode';
            }
            button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            button.setAttribute(
                'aria-label',
                isDark ? 'التبديل إلى الوضع الفاتح' : 'التبديل إلى الوضع الداكن',
            );
        });
    };

    const current = resolveTheme();
    applyTheme(current);

    window.SaltSugerTheme = {
        get: resolveTheme,
        set(theme) {
            if (theme !== 'light' && theme !== 'dark') {
                return;
            }
            window.localStorage.setItem(STORAGE_KEY, theme);
            applyTheme(theme);
        },
        toggle() {
            const next = resolveTheme() === 'dark' ? 'light' : 'dark';
            this.set(next);
            return next;
        },
    };

    document.addEventListener('DOMContentLoaded', () => {
        applyTheme(resolveTheme());

        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                window.SaltSugerTheme.toggle();
            });
        });
    });
})();
