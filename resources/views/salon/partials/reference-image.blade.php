@php
    $imageKey = str_contains($file, '/') ? $file : 'savy/'.$file;
    $imageVariants = collect($referenceMedia->get($imageKey)['variants'] ?? [])->filter(fn ($variant) => str_ends_with($variant['file'], '.webp'))->sortBy('width');
    $imageFallback = $imageVariants->last();
@endphp
@if($imageFallback)
<img class="{{ $class ?? '' }}" src="/assets/optimized/{{ str_replace('\\', '/', $imageFallback['file']) }}" srcset="{{ $imageVariants->map(fn ($variant) => '/assets/optimized/'.str_replace('\\', '/', $variant['file']).' '.$variant['width'].'w')->implode(', ') }}" sizes="{{ $sizes ?? '(max-width: 767px) 100vw, 50vw' }}" width="{{ $imageFallback['width'] }}" height="{{ $imageFallback['height'] }}" alt="{{ $alt ?? 'Nail and spa inspiration' }}" loading="{{ ($eager ?? false) ? 'eager' : 'lazy' }}" @if(($eager ?? false) && !str_contains($imageKey, 'logo')) fetchpriority="high" @endif decoding="async">
@endif
