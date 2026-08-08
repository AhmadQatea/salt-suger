@extends('layouts.public')

@php
    $restaurantName = $settings->restaurant_name ?: config('app.name');
@endphp

@section('title', $restaurantName.' | المنيو')
@section('meta_description', $settings->description ?: ('القائمة الرقمية لمطعم '.$restaurantName))

@section('content')
    <x-menu.header :settings="$settings" :logo-url="$logoUrl" />

    <main class="container mx-auto max-w-[1280px] px-4 py-8 md:px-12">
        <x-menu.hero :settings="$settings" :logo-url="$logoUrl" />

        @if ($categories->isEmpty())
            <x-menu.empty-state message="لا توجد تصنيفات متاحة حالياً." />
        @else
            <x-menu.category-nav
                :categories="$categories"
                :selected-slug="$selectedSlug"
            />

            @if ($products->isEmpty())
                <x-menu.empty-state
                    :message="$selectedCategory
                        ? 'لا توجد أصناف متاحة ضمن هذا التصنيف.'
                        : 'لا توجد أصناف متاحة حالياً.'"
                />
            @else
                <section
                    aria-label="قائمة الأصناف"
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    @foreach ($products as $product)
                        <x-menu.product-card
                            :product="$product"
                            :currency="$currency"
                            :fallback-image="$logoUrl"
                        />
                    @endforeach
                </section>
            @endif
        @endif
    </main>

    <x-menu.sticky-cart />
    <x-menu.bottom-nav />
    <x-menu.product-modal :currency="$currency" :fallback-image="$logoUrl" />
@endsection
