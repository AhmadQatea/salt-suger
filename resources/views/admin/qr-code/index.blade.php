@extends('layouts.admin')

@section('title', 'QR كود المينيو')

@push('styles')
<style>
    .qr-layout {
        display: grid;
        gap: 1.5rem;
    }

    @media (min-width: 840px) {
        .qr-layout {
            grid-template-columns: minmax(260px, 340px) 1fr;
            align-items: start;
        }
    }

    .qr-panel {
        background: var(--ss-surface-container-lowest);
        border: 1px solid var(--ss-outline-variant);
        border-radius: .75rem;
        padding: 1.25rem;
        text-align: center;
    }

    .qr-frame {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        padding: 1.25rem;
        border: 1px solid var(--ss-outline-variant);
        border-radius: .5rem;
        max-width: 100%;
    }

    .qr-frame svg {
        width: min(100%, 280px);
        height: auto;
        display: block;
    }

    .qr-url-box {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        align-items: stretch;
        margin: 1rem 0 1.25rem;
    }

    .qr-url-box input {
        flex: 1 1 220px;
        min-width: 0;
        border: 1px solid var(--ss-outline-variant);
        border-radius: .5rem;
        padding: .7rem .85rem;
        font: inherit;
        direction: ltr;
        text-align: left;
        background: var(--ss-surface-container-lowest);
        color: var(--ss-on-surface);
    }

    .qr-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .6rem;
    }

    .qr-actions .btn-primary,
    .qr-actions .btn-secondary,
    .qr-actions .btn-ghost {
        min-height: 2.75rem;
        padding-inline: 1rem;
    }

    .qr-hint {
        margin: 1rem 0 0;
        color: var(--ss-on-surface-variant);
        font-size: .9rem;
        line-height: 1.6;
    }

    .qr-copy-status {
        min-height: 1.25rem;
        margin-top: .65rem;
        color: var(--ss-primary);
        font-size: .9rem;
        font-weight: 600;
    }

    @media print {
        body.admin-body {
            background: #fff !important;
        }

        aside,
        header,
        nav,
        .no-print,
        .qr-actions,
        .qr-url-box,
        .qr-copy-status,
        .qr-hint,
        .alert-success,
        .alert-error,
        .form-errors {
            display: none !important;
        }

        .admin-main {
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .admin-card {
            border: 0 !important;
            box-shadow: none !important;
            padding: 0 !important;
            background: transparent !important;
        }

        .print-only {
            display: block !important;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .print-only h1 {
            margin: 0 0 .5rem;
            font-size: 1.75rem;
        }

        .print-only p {
            margin: 0;
            font-size: 1.1rem;
        }

        .qr-layout {
            display: block;
        }

        .qr-panel {
            border: 0;
            padding: 0;
            background: transparent;
        }

        .qr-frame {
            border: 0;
            padding: 1.5rem;
        }

        .qr-frame svg {
            width: 420px;
            max-width: 90vw;
        }
    }
</style>
@endpush

@section('content')
    <div class="admin-card relative overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-2 bg-primary no-print"></div>

        <div class="print-only">
            <h1>{{ $restaurantName }}</h1>
            <p>امسح الكود لعرض المينيو</p>
        </div>

        <div class="no-print pt-2">
            <h1>QR كود المينيو</h1>
            <p class="subtitle">يمكن لزبائن المطعم مسح هذا الرمز للوصول مباشرة إلى المينيو الإلكتروني.</p>
        </div>

        @if ($qrError)
            <div class="alert-error no-print" role="alert">{{ $qrError }}</div>
        @endif

        <div class="qr-layout">
            <div class="qr-panel" id="qr-print-area">
                @if ($qrSvg)
                    <div class="qr-frame" aria-label="رمز QR للمينيو">
                        {!! $qrSvg !!}
                    </div>
                @else
                    <p class="empty-state no-print">تعذر إنشاء رمز QR حالياً.</p>
                @endif
            </div>

            <div class="no-print">
                <label for="menu-url" class="mb-1.5 block font-bold text-on-surface">رابط المينيو</label>
                <div class="qr-url-box">
                    <input
                        id="menu-url"
                        type="text"
                        readonly
                        value="{{ $menuUrl }}"
                        dir="ltr"
                        aria-label="رابط المينيو العام"
                    >
                    <button type="button" class="btn-secondary" id="copy-menu-url">نسخ رابط المينيو</button>
                </div>
                <p class="qr-copy-status" id="copy-status" aria-live="polite"></p>

                <div class="qr-actions">
                    @if ($qrSvg)
                        <a href="{{ route('admin.qr-code.download', ['format' => 'svg']) }}" class="btn-primary">تحميل SVG</a>
                        @if ($supportsPng)
                            <a href="{{ route('admin.qr-code.download', ['format' => 'png']) }}" class="btn-secondary">تحميل PNG</a>
                        @endif
                        <button type="button" class="btn-ghost" id="print-qr">طباعة QR</button>
                    @endif
                </div>

                <p class="qr-hint">
                    يعتمد الرابط على إعداد <code>APP_URL</code> في ملف البيئة.
                    في التطوير قد يظهر عنوان محلي؛ للإنتاج استخدم نطاق المطعم الحقيقي.
                    لمسح الرمز من هاتف على شبكة أخرى، يجب أن يكون العنوان قابلاً للوصول من ذلك الجهاز (وليس فقط localhost).
                </p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const copyButton = document.getElementById('copy-menu-url');
    const urlInput = document.getElementById('menu-url');
    const status = document.getElementById('copy-status');
    const printButton = document.getElementById('print-qr');

    const showStatus = (message) => {
        if (status) {
            status.textContent = message;
        }
    };

    const fallbackCopy = () => {
        if (!urlInput) {
            return false;
        }

        urlInput.focus();
        urlInput.select();
        urlInput.setSelectionRange(0, urlInput.value.length);

        try {
            return document.execCommand('copy');
        } catch (error) {
            return false;
        }
    };

    if (copyButton && urlInput) {
        copyButton.addEventListener('click', async () => {
            const value = urlInput.value;

            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(value);
                    showStatus('تم نسخ رابط المينيو.');
                    return;
                }
            } catch (error) {
                // Fall through to legacy copy.
            }

            if (fallbackCopy()) {
                showStatus('تم نسخ رابط المينيو.');
            } else {
                showStatus('تعذر النسخ تلقائياً، يرجى نسخ الرابط يدوياً.');
            }
        });
    }

    if (printButton) {
        printButton.addEventListener('click', () => window.print());
    }
})();
</script>
@endpush
