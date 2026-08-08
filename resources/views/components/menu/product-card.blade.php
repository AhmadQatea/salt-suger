@props([
    'product',
    'currency',
    'fallbackImage',
])

@php
    use App\Support\Money;
    $imageUrl = $product->imageUrl() ?: $fallbackImage;
    $formattedPrice = Money::format($product->price, $currency);
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
    class="card-shadow card-hover flex flex-col overflow-hidden rounded-xl bg-surface-container-lowest transition-all duration-300 dark:bg-surface-container"
>
    <div class="relative h-48 w-full bg-surface-container">
        <button
            type="button"
            data-open-product
            class="block h-full w-full"
            aria-label="عرض تفاصيل {{ $product->name }}"
        >
            <img
                src="{{ $imageUrl }}"
                alt="{{ $product->name }}"
                class="h-full w-full object-cover"
                loading="lazy"
                width="400"
                height="192"
            >
        </button>

        @if ($product->badge)
            <span class="absolute top-3 right-3 rounded-md bg-tertiary-container px-2 py-1 text-xs font-semibold text-on-tertiary-container shadow-sm">
                {{ $product->badge }}
            </span>
        @endif
    </div>

    <div class="flex grow flex-col p-5">
        <h3 class="mb-1 text-xl font-semibold text-on-surface">{{ $product->name }}</h3>
        @if ($product->description)
            <p class="mb-4 grow text-sm leading-relaxed text-on-surface-variant line-clamp-2">
                {{ $product->description }}
            </p>
        @else
            <div class="mb-4 grow"></div>
        @endif

        <div class="mt-auto flex items-center justify-between gap-3">
            <span class="text-lg font-bold text-primary">{{ $formattedPrice }}</span>
            <button
                type="button"
                data-open-product
                class="rounded-full bg-surface-container-high p-2 text-primary transition-colors hover:bg-primary hover:text-on-primary dark:bg-surface-container-highest dark:hover:bg-primary-container dark:hover:text-on-primary-container"
                aria-label="أضف إلى الطلب: {{ $product->name }}"
            >
                <span class="material-symbols-outlined" aria-hidden="true">add</span>
            </button>
        </div>
    </div>
</article>
