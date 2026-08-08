{{--
    SEO component.

    Renders title, description, robots, canonical, and optional JSON-LD.
    Open Graph / Twitter props remain reserved for later social phases.
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
    'jsonLd' => [],
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

    $jsonLdBlocks = [];
    if (is_array($jsonLd) && $jsonLd !== []) {
        $jsonLdBlocks = isset($jsonLd['@type']) ? [$jsonLd] : array_values($jsonLd);
    }
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">

@foreach ($jsonLdBlocks as $block)
    @if (is_array($block) && $block !== [])
        <script type="application/ld+json">{!! json_encode($block, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @endif
@endforeach

{{-- Reserved for later SEO phases: Open Graph / Twitter using $image, $type, $siteName, $locale --}}
