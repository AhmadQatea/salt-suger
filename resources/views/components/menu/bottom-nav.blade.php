@props([
    'cartCount' => 0,
])

<nav
    class="fixed bottom-0 left-0 right-0 z-50 flex w-full flex-row-reverse items-center justify-around rounded-t-xl bg-surface px-4 py-2 pb-safe shadow-(--ss-bottom-nav-shadow) md:hidden dark:border-t dark:border-surface-variant"
    aria-label="التنقل السفلي"
>
    <a
        href="{{ route('menu.index') }}"
        @class([
            'flex flex-col items-center justify-center rounded-xl px-4 py-1 transition-colors',
            'scale-90 bg-primary-container text-on-primary-container' => request()->routeIs('home', 'menu.index'),
            'text-on-surface-variant hover:bg-surface-container-high' => ! request()->routeIs('home', 'menu.index'),
        ])
        @if (request()->routeIs('home', 'menu.index')) aria-current="page" @endif
    >
        <span class="material-symbols-outlined" aria-hidden="true">home</span>
        <span class="mt-1 text-xs font-medium">الرئيسية</span>
    </a>

    <a
        href="{{ route('cart.index') }}"
        @class([
            'relative flex flex-col items-center justify-center rounded-xl px-4 py-1 transition-colors',
            'bg-primary-container text-on-primary-container' => request()->routeIs('cart.*', 'checkout.*'),
            'text-on-surface-variant hover:bg-surface-container-high' => ! request()->routeIs('cart.*', 'checkout.*'),
        ])
        aria-label="سلة الطلبات"
    >
        <span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span>
        <span class="mt-1 text-xs font-medium">السلة</span>
        @if ($cartCount > 0)
            <span
                data-cart-count="{{ $cartCount }}"
                class="absolute -top-1 left-2 min-w-[1.1rem] rounded-full bg-primary px-1 text-center text-[10px] font-bold text-on-primary"
            >{{ $cartCount }}</span>
        @endif
    </a>
</nav>
