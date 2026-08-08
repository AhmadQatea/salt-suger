@extends('layouts.public')

@php
    use App\Support\Money;
    use Illuminate\Support\Facades\Storage;
    $restaurantName = $settings->restaurant_name ?: config('app.name');
@endphp

@section('title', 'السلة — '.$restaurantName)

@section('content')
    <x-menu.header :settings="$settings" :logo-url="$logoUrl" />

    <main class="mx-auto max-w-7xl px-4 py-5 pb-28 md:px-8 md:py-8 md:pb-10">
        @if (session('status'))
            <div class="mb-4 rounded-xl bg-tertiary-fixed/50 px-4 py-3 text-sm font-semibold text-on-surface" role="status">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-xl bg-error/10 px-4 py-3 text-sm font-semibold text-error" role="alert">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-xl bg-error/10 px-4 py-3 text-sm text-error" role="alert">
                <ul class="list-disc pr-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-5 flex items-center justify-between gap-3">
            <h1 class="text-2xl font-bold text-on-surface">السلة</h1>
            @unless ($isEmpty)
                <form method="POST" action="{{ route('cart.clear') }}" onsubmit="return confirm('هل تريد تفريغ السلة؟');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="ss-btn ss-btn-ghost px-3 py-2 text-sm">
                        تفريغ السلة
                    </button>
                </form>
            @endunless
        </div>

        @if ($isEmpty)
            <div class="rounded-2xl bg-surface-container-lowest px-6 py-14 text-center ring-1 ring-outline-variant/30">
                <span class="material-symbols-outlined mb-3 text-4xl text-on-surface-variant" aria-hidden="true">shopping_cart</span>
                <h2 class="mb-2 text-xl font-bold text-on-surface">سلتك فارغة</h2>
                <p class="mb-6 text-sm text-on-surface-variant">أضف بعض الوجبات اللذيذة للبدء.</p>
                <a href="{{ route('menu.index') }}" class="ss-btn ss-btn-primary">تصفح المنيو</a>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-3 lg:col-span-2">
                    @foreach ($items as $item)
                        @php
                            $image = ($item['image'] ?? null) && Storage::disk('public')->exists($item['image'])
                                ? asset('storage/'.$item['image'])
                                : $logoUrl;
                        @endphp
                        <article class="flex gap-3 rounded-2xl bg-surface-container-lowest p-3 ring-1 ring-outline-variant/25 sm:gap-4 sm:p-4">
                            <img
                                src="{{ $image }}"
                                alt="{{ $item['name'] }}"
                                class="h-20 w-20 shrink-0 rounded-xl object-cover bg-surface-container sm:h-24 sm:w-24"
                                width="96"
                                height="96"
                                loading="lazy"
                            >

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <h2 class="truncate text-base font-semibold text-on-surface">{{ $item['name'] }}</h2>
                                        <p class="mt-0.5 text-sm text-on-surface-variant">{{ Money::format($item['price'], $currency) }}</p>
                                        @if (! empty($item['note']))
                                            <p class="mt-1 text-xs text-on-surface-variant line-clamp-2">ملاحظة: {{ $item['note'] }}</p>
                                        @endif
                                    </div>
                                    <p class="shrink-0 text-sm font-bold text-primary tabular-nums">{{ Money::format($item['subtotal'], $currency) }}</p>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                                    <form method="POST" action="{{ route('cart.items.update', ['key' => $item['key']]) }}" class="flex items-center gap-1 rounded-full bg-surface-container px-1 py-0.5">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="note" value="{{ $item['note'] }}">
                                        <button
                                            type="submit"
                                            name="quantity"
                                            value="{{ max(1, ((int) $item['quantity']) - 1) }}"
                                            class="flex h-10 w-10 items-center justify-center rounded-full text-primary"
                                            aria-label="تقليل الكمية"
                                            @disabled((int) $item['quantity'] <= 1)
                                        >
                                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">remove</span>
                                        </button>
                                        <span class="w-7 text-center text-sm font-bold tabular-nums">{{ $item['quantity'] }}</span>
                                        <button
                                            type="submit"
                                            name="quantity"
                                            value="{{ min(99, ((int) $item['quantity']) + 1) }}"
                                            class="flex h-10 w-10 items-center justify-center rounded-full text-primary"
                                            aria-label="زيادة الكمية"
                                            @disabled((int) $item['quantity'] >= 99)
                                        >
                                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">add</span>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('cart.items.destroy', ['key' => $item['key']]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-2 text-sm font-semibold text-error">حذف</button>
                                    </form>
                                </div>

                                <form method="POST" action="{{ route('cart.items.update', ['key' => $item['key']]) }}" class="mt-2 flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="quantity" value="{{ $item['quantity'] }}">
                                    <input
                                        id="note-{{ $item['key'] }}"
                                        type="text"
                                        name="note"
                                        value="{{ $item['note'] }}"
                                        maxlength="255"
                                        class="min-w-0 flex-1 rounded-lg border border-outline-variant/50 bg-surface px-3 py-2 text-sm"
                                        placeholder="ملاحظة الصنف"
                                        aria-label="ملاحظة الصنف"
                                    >
                                    <button type="submit" class="ss-btn ss-btn-secondary px-3 py-2 text-xs">حفظ</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <aside class="lg:sticky lg:top-20 lg:self-start">
                    <div class="rounded-2xl bg-surface-container-lowest p-5 ring-1 ring-outline-variant/25">
                        <h2 class="mb-4 text-lg font-bold text-on-surface">ملخص الطلب</h2>
                        <div class="mb-2 flex justify-between text-sm">
                            <span class="text-on-surface-variant">المجموع الفرعي</span>
                            <span class="font-semibold tabular-nums">{{ Money::format($subtotal, $currency) }}</span>
                        </div>
                        <div class="mb-5 flex justify-between border-t border-outline-variant/40 pt-3 text-lg font-bold text-primary">
                            <span>الإجمالي</span>
                            <span class="tabular-nums">{{ Money::format($subtotal, $currency) }}</span>
                        </div>
                        <a href="{{ route('checkout.index') }}" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#25D366] py-3.5 text-sm font-semibold text-white transition hover:bg-[#1DA851] active:scale-[0.98]">
                            <span class="material-symbols-outlined text-base" aria-hidden="true">send</span>
                            إتمام الطلب عبر واتساب
                        </a>
                    </div>
                </aside>
            </div>
        @endif
    </main>

    <x-menu.bottom-nav />
@endsection
