@extends('salon.layouts.site')
@section('content')
@php($hero=$sections->firstWhere('id','hero'))
@php($intro=$sections->firstWhere('id','intro'))
@php($royal=$salon['categories']->flatMap->services->firstWhere('slug','pedicures-the-k-signature-royal-pedicure'))
@php($heroSlides=collect($salon['profile']['hero_slides'] ?? [$salon['profile']['hero_image']])->map(fn($key)=>$salon['images']->get($key))->filter())
@if($heroSlides->isEmpty()) @php($heroSlides=collect([$salon['images']->get($salon['profile']['hero_image'])])->filter()) @endif
<section class="ks-home-hero kl-hero kl-hero-editorial" aria-label="Welcome to THE K" data-hero-slideshow>
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
        <h1><span>{{ $hero['heading'] }}</span><em>{{ $hero['items'][0]['title'] }}</em></h1>
        <p>{{ $hero['body'] }}</p>
        <div class="ks-hero-actions"><a href="/contact-us" class="ks-button kl-button-gold">Booking <x-salon.icon name="arrow"/></a><a href="/services" class="ks-text-link">Explore the Menu <span aria-hidden="true"><x-salon.icon name="arrow"/></span></a></div>
        <div class="kl-hero-foot"><div class="kl-hero-social" aria-label="Social links">@foreach($settings['social_links'] as $social) @if(in_array(strtolower($social['label']), ['facebook','instagram'], true))<a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="Follow THE K on {{ $social['label'] }}"><x-salon.icon :name="strtolower($social['label'])"/></a>@endif @endforeach</div></div>
    </div>
</section>
<section class="ks-section kl-welcome" id="our-story"><div class="ks-container kl-welcome-grid"><div class="kl-story-image" data-animate="slide-right"><x-salon.picture :image="$salon['images']->get($salon['profile']['story_image'])" sizes="(max-width:730px) 90vw, 38vw"/></div><div class="ks-story-copy" data-animate="slide-left"><h2>{{ $intro['heading'] }}</h2><p>{{ $intro['body'] }}</p><a href="/about-us" class="ks-text-link">Discover Our Story <span aria-hidden="true"><x-salon.icon name="arrow"/></span></a></div></div><x-salon.ornament class="kl-welcome-art"/></section>
@php($collection=$sections->firstWhere('id','collection'))
<section class="ks-section kl-collection"><div class="ks-container"><div class="ks-heading-row"><x-salon.section-heading :eyebrow="$collection['eyebrow']" :heading="$collection['heading']" :body="$collection['body']"/><a href="/services" class="ks-text-link">Our Full Menu <span aria-hidden="true"><x-salon.icon name="arrow"/></span></a></div><div class="kl-collection-grid">
@foreach($salon['categories']->sortBy(fn($c)=>array_search($c->slug,['nails','pedicures','brows-lashes','waxing','kids'])) as $category)
<a href="/services?category={{ $category->slug }}#treatment-menu" class="kl-collection-card" data-animate="fade-up"><span class="kl-card-number">0{{ $loop->iteration }}</span><div><h3>{{ $category->name }}</h3><p>{{ $category->description }}</p></div><footer><span>From ${{ number_format((float)$category->services->whereNotNull('price')->min('price'),0) }}</span><x-salon.icon name="arrow"/></footer></a>
@endforeach
</div></div></section>
@php($ritual=$sections->firstWhere('id','ritual'))
<section class="ks-section kl-ritual"><div class="ks-container"><div class="kl-ritual-heading"><h2>{{ $ritual['heading'] }}</h2><p>{{ $ritual['body'] }}</p></div><div class="kl-ritual-editorial">@foreach($ritual['items'] as $item)<article class="kl-ritual-feature" data-animate="fade-up"><div class="kl-ritual-image"><x-salon.picture :image="$salon['images']->get($item['label'])" sizes="(max-width:730px) 90vw, 48vw"/></div><div class="kl-ritual-copy"><h3>{{ $item['title'] }}</h3><p>{{ $item['text'] }}</p><a href="/contact-us" class="ks-text-link">Ask About This Ritual <span aria-hidden="true"><x-salon.icon name="arrow"/></span></a></div></article>@endforeach</div></div><x-salon.ornament kind="orbit" class="kl-ritual-art"/></section>
@php($edit=$sections->firstWhere('id','lookbook'))
<section class="ks-section kl-lookbook"><div class="ks-container"><x-salon.section-heading :eyebrow="$edit['eyebrow']" :heading="$edit['heading']" :body="$edit['body']" center/><div class="ks-gallery-preview kl-look-rail" data-inspiration-slider tabindex="0" aria-label="The signature nail edit" data-native-scroll>
@foreach($salon['profile']['home_lookbook'] as $key)
@php($image=$salon['images']->get($key))
<figure class="kl-look" data-animate="fade-up"><button type="button" class="ks-gallery-tile" data-lightbox="{{ $image->url() }}" data-caption="{{ $image->caption }} · Editorial style inspiration" aria-label="View {{ $image->caption }}"><x-salon.picture :image="$image" sizes="(max-width:730px) 75vw, 30vw"/><span class="ks-gallery-overlay"><x-salon.icon name="plus"/></span></button><figcaption><span>0{{ $loop->iteration }}</span><h3>{{ $image->caption }}</h3><span aria-hidden="true"><x-salon.icon name="arrow"/></span></figcaption></figure>
@endforeach
</div><div class="ks-slider-controls"><span>Editorial style inspiration.</span><div><button type="button" data-slide="-1" aria-label="Previous inspiration"><x-salon.icon name="arrow"/></button><button type="button" data-slide="1" aria-label="Next inspiration"><x-salon.icon name="arrow"/></button></div></div><div class="ks-center-action"><a href="/gallery" class="ks-text-link">Explore a Different Edit <span aria-hidden="true"><x-salon.icon name="arrow"/></span></a></div></div></section>
@php($journey=$sections->firstWhere('id','journey'))
<section class="ks-section kl-journey"><div class="ks-container"><x-salon.section-heading :eyebrow="$journey['eyebrow']" :heading="$journey['heading']" :body="$journey['body']" center/><div class="kl-journey-grid"><div class="kl-progress-line" data-scroll-line aria-hidden="true"><span></span></div>@foreach($journey['items'] as $item)<article data-animate="fade-up"><span>0{{ $loop->iteration }}</span><h3>{{ $item['title'] }}</h3><p>{{ $item['text'] }}</p></article>@endforeach</div></div></section>
@php($questions=$sections->firstWhere('id','questions'))
<section class="ks-section kl-questions"><div class="ks-container kl-question-grid"><div data-animate="fade-up"><h2>{{ $questions['heading'] }}</h2><p>{{ $questions['body'] }}</p><a href="tel:{{ preg_replace('/[^0-9+]/','',$settings['hotline']) }}" class="ks-text-link">We're a Call Away <span aria-hidden="true"><x-salon.icon name="arrow"/></span></a></div><div class="kl-faqs">@foreach($questions['items'] as $item)<details><summary>{{ $item['title'] }}<span aria-hidden="true"><x-salon.icon name="plus"/></span></summary><p>{{ $item['text'] }}</p></details>@endforeach</div></div></section>
@php($finale=$sections->firstWhere('id','finale'))
<section class="kl-finale kl-dark"><div class="ks-container"><h2>{{ $finale['heading'] }}</h2><p>{{ $finale['body'] }}</p><a href="/contact-us" class="ks-button kl-button-gold">Contact Us <x-salon.icon name="arrow"/></a></div><x-salon.ornament kind="orbit"/></section>
@endsection
