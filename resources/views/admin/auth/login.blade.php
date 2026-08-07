@extends('layouts.app')

@section('title', __('تسجيل الدخول') . ' — ' . config('app.name'))

@section('content')
    <main>
        <h1>{{ __('تسجيل الدخول') }}</h1>
        <p>{{ __('لوحة إدارة المطعم') }}</p>
    </main>
@endsection
