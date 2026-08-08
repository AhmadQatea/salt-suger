@extends('layouts.public')

@php
    $restaurantName = $settings->restaurant_name ?: config('app.name');
@endphp

@section('content')
    <x-menu.header :settings="$settings" :logo-url="$logoUrl" />

    <main class="mx-auto max-w-7xl px-4 py-5 md:px-8 md:py-8">
        <x-menu.hero
            :settings="$settings"
            :logo-url="$logoUrl"
            :hero-url="$heroUrl"
            :selected-category="$selectedCategory"
        />

        @if ($categories->isEmpty())
            <x-menu.empty-state message="لا توجد تصنيفات متاحة حالياً." />
        @else
            <x-menu.category-nav
                :categories="$categories"
                :selected-slug="$selectedSlug"
            />

            <h2 class="mb-4 text-lg font-bold text-on-surface md:text-xl">
                {{ $selectedCategory ? $selectedCategory->name : 'المنيو' }}
            </h2>

            @if ($selectedCategory && $selectedCategory->description)
                <p class="mb-5 max-w-3xl text-sm leading-relaxed text-on-surface-variant md:text-base">
                    {{ $selectedCategory->description }}
                </p>
            @endif

            @if ($products->isEmpty())
                <x-menu.empty-state
                    :message="$selectedCategory
                        ? 'لا توجد أصناف متاحة ضمن هذا التصنيف.'
                        : 'لا توجد أصناف متاحة حالياً.'"
                />
            @else
                <section
                    aria-label="قائمة الأصناف"
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3 xl:grid-cols-4"
                >
                    @foreach ($products as $product)
                        <x-menu.product-card
                            :product="$product"
                            :currency="$currency"
                            :fallback-image="$logoUrl"
                            :restaurant-name="$restaurantName"
                        />
                    @endforeach
                </section>
            @endif
        @endif
    </main>

    <x-menu.footer :settings="$settings" :categories="$categories" />

    <x-menu.sticky-cart />
    <x-menu.bottom-nav />
    <x-menu.product-modal :currency="$currency" :fallback-image="$logoUrl" />
@endsection
