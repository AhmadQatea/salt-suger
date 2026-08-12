document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-password-toggle]');

    if (! button) {
        return;
    }

    const field = button.closest('.password-field');
    const input = field?.querySelector('[data-password-input]');
    const icon = field?.querySelector('[data-password-icon]');

    if (! input || ! icon) {
        return;
    }

    const willShow = input.type === 'password';

    input.type = willShow ? 'text' : 'password';
    icon.textContent = willShow ? 'visibility_off' : 'visibility';
    button.setAttribute('aria-pressed', willShow ? 'true' : 'false');
    button.setAttribute('aria-label', willShow ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
});
