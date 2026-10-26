<header class="ks-header">
    <div class="ks-container ks-header-inner">
        <x-salon.brand :settings="$settings" :media="$media" eager/>
        <nav class="ks-desktop-nav" aria-label="Main navigation">@foreach($navigation->where('location','header') as $item)<a href="{{ $item->url }}" @if($item->url === $page->path()) aria-current="page" @endif>{{ $item->label }}</a>@endforeach</nav>
        <a href="{{ $copy['global']['booking_url'] }}" class="ks-button ks-header-book">{{ $copy['global']['booking_label'] }} </a>
        <button type="button" class="ks-menu-toggle" data-salon-nav-toggle aria-expanded="false" aria-controls="mobile-nav" aria-label="Toggle navigation"><span></span><span></span></button>
    </div>
    <nav id="mobile-nav" class="ks-mobile-nav" aria-label="Mobile navigation">@foreach($navigation->where('location','header') as $item)<a href="{{ $item->url }}" @if($item->url === $page->path()) aria-current="page" @endif>{{ $item->label }} </a>@endforeach<a href="{{ $copy['global']['booking_url'] }}">{{ $copy['global']['booking_label'] }} </a></nav>
</header>
