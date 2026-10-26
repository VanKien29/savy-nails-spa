@extends('salon.layouts.site')
@section('content')
@include('salon.partials.page-hero')
@php($story = $sections->firstWhere('id','story'))
@php($promise = $sections->firstWhere('id','promise'))
<section class="ks-section"><div class="ks-container ks-split ks-about-story"><div class="ks-story-frame" data-animate="fade-up"><x-salon.picture :image="$salon['images']->get($salon['profile']['about_image'] ?? 'blush-manicure')" lcp/><div class="ks-frame-caption"><span>THE K</span><small>YOUR OWN KIND OF LUXURY</small></div></div><div class="ks-story-copy" data-animate="fade-up"><h2>{{ $story['heading'] }}</h2><p>{{ $story['body'] }}</p><p>{{ $promise['body'] }}</p><div class="ks-story-quote"><span aria-hidden="true"><x-salon.icon name="sparkle"/></span><p>A little moment.<br><em>A beautiful difference.</em></p></div><a href="/services" class="ks-button">Discover Our Services</a></div></div></section>
<section class="ks-section ks-soft-section"><div class="ks-container"><x-salon.section-heading eyebrow="The K point of view" :heading="$promise['heading']" center/><div class="ks-values-grid">@foreach($promise['items'] as $item)<article data-animate="fade-up"><span class="ks-value-number">0{{ $loop->iteration }}</span><x-salon.icon name="sparkle"/><h3>{{ $item['title'] }}</h3><p>{{ $item['text'] }}</p></article>@endforeach</div></div></section>
<section class="ks-section"><div class="ks-container ks-brand-story"><div class="ks-brand-art"><x-salon.picture :image="$salon['images']->get('brand-logo')"/></div><div data-animate="fade-up"><h2>One destination.<br><em>So many beautiful possibilities.</em></h2><p>{{ $sections->firstWhere('id','intro')['body'] }}</p><p>Visit us at {{ $settings['address'] }}. For questions about your next treatment, call {{ $settings['hotline'] }} — we would love to help.</p><a class="ks-text-link" href="/contact-us">Find Your Way to THE K</a></div></div></section>

@endsection


