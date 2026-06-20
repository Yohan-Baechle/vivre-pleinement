@props([
    'media' => null,
    'alt' => null,
    'sizes' => '(min-width: 1024px) 1024px, 100vw',
    'class' => null,
    'loading' => 'lazy',
    'fetchpriority' => null,
])

@if ($media)
    @php
        $widthSource = $media->getCustomProperty('width') ?? $media->responsive_images['source']['width'] ?? null;
        $heightSource = $media->getCustomProperty('height') ?? $media->responsive_images['source']['height'] ?? null;

        $thumb = $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : null;
        $medium = $media->hasGeneratedConversion('medium') ? $media->getUrl('medium') : null;
        $large = $media->hasGeneratedConversion('large') ? $media->getUrl('large') : null;

        $srcset = collect([
            $thumb ? $thumb.' 400w' : null,
            $medium ? $medium.' 800w' : null,
            $large ? $large.' 1600w' : null,
        ])->filter()->implode(', ');

        $defaultSrc = $medium ?: $large ?: $thumb ?: $media->getUrl();
        $altText = $alt ?? $media->getCustomProperty('alt') ?? $media->name;
    @endphp

    <img
        src="{{ $defaultSrc }}"
        @if ($srcset) srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif
        @if ($widthSource) width="{{ $widthSource }}" @endif
        @if ($heightSource) height="{{ $heightSource }}" @endif
        alt="{{ $altText }}"
        loading="{{ $loading }}"
        decoding="async"
        @if ($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
        @if ($class) class="{{ $class }}" @endif
    >
@endif
