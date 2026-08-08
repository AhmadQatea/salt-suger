@php
    $logoUrl = asset('images/logo.png');
    $appName = config('app.name', 'Salt&Suger');
@endphp

<div
    id="ss-page-loader"
    class="ss-page-loader"
    role="status"
    aria-live="polite"
    aria-label="جاري التحميل"
>
    <div class="ss-page-loader__mark">
        <img
            src="{{ $logoUrl }}"
            alt="{{ $appName }}"
            class="ss-page-loader__logo"
            width="88"
            height="88"
            decoding="async"
        >
    </div>
    <span class="sr-only">جاري التحميل...</span>
</div>
