@props([
    'categories',
    'selectedSlug' => '',
])

<section id="menu-categories" class="mb-10 md:mb-12" aria-label="تصنيفات القائمة">
    <div class="hide-scrollbar flex gap-3 overflow-x-auto pb-4">
        <a
            href="{{ route('menu.index') }}"
            @class([
                'whitespace-nowrap rounded-full px-6 py-2 text-sm font-semibold transition-transform active:scale-95',
                'bg-primary text-on-primary shadow-sm' => $selectedSlug === '',
                'bg-surface-container text-on-surface-variant hover:bg-surface-variant' => $selectedSlug !== '',
            ])
        >
            الكل
        </a>

        @foreach ($categories as $category)
            <a
                href="{{ route('menu.index', ['category' => $category->slug]) }}"
                @class([
                    'whitespace-nowrap rounded-full px-6 py-2 text-sm font-semibold transition-colors',
                    'bg-primary text-on-primary shadow-sm' => $selectedSlug === $category->slug,
                    'bg-surface-container text-on-surface-variant hover:bg-surface-variant' => $selectedSlug !== $category->slug,
                ])
            >
                {{ $category->name }}
            </a>
        @endforeach
    </div>
</section>
