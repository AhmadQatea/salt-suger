<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    {{-- Prevent theme flash before CSS/JS load --}}
    <script>
        (function () {
            try {
                var theme = localStorage.getItem('ss-theme');
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                    document.documentElement.dataset.theme = 'dark';
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.dataset.theme = 'light';
                }
            } catch (e) {}
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="admin-body min-h-screen overflow-x-hidden bg-background font-sans text-on-background antialiased">
@php
    $isDashboard = request()->routeIs('admin.dashboard');
    $isOrders = request()->routeIs('admin.orders.*');
    $isCategories = request()->routeIs('admin.categories.*');
    $isProducts = request()->routeIs('admin.products.*');
    $isQr = request()->routeIs('admin.qr-code.*');
    $isSettings = request()->routeIs('admin.settings.*');
    $isProfile = request()->routeIs('admin.profile*');
@endphp

@hasSection('body')
    @yield('body')
@else
    {{-- Mobile top header --}}
    <header class="fixed inset-x-0 top-0 z-40 flex h-16 items-center justify-between border-b border-outline-variant bg-surface px-margin-mobile shadow-sm md:hidden">
        <a href="{{ route('admin.dashboard') }}" class="font-bold text-primary-container">
            {{ config('app.name') }}
        </a>
        <div class="flex items-center gap-2">
            <x-theme-toggle />
            <a href="{{ route('admin.profile') }}" class="rounded-full p-2 text-on-surface transition-colors hover:bg-surface-variant" aria-label="الملف الشخصي">
                <span class="material-symbols-outlined" aria-hidden="true">person</span>
            </a>
        </div>
    </header>

    <div class="flex min-h-screen pt-16 md:pt-0">
        {{-- Desktop sidebar: use surface tokens (not inverse-surface) so text contrast stays correct in both themes --}}
        <aside class="fixed right-0 top-0 z-50 hidden h-screen w-64 flex-col border-l border-outline-variant bg-surface-container-lowest pt-8 shadow-sm dark:bg-surface-container md:flex">
            <div class="mb-8 px-6 text-right">
                <div class="mb-2 flex items-center justify-end gap-3">
                    <div>
                        <h2 class="text-xl font-bold text-primary-container">{{ config('app.name') }}</h2>
                        <p class="admin-sidebar-label">{{ __('لوحة التحكم') }}</p>
                    </div>
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="{{ config('app.name') }}"
                        class="h-12 w-12 rounded-full border-2 border-primary-container object-cover"
                    >
                </div>
            </div>

            <nav class="flex flex-1 flex-col gap-1 overflow-y-auto" aria-label="التنقل">
                <a href="{{ route('admin.dashboard') }}" @class(['admin-nav-link', 'is-active' => $isDashboard])>
                    <span class="material-symbols-outlined" aria-hidden="true">dashboard</span>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.orders.index') }}" @class(['admin-nav-link', 'is-active' => $isOrders])>
                    <span class="material-symbols-outlined" aria-hidden="true">receipt_long</span>
                    <span>الطلبات</span>
                    @if (($pendingOrdersCount ?? 0) > 0)
                        <span class="nav-count">{{ $pendingOrdersCount }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.categories.index') }}" @class(['admin-nav-link', 'is-active' => $isCategories])>
                    <span class="material-symbols-outlined" aria-hidden="true">category</span>
                    <span>التصنيفات</span>
                </a>

                <a href="{{ route('admin.products.index') }}" @class(['admin-nav-link', 'is-active' => $isProducts])>
                    <span class="material-symbols-outlined" aria-hidden="true">restaurant_menu</span>
                    <span>الأصناف</span>
                </a>

                {{-- Text must immediately follow is-active"> for QrCodeManagementTest --}}
                <a href="{{ route('admin.qr-code.index') }}" @class(['admin-nav-link', 'is-active' => $isQr])>QR كود المينيو<span class="material-symbols-outlined ms-auto" aria-hidden="true">qr_code_2</span></a>

                <a href="{{ route('admin.settings.edit') }}" @class(['admin-nav-link', 'is-active' => $isSettings])>
                    <span class="material-symbols-outlined" aria-hidden="true">storefront</span>
                    <span>إعدادات المطعم</span>
                </a>

                <a href="{{ route('admin.profile') }}" @class(['admin-nav-link', 'is-active' => $isProfile])>
                    <span class="material-symbols-outlined" aria-hidden="true">person</span>
                    <span>{{ __('الملف الشخصي') }}</span>
                </a>
            </nav>

            <div class="mt-auto space-y-3 border-t border-outline-variant p-4">
                <div class="flex items-center justify-between px-2">
                    <span class="admin-sidebar-label">المظهر</span>
                    <x-theme-toggle />
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary-container px-4 py-3 font-semibold text-on-primary-container transition-colors hover:bg-primary hover:text-on-primary">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">logout</span>
                        {{ __('تسجيل الخروج') }}
                    </button>
                </form>
            </div>
        </aside>

        <main class="admin-main w-full flex-1 overflow-y-auto bg-surface-container-low p-margin-mobile pb-28 md:mr-64 md:p-margin-desktop md:pb-margin-desktop">
            <div class="mx-auto max-w-[1280px]">
                @include('admin.partials.flash')
                @yield('content')
            </div>
        </main>
    </div>

    {{-- Mobile bottom nav --}}
    <nav class="fixed inset-x-0 bottom-0 z-50 flex items-center justify-around rounded-t-xl border-t border-outline-variant bg-surface-container-lowest px-2 py-2 pb-safe shadow-(--ss-bottom-nav-shadow) dark:bg-surface-container md:hidden" aria-label="التنقل السريع">
        <a href="{{ route('admin.dashboard') }}" @class(['admin-mobile-nav-link', 'is-active' => $isDashboard])>
            <span class="material-symbols-outlined mb-0.5" aria-hidden="true">dashboard</span>
            <span class="text-[11px] font-medium">Dashboard</span>
        </a>
        <a href="{{ route('admin.orders.index') }}" @class(['admin-mobile-nav-link', 'is-active' => $isOrders])>
            <span class="material-symbols-outlined mb-0.5" aria-hidden="true">receipt_long</span>
            <span class="text-[11px] font-medium">الطلبات</span>
            @if (($pendingOrdersCount ?? 0) > 0)
                <span class="nav-count absolute -top-0.5 inset-e-1">{{ $pendingOrdersCount }}</span>
            @endif
        </a>
        <a href="{{ route('admin.products.index') }}" @class(['admin-mobile-nav-link', 'is-active' => $isProducts])>
            <span class="material-symbols-outlined mb-0.5" aria-hidden="true">restaurant_menu</span>
            <span class="text-[11px] font-medium">الأصناف</span>
        </a>
        <a href="{{ route('admin.categories.index') }}" @class(['admin-mobile-nav-link', 'is-active' => $isCategories])>
            <span class="material-symbols-outlined mb-0.5" aria-hidden="true">category</span>
            <span class="text-[11px] font-medium">التصنيفات</span>
        </a>
        <a href="{{ route('admin.qr-code.index') }}" @class(['admin-mobile-nav-link', 'is-active' => $isQr])>
            <span class="material-symbols-outlined mb-0.5" aria-hidden="true">qr_code_2</span>
            <span class="text-[11px] font-medium">QR</span>
        </a>
    </nav>
@endif

@stack('scripts')
</body>
</html>
