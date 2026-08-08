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
            class="password-field__toggle"
            data-password-toggle
            aria-label="إظهار كلمة المرور"
            aria-pressed="false"
            aria-controls="{{ $inputId }}"
        >
            <span class="material-symbols-outlined" data-password-icon="show" aria-hidden="true">visibility</span>
            <span class="material-symbols-outlined hidden" data-password-icon="hide" aria-hidden="true">visibility_off</span>
        </button>
    </div>
</div>
