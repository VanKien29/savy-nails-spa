<!doctype html>
<html lang="{{ $seo['language'] }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo :seo="$seo" />
    @if($settings['heading_font'] === 'sans')
        <link rel="preload" href="{{ Vite::asset('node_modules/@fontsource/be-vietnam-pro/files/be-vietnam-pro-vietnamese-500-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="{{ Vite::asset('node_modules/@fontsource/be-vietnam-pro/files/be-vietnam-pro-latin-500-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    @endif
    <x-favicon :settings="$settings" :media="$media"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php($fonts = ['sans' => '"Be Vietnam Pro", sans-serif', 'serif' => 'Georgia, serif', 'system' => 'system-ui, sans-serif'])
    <style>:root{--brand:{{ $settings['primary_color'] }};--ink:{{ $settings['secondary_color'] }};--font-heading:{!! $fonts[$settings['heading_font']] !!};--font-body:{!! $fonts[$settings['body_font']] !!}}</style>
    @include('partials.custom-code', ['placement' => 'script_head'])
</head>
<body id="top" data-animations="{{ $settings['features']['animations'] ? 'on' : 'off' }}" @if(!$isDemo && $settings['cookie_consent'] && ($settings['ga4_id'] || $settings['gtm_id'] || $settings['meta_pixel_id'])) data-tracking="{{ json_encode(collect($settings)->only(['ga4_id','gtm_id','meta_pixel_id'])) }}" @endif>
    <a class="skip-link" href="#main">Bỏ qua đến nội dung</a>
    @if($isDemo)<div class="demo-banner">Bản trình diễn · Nội dung minh họa · Form không lưu dữ liệu</div>@endif
    <x-header :settings="$settings" :navigation="$navigation" :media="$media" />
    <main id="main">@yield('content')</main>
    <x-footer :settings="$settings" :navigation="$navigation" :is-demo="$isDemo" />
    <x-floating-contact :settings="$settings" />
    @if(!$isDemo && $settings['cookie_consent'] && ($settings['ga4_id'] || $settings['gtm_id'] || $settings['meta_pixel_id']))<x-cookie-consent />@endif
    <dialog id="image-lightbox" class="lightbox" aria-label="Xem ảnh"><button type="button" data-close-dialog aria-label="Đóng ảnh">✕</button><img alt=""><p></p></dialog>
    @include('partials.custom-code', ['placement' => 'script_body_end'])
</body>
</html>
