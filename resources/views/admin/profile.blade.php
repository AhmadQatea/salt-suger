@extends('layouts.admin')

@section('title', __('إعدادات الحساب') . ' — ' . config('app.name'))

@section('content')
    <div class="admin-card">
        <h1>{{ __('إعدادات الحساب') }}</h1>
        <p class="subtitle">{{ auth()->user()->email }}</p>

        <form method="POST" action="{{ route('admin.profile.update') }}" class="admin-form" novalidate>
            @csrf
            @method('PUT')

            <div>
                <label for="email">{{ __('البريد الإلكتروني') }}</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    required
                    autocomplete="username"
                >
                @error('email')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="current_password">{{ __('كلمة المرور الحالية') }}</label>
                <input
                    id="current_password"
                    type="password"
                    name="current_password"
                    autocomplete="current-password"
                >
                @error('current_password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password">{{ __('كلمة المرور الجديدة') }}</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    autocomplete="new-password"
                >
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation">{{ __('تأكيد كلمة المرور الجديدة') }}</label>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    autocomplete="new-password"
                >
            </div>

            <button type="submit" class="btn-primary">{{ __('حفظ التغييرات') }}</button>
        </form>
    </div>
@endsection
