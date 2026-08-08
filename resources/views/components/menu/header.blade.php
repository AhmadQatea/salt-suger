@props([
    'settings',
    'logoUrl',
    'cartCount' => 0,
])

@php
    $restaurantName = $settings->restaurant_name ?: config('app.name');
@endphp

<header class="sticky top-0 z-40 h-16 w-full bg-surface shadow-sm">
    <div class="container mx-auto flex h-16 max-w-[1280px] flex-row items-center justify-between px-4 md:px-12">
        <div class="flex items-center gap-1 sm:gap-2">
            <x-theme-toggle />

            <a
                href="{{ route('cart.index') }}"
                class="relative rounded-full p-2 text-on-surface-variant transition-colors hover:bg-surface-variant"
                aria-label="سلة الطلبات"
            >
                <span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span>
                <span
                    id="cart-count-badge"
                    class="absolute top-1 right-1 min-w-[1.1rem] rounded-full bg-primary px-1.5 py-0.5 text-center text-[10px] font-bold text-on-primary"
                    data-cart-count="{{ $cartCount }}"
                >{{ $cartCount }}</span>
            </a>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('menu.index') }}" class="flex items-center gap-3">
                <img
                    src="{{ $logoUrl }}"
                    alt="{{ $restaurantName }}"
                    class="h-10 w-10 rounded-full bg-surface-container object-cover"
                    width="40"
                    height="40"
                >
                <span class="hidden text-xl font-bold text-primary sm:block">
                    {{ $restaurantName }}
                </span>
            </a>
        </div>

        <nav aria-label="التنقل الرئيسي" class="hidden items-center gap-6 md:flex">
            <a
                href="{{ route('menu.index') }}"
                @class([
                    'rounded-lg px-3 py-2 font-bold transition-colors hover:bg-surface-variant',
                    'text-primary' => request()->routeIs('home', 'menu.index'),
                    'text-on-surface-variant' => ! request()->routeIs('home', 'menu.index'),
                ])
            >الرئيسية</a>
            <a
                href="{{ route('cart.index') }}"
                @class([
                    'rounded-lg px-3 py-2 font-bold transition-colors hover:bg-surface-variant',
                    'text-primary' => request()->routeIs('cart.*', 'checkout.*'),
                    'text-on-surface-variant' => ! request()->routeIs('cart.*', 'checkout.*'),
                ])
            >السلة</a>
        </nav>
    </div>
</header>
