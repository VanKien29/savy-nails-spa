@php
    $desktopLogo = $media->get($settings['logo_id'] ?? null);
    $mobileLogo = ($mobile ?? false) ? $media->get($settings['mobile_logo_id'] ?? null) : null;
@endphp
@if($desktopLogo || $mobileLogo)
    <x-media.picture :media="$desktopLogo ?? $mobileLogo" :mobile-media="$mobileLogo" :alt="$settings['site_name']" :sizes="$sizes" :eager="$eager ?? false" :class="$class"/>
@else
    @include('salon.partials.reference-image', ['key' => 'savy-logo-art', 'alt' => $settings['site_name']])
@endif
