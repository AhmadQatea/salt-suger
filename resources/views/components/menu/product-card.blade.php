@props([
    'product',
    'currency',
    'fallbackImage',
    'restaurantName' => null,
])

@php
    use App\Support\Money;
    $imageUrl = $product->imageUrl() ?: $fallbackImage;
    $formattedPrice = Money::format($product->price, $currency);
    $brand = $restaurantName ?: config('app.name');
    $imageAlt = $product->name.' من '.$brand;
@endphp

<article
    data-product-card
    data-product-id="{{ $product->id }}"
    data-product-name="{{ $product->name }}"
    data-product-description="{{ $product->description }}"
    data-product-price="{{ $product->price }}"
    data-product-badge="{{ $product->badge }}"
    data-product-image="{{ $imageUrl }}"
    data-product-fallback="{{ $fallbackImage }}"
    data-product-currency="{{ $currency }}"
    class="menu-product-card group flex flex-col overflow-hidden rounded-2xl bg-surface-container-lowest ring-1 ring-outline-variant/25 transition duration-200 hover:ring-primary/30 dark:bg-surface-container"
>
    <div class="relative aspect-square w-full overflow-hidden bg-surface-container">
        <button
            type="button"
            data-open-product
            class="block h-full w-full"
            aria-label="عرض تفاصيل {{ $product->name }}"
        >
            <img
                src="{{ $imageUrl }}"
                alt="{{ $imageAlt }}"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                loading="lazy"
                decoding="async"
                width="400"
                height="400"
            >
        </button>

        @if ($product->badge)
            <span class="absolute top-3 right-3 rounded-md bg-tertiary-container px-2 py-1 text-[11px] font-semibold text-on-tertiary-container">
                {{ $product->badge }}
            </span>
        @endif
    </div>

    <div class="flex grow flex-col gap-3 p-4">
        <div class="min-h-16">
            <h3 class="text-base font-semibold leading-snug text-on-surface line-clamp-1">{{ $product->name }}</h3>
            @if ($product->description)
                <p class="mt-1 text-sm leading-relaxed text-on-surface-variant line-clamp-2">
                    {{ $product->description }}
                </p>
            @endif
        </div>

        <div class="mt-auto flex items-center justify-between gap-3">
            <span class="text-base font-bold text-primary">{{ $formattedPrice }}</span>
            <button
                type="button"
                data-open-product
                class="flex h-11 w-11 items-center justify-center rounded-full bg-primary text-on-primary transition hover:bg-primary-container active:scale-95"
                aria-label="أضف إلى الطلب: {{ $product->name }}"
            >
                <span class="material-symbols-outlined text-[22px]" aria-hidden="true">add</span>
            </button>
        </div>
    </div>
</article>
