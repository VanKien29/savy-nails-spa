@props(['image'=>null,'mobileImage'=>null,'alt'=>null,'lcp'=>false,'sizes'=>'(max-width: 700px) 100vw, 50vw'])
@if($image)
@php($variants = collect($image->displayVariants())->sortBy('width'))
@php($fallback = $variants->last())
@if($fallback)
<picture data-salon-image="{{ $image->key }}" {{ $attributes->class(['ks-picture']) }}>@if($mobileImage)@php($mobileVariants=collect($mobileImage->displayVariants())->sortBy('width'))<source media="(max-width:730px)" srcset="{{ $mobileVariants->map(fn($v)=>$v['url'].' '.$v['width'].'w')->implode(', ') }}" sizes="100vw">@endif<source srcset="{{ $variants->map(fn($v)=>$v['url'].' '.$v['width'].'w')->implode(', ') }}" sizes="{{ $sizes }}"><img src="{{ $fallback['url'] }}" width="{{ $fallback['width'] }}" height="{{ $fallback['height'] }}" alt="{{ $alt ?? $image->alt }}" loading="{{ $lcp ? 'eager' : 'lazy' }}" @if($lcp) fetchpriority="high" @endif decoding="async"></picture>
@endif
@endif
