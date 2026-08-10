@props([
    'settings',
    'logoUrl',
    'heroUrl' => null,
    'selectedCategory' => null,
])

@php
    $restaurantName = $settings->restaurant_name ?: config('app.name');
    $description = $settings->description
        ?: ($restaurantName.' مطعم حلو ومالح ووجبات سريعة في إدلب — برجر وساندويشات بنكهات خاصة.');
    $image = $heroUrl ?: $logoUrl;
    $heading = $selectedCategory
        ? $selectedCategory->name.' من '.$restaurantName
        : 'أهلاً بك في '.$restaurantName;
    $alt = $selectedCategory
        ? $selectedCategory->name.' من '.$restaurantName
        : 'Salt&Suger مطعم حلو ومالح ووجبات سريعة في إدلب';
@endphp

<section class="menu-hero relative mb-5 overflow-hidden rounded-xl md:mb-10 md:rounded-2xl" aria-label="الغلاف الرئيسي">
    <div class="menu-hero__media relative">
        <img
            src="{{ $image }}"
            alt="{{ $alt }}"
            class="h-full w-full object-cover object-center"
            width="1280"
            height="420"
            fetchpriority="high"
            loading="eager"
            decoding="async"
        >
        <div class="absolute inset-0 bg-linear-to-t from-black/75 via-black/35 to-black/10"></div>
    </div>

    <div class="absolute inset-0 flex flex-col justify-end p-3.5 sm:p-5 md:p-8 lg:p-10">
        <p class="mb-0.5 text-[11px] font-medium text-white/80 sm:mb-1 sm:text-sm">Salt&Suger — حلو ومالح</p>
        <h1 class="mb-1 max-w-xl text-xl font-bold leading-tight text-white sm:mb-2 sm:text-2xl md:text-4xl">
            {{ $heading }}
        </h1>
        <p class="mb-3 max-w-md text-xs leading-relaxed text-white/85 line-clamp-2 sm:mb-5 sm:text-sm md:line-clamp-none md:text-base">
            {{ $selectedCategory?->description ?: $description }}
        </p>
        <a href="#menu-categories" class="ss-btn ss-btn-primary self-start px-3 py-2 text-xs sm:px-4 sm:py-2.5 sm:text-sm">
            استعرض المنيو
            <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_downward</span>
        </a>
    </div>
</section>
