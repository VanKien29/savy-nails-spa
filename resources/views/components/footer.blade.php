@props(['settings','navigation','isDemo'=>false])
<footer class="site-footer"><div class="container">
    <div class="footer-top"><a class="footer-brand" href="/">{{ $settings['site_name'] }}<span>↗</span></a><p>{{ $settings['slogan'] }}</p></div>
    <div class="footer-grid"><div><p class="eyebrow">Kết nối với chúng tôi</p>@if($settings['email'])<a class="footer-email" href="mailto:{{ $settings['email'] }}">{{ $settings['email'] }}</a>@endif<p>{{ $settings['address'] }}</p></div>
        <nav aria-label="Footer">@foreach($navigation->where('location','footer') as $item)<a href="{{ $item->url }}">{{ $item->label }}</a>@endforeach</nav>
        <div>@foreach($settings['social_links'] as $link)<a href="{{ $link['url'] }}" rel="noopener noreferrer" target="_blank">{{ $link['label'] }} ↗</a>@endforeach</div>
    </div>
    <div class="footer-bottom"><small>© {{ date('Y') }} {{ $settings['company_name'] }}. {{ $settings['copyright'] }}</small><small>{{ $settings['legal'] }}</small>@if(!$isDemo && $settings['cookie_consent'] && ($settings['ga4_id'] || $settings['gtm_id'] || $settings['meta_pixel_id']))<button type="button" data-reset-consent class="text-button">Tùy chọn cookie</button>@endif</div>
</div></footer>
