@extends('layouts.admin')

@section('title', __('لوحة تحكم Salt&Suger'))

@section('content')
    <div class="admin-card">
        <h1>{{ __('لوحة تحكم Salt&Suger') }}</h1>
        <p class="subtitle">{{ __('مرحباً بك في لوحة الإدارة') }}</p>

        <div class="dashboard-stats mb-5 mt-4 grid grid-cols-2 gap-2.5 sm:gap-3 lg:grid-cols-4">
            <a href="{{ route('admin.orders.index', ['date' => now()->toDateString()]) }}" class="dashboard-stat-card">
                <div class="dashboard-stat-card__label">طلبات اليوم</div>
                <strong class="dashboard-stat-card__value">{{ $orderSummary['today'] }}</strong>
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="dashboard-stat-card">
                <div class="dashboard-stat-card__label">قيد التأكيد</div>
                <strong class="dashboard-stat-card__value">{{ $orderSummary['pending'] }}</strong>
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'preparing']) }}" class="dashboard-stat-card">
                <div class="dashboard-stat-card__label">قيد التحضير</div>
                <strong class="dashboard-stat-card__value">{{ $orderSummary['preparing'] }}</strong>
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'completed', 'date' => now()->toDateString()]) }}" class="dashboard-stat-card">
                <div class="dashboard-stat-card__label">مكتملة اليوم</div>
                <strong class="dashboard-stat-card__value">{{ $orderSummary['completed_today'] }}</strong>
            </a>
        </div>

        <div class="page-toolbar">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.orders.index') }}" class="btn-primary">الطلبات</a>
                <a href="{{ route('admin.categories.index') }}" class="btn-secondary">إدارة التصنيفات</a>
                <a href="{{ route('admin.products.index') }}" class="btn-ghost">إدارة الأصناف</a>
            </div>
        </div>

        <div class="admin-meta">
            <p>{{ __('الحساب المسجّل') }}: <strong>{{ auth()->user()->email }}</strong></p>
        </div>
    </div>
@endsection
