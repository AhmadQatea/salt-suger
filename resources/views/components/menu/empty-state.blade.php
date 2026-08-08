@props([
    'message',
])

<div class="rounded-2xl bg-surface-container-lowest px-6 py-12 text-center ring-1 ring-outline-variant/30" role="status">
    <span class="material-symbols-outlined mb-3 text-4xl text-on-surface-variant" aria-hidden="true">restaurant_menu</span>
    <p class="text-sm font-medium text-on-surface-variant md:text-base">{{ $message }}</p>
</div>
