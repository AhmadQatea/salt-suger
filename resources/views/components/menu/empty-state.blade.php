@props([
    'message',
])

<div class="rounded-xl border border-outline-variant/40 bg-surface-container-lowest px-6 py-16 text-center shadow-sm" role="status">
    <span class="material-symbols-outlined mb-3 text-4xl text-on-surface-variant" aria-hidden="true">restaurant_menu</span>
    <p class="text-base font-medium text-on-surface-variant">{{ $message }}</p>
</div>
