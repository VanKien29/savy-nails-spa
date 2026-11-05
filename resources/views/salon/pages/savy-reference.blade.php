@php
    $home = $page->is_home;
    $slug = $page->slug;
    $savyContent = app(\App\Services\SavyContent::class);
    $area = $savyContent->area($page);
    $blocks = $savyContent->blocks($page);
    $savy = $savyContent->profile($salon['profile']);
    $block = fn ($id) => $blocks['savy-'.$id] ?? [];
    $enabled = fn ($id) => $block($id)['enabled'] ?? true;
    $imageUrl = fn ($id) => $salon['images']->get($savy['images'][$id] ?? '')?->url() ?? '';
    $phone = preg_replace('/[^0-9+]/', '', $settings['hotline']);
    $galleryImages = $home ? $savy['home_gallery'] : $savy['gallery'];
@endphp
<!doctype html>
<html lang="{{ $seo['language'] }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#eaddca">
    <x-seo :seo="$seo"/>
    <x-favicon :settings="$settings" :media="$media" fallback="savy"/>
    @vite(['resources/css/savy-reference.css', 'resources/js/savy-reference.js'])
    <style>:root{
        --av-circle:url("{{ $imageUrl('circle') }}");--av-about-scene:url("{{ $imageUrl('intro') }}");
        --av-menu-background:url("{{ $imageUrl('menu') }}");--av-feature-background:url("{{ $imageUrl('feature_background') }}");
        --av-gallery-background:url("{{ $imageUrl('home_gallery_background') }}");--av-contact-background:url("{{ $imageUrl('contact_background') }}");
        --av-footer-background:url("{{ $imageUrl('footer_background') }}");
    }</style>
    @include('partials.custom-code', ['placement' => 'script_head'])
</head>
<body id="top" class="av-site av-page-{{ $slug }}" data-animations="{{ ($settings['features']['animations'] ?? true) ? 'true' : 'false' }}">
<div class="av-scroll-progress" aria-hidden="true"><span></span></div>
<a class="av-skip" href="#main">{{ $copy['global']['skip_content'] }}</a>
<header class="av-header">
    <div class="av-header-inner">
        <a class="av-brand" href="/" aria-label="{{ $settings['site_name'] }} — Home">@include('salon.partials.reference-logo', ['class' => 'av-brand-logo', 'sizes' => '150px', 'eager' => true, 'mobile' => true])</a>
        <nav class="av-nav" id="av-navigation" aria-label="Main navigation">
            @foreach($navigation->where('location', 'header') as $link)
                <a href="{{ $link->url }}" @if($page->path() === $link->url) aria-current="page" @endif>{{ $link->label }}</a>
            @endforeach
        </nav>
        <a class="av-button av-header-book" href="{{ $copy['global']['booking_url'] }}">{{ $copy['global']['booking_label'] }}</a>
        <button class="av-nav-toggle" type="button" aria-label="Toggle navigation" aria-controls="av-navigation" aria-expanded="false"><span></span><span></span><span></span></button>
    </div>
</header>
<main id="main">
@if($home)
    @if($enabled('hero'))
    <section class="av-hero">
        <div class="av-hero-photo">@include('salon.partials.reference-image', ['key' => $savy['images']['hero'], 'eager' => true, 'priority' => true, 'sizes' => '100vw'])</div>
        <div class="av-container av-hero-inner"><div class="av-hero-brand" data-reveal>@include('salon.partials.reference-image', ['key' => $savy['images']['hero_logo'], 'class' => 'av-hero-logo', 'sizes' => '470px', 'eager' => true])</div><h1>{{ $block('hero')['heading'] }}</h1></div>
    </section>
    @endif
    @if($enabled('intro'))
    <section class="av-home-about"><div class="av-about-scene"><div class="av-about-overlay" data-reveal>
        <h2>{{ $block('intro')['heading'] }}</h2><p><strong>{{ $block('intro')['eyebrow'] }}</strong><br>{{ $block('intro')['body'] }}</p>
        <p>{{ $block('intro')['items'][0]['text'] ?? '' }} <a href="{{ $settings['maps_url'] }}" target="_blank" rel="noopener noreferrer">{{ $settings['address'] }}</a>.</p>
    </div></div></section>
    @endif
    @if($enabled('menu')) @include('salon.partials.reference-menu', ['menuBlock' => $block('menu')]) @endif
    @if($enabled('feature'))
    <section class="av-feature av-container">
        <div class="av-feature-image" data-reveal><h2 class="av-section-title">{{ $block('feature')['heading'] }}</h2>@include('salon.partials.reference-image', ['key' => $savy['images']['feature']])</div>
        <div class="av-feature-copy" data-reveal><div class="av-quote"><span class="av-preserve-lines">{{ $block('feature')['body'] }}</span><p>{{ $block('feature')['items'][0]['text'] ?? '' }}</p></div>
        @if(filled($block('feature')['button_label']))<a href="{{ $block('feature')['button_url'] }}">{{ $block('feature')['button_label'] }}</a>@endif</div>
    </section>
    @endif
    @if($enabled('gallery') && count($galleryImages))
    <section class="av-home-gallery"><div class="av-container"><h2 class="av-section-title" data-reveal>{{ $block('gallery')['heading'] }}</h2>
        @include('salon.partials.reference-gallery', ['rail' => true])
        <div class="av-carousel-controls"><button data-carousel="-1" aria-label="Previous gallery images">‹</button><button data-carousel="1" aria-label="Next gallery images">›</button></div>
        @if(filled($block('gallery')['button_label']))<div class="av-center"><a class="av-button" href="{{ $block('gallery')['button_url'] }}">{{ $block('gallery')['button_label'] }}</a></div>@endif
    </div></section>
    @endif
    @if($enabled('faq'))
    <section class="av-faq av-container"><h2 class="av-section-title" data-reveal>{{ $block('faq')['heading'] }}</h2>
        @foreach($block('faq')['items'] as $item)
            @php($answer = match($item['value'] ?? 'custom') {
                'address' => $settings['address'],
                'hours' => collect($salon['profile']['hours'] ?? [])->map(fn ($hour) => $hour['days'].': '.$hour['time'].'.')->implode(' ') ?: ($salon['profile']['hours_note'] ?? ''),
                'booking' => 'Call '.$settings['hotline'].' or message us through '.collect($settings['social_links'])->reject(fn ($social) => $social['label'] === 'Google')->pluck('label')->implode(' or ').' to book.',
                default => $item['text'] ?? '',
            })
            <details><summary>{{ $item['title'] }}<span>+</span></summary><p>{{ $answer }}</p></details>
        @endforeach
    </section>
    @endif
@else
    <section class="av-page-banner">
        <div class="av-page-banner-image" aria-hidden="true">@include('salon.partials.reference-image', ['key' => $savy['images'][$area.'_banner'] ?? null, 'eager' => true, 'priority' => true, 'sizes' => '100vw', 'alt' => ''])</div>
        <h1>{{ $block('banner')['heading'] ?? $page->title }}</h1>
    </section>
    @if($slug === 'services')
        @include('salon.partials.reference-menu', ['menuBlock' => $block('menu')])
    @elseif($slug === 'gallery')
        <section class="av-gallery-page av-container">@include('salon.partials.reference-gallery', ['rail' => false])</section>
    @elseif($slug === 'about-us')
        @if($enabled('intro'))<section class="av-about-intro av-container"><h2 data-reveal>{{ $block('intro')['heading'] }}</h2><p>{{ $block('intro')['body'] }}</p></section>@endif
        @if($enabled('strengths'))
        <section class="av-strengths"><div class="av-container"><h2 class="av-section-title" data-reveal>{{ $block('strengths')['heading'] }}</h2><div class="av-strength-grid">
        @foreach($block('strengths')['items'] as $item)
            <article data-reveal>@include('salon.partials.reference-image', ['key' => $item['label'] ?? null])<h3>{{ $item['title'] }}</h3><p>{{ $item['text'] }}</p></article>
        @endforeach
        </div>@if(filled($block('strengths')['button_label']))<p class="av-center"><a class="av-button" href="{{ $block('strengths')['button_url'] }}">{{ $block('strengths')['button_label'] }}</a></p>@endif</div></section>
        @endif
    @elseif($slug === 'contact-us')
        <section class="av-contact"><div class="av-container av-contact-grid"><div class="av-contact-info" data-reveal>
            <h2>{{ $block('details')['heading'] }}</h2><a href="tel:{{ $phone }}">{{ $settings['hotline'] }}</a><a href="{{ $settings['maps_url'] }}" target="_blank" rel="noopener noreferrer">{{ $settings['address'] }}</a>
            <h2>{{ $block('details')['items'][0]['title'] ?? '' }}</h2>@include('salon.partials.reference-hours')
            <h2>{{ $block('details')['items'][1]['title'] ?? '' }}</h2><div class="av-socials">@include('salon.partials.reference-socials')</div>
        </div><div class="av-contact-card" data-reveal>
            <h2>{{ $block('card')['heading'] }}</h2><p>{{ $block('card')['body'] }}</p>
            <a class="av-button" href="tel:{{ $phone }}">{{ $block('card')['items'][0]['title'] ?? '' }} {{ $settings['hotline'] }}</a>
            @foreach($settings['social_links'] as $social)@if(strtolower($social['label']) !== 'google')<a class="av-button av-outline" href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer">{{ $block('card')['items'][1]['title'] ?? '' }} {{ $social['label'] }}</a>@endif @endforeach
        </div></div></section>
    @else
        @foreach($sections as $section)
        <section class="av-container av-about-intro">
            @if($section['heading'] ?? null)<h2>{{ $section['heading'] }}</h2>@endif
            @if($section['body'] ?? null)<p>{!! nl2br(e($section['body'])) !!}</p>@endif
            @if($image = $media->get($section['media_id'] ?? null))<x-media.picture :media="$image" sizes="(max-width: 767px) 100vw, 70vw"/>@endif
            @foreach($section['items'] ?? [] as $item)<article>@if($item['title'] ?? null)<h3>{{ $item['title'] }}</h3>@endif @if($item['text'] ?? null)<p>{{ $item['text'] }}</p>@endif</article>@endforeach
            @if(($section['button_label'] ?? null) && ($section['button_url'] ?? null))<a class="av-button" href="{{ $section['button_url'] }}">{{ $section['button_label'] }}</a>@endif
        </section>
        @endforeach
    @endif
@endif
</main>
<footer class="av-footer">
    <div class="av-container av-footer-grid">
        <div><a class="av-brand av-footer-brand" href="/" aria-label="{{ $settings['site_name'] }} — Home">@include('salon.partials.reference-logo', ['class' => 'av-footer-logo', 'sizes' => '220px'])</a><div class="av-socials">@include('salon.partials.reference-socials')</div></div>
        <div><h2>{{ $savy['footer']['contact_label'] }}</h2><a href="tel:{{ $phone }}">{{ $settings['hotline'] }}</a><a href="{{ $settings['maps_url'] }}" target="_blank" rel="noopener noreferrer">{{ $settings['address'] }}</a><h2>{{ $savy['footer']['hours_label'] }}</h2>@include('salon.partials.reference-hours')</div>
        <div class="av-map-card">
            @if($settings['maps_embed_url'])
                <iframe data-auto-map="{{ $settings['maps_embed_url'] }}" title="Google Maps — salon location" width="480" height="295" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                <noscript><style>.av-map-card>[data-auto-map]{display:none}</style><iframe src="{{ $settings['maps_embed_url'] }}" title="Google Maps — salon location" width="480" height="295" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></noscript>
            @endif
            <a href="{{ $settings['maps_url'] }}" target="_blank" rel="noopener noreferrer">{{ $savy['footer']['map_label'] }}</a>
        </div>
    </div>
    <div class="av-copyright">Copyright © {{ date('Y') }} {{ $settings['company_name'] }}. {{ $settings['copyright'] }}</div>
</footer>
<div class="av-floating-actions" aria-label="Quick links">
    @foreach($settings['social_links'] as $social)
        @if(in_array($social['label'], ['Facebook', 'Instagram']))
            <a class="av-floating-social" href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $social['label'] }}" title="{{ $social['label'] }}"><x-salon.icon :name="strtolower($social['label'])"/></a>
        @endif
    @endforeach
    <a class="av-back-top" href="#top" aria-label="Back to top"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m6 11 6-6 6 6M12 5v14"/></svg></a>
</div>
<dialog class="av-lightbox" aria-label="Nail inspiration preview"><button type="button" class="av-dialog-close" aria-label="Close image">×</button><img alt="Nail design inspiration"></dialog>
<noscript><style>.av-nav{display:flex!important}.av-nav-toggle{display:none}</style></noscript>
@include('partials.custom-code', ['placement' => 'script_body_end'])
</body></html>
