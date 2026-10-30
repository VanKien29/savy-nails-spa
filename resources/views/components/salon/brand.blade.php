@props(['settings', 'media', 'eager' => false, 'footer' => false])
@php($logo = $media->get($settings['logo_id']) ?? $media->get($settings['mobile_logo_id']))
@php($mobileLogo = $media->get($settings['mobile_logo_id']))
<a href="/" {{ $attributes->class(['ks-brand', 'ks-footer-brand' => $footer]) }} title="{{ $settings['site_name'] }} — Home">
    @if($logo)
        <x-media.picture :media="$logo" :mobile-media="$mobileLogo" :mobile-breakpoint="730" :alt="$settings['site_name']" :eager="$eager" :sizes="$footer ? '(max-width: 730px) 186px, 210px' : '(max-width: 730px) 186px, 210px'" class="ks-brand-logo"/>
    @else
        @php($brandCopy = app(\App\Services\SalonContent::class)->all()['profile']['copy']['global'])
        <span class="ks-monogram" aria-hidden="true">{{ $brandCopy['brand_monogram'] }}<span><x-salon.icon name="sparkle"/></span></span><span><strong>{{ $brandCopy['brand_title'] }}</strong><small>{{ $brandCopy['brand_subtitle'] }}</small></span>
    @endif
</a>
