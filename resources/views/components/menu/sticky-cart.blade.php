@props([
    'cartCount' => 0,
    'cartSubtotal' => '0.00',
    'cartCurrency' => 'ل.س',
])

@php
    use App\Support\Money;
    $isEmpty = (int) $cartCount <= 0;
    $countLabel = (int) $cartCount === 1 ? 'منتج' : 'منتجات';
@endphp

{{-- Mobile-only floating cart — always in DOM so JS can reveal after add-to-cart --}}
<div
    data-floating-cart-wrap
    @class([
        'pointer-events-none fixed inset-x-0 z-40 px-3 md:hidden',
        'hidden' => $isEmpty,
    ])
    style="bottom: calc(4.25rem + env(safe-area-inset-bottom, 0px));"
    @if ($isEmpty) hidden @endif
>
    <div class="mx-auto flex max-w-lg justify-center">
        <a
            href="{{ route('cart.index') }}"
            data-floating-cart
            class="floating-cart pointer-events-auto flex w-full items-center justify-between gap-3 rounded-2xl bg-primary px-4 py-3 text-on-primary shadow-lg ring-1 ring-black/10 transition hover:bg-primary-container active:scale-[0.99]"
            aria-label="فتح السلة"
        >
            <div class="flex min-w-0 items-center gap-2.5">
                <span class="material-symbols-outlined shrink-0 text-[22px]" aria-hidden="true">shopping_cart</span>
                <div class="min-w-0">
                    <span class="block text-sm font-bold leading-tight">السلة</span>
                    <span class="block text-xs font-medium leading-tight text-on-primary/90">
                        <span data-cart-count="{{ $cartCount }}">{{ $cartCount }}</span>
                        <span data-cart-count-label>{{ $countLabel }}</span>
                    </span>
                </div>
            </div>
            <span
                data-cart-subtotal
                class="shrink-0 text-sm font-bold tabular-nums"
            >{{ Money::format($cartSubtotal, $cartCurrency) }}</span>
        </a>
    </div>
</div>
