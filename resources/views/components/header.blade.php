@props(['settings','navigation','media'])
<header class="site-header" x-data="{open:false}" @keydown.escape.window="open=false">
    <div class="container header-inner">
        <a class="brand" href="/" title="{{ $settings['site_name'] }} — Trang chủ">
            @if($logo = $media->get($settings['logo_id']))
                <x-media.picture :media="$logo" :alt="$settings['site_name']" class="desktop-logo" />
                @if($mobile = $media->get($settings['mobile_logo_id']))<x-media.picture :media="$mobile" :alt="$settings['site_name']" class="mobile-logo" />@endif
            @else<span class="brand-symbol" aria-hidden="true">f<span>↗</span></span><span>{{ $settings['site_name'] }}<span class="brand-dot" aria-hidden="true">.</span></span>@endif
        </a>
        <nav class="desktop-nav" aria-label="Điều hướng chính">@foreach($navigation->where('location','header') as $item)<a href="{{ $item->url }}">{{ $item->label }}</a>@endforeach</nav>
        <a class="button button-dark header-cta" href="/#lien-he">Cùng bắt đầu <span aria-hidden="true">↗</span></a>
        <button type="button" class="menu-toggle" @click="open=!open" :aria-expanded="open" aria-controls="mobile-nav" aria-label="Mở menu">☰</button>
    </div>
    <nav id="mobile-nav" class="mobile-nav" :class="{'is-open':open}" aria-label="Điều hướng di động" @click="open=false">
        @foreach($navigation->where('location','header') as $item)<a href="{{ $item->url }}">{{ $item->label }}</a>@endforeach
        <a href="/#lien-he">Cùng bắt đầu ↗</a>
    </nav>
</header>
