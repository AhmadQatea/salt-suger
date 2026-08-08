@props([
    'currency',
    'fallbackImage',
])

<div
    id="product-modal"
    class="fixed inset-0 z-60 hidden"
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-product-name"
    data-store-url="{{ route('cart.items.store') }}"
>
    <div id="product-modal-overlay" class="absolute inset-0 bg-black/50" data-close-modal></div>

    <div class="relative mx-auto flex h-full w-full max-w-md flex-col overflow-hidden bg-surface shadow-2xl md:my-8 md:h-auto md:max-h-[90vh] md:rounded-xl">
        <div class="absolute top-0 left-0 right-0 z-10 flex items-start justify-between p-4">
            <button
                type="button"
                data-close-modal
                class="flex h-10 w-10 items-center justify-center rounded-full bg-surface/80 text-on-surface shadow-sm backdrop-blur-sm"
                aria-label="إغلاق"
            >
                <span class="material-symbols-outlined text-xl" aria-hidden="true">arrow_forward</span>
            </button>
        </div>

        <div class="relative h-72 shrink-0 bg-surface-container md:h-64">
            <img
                id="modal-product-image"
                src="{{ $fallbackImage }}"
                alt=""
                class="h-full w-full object-cover"
            >
        </div>

        <div class="relative -mt-6 flex flex-1 flex-col gap-5 overflow-y-auto rounded-t-3xl bg-surface p-6 shadow-[0_-8px_16px_rgba(0,0,0,0.05)]">
            <div class="flex flex-col gap-2">
                <div class="flex items-start justify-between gap-4">
                    <h2 id="modal-product-name" class="text-2xl font-bold text-on-surface"></h2>
                    <span id="modal-product-price" class="shrink-0 text-xl font-semibold text-primary"></span>
                </div>
                <p id="modal-product-description" class="text-sm leading-relaxed text-on-surface-variant"></p>
            </div>

            <div>
                <span
                    id="modal-product-badge"
                    class="hidden rounded-full bg-tertiary-fixed px-3 py-1 text-xs font-semibold text-on-tertiary-fixed"
                ></span>
            </div>

            <div class="flex items-center justify-between rounded-xl border border-outline-variant/20 bg-surface-container-low p-4">
                <span class="text-sm font-semibold text-on-surface">الكمية</span>
                <div class="flex items-center gap-3 rounded-full border border-outline-variant/30 bg-surface px-2 py-1 shadow-sm">
                    <button
                        type="button"
                        id="modal-qty-decrease"
                        class="flex h-10 w-10 items-center justify-center rounded-full text-primary hover:bg-primary-container hover:text-on-primary-container"
                        aria-label="تقليل الكمية"
                    >
                        <span class="material-symbols-outlined" aria-hidden="true">remove</span>
                    </button>
                    <span id="modal-quantity" class="w-8 text-center text-xl font-semibold">1</span>
                    <button
                        type="button"
                        id="modal-qty-increase"
                        class="flex h-10 w-10 items-center justify-center rounded-full text-primary hover:bg-primary-container hover:text-on-primary-container"
                        aria-label="زيادة الكمية"
                    >
                        <span class="material-symbols-outlined" aria-hidden="true">add</span>
                    </button>
                </div>
            </div>

            <div class="flex flex-col gap-2 pb-24">
                <label for="modal-note" class="text-sm font-semibold text-on-surface">ملاحظات خاصة</label>
                <textarea
                    id="modal-note"
                    rows="3"
                    class="w-full resize-none rounded-xl border-2 border-outline-variant/50 bg-surface-container-lowest p-4 text-sm text-on-surface shadow-sm placeholder:text-on-surface-variant/50 focus:border-(--menu-primary) focus:outline-none"
                    placeholder="مثلاً: بدون بصل، صوص إضافي..."
                ></textarea>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 right-0 border-t border-outline-variant/20 bg-surface p-4 shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
            <button
                type="button"
                id="modal-add-to-order"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary py-4 text-sm font-semibold text-on-primary shadow-md transition hover:bg-primary-container active:scale-[0.98]"
            >
                <span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span>
                أضف إلى الطلب -
                <span id="modal-total-price">0</span>
                <span id="modal-currency">{{ $currency }}</span>
            </button>
        </div>
    </div>
</div>
