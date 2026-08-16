<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $seoSettings = $settings ?? \App\Models\RestaurantSetting::cached();
        $seoHelper = \App\Support\Seo::fromSettings($seoSettings);
    @endphp

    <x-seo
        :title="$seoTitle ?? null"
        :description="$seoDescription ?? null"
        :canonical="$seoCanonical ?? null"
        :image="$seoImage ?? $seoHelper->imageUrl()"
        :robots="$seoRobots ?? 'index,follow'"
        :site-name="$seoSettings->restaurant_name ?? config('app.name')"
        :json-ld="$seoJsonLd ?? []"
    />

    <x-site-icons :settings="$seoSettings" />

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

    {{-- Icons are non-critical for LCP; load after first paint --}}
    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap"
        media="print"
        onload="this.media='all'"
    >
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
    </noscript>

    @vite([
        'resources/css/app.css',
        ($orderingEnabled ?? true) ? 'resources/js/public.js' : 'resources/js/display-menu.js',
    ])

    <style>
        :root {
            --menu-primary: {{ $seoSettings->primary_color ?? '#ba0013' }};
            --menu-secondary: {{ $seoSettings->secondary_color ?? '#111111' }};
            --menu-accent: {{ $seoSettings->accent_color ?? '#cca800' }};
        }
    </style>

    @stack('head')
    @stack('styles')
</head>
<body @class([
    'min-h-screen overflow-x-hidden bg-background font-sans text-on-background antialiased',
    'pb-[calc(6.5rem+env(safe-area-inset-bottom,0px))] md:pb-0' => $orderingEnabled ?? true,
])>
    <x-page-loader />
    @yield('content')

    @if ($orderingEnabled ?? true)
    <div
        id="menu-toast"
        class="hidden fixed bottom-[calc(7.25rem+env(safe-area-inset-bottom,0px))] md:bottom-8 left-1/2 z-70 max-w-[min(92vw,22rem)] -translate-x-1/2 rounded-xl bg-primary px-3 py-2 text-xs font-semibold text-on-primary shadow-lg sm:px-4 sm:py-2.5 sm:text-sm"
        role="status"
        aria-live="polite"
    ></div>
    @endif

    @stack('scripts')
</body>
</html>
