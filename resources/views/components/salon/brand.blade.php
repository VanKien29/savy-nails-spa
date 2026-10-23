@props(['settings', 'media', 'eager' => false, 'footer' => false])
@php($logo = $media->get($settings['logo_id']) ?? $media->get($settings['mobile_logo_id']))
@php($mobileLogo = $media->get($settings['mobile_logo_id']))
<a href="/" {{ $attributes->class(['ks-brand', 'ks-footer-brand' => $footer]) }} title="{{ $settings['site_name'] }} — Home">
    @if($logo)
        <x-media.picture :media="$logo" :mobile-media="$mobileLogo" :mobile-breakpoint="730" :alt="$settings['site_name']" :eager="$eager" sizes="(max-width: 730px) 186px, 210px" class="ks-brand-logo"/>
    @else
        <span class="ks-monogram" aria-hidden="true">K<span><x-salon.icon name="sparkle"/></span></span><span><strong>THE K</strong><small>LUXURY NAIL BAR</small></span>
    @endif
</a>
