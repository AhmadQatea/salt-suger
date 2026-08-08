@props([
    'settings',
    'categories' => collect(),
])

@php
    $restaurantName = $settings->restaurant_name ?: config('app.name');
@endphp

<footer class="border-t border-outline-variant/40 bg-surface-container-low">
    <div class="mx-auto max-w-7xl px-4 py-8 md:px-8 md:py-10">
        <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
            <div class="max-w-lg space-y-2">
                <p class="text-base font-bold text-on-surface">{{ $restaurantName }}</p>
                <p class="text-sm leading-relaxed text-on-surface-variant">
                    مطعم وجبات سريعة في إدلب، سوريا — برجر وساندويشات بنكهات خاصة، مع تشكيلة من الوجبات والمقبلات والمشروبات.
                </p>
            </div>

            <nav aria-label="روابط مفيدة" class="flex flex-wrap gap-x-5 gap-y-2 text-sm font-semibold">
                <a href="{{ route('home') }}" class="text-on-surface-variant transition hover:text-primary">الرئيسية</a>
                <a href="{{ route('menu.index') }}" class="text-on-surface-variant transition hover:text-primary">المنيو</a>
                @foreach ($categories->take(6) as $category)
                    <a
                        href="{{ route('menu.category', $category) }}"
                        class="text-on-surface-variant transition hover:text-primary"
                    >{{ $category->name }}</a>
                @endforeach
            </nav>
        </div>

        <p class="mt-6 text-xs text-on-surface-variant/80">
            © {{ date('Y') }} {{ $restaurantName }} — إدلب، سوريا
        </p>
    </div>
</footer>
