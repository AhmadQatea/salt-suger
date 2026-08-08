@extends('layouts.admin')

@section('title', __('لوحة تحكم Salt&Suger'))

@section('content')
    <div class="admin-card">
        <h1>{{ __('لوحة تحكم Salt&Suger') }}</h1>
        <p class="subtitle">{{ __('مرحباً بك في لوحة الإدارة') }}</p>

        <div class="dashboard-stats" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.85rem;margin:1.25rem 0 1.5rem;">
            <a href="{{ route('admin.orders.index', ['date' => now()->toDateString()]) }}" class="admin-card" style="padding:1rem;text-decoration:none;color:inherit;margin:0;box-shadow:none;">
                <div style="color:var(--ss-muted);font-size:.85rem;">طلبات اليوم</div>
                <strong style="font-size:1.6rem;">{{ $orderSummary['today'] }}</strong>
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="admin-card" style="padding:1rem;text-decoration:none;color:inherit;margin:0;box-shadow:none;">
                <div style="color:var(--ss-muted);font-size:.85rem;">قيد التأكيد</div>
                <strong style="font-size:1.6rem;">{{ $orderSummary['pending'] }}</strong>
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'preparing']) }}" class="admin-card" style="padding:1rem;text-decoration:none;color:inherit;margin:0;box-shadow:none;">
                <div style="color:var(--ss-muted);font-size:.85rem;">قيد التحضير</div>
                <strong style="font-size:1.6rem;">{{ $orderSummary['preparing'] }}</strong>
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'completed', 'date' => now()->toDateString()]) }}" class="admin-card" style="padding:1rem;text-decoration:none;color:inherit;margin:0;box-shadow:none;">
                <div style="color:var(--ss-muted);font-size:.85rem;">مكتملة اليوم</div>
                <strong style="font-size:1.6rem;">{{ $orderSummary['completed_today'] }}</strong>
            </a>
        </div>

        <div class="page-toolbar">
            <div>
                <a href="{{ route('admin.orders.index') }}" class="btn-primary">الطلبات</a>
                <a href="{{ route('admin.categories.index') }}" class="btn-secondary" style="margin-inline-start:.5rem;">إدارة التصنيفات</a>
                <a href="{{ route('admin.products.index') }}" class="btn-ghost" style="margin-inline-start:.5rem;">إدارة الأصناف</a>
            </div>
        </div>

        <div class="admin-meta">
            <p>{{ __('الحساب المسجّل') }}: <strong>{{ auth()->user()->email }}</strong></p>
        </div>
    </div>
@endsection
