@extends('layouts.admin')

@section('title', __('تسجيل الدخول إلى لوحة التحكم') . ' — ' . config('app.name'))

@section('body')
    <div class="login-bg-pattern flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-md overflow-hidden rounded-xl border border-outline-variant bg-surface card-shadow">
            <div class="h-2 w-full bg-primary"></div>

            <div class="p-8">
                <div class="mb-8 text-center">
                    <div class="mx-auto mb-4 flex h-24 w-24 items-center justify-center overflow-hidden rounded-full bg-surface-container shadow-sm">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-full w-full object-cover">
                    </div>
                    <h1 class="mb-2 text-2xl font-bold text-on-surface">{{ config('app.name') }}</h1>
                    <p class="text-on-surface-variant">{{ __('تسجيل الدخول إلى لوحة التحكم') }}</p>
                </div>

                <div class="mb-4 flex justify-end">
                    <x-theme-toggle />
                </div>

                @if ($errors->any())
                    <div class="form-errors" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.store') }}" class="admin-form max-w-none space-y-1" novalidate>
                    @csrf

                    <div>
                        <label for="email">{{ __('البريد الإلكتروني') }}</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            dir="ltr"
                        >
                        @error('email')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-password-input
                        id="password"
                        name="password"
                        label="{{ __('كلمة المرور') }}"
                        autocomplete="current-password"
                        :required="true"
                    />
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror

                    <label class="admin-checkbox" for="remember">
                        <input id="remember" type="checkbox" name="remember" value="1" @checked(old('remember'))>
                        <span>{{ __('تذكرني') }}</span>
                    </label>

                    <button type="submit" class="btn-primary w-full">{{ __('تسجيل الدخول') }}</button>
                </form>
            </div>
        </div>
    </div>
@endsection
