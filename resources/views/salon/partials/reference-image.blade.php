@php
    $image = $salon['images']->get($key ?? '');
    $imageKey = $image?->key ?? '';
    $imageVariants = collect($image?->displayVariants() ?? [])->sortBy('width');
    $imageFallback = $imageVariants->last();
@endphp
@if($imageFallback)
<img data-salon-image="{{ $imageKey }}" class="{{ $class ?? '' }}" src="{{ $imageFallback['url'] }}" srcset="{{ $imageVariants->map(fn ($variant) => $variant['url'].' '.$variant['width'].'w')->implode(', ') }}" sizes="{{ $sizes ?? '(max-width: 767px) 100vw, 50vw' }}" width="{{ $imageFallback['width'] }}" height="{{ $imageFallback['height'] }}" alt="{{ $alt ?? ($image->media?->alt ?: $image->alt) }}" loading="{{ ($eager ?? false) ? 'eager' : 'lazy' }}" @if(($priority ?? false)) fetchpriority="high" @endif decoding="async">
@endif
