@extends('salon.layouts.site')

@php
    $images = $salon['images'];
    $hero = $images->get($salon['profile']['hero_image'] ?? 'luxury-hero');
    $story = $images->get($salon['profile']['story_image'] ?? 'luxury-story');
    $signature = $images->get('luxury-ritual-nude');
    $logoArt = $images->get('savy-logo-art');
    $galleryKeys = ['luxury-look-french', 'luxury-look-chrome', 'luxury-look-gold', 'luxury-look-milky'];
    $galleryImages = collect($galleryKeys)->map(fn (string $key) => $images->get($key))->filter();
    $pedicure = $salon['categories']->firstWhere('slug', 'pedicure-services')?->services?->first();
    $phone = preg_replace('/[^0-9+]/', '', $settings['hotline']);
@endphp

@section('content')
<div class="savy-home">
    <div class="savy-topline">
        <div class="savy-container savy-topline-inner">
            <span>Savy Nails &amp; Spa · Walla Walla, WA</span>
            <span>Mon–Sat 09:00 AM–07:00 PM · Sun 10:00 AM–05:00 PM</span>
        </div>
    </div>

    <section class="savy-hero" aria-labelledby="savy-hero-title">
        <div class="savy-hero-media" data-animate="fade-in">
            <x-salon.picture :image="$hero" :mobile-image="$images->get($salon['profile']['hero_mobile_image'] ?? '')" :lcp="true" sizes="(max-width: 800px) 100vw, 58vw" alt="Savy Nails & Spa nail and beauty detail"/>
            <span class="savy-hero-stamp" aria-hidden="true">S<br><small>Est.</small><br>2024</span>
        </div>
        <div class="savy-hero-copy" data-animate="slide-left">
            <p class="savy-kicker">Nails · Spa · Waxing</p>
            <p class="savy-mark" aria-hidden="true">SAVY</p>
            <h1 id="savy-hero-title">Nail care,<br><em>with a golden touch.</em></h1>
            <p class="savy-lead">A polished place for manicures, spa pedicures, enhancements and waxing in Walla Walla.</p>
            <div class="savy-actions">
                <a class="savy-button savy-button-dark" href="{{ $copy['global']['booking_url'] }}">{{ $copy['global']['booking_label'] }} <span aria-hidden="true">↗</span></a>
                <a class="savy-text-link" href="{{ $copy['home']['menu_url'] }}">{{ $copy['home']['menu_label'] }} <span aria-hidden="true">→</span></a>
            </div>
            <div class="savy-hero-socials" aria-label="Follow {{ $settings['site_name'] }}">
                @foreach($settings['social_links'] as $social)
                    @if(in_array(strtolower($social['label']), ['facebook', 'instagram'], true))
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer">{{ $social['label'] }}</a>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <div class="savy-service-strip" aria-label="Savy services">
        <div class="savy-container savy-strip-grid">
            <span>01 <strong>MANICURE</strong></span>
            <span>02 <strong>SPA PEDICURE</strong></span>
            <span>03 <strong>WAXING</strong></span>
        </div>
    </div>

    <section class="savy-about savy-container" id="our-story" aria-labelledby="savy-about-title">
        <div class="savy-about-copy" data-animate="slide-right">
            <p class="savy-kicker">The Savy way</p>
            <h2 id="savy-about-title">A little time<br><em>for you.</em></h2>
            <p>Step into Savy Nails &amp; Spa for thoughtful care, beautiful finishes and a moment to slow down.</p>
            <p>Bring your favorite shape, color or finish. We will help you make it feel like yours.</p>
            <a class="savy-text-link" href="{{ $copy['home']['story_url'] }}">{{ $copy['home']['story_label'] }} <span aria-hidden="true">→</span></a>
        </div>
        <div class="savy-about-media" data-animate="fade-up">
            <x-salon.picture :image="$story" sizes="(max-width: 800px) 90vw, 48vw" alt="A calm nail salon detail at Savy Nails & Spa"/>
            <span class="savy-image-note">Thoughtful care<br><em>beautiful details</em></span>
        </div>
    </section>

    <section class="savy-collection" id="signature-services" aria-labelledby="savy-collection-title">
        <div class="savy-container">
            <div class="savy-heading-row" data-animate="fade-up">
                <div><p class="savy-kicker">The menu</p><h2 id="savy-collection-title">Your next signature<br><em>look starts here.</em></h2></div>
                <a class="savy-text-link" href="{{ $copy['home']['full_menu_url'] }}">{{ $copy['home']['full_menu_label'] }} <span aria-hidden="true">→</span></a>
            </div>
            <div class="savy-service-grid">
                @foreach($salon['categories'] as $category)
                    @php($categoryImage = $category->image ?? $images->get(['manicure-nail-enhancements' => 'nails-menu', 'pedicure-services' => 'luxury-ritual-nude', 'additions' => 'luxury-look-gold', 'waxing' => 'brows-lashes'][$category->slug] ?? 'nails-menu'))
                    <a class="savy-service-card" href="/services#menu-{{ $category->slug }}" data-animate="fade-up">
                        <div class="savy-service-image"><x-salon.picture :image="$categoryImage" sizes="(max-width: 700px) 90vw, 25vw" alt="{{ $category->name }} at Savy Nails & Spa"/><span>0{{ $loop->iteration }}</span></div>
                        @php($minPrice = $category->services->whereNotNull('price')->min('price'))
                        <div class="savy-service-meta"><h3>{{ $category->name }}</h3><p>{{ $category->description }}</p><span>@if($minPrice !== null){{ $copy['home']['price_prefix'] }} ${{ number_format((float) $minPrice, (float) $minPrice === floor((float) $minPrice) ? 0 : 2) }}@else{{ $copy['menu']['inquire_label'] }}@endif <b aria-hidden="true">↗</b></span></div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="savy-signature savy-container" aria-labelledby="savy-signature-title">
        <div class="savy-signature-media" data-animate="slide-right"><x-salon.picture :image="$signature" sizes="(max-width: 800px) 90vw, 48vw" alt="Spa pedicure detail at Savy Nails & Spa"/><span class="savy-vertical-label">THE PEDICURE EDIT</span></div>
        <div class="savy-signature-copy" data-animate="slide-left">
            <p class="savy-kicker">A moment to reset</p>
            <h2 id="savy-signature-title">The spa<br><em>pedicure edit.</em></h2>
            <p>{{ $pedicure?->description ?? 'Thoughtful care, a relaxing massage and a beautiful finish.' }}</p>
            @if($pedicure)<p class="savy-price">{{ $pedicure->name }} <strong>{{ $pedicure->priceLabel() }}</strong></p>@endif
            <a class="savy-button savy-button-outline" href="/services#menu-pedicure-services">Explore pedicures <span aria-hidden="true">↗</span></a>
        </div>
    </section>

    <section class="savy-gallery" id="inspiration" aria-labelledby="savy-gallery-title">
        <div class="savy-container">
            <div class="savy-heading-center" data-animate="fade-up"><p class="savy-kicker">Nail inspiration</p><h2 id="savy-gallery-title">A little <em>inspiration.</em></h2><p>Bring your favorite finish, shape or color idea and let’s make it yours.</p></div>
            <div class="savy-gallery-grid">
                @foreach($galleryImages as $image)
                    <figure data-animate="fade-up"><button type="button" class="savy-gallery-tile" data-lightbox="{{ $image->url() }}" data-caption="{{ $image->caption }}" aria-label="View {{ $image->caption }}"><x-salon.picture :image="$image" sizes="(max-width: 700px) 46vw, 24vw" alt="{{ $image->alt }}"/><span aria-hidden="true">+</span></button></figure>
                @endforeach
            </div>
            <div class="savy-gallery-follow"><span>See more of the Savy edit</span>@foreach($settings['social_links'] as $social)<a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer">{{ $social['label'] }} ↗</a>@endforeach</div>
        </div>
    </section>

    <section class="savy-visit" id="visit" aria-labelledby="savy-visit-title">
        <div class="savy-visit-media"><x-salon.picture :image="$images->get('contact-welcome')" sizes="(max-width: 800px) 100vw, 50vw" alt="Welcome to Savy Nails & Spa in Walla Walla"/></div>
        <div class="savy-visit-copy" data-animate="slide-left">
            <p class="savy-kicker">Come say hello</p><h2 id="savy-visit-title">Your next polished<br><em>moment is here.</em></h2>
            <div class="savy-visit-details"><div><span>Visit the salon</span><a href="{{ $settings['maps_url'] }}" target="_blank" rel="noopener noreferrer">{{ $settings['address'] }} ↗</a></div><div><span>Give us a call</span><a href="tel:{{ $phone }}">{{ $settings['hotline'] }}</a></div><div><span>Business hours</span>@foreach($salon['profile']['hours'] ?? [] as $hour)<p>{{ $hour['days'] }}<br><strong>{{ $hour['time'] }}</strong></p>@endforeach</div></div>
            <div class="savy-visit-socials">@foreach($settings['social_links'] as $social)<a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer">{{ $social['label'] }} <span aria-hidden="true">↗</span></a>@endforeach</div>
        </div>
    </section>

    <section class="savy-cta" aria-labelledby="savy-cta-title"><div class="savy-container"><p class="savy-mark" aria-hidden="true">SAVY</p><h2 id="savy-cta-title">Polished with care.<br><em>Made for your moment.</em></h2><a class="savy-button savy-button-light" href="{{ $copy['global']['booking_url'] }}">{{ $copy['global']['booking_label'] }} <span aria-hidden="true">↗</span></a></div></section>
</div>
@endsection
