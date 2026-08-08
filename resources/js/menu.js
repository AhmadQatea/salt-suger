/**
 * Public digital menu interactions: product modal + add-to-cart.
 */
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('product-modal');
    if (!modal) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const storeUrl = modal.dataset.storeUrl;
    const overlay = document.getElementById('product-modal-overlay');
    const closeButtons = modal.querySelectorAll('[data-close-modal]');
    const imageEl = document.getElementById('modal-product-image');
    const nameEl = document.getElementById('modal-product-name');
    const descriptionEl = document.getElementById('modal-product-description');
    const priceEl = document.getElementById('modal-product-price');
    const badgeEl = document.getElementById('modal-product-badge');
    const qtyEl = document.getElementById('modal-quantity');
    const noteEl = document.getElementById('modal-note');
    const totalEl = document.getElementById('modal-total-price');
    const currencyEl = document.getElementById('modal-currency');
    const addButton = document.getElementById('modal-add-to-order');
    const decreaseBtn = document.getElementById('modal-qty-decrease');
    const increaseBtn = document.getElementById('modal-qty-increase');

    let quantity = 1;
    let unitPrice = '0.00';
    let currentProduct = null;
    let submitting = false;

    const formatDigits = (amount) => {
        const normalized = Number(amount).toFixed(2);
        const [integerPart, decimalPart] = normalized.split('.');
        const withCommas = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return decimalPart === '00' ? withCommas : `${withCommas}.${decimalPart}`;
    };

    const showToast = (message) => {
        const feedback = document.getElementById('menu-toast');
        if (!feedback) {
            return;
        }
        feedback.textContent = message;
        feedback.classList.remove('hidden');
        window.setTimeout(() => feedback.classList.add('hidden'), 2200);
    };

    const updateCartBadge = (count) => {
        document.querySelectorAll('#cart-count-badge, [data-cart-count]').forEach((badge) => {
            badge.textContent = String(count);
            badge.setAttribute('data-cart-count', String(count));
        });
    };

    const updateTotal = () => {
        const total = (Number(unitPrice) * quantity).toFixed(2);
        if (totalEl) {
            totalEl.textContent = formatDigits(total);
        }
        if (qtyEl) {
            qtyEl.textContent = String(quantity);
        }
    };

    const openModal = (product) => {
        currentProduct = product;
        quantity = 1;
        unitPrice = product.price || '0.00';

        if (nameEl) nameEl.textContent = product.name || '';
        if (descriptionEl) descriptionEl.textContent = product.description || '';
        if (priceEl) {
            priceEl.textContent = `${formatDigits(unitPrice)} ${product.currency || ''}`.trim();
        }
        if (currencyEl) currencyEl.textContent = product.currency || '';
        if (noteEl) noteEl.value = '';

        if (badgeEl) {
            if (product.badge) {
                badgeEl.textContent = product.badge;
                badgeEl.classList.remove('hidden');
                badgeEl.classList.add('inline-flex');
            } else {
                badgeEl.classList.add('hidden');
                badgeEl.classList.remove('inline-flex');
            }
        }

        if (imageEl) {
            imageEl.src = product.image || product.fallback || '';
            imageEl.alt = product.name || '';
        }

        updateTotal();
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        currentProduct = null;
        submitting = false;
        if (addButton) {
            addButton.disabled = false;
        }
    };

    document.querySelectorAll('[data-product-card]').forEach((card) => {
        const open = () => {
            openModal({
                id: card.dataset.productId,
                name: card.dataset.productName,
                description: card.dataset.productDescription,
                price: card.dataset.productPrice,
                badge: card.dataset.productBadge,
                image: card.dataset.productImage,
                fallback: card.dataset.productFallback,
                currency: card.dataset.productCurrency,
            });
        };

        card.querySelectorAll('[data-open-product]').forEach((trigger) => {
            trigger.addEventListener('click', (event) => {
                event.preventDefault();
                open();
            });
        });
    });

    decreaseBtn?.addEventListener('click', () => {
        if (quantity > 1) {
            quantity -= 1;
            updateTotal();
        }
    });

    increaseBtn?.addEventListener('click', () => {
        if (quantity < 99) {
            quantity += 1;
            updateTotal();
        }
    });

    closeButtons.forEach((button) => button.addEventListener('click', closeModal));
    overlay?.addEventListener('click', closeModal);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    addButton?.addEventListener('click', async () => {
        if (!currentProduct || submitting || !storeUrl || !csrfToken) {
            return;
        }

        submitting = true;
        addButton.disabled = true;

        try {
            const response = await fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    product_id: Number(currentProduct.id),
                    quantity,
                    note: noteEl?.value?.trim() || null,
                }),
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                const message =
                    payload?.errors?.product_id?.[0] ||
                    payload?.errors?.quantity?.[0] ||
                    payload?.message ||
                    'تعذر إضافة الصنف إلى الطلب.';
                showToast(message);
                return;
            }

            if (typeof payload.cart_count !== 'undefined') {
                updateCartBadge(payload.cart_count);
            }

            showToast(payload.message || `تمت إضافة «${currentProduct.name}» إلى الطلب.`);
            closeModal();
        } catch (error) {
            showToast('تعذر إضافة الصنف إلى الطلب.');
        } finally {
            submitting = false;
            if (addButton) {
                addButton.disabled = false;
            }
        }
    });
});
