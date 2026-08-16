@props([
    'categories',
    'selectedSlug' => '',
    'menuIndexRoute' => 'menu.index',
    'menuCategoryRoute' => 'menu.category',
])

<section
    id="menu-categories"
    class="sticky top-12 z-30 -mx-3 mb-3 border-b border-outline-variant/30 bg-background/95 px-3 py-2 backdrop-blur-md sm:top-14 sm:-mx-4 sm:mb-4 sm:px-4 md:static md:mx-0 md:mb-8 md:border-0 md:bg-transparent md:px-0 md:py-0 md:backdrop-blur-none"
    aria-label="تصنيفات القائمة"
>
    <div class="hide-scrollbar flex gap-2 overflow-x-auto whitespace-nowrap pb-0.5 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        <a
            href="{{ route($menuIndexRoute) }}"
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
                href="{{ route($menuCategoryRoute, $category) }}"
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
