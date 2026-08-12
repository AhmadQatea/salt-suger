@php
    use App\Support\PublicStorage;

    $settings = ($settings ?? null) instanceof \App\Models\RestaurantSetting
        ? $settings
        : \App\Models\RestaurantSetting::cached();

    $version = $settings->updated_at?->timestamp;
    $iconUrl = PublicStorage::url($settings->favicon, $version)
        ?: PublicStorage::url($settings->logo, $version)
        ?: asset('images/logo.png');

    $appName = $settings->restaurant_name ?: config('app.name', 'Salt&Suger');
@endphp

<link rel="icon" href="{{ $iconUrl }}" type="image/png" sizes="32x32">
<link rel="icon" href="{{ $iconUrl }}" type="image/png" sizes="192x192">
<link rel="apple-touch-icon" href="{{ $iconUrl }}">
<meta name="theme-color" content="{{ $settings->primary_color ?? '#ba0013' }}">
<meta name="application-name" content="{{ $appName }}">
