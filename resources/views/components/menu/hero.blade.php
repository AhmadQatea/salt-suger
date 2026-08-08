@props([
    'settings',
    'logoUrl',
    'heroUrl' => null,
    'selectedCategory' => null,
])

@php
    $restaurantName = $settings->restaurant_name ?: config('app.name');
    $description = $settings->description
        ?: 'Salt&Suger مطعم وجبات سريعة في إدلب يقدم برجر وساندويشات بنكهات خاصة.';
    $image = $heroUrl ?: $logoUrl;
    $heading = $selectedCategory
        ? $selectedCategory->name.' من '.$restaurantName
        : 'أهلاً بك في '.$restaurantName;
    $alt = $selectedCategory
        ? 'تصنيف '.$selectedCategory->name.' في مطعم '.$restaurantName.' بإدلب'
        : 'واجهة مطعم '.$restaurantName.' للوجبات السريعة في إدلب';
@endphp

<section class="menu-hero relative mb-8 overflow-hidden rounded-2xl md:mb-10" aria-label="الغلاف الرئيسي">
    <div class="menu-hero__media relative">
        <img
            src="{{ $image }}"
            alt="{{ $alt }}"
            class="h-full w-full object-cover object-center"
            width="1280"
            height="420"
            fetchpriority="high"
            decoding="async"
        >
        <div class="absolute inset-0 bg-linear-to-t from-black/75 via-black/35 to-black/10"></div>
    </div>

    <div class="absolute inset-0 flex flex-col justify-end p-5 md:p-8 lg:p-10">
        <p class="mb-1 text-sm font-medium text-white/80">Salt&Suger — إدلب</p>
        <h1 class="mb-2 max-w-xl text-2xl font-bold leading-tight text-white md:text-4xl">
            {{ $heading }}
        </h1>
        <p class="mb-5 max-w-md text-sm leading-relaxed text-white/85 md:text-base">
            {{ $selectedCategory?->description ?: $description }}
        </p>
        <a href="#menu-categories" class="ss-btn ss-btn-primary self-start">
            استعرض المنيو
            <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_downward</span>
        </a>
    </div>
</section>
