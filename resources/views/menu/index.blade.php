@extends('layouts.public')

@php
    $restaurantName = $settings->restaurant_name ?: config('app.name');
@endphp

@push('head')
    @if (! empty($heroUrl))
        <link rel="preload" as="image" href="{{ $heroUrl }}" fetchpriority="high">
    @endif
@endpush

@section('content')
    <x-menu.header :settings="$settings" :logo-url="$logoUrl" />

    <main class="mx-auto max-w-7xl px-3 py-3 sm:px-4 sm:py-5 md:px-8 md:py-8">
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

            <h2 class="mb-3 text-base font-bold text-on-surface sm:mb-4 sm:text-lg md:text-xl">
                {{ $selectedCategory ? $selectedCategory->name : 'المنيو' }}
            </h2>

            @if ($selectedCategory && $selectedCategory->description)
                <p class="mb-3 max-w-3xl text-xs leading-relaxed text-on-surface-variant sm:mb-5 sm:text-sm md:text-base">
                    {{ $selectedCategory->description }}
                </p>
            @elseif (! $selectedCategory)
                <p class="mb-3 max-w-3xl text-xs leading-relaxed text-on-surface-variant sm:mb-5 sm:text-sm md:text-base">
                    برجر ووجبات سريعة بنكهات حلوة ومالحة — اختر من منيو {{ $restaurantName }} واطلب بسهولة.
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
                    class="grid grid-cols-2 gap-2 sm:gap-3 md:grid-cols-2 md:gap-5 lg:grid-cols-3 xl:grid-cols-4"
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
