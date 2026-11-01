<!doctype html>
<html lang="{{ $seo['language'] }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="{{ $settings['primary_color'] }}">
    <x-seo :seo="$seo" />
    <x-favicon :settings="$settings" :media="$media" fallback="the-k"/>
    <link rel="preload" href="{{ Vite::asset('node_modules/@fontsource/cormorant-garamond/files/cormorant-garamond-latin-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ Vite::asset('node_modules/@fontsource/cormorant-garamond/files/cormorant-garamond-latin-400-italic.woff2') }}" as="font" type="font/woff2" crossorigin>
    @vite(['resources/css/salon.css','resources/js/salon.js'])
    @if(in_array($page->template, ['salon-home', 'salon-savy-home'], true))@vite('resources/css/salon-luxury.css')@endif
    <style>
        :root{--ks-gold:{{ $settings['primary_color'] }};--ks-ink:{{ $settings['secondary_color'] }};--ks-gold-text:{{ $settings['accent_text_color'] }};--ks-cream:{{ $settings['surface_color'] }};--ks-serif:{!! match($settings['heading_font']){'sans'=>"'Be Vietnam Pro',sans-serif",'system'=>'system-ui,sans-serif',default=>"'Cormorant Garamond',Georgia,serif"} !!};--ks-sans:{!! match($settings['body_font']){'serif'=>"'Cormorant Garamond',Georgia,serif",'system'=>'system-ui,sans-serif',default=>"'Be Vietnam Pro',sans-serif"} !!}}
        .ks-site{background:{{ $settings['background_color'] }}}
        .page-home{--kl-gold:{{ $settings['home_gold_color'] }};--kl-black:{{ $settings['home_dark_color'] }}}
    </style>
    <noscript><style>@media(max-width:730px){.ks-mobile-nav{display:grid!important}}</style></noscript>
    @include('partials.custom-code', ['placement' => 'script_head'])
</head>
<body id="top" class="ks-site page-{{ $page->slug }}{{ $settings['site_name'] === 'Savy Nails & Spa' ? ' client-savy' : '' }}{{ $isDemo ? ' is-demo' : '' }}" data-animations="{{ $settings['features']['animations'] ? 'on' : 'off' }}" @if(!$isDemo && $settings['cookie_consent'] && ($settings['ga4_id'] || $settings['gtm_id'] || $settings['meta_pixel_id'])) data-tracking="{{ json_encode(collect($settings)->only(['ga4_id','gtm_id','meta_pixel_id'])) }}" @endif>
    <a href="#main" class="ks-skip">{{ $copy['global']['skip_content'] }}</a>
    @include('salon.partials.header')
    <main id="main">@yield('content')</main>
    @include('salon.partials.footer')
    <nav class="ks-floating-actions" aria-label="Social links and page controls">
        @foreach($settings['social_links'] as $social)
            @if(in_array(strtolower($social['label']), ['facebook', 'instagram'], true))
                <a class="ks-floating-social" href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $social['label'] }} — opens in a new tab"><x-salon.icon :name="strtolower($social['label'])" /></a>
            @endif
        @endforeach
    </nav>
    @if($page->template !== 'salon-gallery')<dialog id="image-lightbox" class="ks-lightbox" aria-label="Image preview"><button type="button" data-close-dialog aria-label="Close image"><x-salon.icon name="close" /></button><img alt=""><p></p></dialog>@endif
    <dialog id="appointment-policy" class="ks-policy" aria-label="Privacy and contact requests"><button type="button" data-close-dialog aria-label="Close policy"><x-salon.icon name="close" /></button><h2>{{ $copy['global']['policy_title'] }}</h2><p>{{ $copy['global']['policy_body'] }}</p><p>{{ $copy['global']['policy_note'] }}</p></dialog>
    @if(!$isDemo && $settings['cookie_consent'] && ($settings['ga4_id'] || $settings['gtm_id'] || $settings['meta_pixel_id']))
        <aside class="ks-cookie" data-consent-banner hidden aria-label="Cookie preferences"><p>{{ $copy['global']['cookie_question'] }}</p><button type="button" data-consent="accepted" class="ks-button">{{ $copy['global']['cookie_accept'] }}</button><button type="button" data-consent="rejected" class="ks-link">{{ $copy['global']['cookie_reject'] }}</button></aside>
    @endif
    @include('partials.custom-code', ['placement' => 'script_body_end'])
</body>
</html>
