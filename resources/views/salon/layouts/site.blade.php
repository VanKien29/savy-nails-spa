<!doctype html>
<html lang="{{ $seo['language'] }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="{{ $settings['primary_color'] }}">
    <x-seo :seo="$seo" />
    <link rel="icon" type="image/svg+xml" href="/assets/the-k/favicon.svg">
    <link rel="preload" href="{{ Vite::asset('node_modules/@fontsource/cormorant-garamond/files/cormorant-garamond-latin-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ Vite::asset('node_modules/@fontsource/cormorant-garamond/files/cormorant-garamond-latin-400-italic.woff2') }}" as="font" type="font/woff2" crossorigin>
    @vite(['resources/css/salon.css','resources/js/salon.js'])
    @if($page->template === 'salon-home')@vite('resources/css/salon-luxury.css')@endif
    <style>:root{--ks-gold:{{ $settings['primary_color'] }};--ks-ink:{{ $settings['secondary_color'] }}}</style>
    <noscript><style>@media(max-width:730px){.ks-mobile-nav{display:grid!important}}</style></noscript>
</head>
<body class="ks-site page-{{ $page->slug }}{{ $isDemo ? ' is-demo' : '' }}" data-animations="{{ $settings['features']['animations'] ? 'on' : 'off' }}" @if(!$isDemo && $settings['cookie_consent'] && ($settings['ga4_id'] || $settings['gtm_id'] || $settings['meta_pixel_id'])) data-tracking="{{ json_encode(collect($settings)->only(['ga4_id','gtm_id','meta_pixel_id'])) }}" @endif>
    <a href="#main" class="ks-skip">Skip to content</a>
    @include('salon.partials.header')
    <main id="main">@yield('content')</main>
    @include('salon.partials.footer')
    <div class="ks-mobile-actions"><a href="tel:{{ preg_replace('/[^0-9+]/','',$settings['hotline']) }}"><x-salon.icon name="phone"/> Call the Salon</a><a href="/contact-us"><x-salon.icon name="arrow"/> Booking</a></div>
    <a class="ks-back-top" href="#top" aria-label="Back to top"><x-salon.icon name="arrow-up" /></a>
    <dialog id="image-lightbox" class="ks-lightbox" aria-label="Image preview"><button type="button" data-close-dialog aria-label="Close image"><x-salon.icon name="close" /></button><img alt=""><p></p></dialog>
    <dialog id="appointment-policy" class="ks-policy" aria-label="Privacy and contact requests"><button type="button" data-close-dialog aria-label="Close policy"><x-salon.icon name="close" /></button><h2>Privacy & contact requests</h2><p>Contact details are used to respond to your inquiry and coordinate a visit. Please wait for our team to contact you, or call {{ $settings['hotline'] }}.</p><p>Please do not include payment details or sensitive health information in these forms.</p></dialog>
    @if(!$isDemo && $settings['cookie_consent'] && ($settings['ga4_id'] || $settings['gtm_id'] || $settings['meta_pixel_id']))
        <aside class="ks-cookie" data-consent-banner hidden aria-label="Cookie preferences"><p>May we use optional analytics cookies to improve your experience?</p><button type="button" data-consent="accepted" class="ks-button">Allow analytics</button><button type="button" data-consent="rejected" class="ks-link">Essential only</button></aside>
    @endif
</body>
</html>
