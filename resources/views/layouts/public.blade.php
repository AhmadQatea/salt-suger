<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', ($settings->restaurant_name ?? config('app.name')).' | المنيو')</title>
    <meta name="description" content="@yield('meta_description', $settings->description ?? 'القائمة الرقمية لمطعم '.($settings->restaurant_name ?? config('app.name')))">

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

    <style>
        :root {
            --menu-primary: {{ $settings->primary_color ?? '#ba0013' }};
            --menu-secondary: {{ $settings->secondary_color ?? '#111111' }};
            --menu-accent: {{ $settings->accent_color ?? '#cca800' }};
        }
    </style>

    @stack('styles')
</head>
<body class="bg-background text-on-background font-sans antialiased pb-24 md:pb-0 min-h-screen overflow-x-hidden">
    @yield('content')

    <div
        id="menu-toast"
        class="hidden fixed bottom-24 md:bottom-8 left-1/2 z-70 -translate-x-1/2 rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-on-primary shadow-lg"
        role="status"
        aria-live="polite"
    ></div>

    @stack('scripts')
</body>
</html>
