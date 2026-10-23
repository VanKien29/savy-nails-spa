@props(['seo'])
<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
<meta name="robots" content="{{ $seo['robots'] }}">
<link rel="canonical" href="{{ $seo['canonical'] }}">
<meta property="og:type" content="website"><meta property="og:locale" content="{{ $seo['locale'] ?? 'en_US' }}">
<meta property="og:site_name" content="{{ $seo['site_name'] }}">
<meta property="og:title" content="{{ $seo['title'] }}"><meta property="og:description" content="{{ $seo['description'] }}"><meta property="og:url" content="{{ $seo['canonical'] }}">
<meta name="twitter:card" content="{{ $seo['image'] ? 'summary_large_image' : 'summary' }}"><meta name="twitter:title" content="{{ $seo['title'] }}"><meta name="twitter:description" content="{{ $seo['description'] }}">
@if($seo['image'])
<meta property="og:image" content="{{ $seo['image'] }}"><meta name="twitter:image" content="{{ $seo['image'] }}">
@if($seo['image_alt'])<meta property="og:image:alt" content="{{ $seo['image_alt'] }}"><meta name="twitter:image:alt" content="{{ $seo['image_alt'] }}">@endif
@if($seo['image_width'] && $seo['image_height'])<meta property="og:image:width" content="{{ $seo['image_width'] }}"><meta property="og:image:height" content="{{ $seo['image_height'] }}">@endif
@endif
@foreach($seo['alternates'] as $alternate)<link rel="alternate" hreflang="{{ $alternate['locale'] }}" href="{{ $alternate['url'] }}">@endforeach
<script type="application/ld+json">{!! json_encode($seo['schema'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
