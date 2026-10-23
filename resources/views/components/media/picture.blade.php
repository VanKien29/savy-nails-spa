@props(['media' => null, 'mobileMedia' => null, 'mobileBreakpoint' => 768, 'variant' => 'content', 'alt' => '', 'sizes' => '(max-width: 768px) 100vw, 50vw', 'lcp' => false, 'eager' => false])
@if($media && $media->status === 'ready' && count($media->variants ?? []))
@php($variants = collect($media->variants)->sortBy('width'))
@php($fallback = $variants->last())
@php($variantUrl = fn($record, $variant) => $record->disk === 'public' && rtrim(config('filesystems.disks.public.url'), '/') === rtrim(config('app.url'), '/').'/storage' ? '/storage/'.$variant['path'] : Storage::disk($record->disk)->url($variant['path']))
<picture {{ $attributes->class(['media-picture']) }}>
    @if($mobileMedia && $mobileMedia->status === 'ready' && count($mobileMedia->variants ?? []) && $mobileMedia->id !== $media->id)
    @php($mobileVariants = collect($mobileMedia->variants)->sortBy('width'))
    @php($mobileFallback = $mobileVariants->last())
    <source media="(max-width: {{ (int) $mobileBreakpoint }}px)" type="image/{{ $mobileFallback['format'] }}" srcset="{{ $mobileVariants->map(fn($v) => $variantUrl($mobileMedia,$v).' '.$v['width'].'w')->implode(', ') }}" sizes="{{ $sizes }}" width="{{ $mobileFallback['width'] }}" height="{{ $mobileFallback['height'] }}">
    @endif
    <source type="image/{{ $fallback['format'] }}" srcset="{{ $variants->map(fn($v) => $variantUrl($media,$v).' '.$v['width'].'w')->implode(', ') }}" sizes="{{ $sizes }}">
    <img src="{{ $variantUrl($media,$fallback) }}" alt="{{ $alt }}" width="{{ $fallback['width'] }}" height="{{ $fallback['height'] }}" loading="{{ $lcp || $eager || $variant === 'hero' ? 'eager' : 'lazy' }}" @if($lcp || $variant === 'hero') fetchpriority="high" @endif decoding="async">
</picture>
@endif
