@extends('layouts.admin')

@php
    use App\Support\Money;
@endphp

@section('title', 'الطلبات — '.config('app.name'))

@section('content')
    <div class="admin-card">
        <div class="page-toolbar">
            <div>
                <h1>الطلبات</h1>
                <p class="subtitle">متابعة طلبات العملاء وتحديث حالتها</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.orders.index') }}" class="filters">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="بحث برقم الطلب"
            >

            <select name="status">
                <option value="">كل الحالات</option>
                @foreach ($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <input
                type="date"
                name="date"
                value="{{ $date }}"
                aria-label="التاريخ"
            >

            <button type="submit" class="btn-secondary">بحث</button>
            @if ($hasFilters)
                <a href="{{ route('admin.orders.index') }}" class="btn-ghost">إعادة ضبط</a>
            @endif
        </form>

        @if ($orders->isEmpty())
            <div class="empty-state">
                @if ($hasFilters)
                    <p>لا توجد نتائج مطابقة لبحثك.</p>
                    <a href="{{ route('admin.orders.index') }}" class="btn-ghost mt-4">إعادة ضبط الفلاتر</a>
                @else
                    <p>لا توجد طلبات حالياً.</p>
                @endif
            </div>
        @else
            <div class="admin-table-wrap orders-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>المنتجات</th>
                            <th>الإجمالي</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th>واتساب</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr class="hover:bg-surface-container-low/80">
                                <td><strong class="text-on-surface">{{ $order->order_number }}</strong></td>
                                <td>{{ $order->items_count }} منتجات</td>
                                <td>{{ Money::format($order->total, $order->currency) }}</td>
                                <td>
                                    <span class="badge-order {{ $order->status->badgeModifier() }}">
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td>{{ $order->created_at?->timezone(config('app.timezone'))->format('d/m/Y h:i A') }}</td>
                                <td>
                                    @if ($order->whatsapp_sent_at)
                                        {{ $order->whatsapp_sent_at->timezone(config('app.timezone'))->format('d/m/Y h:i A') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn-ghost btn-sm">عرض الطلب</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="order-cards">
                @foreach ($orders as $order)
                    <article class="order-card">
                        <div class="order-card-top">
                            <strong>{{ $order->order_number }}</strong>
                            <span class="badge-order {{ $order->status->badgeModifier() }}">
                                {{ $order->status->label() }}
                            </span>
                        </div>
                        <div class="order-card-meta">
                            <div>{{ $order->items_count }} منتجات · {{ Money::format($order->total, $order->currency) }}</div>
                            <div>{{ $order->created_at?->timezone(config('app.timezone'))->format('d/m/Y h:i A') }}</div>
                            <div>
                                واتساب:
                                <strong class="rtl">
                                    @if ($order->whatsapp_sent_at)
                                        تم التحويل {{ $order->whatsapp_sent_at->timezone(config('app.timezone'))->format('d/m/Y h:i A') }}
                                    @else
                                        لم يتم التحويل
                                    @endif
                                </strong>
                            </div>
                        </div>
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn-primary w-full">عرض الطلب</a>
                    </article>
                @endforeach
            </div>

            <div class="pagination-wrap">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
@endsection
