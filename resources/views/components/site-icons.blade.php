@php
    use Illuminate\Support\Facades\Storage;

    $settings = ($settings ?? null) instanceof \App\Models\RestaurantSetting
        ? $settings
        : \App\Models\RestaurantSetting::cached();

    $iconUrl = asset('images/logo.png');

    if ($settings->favicon && Storage::disk('public')->exists($settings->favicon)) {
        $iconUrl = asset('storage/'.$settings->favicon);
    } elseif ($settings->logo && Storage::disk('public')->exists($settings->logo)) {
        $iconUrl = asset('storage/'.$settings->logo);
    }

    $appName = $settings->restaurant_name ?: config('app.name', 'Salt&Suger');
@endphp

<link rel="icon" href="{{ $iconUrl }}" type="image/png" sizes="32x32">
<link rel="icon" href="{{ $iconUrl }}" type="image/png" sizes="192x192">
<link rel="apple-touch-icon" href="{{ $iconUrl }}">
<meta name="theme-color" content="{{ $settings->primary_color ?? '#ba0013' }}">
<meta name="application-name" content="{{ $appName }}">
