@props(['settings', 'media', 'fallback' => null])
@php($favicon = $media->get($settings['favicon_id'] ?? null))
@if($favicon)
    <link rel="icon" href="{{ $favicon->url() }}">
@elseif($fallback === 'the-k')
    <link rel="icon" type="image/x-icon" sizes="16x16 32x32 48x48" href="/favicon.ico?v=2">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/the-k/favicon-32.png?v=2">
    <link rel="icon" type="image/svg+xml" sizes="any" href="/assets/the-k/favicon.svg?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/the-k/favicon-180.png?v=2">
    <link rel="manifest" href="/site.webmanifest?v=2">
@endif
