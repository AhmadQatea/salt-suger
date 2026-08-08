{{--
    SEO component (Phases 1–2).

    Renders title, description, robots, and canonical.
    Open Graph / Twitter props are reserved for later phases.
--}}
@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'image' => null,
    'robots' => 'index,follow',
    'type' => 'website',
    'siteName' => null,
    'locale' => null,
])

@php
    $seo = \App\Support\Seo::fromSettings(
        ($settings ?? null) instanceof \App\Models\RestaurantSetting
            ? $settings
            : null
    );

    $title = $title ?: $seo->title();
    $description = $seo->description($description);
    $canonical = $seo->absoluteUrl($canonical ?: url()->current());
    $image = $seo->absoluteUrl($image ?: $seo->imageUrl());
    $siteName = $siteName ?: $seo->restaurantName();
    $locale = $locale ?: (string) config('seo.locale', 'ar_SY');
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">

{{-- Reserved for later SEO phases: Open Graph / Twitter using $image, $type, $siteName, $locale --}}
