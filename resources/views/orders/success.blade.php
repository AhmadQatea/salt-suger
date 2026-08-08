@extends('layouts.public')

@php
    use App\Support\Money;
    $restaurantName = $settings->restaurant_name ?: config('app.name');
    $seoTitle = 'تم حفظ طلبك — '.$restaurantName;
    $seoDescription = 'تأكيد طلبك من '.$restaurantName.'. هذه الصفحة غير مخصصة لمحركات البحث.';
    $seoRobots = 'noindex,nofollow';
@endphp

@section('content')
    <x-menu.header :settings="$settings" :logo-url="$logoUrl" />

    <main class="mx-auto max-w-180 px-4 md:px-12 py-8 pb-28 md:pb-8">
        <div class="rounded-xl border border-surface-variant bg-surface-container-lowest p-6 shadow-sm md:p-8">
            <div class="mb-6 text-center">
                <span class="material-symbols-outlined mb-3 text-5xl text-primary" aria-hidden="true">check_circle</span>

                @if ($whatsappFallback)
                    <h1 class="mb-2 text-2xl font-bold text-on-surface md:text-3xl">تم حفظ طلبك بنجاح</h1>
                    <p class="text-on-surface-variant">تعذر فتح واتساب حالياً.</p>
                    <p class="mt-2 text-sm text-on-surface-variant">
                        تم حفظ طلبك بنجاح، ولكن تعذر فتح واتساب حالياً. يرجى التواصل مع المطعم مع ذكر رقم الطلب إن لزم الأمر.
                    </p>
                @else
                    <h1 class="mb-2 text-2xl font-bold text-on-surface md:text-3xl">تم استلام طلبك بنجاح</h1>
                    <p class="text-on-surface-variant">شكراً لطلبك من {{ $restaurantName }}</p>
                @endif
            </div>

            <div class="mb-6 rounded-xl bg-surface-container-low p-4 text-sm">
                <p class="mb-2"><span class="text-on-surface-variant">رقم الطلب:</span> <strong>{{ $order->order_number }}</strong></p>
                @if ($order->customer_name)
                    <p class="mb-2"><span class="text-on-surface-variant">الاسم:</span> <strong>{{ $order->customer_name }}</strong></p>
                @endif
                @if ($order->customer_phone)
                    <p class="mb-2"><span class="text-on-surface-variant">رقم الواتساب:</span> <strong dir="ltr">{{ $order->customer_phone }}</strong></p>
                @endif
                <p><span class="text-on-surface-variant">الإجمالي:</span> <strong>{{ Money::format($order->total, $currency) }}</strong></p>
            </div>

            <ul class="mb-6 space-y-3">
                @foreach ($order->items as $item)
                    <li class="flex items-start justify-between gap-3 border-b border-surface-variant/60 pb-3 text-sm">
                        <div>
                            <p class="font-semibold">{{ $item->product_name }} × {{ $item->quantity }}</p>
                            @if ($item->note)
                                <p class="text-on-surface-variant">{{ $item->note }}</p>
                            @endif
                        </div>
                        <span class="font-bold text-primary">{{ Money::format($item->subtotal, $currency) }}</span>
                    </li>
                @endforeach
            </ul>

            @if ($order->notes)
                <p class="mb-6 rounded-lg bg-surface-container p-3 text-sm text-on-surface-variant">
                    ملاحظات الطلب: {{ $order->notes }}
                </p>
            @endif

            <div class="mb-8 flex justify-between border-t border-surface-variant pt-4 text-lg font-bold text-primary">
                <span>الإجمالي</span>
                <span>{{ Money::format($order->total, $currency) }}</span>
            </div>

            <a href="{{ route('menu.index') }}" class="flex w-full items-center justify-center rounded-xl bg-primary py-4 text-sm font-semibold text-on-primary shadow-md">
                العودة إلى المنيو
            </a>
        </div>
    </main>

    <x-menu.bottom-nav />
@endsection
