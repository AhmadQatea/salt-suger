@extends('layouts.admin')

@php
    use App\Support\Money;
    $isTerminal = in_array($order->status->value, ['completed', 'cancelled'], true);
    $allowedValues = collect($allowedTransitions)->map->value->all();
@endphp

@section('title', 'تفاصيل الطلب — '.$order->order_number)

@section('content')
    <div class="admin-card">
        <div class="page-toolbar">
            <div>
                <h1>تفاصيل الطلب</h1>
                <p class="subtitle">{{ $order->order_number }}</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="btn-ghost">العودة إلى الطلبات</a>
        </div>

        <div class="detail-grid">
            <div>
                <section class="detail-block mb-4">
                    <h2>معلومات الطلب</h2>
                    <div class="detail-row">
                        <span>رقم الطلب</span>
                        <strong class="rtl">{{ $order->order_number }}</strong>
                    </div>
                    <div class="detail-row">
                        <span>تاريخ الإنشاء</span>
                        <strong>{{ $order->created_at?->timezone(config('app.timezone'))->format('d/m/Y h:i A') }}</strong>
                    </div>
                    <div class="detail-row">
                        <span>الحالة</span>
                        <strong class="rtl">
                            <span class="badge-order {{ $order->status->badgeModifier() }}">
                                {{ $order->status->label() }}
                            </span>
                        </strong>
                    </div>
                    <div class="detail-row">
                        <span>واتساب</span>
                        <strong class="rtl">
                            @if ($order->whatsapp_sent_at)
                                تم التحويل إلى واتساب:
                                {{ $order->whatsapp_sent_at->timezone(config('app.timezone'))->format('d/m/Y h:i A') }}
                            @else
                                لم يتم التحويل إلى واتساب
                            @endif
                        </strong>
                    </div>
                </section>

                @if (filled($order->customer_name) || filled($order->customer_phone))
                    <section class="detail-block mb-4">
                        <h2>بيانات الزبون (طلبات سابقة)</h2>
                        @if (filled($order->customer_name))
                            <div class="detail-row">
                                <span>الاسم</span>
                                <strong class="rtl">{{ $order->customer_name }}</strong>
                            </div>
                        @endif
                        @if (filled($order->customer_phone))
                            <div class="detail-row">
                                <span>رقم الواتساب</span>
                                <strong>
                                    <a href="https://wa.me/{{ ltrim($order->customer_phone, '+') }}" dir="ltr" class="text-primary hover:underline">
                                        {{ $order->customer_phone }}
                                    </a>
                                </strong>
                            </div>
                        @endif
                    </section>
                @endif

                <section class="detail-block mb-4">
                    <h2>تفاصيل الأصناف</h2>
                    <div class="admin-table-wrap">
                        <table class="admin-table min-w-0">
                            <thead>
                                <tr>
                                    <th>الصنف</th>
                                    <th>الكمية</th>
                                    <th>السعر</th>
                                    <th>المجموع</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>
                                            <strong class="text-on-surface">{{ $item->product_name }}</strong>
                                            @if ($item->note)
                                                <div class="mt-1 text-sm text-on-surface-variant">
                                                    ملاحظة: {{ $item->note }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ Money::format($item->product_price, $order->currency) }}</td>
                                        <td>{{ Money::format($item->subtotal, $order->currency) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                @if ($order->notes)
                    <section class="detail-block mb-4">
                        <h2>ملاحظات الطلب</h2>
                        <p class="m-0 leading-relaxed text-on-surface">{{ $order->notes }}</p>
                    </section>
                @endif

                <section class="detail-block">
                    <h2>الملخص المالي</h2>
                    <div class="detail-row">
                        <span>المجموع الفرعي</span>
                        <strong class="rtl">{{ Money::format($order->subtotal, $order->currency) }}</strong>
                    </div>
                    <div class="detail-row">
                        <span>الإجمالي</span>
                        <strong class="rtl">{{ Money::format($order->total, $order->currency) }}</strong>
                    </div>
                    <div class="detail-row">
                        <span>العملة</span>
                        <strong class="rtl">{{ $order->currency }}</strong>
                    </div>
                </section>
            </div>

            <aside>
                <section class="detail-block">
                    <h2>حالة الطلب</h2>

                    @if ($isTerminal)
                        <p class="mb-4 leading-relaxed text-on-surface-variant">
                            هذا الطلب في حالة نهائية ولا يمكن تغيير حالته.
                        </p>
                        <span class="badge-order {{ $order->status->badgeModifier() }}">
                            {{ $order->status->label() }}
                        </span>
                    @else
                        <form method="POST" action="{{ route('admin.orders.status.update', $order) }}" class="admin-form max-w-none" >
                            @csrf
                            @method('PATCH')

                            <div>
                                <label for="status">حالة الطلب</label>
                                <select id="status" name="status" required>
                                    <option value="{{ $order->status->value }}" selected>
                                        {{ $order->status->label() }} (الحالية)
                                    </option>
                                    @foreach ($statusOptions as $value => $label)
                                        @if (in_array($value, $allowedValues, true))
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('status')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" class="btn-primary">تحديث الحالة</button>
                        </form>
                    @endif
                </section>
            </aside>
        </div>
    </div>
@endsection
