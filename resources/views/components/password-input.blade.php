@props([
    'id',
    'name',
    'label',
    'autocomplete' => 'current-password',
    'required' => false,
    'value' => null,
    'autofocus' => false,
])

@php
    $inputId = $id ?: $name;
@endphp

<div {{ $attributes->class([]) }}>
    <label for="{{ $inputId }}">{{ $label }}</label>
    <div class="password-field">
        <input
            id="{{ $inputId }}"
            type="password"
            name="{{ $name }}"
            @if ($value !== null) value="{{ $value }}" @endif
            @required($required)
            @autofocus($autofocus)
            autocomplete="{{ $autocomplete }}"
            dir="ltr"
            class="password-field__input"
            data-password-input
        >
        <button
            type="button"
            class="password-field__toggle ss-icon-toggle ss-icon-toggle--inset"
            data-password-toggle
            aria-label="إظهار كلمة المرور"
            aria-pressed="false"
            aria-controls="{{ $inputId }}"
        >
            <span class="material-symbols-outlined ss-icon-toggle__icon" data-password-icon aria-hidden="true">visibility</span>
        </button>
    </div>
</div>
