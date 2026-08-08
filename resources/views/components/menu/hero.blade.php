@props([
    'settings',
    'logoUrl',
])

@php
    $restaurantName = $settings->restaurant_name ?: config('app.name');
    $description = $settings->description ?: 'وجبتك المفضلة... بطلب أسهل وأسرع';
@endphp

<section class="group relative mb-10 overflow-hidden rounded-xl shadow-lg md:mb-12">
    <div
        class="h-80 w-full bg-cover bg-center transition-transform duration-700 group-hover:scale-105 md:h-100"
        style="background-image: linear-gradient(to top, rgba(0,0,0,.8), rgba(0,0,0,.4), transparent), url('{{ $logoUrl }}'); background-color:#1a0507;"
        role="img"
        aria-label="{{ $restaurantName }}"
    ></div>
    <div class="absolute inset-0 flex flex-col justify-end bg-linear-to-t from-black/80 via-black/40 to-transparent p-8">
        <h1 class="mb-2 text-3xl font-bold text-white md:text-5xl">
            أهلاً بك في {{ $restaurantName }}
        </h1>
        <p class="mb-6 max-w-lg text-base text-gray-200 md:text-lg">
            {{ $description }}
        </p>
        <a
            href="#menu-categories"
            class="inline-flex items-center gap-2 self-start rounded-full bg-primary px-8 py-3 text-sm font-semibold text-on-primary shadow-md transition-all hover:bg-primary-container active:scale-95"
        >
            <span>استعرض القائمة</span>
            <span class="material-symbols-outlined text-sm" aria-hidden="true">arrow_forward</span>
        </a>
    </div>
</section>
