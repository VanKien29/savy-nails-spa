@php
    $home = $page->is_home;
    $slug = $page->slug;
    $pageLabel = match($slug) {'about-us' => 'About Us', 'contact-us' => 'Contact Us', default => $page->title};
    $referenceMedia = collect(json_decode(file_get_contents(public_path('assets/optimized/report.json')), true))->keyBy(fn ($entry) => str_replace('\\', '/', $entry['file']));
    $galleryFiles = $home ? [8, 2, 1, 4, 12, 7] : range(1, 12);
    $phone = preg_replace('/[^0-9+]/', '', $settings['hotline']);
@endphp
<!doctype html>
<html lang="{{ $seo['language'] }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#eaddca">
    <x-seo :seo="$seo"/>
    @vite(['resources/css/savy-reference.css', 'resources/js/savy-reference.js'])
    @include('partials.custom-code', ['placement' => 'script_head'])
</head>
<body id="top" class="av-site av-page-{{ $slug }}">
<a class="av-skip" href="#main">Skip to content</a>
<header class="av-header">
    <div class="av-header-inner">
        <a class="av-brand" href="/" aria-label="Savy Nails & Spa — Home"><span class="av-brand-ring"><b>S</b><i>✧</i></span><small>SAVY NAILS &amp; SPA</small></a>
        <nav class="av-nav" id="av-navigation" aria-label="Main navigation">
            @foreach([['Home','/'],['About Us','/about-us'],['Services','/services'],['Gallery','/gallery'],['Contact Us','/contact-us']] as [$label,$url])
            <a href="{{ $url }}" @if($page->path() === $url) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
        <a class="av-button av-header-book" href="/contact-us">Contact to Book</a>
        <button class="av-nav-toggle" type="button" aria-label="Toggle navigation" aria-controls="av-navigation" aria-expanded="false"><span></span><span></span><span></span></button>
    </div>
</header>
<main id="main">
@if($home)
    <section class="av-hero">
        <div class="av-hero-photo">@include('salon.partials.reference-image', ['file'=>'26867-e1713761727547.jpg','eager'=>true,'sizes'=>'100vw','alt'=>'Nude manicure with delicate dried flowers'])</div>
        <div class="av-container av-hero-inner"><div class="av-hero-brand" data-reveal><span>SAVY</span><p>Nails &amp; Spa</p></div><h1>Nail Salon in Walla Walla, WA 99362</h1></div>
    </section>
    <section class="av-home-about"><div class="av-about-scene"><div class="av-about-overlay" data-reveal><h2>About Us</h2><p><strong>Welcome to Savy Nails &amp; Spa, where beauty meets relaxation!</strong><br>Take a moment for yourself with a manicure, nail enhancement, relaxing spa pedicure or waxing service. Bring your inspiration and enjoy a finish that feels like you.</p><p>Savy Nails &amp; Spa is located at <a href="{{ $settings['maps_url'] }}" target="_blank" rel="noopener noreferrer">{{ $settings['address'] }}</a>.</p></div></div></section>
    @include('salon.partials.reference-menu')
    <section class="av-feature av-container">
        <div class="av-feature-image" data-reveal><h2 class="av-section-title">Your Savy Moment</h2>@include('salon.partials.reference-image',['file'=>'22224199.jpg','alt'=>'Nail care inspiration'])</div>
        <div class="av-feature-copy" data-reveal><div class="av-quote">A little self-care.<br>A beautiful finish.<p>From everyday polish to your next special occasion, explore a menu made for your moment.</p></div><a href="/contact-us">Call or message Savy to book</a></div>
    </section>
    <section class="av-home-gallery"><div class="av-container"><h2 class="av-section-title" data-reveal>Gallery</h2><div class="av-gallery-rail" aria-label="Nail inspiration carousel" tabindex="0">
        @foreach($galleryFiles as $number)<button class="av-gallery-photo" type="button" data-lightbox-file="avatar-{{ $number }}.jpg" aria-label="View nail inspiration {{ $number }}">@include('salon.partials.reference-image',['file'=>"avatar-$number.jpg",'sizes'=>'(max-width:767px) 85vw, 33vw','alt'=>"Nail design inspiration $number"])</button>@endforeach
        </div><div class="av-carousel-controls"><button data-carousel="-1" aria-label="Previous gallery images">‹</button><button data-carousel="1" aria-label="Next gallery images">›</button></div><div class="av-center"><a class="av-button" href="/gallery">Show More</a></div></div></section>
    <section class="av-faq av-container"><h2 class="av-section-title" data-reveal>FAQ</h2>
        @foreach([['Where is Savy Nails & Spa?',$settings['address']],['What services do you offer?','Explore manicures, nail enhancements, spa pedicures, additions and waxing on our Services page.'],['How can I make an appointment?','Call '.$settings['hotline'].' or message us through Facebook or Instagram to book.'],['What are your business hours?','Monday - Saturday: 09:00 AM - 07:00 PM. Sunday: 10:00 AM - 05:00 PM.'],['How can I ask about a price?','Call or message the salon for prices marked $.... and details about services marked +.']] as [$question,$answer])
        <details><summary>{{ $question }}<span>+</span></summary><p>{{ $answer }}</p></details>
        @endforeach
    </section>
@else
    <section class="av-page-banner"><h1 data-reveal>{{ $pageLabel }}</h1></section>
    @if($slug === 'services')
        @include('salon.partials.reference-menu')
    @elseif($slug === 'gallery')
        <section class="av-gallery-page av-container"><div class="av-gallery-grid">
        @foreach($galleryFiles as $number)<button class="av-gallery-photo" data-reveal type="button" data-lightbox-file="avatar-{{ $number }}.jpg" aria-label="View nail inspiration {{ $number }}">@include('salon.partials.reference-image',['file'=>"avatar-$number.jpg",'sizes'=>'(max-width:767px) 90vw, 33vw','alt'=>"Nail design inspiration $number"])</button>@endforeach
        </div></section>
    @elseif($slug === 'about-us')
        <section class="av-about-intro av-container"><h2 data-reveal>Welcome to Savy Nails &amp; Spa, where beauty meets relaxation!</h2><p>Make time for a little self-care. At Savy Nails &amp; Spa, explore manicures, spa pedicures, nail enhancements and waxing in Walla Walla. Bring your favorite shape, color or inspiration and let us help you choose your next finish.</p></section>
        <section class="av-strengths"><div class="av-container"><h2 class="av-section-title" data-reveal>Our Strengths</h2><div class="av-strength-grid">
        @foreach([['5.jpg','Beautiful Details','From a simple polish to your next set, discover the finishing details on our manicure and enhancement menu.'],['1.jpg','A Moment for You','Take a little time to slow down and enjoy your next nail or spa service.'],['3.jpg','A Menu for Your Style','Explore manicures, spa pedicures, additions and waxing, with prices available on our Services page.'],['6.jpg','Your Next Visit','Find us in Walla Walla. Call the salon or message us on Facebook or Instagram to arrange your visit.']] as [$file,$heading,$body])
            <article data-reveal>@include('salon.partials.reference-image',['file'=>$file,'alt'=>'Nail and spa treatment inspiration'])<h3>{{ $heading }}</h3><p>{{ $body }}</p></article>
        @endforeach
        </div><p class="av-center"><a class="av-button" href="/contact-us">Contact to Book</a></p></div></section>
    @elseif($slug === 'contact-us')
        <section class="av-contact"><div class="av-container av-contact-grid"><div class="av-contact-info" data-reveal><h2>Contact</h2><a href="tel:{{ $phone }}">{{ $settings['hotline'] }}</a><a href="{{ $settings['maps_url'] }}" target="_blank" rel="noopener noreferrer">{{ $settings['address'] }}</a><h2>Business Hours</h2>@foreach($salon['profile']['hours'] as $hour)<p>{{ $hour['days'] }}:<br>{{ $hour['time'] }}</p>@endforeach<h2>Social Media</h2><div class="av-socials">@foreach($settings['social_links'] as $social)<a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer">{{ $social['label'] }}</a>@endforeach</div></div><div class="av-contact-card" data-reveal><h2>Let’s plan your visit</h2><p>For appointments, call Savy Nails &amp; Spa or message us through our social pages.</p><a class="av-button" href="tel:{{ $phone }}">Call {{ $settings['hotline'] }}</a>@foreach($settings['social_links'] as $social)@if(strtolower($social['label']) !== 'google')<a class="av-button av-outline" href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer">Message on {{ $social['label'] }}</a>@endif @endforeach</div></div></section>
    @endif
@endif
</main>
<footer class="av-footer">
    <div class="av-container av-footer-grid">
        <div><a class="av-brand av-footer-brand" href="/" aria-label="Savy Nails & Spa — Home"><span class="av-brand-ring"><b>S</b><i>✧</i></span><small>SAVY NAILS &amp; SPA</small></a><div class="av-socials">@foreach($settings['social_links'] as $social)<a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $social['label'] }}">{{ $social['label'] }}</a>@endforeach</div></div>
        <div><h2>Contact</h2><a href="tel:{{ $phone }}">{{ $settings['hotline'] }}</a><a href="{{ $settings['maps_url'] }}" target="_blank" rel="noopener noreferrer">{{ $settings['address'] }}</a><h2>Business Hours</h2>@foreach($salon['profile']['hours'] as $hour)<p>{{ $hour['days'] }}:<br>{{ $hour['time'] }}</p>@endforeach</div>
        <a class="av-map-card" href="{{ $settings['maps_url'] }}" target="_blank" rel="noopener noreferrer"><span class="av-map-pin">⌖</span><strong>Savy Nails &amp; Spa</strong><span>634 S 9th Ave<br>Walla Walla, WA 99362</span><b>Open in Google Maps ↗</b></a>
    </div>
    <div class="av-copyright">Copyright © {{ date('Y') }} Savy Nails &amp; Spa. All rights reserved.</div>
</footer>
<a class="av-back-top" href="#top" aria-label="Back to top">↑</a>
<dialog class="av-lightbox" aria-label="Nail inspiration preview"><button type="button" class="av-dialog-close" aria-label="Close image">×</button><img alt="Nail design inspiration"></dialog>
<noscript><style>.av-nav{display:flex!important}.av-nav-toggle{display:none}</style></noscript>
@include('partials.custom-code', ['placement' => 'script_body_end'])
</body></html>
