@props([
    'settings',
    'logoUrl',
    'cartCount' => 0,
])

@php
    $restaurantName = $settings->restaurant_name ?: config('app.name');
@endphp

<header class="sticky top-0 z-40 border-b border-outline-variant/40 bg-surface/95 backdrop-blur-md">
    <div class="mx-auto flex h-12 max-w-7xl items-center justify-between gap-2 px-3 sm:h-14 sm:gap-3 sm:px-4 md:h-16 md:px-8">
        <a href="{{ route('menu.index') }}" class="flex min-w-0 items-center gap-2 sm:gap-2.5">
            <img
                src="{{ $logoUrl }}"
                alt="شعار {{ $restaurantName }}"
                class="h-8 w-8 shrink-0 rounded-full object-cover ring-1 ring-outline-variant/50 sm:h-9 sm:w-9 md:h-10 md:w-10"
                width="40"
                height="40"
            >
            <span class="truncate text-sm font-bold text-primary sm:text-base md:text-lg">
                {{ $restaurantName }}
            </span>
        </a>

        <nav aria-label="التنقل الرئيسي" class="hidden items-center gap-1 md:flex">
            <a
                href="{{ route('menu.index') }}"
                @class([
                    'rounded-lg px-3 py-2 text-sm font-semibold transition-colors',
                    'bg-primary/10 text-primary' => request()->routeIs('home', 'menu.index', 'menu.category'),
                    'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface' => ! request()->routeIs('home', 'menu.index', 'menu.category'),
                ])
            >الرئيسية</a>
            <a
                href="{{ route('cart.index') }}"
                @class([
                    'rounded-lg px-3 py-2 text-sm font-semibold transition-colors',
                    'bg-primary/10 text-primary' => request()->routeIs('cart.*', 'checkout.*'),
                    'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface' => ! request()->routeIs('cart.*', 'checkout.*'),
                ])
            >السلة</a>
        </nav>

        <div class="flex shrink-0 items-center gap-1">
            <x-theme-toggle />
            <a
                href="{{ route('cart.index') }}"
                class="relative rounded-full p-2 text-on-surface transition-colors hover:bg-surface-variant"
                aria-label="سلة الطلبات"
            >
                <span class="material-symbols-outlined text-[22px]" aria-hidden="true">shopping_cart</span>
                <span
                    id="cart-count-badge"
                    class="absolute top-0.5 inset-e-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-bold leading-none text-on-primary"
                    data-cart-count="{{ $cartCount }}"
                >{{ $cartCount }}</span>
            </a>
        </div>
    </div>
</header>
