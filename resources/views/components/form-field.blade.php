@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'autofocus' => false,
    'autocomplete' => null,
    'placeholder' => null,
    'showErrorRing' => true,
    'showErrorMessage' => true,
    'errorClass' => 'ring-rose-400',
    'bag' => 'default',
])

@php
    $fieldClasses = 'mt-2 w-full rounded-2xl border-0 bg-cream-50 px-4 py-3 text-sm text-ink ring-1 ring-ink/10 transition placeholder:text-ink-muted focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-hidden';

    $bagErrors = $errors->getBag($bag);

    $inputClasses = $fieldClasses.($showErrorRing && $bagErrors->has($name) ? ' '.$errorClass : '');
@endphp

<div>
    <label for="{{ $name }}" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">{{ $label }}</label>
    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        @if ($type !== 'password') value="{{ $value }}" @endif
        @if ($required) required @endif
        @if ($autofocus) autofocus @endif
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        class="{{ $inputClasses }}"
    >
    @if ($showErrorMessage && $bagErrors->has($name))
        <p class="mt-1 text-xs text-rose-700">{{ $bagErrors->first($name) }}</p>
    @endif
    {{ $slot }}
</div>
