@props([
    'product',
    'currency',
    'fallbackImage',
    'restaurantName' => null,
    'orderingEnabled' => true,
])

@php
    use App\Support\Money;
    $imageUrl = $product->imageUrl() ?: $fallbackImage;
    $formattedPrice = Money::format($product->price, $currency);
    $brand = $restaurantName ?: config('app.name');
    $imageAlt = $product->name.' من '.$brand;
@endphp

<article
    @if ($orderingEnabled)
        data-product-card
        data-product-id="{{ $product->id }}"
        data-product-name="{{ $product->name }}"
        data-product-description="{{ $product->description }}"
        data-product-price="{{ $product->price }}"
        data-product-badge="{{ $product->badge }}"
        data-product-image="{{ $imageUrl }}"
        data-product-fallback="{{ $fallbackImage }}"
        data-product-currency="{{ $currency }}"
    @endif
    class="menu-product-card group flex flex-col overflow-hidden rounded-lg bg-surface-container-lowest ring-1 ring-outline-variant/25 transition duration-200 hover:ring-primary/30 dark:bg-surface-container md:rounded-2xl"
>
    <div class="menu-product-card__media relative w-full overflow-hidden bg-surface-container">
        @if ($orderingEnabled)
        <button
            type="button"
            data-open-product
            class="block h-full w-full"
            aria-label="عرض تفاصيل {{ $product->name }}"
        >
        @else
        <div class="h-full w-full">
        @endif
            <img
                src="{{ $imageUrl }}"
                alt="{{ $imageAlt }}"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                loading="lazy"
                decoding="async"
                width="400"
                height="400"
            >
        @if ($orderingEnabled)
        </button>
        @else
        </div>
        @endif

        @if ($product->badge)
            <span class="absolute top-1 right-1 max-w-[85%] truncate rounded md:rounded-md bg-tertiary-container px-1 py-0.5 text-[9px] font-semibold text-on-tertiary-container md:top-3 md:right-3 md:px-2 md:py-1 md:text-[11px]">
                {{ $product->badge }}
            </span>
        @endif
    </div>

    <div class="flex grow flex-col gap-1 p-1.5 sm:gap-2 sm:p-3 md:gap-3 md:p-4">
        <div class="min-h-0">
            <h3 class="text-[11px] font-semibold leading-snug text-on-surface line-clamp-2 sm:text-sm md:text-base">
                {{ $product->name }}
            </h3>
            @if ($product->description)
                <p @class([
                    'mt-0.5 text-xs leading-relaxed text-on-surface-variant line-clamp-2 md:text-sm',
                    'hidden sm:block' => $orderingEnabled,
                ])>
                    {{ $product->description }}
                </p>
            @endif
        </div>

        <div class="mt-auto flex items-center justify-between gap-1 pt-0.5">
            <span class="min-w-0 truncate text-[11px] font-bold text-primary tabular-nums sm:text-sm md:text-base">
                {{ $formattedPrice }}
            </span>
            @if ($orderingEnabled)
            <button
                type="button"
                data-open-product
                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-on-primary transition hover:bg-primary-container active:scale-95 sm:h-9 sm:w-9 md:h-11 md:w-11"
                aria-label="أضف إلى الطلب: {{ $product->name }}"
            >
                <span class="material-symbols-outlined text-[16px] sm:text-[20px] md:text-[22px]" aria-hidden="true">add</span>
            </button>
            @endif
        </div>
    </div>
</article>
