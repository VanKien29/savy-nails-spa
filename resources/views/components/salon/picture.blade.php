@props(['image'=>null,'mobileImage'=>null,'alt'=>null,'lcp'=>false,'sizes'=>'(max-width: 700px) 100vw, 50vw'])
@if($image)
@php($variants = collect($image->variants)->filter(fn($v)=>str_ends_with($v['file'],'.webp'))->sortBy('width'))
@php($fallback = $variants->last())
<picture data-salon-image="{{ $image->key }}" {{ $attributes->class(['ks-picture']) }}>@if($mobileImage)@php($mobileVariants=collect($mobileImage->variants)->filter(fn($v)=>str_ends_with($v['file'],'.webp'))->sortBy('width'))<source media="(max-width:730px)" type="image/webp" srcset="{{ $mobileVariants->map(fn($v)=>asset('assets/optimized/'.$v['file']).' '.$v['width'].'w')->implode(', ') }}" sizes="100vw">@endif<source type="image/webp" srcset="{{ $variants->map(fn($v)=>asset('assets/optimized/'.$v['file']).' '.$v['width'].'w')->implode(', ') }}" sizes="{{ $sizes }}"><img src="{{ asset('assets/optimized/'.$fallback['file']) }}" width="{{ $fallback['width'] }}" height="{{ $fallback['height'] }}" alt="{{ $alt ?? $image->alt }}" loading="{{ $lcp ? 'eager' : 'lazy' }}" @if($lcp) fetchpriority="high" @endif decoding="async"></picture>
@endif
