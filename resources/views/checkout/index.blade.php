@extends('layouts.public')

@php
    use App\Support\Money;
    $restaurantName = $settings->restaurant_name ?: config('app.name');
    $seoTitle = 'إتمام الطلب — '.$restaurantName;
    $seoDescription = 'إتمام طلبك من '.$restaurantName.' عبر واتساب. هذه الصفحة غير مخصصة لمحركات البحث.';
    $seoRobots = 'noindex,nofollow';
    $seoCanonical = route('checkout.index');
@endphp

@section('content')
    <x-menu.header :settings="$settings" :logo-url="$logoUrl" />

    <main class="mx-auto max-w-3xl px-4 py-5 pb-28 md:px-8 md:py-8 md:pb-10">
        @if ($errors->any())
            <div class="mb-4 rounded-xl bg-error/10 px-4 py-3 text-sm text-error" role="alert">
                <ul class="list-disc pr-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @unless ($whatsappAvailable)
            <div class="mb-4 rounded-xl bg-error/10 px-4 py-3 text-sm text-error" role="alert">
                الطلب عبر واتساب غير متاح حالياً.
            </div>
        @endunless

        <h1 class="mb-5 text-2xl font-bold text-on-surface">إتمام الطلب</h1>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <section class="rounded-2xl bg-surface-container-lowest p-5 ring-1 ring-outline-variant/25">
                <h2 class="mb-4 text-lg font-bold text-on-surface">ملخص الطلب</h2>
                <ul class="mb-4 space-y-3">
                    @foreach ($items as $item)
                        <li class="flex items-start justify-between gap-3 border-b border-surface-variant/60 pb-3 text-sm">
                            <div>
                                <p class="font-semibold text-on-surface">{{ $item['name'] }} × {{ $item['quantity'] }}</p>
                                @if (! empty($item['note']))
                                    <p class="mt-1 text-on-surface-variant">ملاحظة: {{ $item['note'] }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 font-bold text-primary">{{ Money::format($item['subtotal'], $currency) }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="flex justify-between text-sm">
                    <span class="text-on-surface-variant">المجموع الفرعي</span>
                    <span class="font-bold">{{ Money::format($subtotal, $currency) }}</span>
                </div>
                <div class="mt-3 flex justify-between border-t border-surface-variant pt-3 text-lg font-bold text-primary">
                    <span>الإجمالي</span>
                    <span>{{ Money::format($subtotal, $currency) }}</span>
                </div>

                <a href="{{ route('cart.index') }}" class="mt-6 inline-flex text-sm font-semibold text-on-surface-variant hover:text-primary">
                    العودة إلى السلة
                </a>
            </section>

            <section class="rounded-2xl bg-surface-container-lowest p-5 ring-1 ring-outline-variant/25">
                <h2 class="mb-4 text-lg font-bold text-on-surface">ملاحظات الطلب</h2>

                <form
                    id="checkout-form"
                    method="POST"
                    action="{{ route('checkout.store') }}"
                    class="space-y-4"
                    novalidate
                >
                    @csrf

                    <div>
                        <label for="notes" class="mb-1 block text-sm font-medium text-on-surface-variant">ملاحظات إضافية على الطلب</label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="3"
                            maxlength="1000"
                            class="w-full rounded-xl border border-outline-variant/50 bg-surface px-4 py-3 text-sm"
                            placeholder="يرجى تجهيز الطلب بسرعة..."
                            @disabled(! $whatsappAvailable)
                        >{{ old('notes') }}</textarea>
                        @error('notes') <p class="mt-1 text-sm text-error">{{ $message }}</p> @enderror
                    </div>

                    <p class="text-sm leading-relaxed text-on-surface-variant">
                        سيتم فتح واتساب لإرسال طلبك مباشرة إلى المطعم.
                    </p>
                    <p class="text-sm leading-relaxed text-on-surface-variant">
                        بعد فتح واتساب، اضغط إرسال لإتمام إرسال الطلب.
                    </p>

                    <button
                        id="checkout-submit"
                        type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#25D366] py-3.5 text-sm font-semibold text-white transition hover:bg-[#1DA851] active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                        @disabled(! $whatsappAvailable)
                        data-default-label="إتمام الطلب عبر واتساب"
                        data-loading-label="جاري تجهيز الطلب..."
                    >
                        <span class="material-symbols-outlined text-base" aria-hidden="true">send</span>
                        إتمام الطلب عبر واتساب
                    </button>
                </form>
            </section>
        </div>
    </main>

    <x-menu.bottom-nav />
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('checkout-form');
    const button = document.getElementById('checkout-submit');
    if (!form || !button || button.disabled) {
        return;
    }

    let submitting = false;

    form.addEventListener('submit', () => {
        if (submitting) {
            return;
        }

        submitting = true;
        button.disabled = true;
        button.textContent = button.dataset.loadingLabel || 'جاري تجهيز الطلب...';
    });
})();
</script>
@endpush
