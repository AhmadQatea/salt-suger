@extends('layouts.app')

@section('title', __('لوحة التحكم') . ' — ' . config('app.name'))

@section('content')
    <main>
        <h1>{{ __('لوحة التحكم') }}</h1>
        <p>{{ config('app.name') }}</p>
    </main>
@endsection
