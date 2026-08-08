@props([
    'categories',
    'selectedSlug' => '',
])

<section id="menu-categories" class="mb-6 md:mb-8" aria-label="تصنيفات القائمة">
    <div class="hide-scrollbar -mx-4 flex gap-2 overflow-x-auto px-4 pb-1 md:mx-0 md:px-0">
        <a
            href="{{ route('menu.index') }}"
            data-fast-nav
            @class([
                'ss-chip shrink-0',
                'ss-chip-active' => $selectedSlug === '',
            ])
        >
            الكل
        </a>

        @foreach ($categories as $category)
            <a
                href="{{ route('menu.category', $category) }}"
                data-fast-nav
                @class([
                    'ss-chip shrink-0',
                    'ss-chip-active' => $selectedSlug === $category->slug,
                ])
            >
                {{ $category->name }}
            </a>
        @endforeach
    </div>
</section>
