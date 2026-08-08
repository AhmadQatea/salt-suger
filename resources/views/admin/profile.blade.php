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

            <x-password-input
                id="current_password"
                name="current_password"
                label="{{ __('كلمة المرور الحالية') }}"
                autocomplete="current-password"
            />
            @error('current_password')
                <p class="form-error">{{ $message }}</p>
            @enderror

            <x-password-input
                id="password"
                name="password"
                label="{{ __('كلمة المرور الجديدة') }}"
                autocomplete="new-password"
            />
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror

            <x-password-input
                id="password_confirmation"
                name="password_confirmation"
                label="{{ __('تأكيد كلمة المرور الجديدة') }}"
                autocomplete="new-password"
            />

            <button type="submit" class="btn-primary">{{ __('حفظ التغييرات') }}</button>
        </form>
    </div>
@endsection
