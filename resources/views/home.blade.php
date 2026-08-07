@extends('layouts.app')

@section('title', config('app.name'))

@section('content')
    <main>
        <h1>{{ config('app.name') }}</h1>
        <p>{{ __('القائمة الرقمية') }}</p>
    </main>
@endsection
