@php($heroSlides=collect($salon['profile']['hero_slides'] ?? [$salon['profile']['hero_image'] ?? ''])->map(fn($key)=>$salon['images']->get($key))->filter())
@if($heroSlides->isEmpty()) @php($heroSlides=collect([$salon['images']->get($salon['profile']['hero_image'] ?? '')])->filter()) @endif
<section class="ks-home-hero kl-hero kl-hero-editorial" aria-label="Welcome to {{ $settings['site_name'] }}" data-hero-slideshow>
    <div class="kl-hero-slides" aria-live="off">
        @foreach($heroSlides as $slide)
        <div class="kl-hero-slide {{ $loop->first ? 'is-active' : '' }}" data-hero-slide="{{ $loop->index }}" aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
            <x-salon.picture :image="$slide" :lcp="$loop->first" sizes="(max-width:960px) 100vw, 65vw" class="ks-hero-image"/>
        </div>
        @endforeach
    @if($heroSlides->count() > 1)
    <div class="kl-hero-controls" aria-label="Hero slideshow progress"><div class="kl-hero-dots">@foreach($heroSlides as $slide)<button type="button" data-hero-dot="{{ $loop->index }}" aria-label="Show hero image {{ $loop->iteration }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"></button>@endforeach</div></div>
    @endif
    </div>
    <div class="ks-container ks-hero-content">
        <h1><span>{{ ($hero['heading'] ?? '') }}</span><em>{{ $hero['items'][0]['title'] ?? '' }}</em></h1>
        <p>{{ ($hero['body'] ?? '') }}</p>
        <div class="ks-hero-actions"><a href="{{ $copy['global']['booking_url'] }}" class="ks-button kl-button-gold">{{ $copy['global']['booking_label'] }} </a><a href="{{ $copy['home']['menu_url'] }}" class="ks-text-link">{{ $copy['home']['menu_label'] }} </a></div>
        <div class="kl-hero-foot"><div class="kl-hero-social" aria-label="Social links">@foreach($settings['social_links'] as $social) @if(in_array(strtolower($social['label']), ['facebook','instagram'], true))<a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="Follow {{ $settings['site_name'] }} on {{ $social['label'] }}"><x-salon.icon :name="strtolower($social['label'])"/></a>@endif @endforeach</div></div>
    </div>
</section>
