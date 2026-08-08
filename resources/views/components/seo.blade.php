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
    $siteName = $siteName ?: config('app.name', 'Salt&Suger');
    $locale = $locale ?: config('seo.locale', 'ar_SY');
    $canonical = $canonical ?: url()->current();
    $image = $image ?: asset('images/logo.png');
    $title = $title ?: $siteName;
    $description = \Illuminate\Support\Str::limit(
        trim(strip_tags((string) ($description ?: config('seo.default_description')))),
        160,
        '…'
    );

    $jsonLdBlocks = [];
    if (is_array($jsonLd) && $jsonLd !== []) {
        $jsonLdBlocks = isset($jsonLd['@type']) ? [$jsonLd] : array_values($jsonLd);
    }
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">

<meta property="og:locale" content="{{ $locale }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $image }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">

@if (filled(config('seo.google_site_verification')))
    <meta name="google-site-verification" content="{{ config('seo.google_site_verification') }}">
@endif
@if (filled(config('seo.bing_site_verification')))
    <meta name="msvalidate.01" content="{{ config('seo.bing_site_verification') }}">
@endif

@foreach ($jsonLdBlocks as $block)
    @if (is_array($block) && $block !== [])
        <script type="application/ld+json">{!! json_encode($block, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @endif
@endforeach
