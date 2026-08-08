@extends('layouts.public')

@php
    use App\Support\Money;
    use Illuminate\Support\Facades\Storage;
    $restaurantName = $settings->restaurant_name ?: config('app.name');
@endphp

@section('title', 'السلة — '.$restaurantName)

@section('content')
    <x-menu.header :settings="$settings" :logo-url="$logoUrl" />

    <main class="mx-auto max-w-[1280px] px-4 md:px-12 py-6 md:py-8 pb-28 md:pb-8">
        @if (session('status'))
            <div class="mb-4 rounded-xl border border-tertiary-container/40 bg-tertiary-fixed/40 px-4 py-3 text-sm font-semibold text-on-surface" role="status">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-xl border border-error/30 bg-error/10 px-4 py-3 text-sm font-semibold text-error" role="alert">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-error/30 bg-error/10 px-4 py-3 text-sm text-error" role="alert">
                <ul class="list-disc pr-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6 flex items-center justify-between gap-3">
            <h1 class="text-2xl font-bold text-primary md:text-3xl">السلة</h1>
            @unless ($isEmpty)
                <form method="POST" action="{{ route('cart.clear') }}" onsubmit="return confirm('هل تريد تفريغ السلة؟');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-full border border-outline-variant px-4 py-2 text-sm font-semibold text-on-surface-variant hover:bg-surface-variant">
                        تفريغ السلة
                    </button>
                </form>
            @endunless
        </div>

        @if ($isEmpty)
            <div class="rounded-xl border border-outline-variant/40 bg-surface-container-lowest px-6 py-16 text-center shadow-sm">
                <span class="material-symbols-outlined mb-3 text-5xl text-on-surface-variant" aria-hidden="true">shopping_cart</span>
                <h2 class="mb-2 text-2xl font-bold text-on-surface">سلتك فارغة</h2>
                <p class="mb-6 text-on-surface-variant">أضف بعض الوجبات اللذيذة للبدء.</p>
                <a href="{{ route('menu.index') }}" class="inline-flex rounded-full bg-primary px-8 py-3 text-sm font-semibold text-on-primary shadow-md">
                    تصفح المنيو
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <div class="space-y-4 lg:col-span-2">
                    @foreach ($items as $item)
                        @php
                            $image = ($item['image'] ?? null) && Storage::disk('public')->exists($item['image'])
                                ? asset('storage/'.$item['image'])
                                : $logoUrl;
                        @endphp
                        <article class="flex flex-col gap-4 rounded-xl border border-surface-variant bg-surface-container-lowest p-4 shadow-sm sm:flex-row sm:items-center">
                            <img src="{{ $image }}" alt="{{ $item['name'] }}" class="h-24 w-24 shrink-0 rounded-lg object-cover bg-surface-container" width="96" height="96">

                            <div class="min-w-0 flex-1">
                                <h2 class="text-base font-bold text-on-surface">{{ $item['name'] }}</h2>
                                <p class="mt-1 text-sm text-on-surface-variant">{{ Money::format($item['price'], $currency) }}</p>
                                @if (! empty($item['note']))
                                    <p class="mt-1 text-sm text-on-surface-variant">ملاحظة: {{ $item['note'] }}</p>
                                @endif

                                <form method="POST" action="{{ route('cart.items.update', ['key' => $item['key']]) }}" class="mt-3 flex flex-wrap items-end gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-on-surface-variant" for="note-{{ $item['key'] }}">ملاحظة الصنف</label>
                                        <input
                                            id="note-{{ $item['key'] }}"
                                            type="text"
                                            name="note"
                                            value="{{ $item['note'] }}"
                                            maxlength="255"
                                            class="w-full min-w-45 rounded-lg border border-outline-variant bg-surface px-3 py-2 text-sm"
                                            placeholder="مثلاً: بدون بصل"
                                        >
                                    </div>
                                    <button type="submit" class="rounded-lg bg-surface-container-high px-3 py-2 text-xs font-semibold text-on-surface">حفظ الملاحظة</button>
                                </form>
                            </div>

                            <div class="flex flex-col items-stretch gap-3 sm:items-end">
                                <form method="POST" action="{{ route('cart.items.update', ['key' => $item['key']]) }}" class="flex items-center gap-2 rounded-full bg-surface-container px-2 py-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="note" value="{{ $item['note'] }}">
                                    <button
                                        type="submit"
                                        name="quantity"
                                        value="{{ max(1, ((int) $item['quantity']) - 1) }}"
                                        class="flex h-9 w-9 items-center justify-center rounded-full text-primary hover:bg-primary-container hover:text-on-primary-container"
                                        aria-label="تقليل الكمية"
                                        @disabled((int) $item['quantity'] <= 1)
                                    >
                                        <span class="material-symbols-outlined text-sm" aria-hidden="true">remove</span>
                                    </button>
                                    <span class="w-8 text-center text-sm font-bold">{{ $item['quantity'] }}</span>
                                    <button
                                        type="submit"
                                        name="quantity"
                                        value="{{ min(99, ((int) $item['quantity']) + 1) }}"
                                        class="flex h-9 w-9 items-center justify-center rounded-full text-primary hover:bg-primary-container hover:text-on-primary-container"
                                        aria-label="زيادة الكمية"
                                        @disabled((int) $item['quantity'] >= 99)
                                    >
                                        <span class="material-symbols-outlined text-sm" aria-hidden="true">add</span>
                                    </button>
                                </form>

                                <p class="text-sm font-bold text-primary">{{ Money::format($item['subtotal'], $currency) }}</p>

                                <form method="POST" action="{{ route('cart.items.destroy', ['key' => $item['key']]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-semibold text-error hover:underline">حذف</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <aside class="space-y-4">
                    <div class="rounded-xl border border-surface-variant bg-surface-container-lowest p-6 shadow-sm">
                        <h2 class="mb-4 border-b border-surface-variant pb-3 text-xl font-bold">ملخص الطلب</h2>
                        <div class="mb-3 flex justify-between text-sm">
                            <span class="text-on-surface-variant">المجموع الفرعي</span>
                            <span class="font-bold">{{ Money::format($subtotal, $currency) }}</span>
                        </div>
                        <div class="mb-6 flex justify-between border-t border-surface-variant pt-3 text-lg font-bold text-primary">
                            <span>الإجمالي</span>
                            <span>{{ Money::format($subtotal, $currency) }}</span>
                        </div>
                        <a href="{{ route('checkout.index') }}" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#25D366] py-4 text-sm font-semibold text-white shadow-md transition-transform hover:bg-[#1DA851] hover:scale-[1.02] active:scale-95">
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
