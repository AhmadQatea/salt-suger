@props([
    'cartCount' => 0,
])

<nav
    class="fixed inset-x-0 bottom-0 z-50 flex items-center justify-around border-t border-outline-variant/40 bg-surface/95 px-2 py-1.5 pb-safe backdrop-blur-md md:hidden"
    aria-label="التنقل السفلي"
>
    <a
        href="{{ route('menu.index') }}"
        @class([
            'flex min-w-20 flex-col items-center justify-center rounded-xl px-3 py-2 transition-colors',
            'bg-primary/10 text-primary' => request()->routeIs('home', 'menu.index'),
            'text-on-surface-variant' => ! request()->routeIs('home', 'menu.index'),
        ])
        @if (request()->routeIs('home', 'menu.index')) aria-current="page" @endif
    >
        <span class="material-symbols-outlined text-[22px]" aria-hidden="true">home</span>
        <span class="mt-0.5 text-[11px] font-medium">الرئيسية</span>
    </a>

    <a
        href="{{ route('cart.index') }}"
        @class([
            'relative flex min-w-20 flex-col items-center justify-center rounded-xl px-3 py-2 transition-colors',
            'bg-primary/10 text-primary' => request()->routeIs('cart.*', 'checkout.*'),
            'text-on-surface-variant' => ! request()->routeIs('cart.*', 'checkout.*'),
        ])
        aria-label="سلة الطلبات"
    >
        <span class="material-symbols-outlined text-[22px]" aria-hidden="true">shopping_cart</span>
        <span class="mt-0.5 text-[11px] font-medium">السلة</span>
        @if ($cartCount > 0)
            <span
                data-cart-count="{{ $cartCount }}"
                class="absolute top-1 end-3 flex h-5 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-bold text-on-primary"
            >{{ $cartCount }}</span>
        @endif
    </a>
</nav>
