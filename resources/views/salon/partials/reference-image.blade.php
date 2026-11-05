@php
    $imageKey = str_contains($file, '/') ? $file : 'savy/'.$file;
    $imageVariants = collect($referenceMedia->get($imageKey)['variants'] ?? [])->filter(fn ($variant) => str_ends_with($variant['file'], '.webp'))->sortBy('width');
    $imageFallback = $imageVariants->last();
    $imageRecordKey = match($file) {
        '26867-e1713761727547.jpg' => $salon['profile']['hero_image'] ?? 'savy-hero',
        '22224199.jpg' => $salon['profile']['story_image'] ?? 'savy-story',
        'savy-logo-transparent.png' => 'savy-logo-art',
        'banner-services.png' => 'savy-pedicure',
        'banner-gallery.png' => 'savy-additions',
        default => preg_match('/^avatar-(\d+)\.jpg$/', $file, $matches) ? 'savy-gallery-'.$matches[1] : null,
    };
    $editableImage = $salon['images']->get($imageRecordKey);
@endphp
@if($editableImage?->media?->status === 'ready')
<x-media.picture :media="$editableImage->media" :alt="$alt ?? $editableImage->alt" :sizes="$sizes ?? '(max-width: 767px) 100vw, 50vw'" :eager="$eager ?? false" :lcp="($eager ?? false) && !str_contains($file, 'logo')" :class="$class ?? ''"/>
@elseif($imageFallback)
<img class="{{ $class ?? '' }}" src="/assets/optimized/{{ str_replace('\\', '/', $imageFallback['file']) }}" srcset="{{ $imageVariants->map(fn ($variant) => '/assets/optimized/'.str_replace('\\', '/', $variant['file']).' '.$variant['width'].'w')->implode(', ') }}" sizes="{{ $sizes ?? '(max-width: 767px) 100vw, 50vw' }}" width="{{ $imageFallback['width'] }}" height="{{ $imageFallback['height'] }}" alt="{{ $alt ?? 'Nail and spa inspiration' }}" loading="{{ ($eager ?? false) ? 'eager' : 'lazy' }}" @if(($eager ?? false) && !str_contains($imageKey, 'logo')) fetchpriority="high" @endif decoding="async">
@endif
