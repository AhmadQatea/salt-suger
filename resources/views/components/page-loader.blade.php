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
        <span class="ss-page-loader__ring" aria-hidden="true"></span>
        <img
            src="{{ $logoUrl }}"
            alt="{{ $appName }}"
            class="ss-page-loader__logo"
            width="140"
            height="140"
            decoding="async"
            fetchpriority="high"
        >
    </div>
    <span class="sr-only">جاري التحميل...</span>
</div>
<script>
    (function () {
        try {
            if (sessionStorage.getItem('ss-fast-nav') === '1') {
                sessionStorage.removeItem('ss-fast-nav');
                var el = document.getElementById('ss-page-loader');
                if (el) {
                    el.dataset.hidden = 'true';
                    el.classList.add('is-hidden', 'is-fast-nav');
                    el.setAttribute('hidden', '');
                }
                window.__ssFastNav = true;
            }
        } catch (e) {}
    })();
</script>
