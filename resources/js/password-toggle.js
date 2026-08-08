document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-password-toggle]');

    if (! button) {
        return;
    }

    const field = button.closest('.password-field');
    const input = field?.querySelector('[data-password-input]');
    const showIcon = field?.querySelector('[data-password-icon="show"]');
    const hideIcon = field?.querySelector('[data-password-icon="hide"]');

    if (! input) {
        return;
    }

    const willShow = input.type === 'password';

    input.type = willShow ? 'text' : 'password';
    button.setAttribute('aria-pressed', willShow ? 'true' : 'false');
    button.setAttribute('aria-label', willShow ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
    showIcon?.classList.toggle('hidden', willShow);
    hideIcon?.classList.toggle('hidden', ! willShow);
});
