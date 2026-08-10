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
    <div id="product-modal-overlay" class="absolute inset-0 bg-black/55 backdrop-blur-[2px]" data-close-modal></div>

    {{-- Mobile: bottom sheet | Desktop: centered modal --}}
    <div class="product-sheet absolute inset-x-0 bottom-0 flex max-h-[92dvh] w-full flex-col overflow-hidden rounded-t-2xl bg-surface shadow-2xl md:inset-auto md:top-1/2 md:left-1/2 md:max-h-[min(90vh,720px)] md:w-full md:max-w-md md:-translate-x-1/2 md:-translate-y-1/2 md:rounded-2xl">
        <div class="flex shrink-0 items-center justify-between border-b border-outline-variant/30 px-3 py-2.5 md:px-4 md:py-3">
            <span class="text-sm font-semibold text-on-surface-variant">تفاصيل الصنف</span>
            <button
                type="button"
                data-close-modal
                class="flex h-11 w-11 items-center justify-center rounded-full text-on-surface transition hover:bg-surface-variant"
                aria-label="إغلاق"
            >
                <span class="material-symbols-outlined text-[22px]" aria-hidden="true">close</span>
            </button>
        </div>

        <div class="flex min-h-0 flex-1 flex-col overflow-y-auto overscroll-contain">
            <div class="relative mx-3 mt-2 aspect-[4/3] max-h-44 shrink-0 overflow-hidden rounded-xl bg-surface-container sm:max-h-52 md:mx-5 md:mt-4 md:aspect-16/10 md:max-h-none">
                <img
                    id="modal-product-image"
                    src="{{ $fallbackImage }}"
                    alt=""
                    class="h-full w-full object-cover"
                >
            </div>

            <div class="flex flex-col gap-3 px-3 py-3 md:gap-4 md:px-5 md:py-4">
                <div class="space-y-1.5 md:space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <h2 id="modal-product-name" class="text-lg font-bold leading-snug text-on-surface md:text-xl"></h2>
                        <span
                            id="modal-product-badge"
                            class="hidden shrink-0 rounded-md bg-tertiary-container px-2 py-1 text-[11px] font-semibold text-on-tertiary-container"
                        ></span>
                    </div>
                    <p id="modal-product-description" class="text-sm leading-relaxed text-on-surface-variant line-clamp-3 md:line-clamp-none"></p>
                    <p id="modal-product-price" class="text-base font-bold text-primary md:text-lg"></p>
                </div>

                <div class="flex items-center justify-between gap-3 rounded-xl bg-surface-container-low px-3 py-2 md:py-2.5">
                    <span class="text-sm font-semibold text-on-surface">الكمية</span>
                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            id="modal-qty-decrease"
                            class="flex h-11 w-11 items-center justify-center rounded-full text-primary transition hover:bg-primary/10"
                            aria-label="تقليل الكمية"
                        >
                            <span class="material-symbols-outlined" aria-hidden="true">remove</span>
                        </button>
                        <span id="modal-quantity" class="w-8 text-center text-lg font-semibold tabular-nums">1</span>
                        <button
                            type="button"
                            id="modal-qty-increase"
                            class="flex h-11 w-11 items-center justify-center rounded-full text-primary transition hover:bg-primary/10"
                            aria-label="زيادة الكمية"
                        >
                            <span class="material-symbols-outlined" aria-hidden="true">add</span>
                        </button>
                    </div>
                </div>

                <div class="space-y-1.5 pb-1 md:space-y-2 md:pb-2">
                    <label for="modal-note" class="text-sm font-medium text-on-surface-variant">ملاحظات خاصة</label>
                    <textarea
                        id="modal-note"
                        rows="2"
                        class="w-full resize-none rounded-xl border border-outline-variant/50 bg-surface-container-lowest px-3 py-2.5 text-sm text-on-surface placeholder:text-on-surface-variant/60 focus:border-primary focus:outline-none"
                        placeholder="مثال: بدون بصل، بدون مخلل..."
                    ></textarea>
                </div>
            </div>
        </div>

        <div class="shrink-0 border-t border-outline-variant/30 bg-surface p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] md:p-4 md:pb-[max(1rem,env(safe-area-inset-bottom))]">
            <button
                type="button"
                id="modal-add-to-order"
                class="ss-btn ss-btn-primary min-h-11 w-full py-3 text-sm md:py-3.5"
            >
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">shopping_cart</span>
                <span>أضف إلى السلة —</span>
                <span id="modal-total-price" class="tabular-nums">0</span>
                <span id="modal-currency">{{ $currency }}</span>
            </button>
        </div>
    </div>
</div>
