@props([
    'cartCount' => 0,
    'cartSubtotal' => '0.00',
    'cartCurrency' => 'ل.س',
])

@php
    use App\Support\Money;
@endphp

@if ($cartCount > 0)
    <div class="pointer-events-none fixed bottom-20 inset-x-0 z-40 px-4 md:bottom-6 md:px-8">
        <div class="mx-auto flex max-w-7xl justify-center md:justify-end">
            <a
                href="{{ route('cart.index') }}"
                class="pointer-events-auto flex w-full max-w-md items-center justify-between rounded-2xl bg-inverse-surface px-5 py-3.5 text-inverse-on-surface shadow-lg transition hover:opacity-95"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-sm font-bold text-on-primary"
                        data-cart-count="{{ $cartCount }}"
                    >{{ $cartCount }}</div>
                    <span class="text-sm font-semibold">عرض السلة</span>
                </div>
                <span class="text-base font-bold text-inverse-primary tabular-nums">
                    {{ Money::format($cartSubtotal, $cartCurrency) }}
                </span>
            </a>
        </div>
    </div>
@endif
