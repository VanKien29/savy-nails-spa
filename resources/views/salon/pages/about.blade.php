@extends('salon.layouts.site')
@section('content')
@include('salon.partials.page-hero')
@php($story = $sections->firstWhere('id','story') ?? [])
@php($promise = $sections->firstWhere('id','promise') ?? [])
<section class="ks-section"><div class="ks-container ks-split ks-about-story"><div class="ks-story-frame" data-animate="fade-up"><x-salon.picture :image="$salon['images']->get($salon['profile']['about_image'] ?? 'blush-manicure')" lcp/><div class="ks-frame-caption"><span>{{ $copy['about']['frame_title'] }}</span><small>{{ $copy['about']['frame_caption'] }}</small></div></div><div class="ks-story-copy" data-animate="fade-up"><h2>{{ $story['heading'] ?? '' }}</h2><p>{{ $story['body'] ?? '' }}</p><p>{{ $promise['body'] ?? '' }}</p><div class="ks-story-quote"><span aria-hidden="true"><x-salon.icon name="sparkle"/></span><p>{{ $copy['about']['quote'] }}<br><em>{{ $copy['about']['quote_accent'] }}</em></p></div><a href="{{ $copy['about']['services_url'] }}" class="ks-button">{{ $copy['about']['services_label'] }} </a></div></div></section>
<section class="ks-section ks-soft-section"><div class="ks-container"><x-salon.section-heading :eyebrow="$copy['about']['values_eyebrow']" :heading="$promise['heading'] ?? ''" center/><div class="ks-values-grid">@foreach($promise['items'] ?? [] as $item)<article data-animate="fade-up"><span class="ks-value-number">0{{ $loop->iteration }}</span><x-salon.icon name="sparkle"/><h3>{{ $item['title'] }}</h3><p>{{ $item['text'] }}</p></article>@endforeach</div></div></section>
<section class="ks-section"><div class="ks-container ks-brand-story"><div class="ks-brand-art"><x-salon.picture :image="$salon['images']->get($salon['profile']['about_brand_image'] ?? 'brand-logo')"/></div><div data-animate="fade-up"><h2>{{ $copy['about']['brand_heading'] }}<br><em>{{ $copy['about']['brand_accent'] }}</em></h2><p>{{ $sections->firstWhere('id','intro')['body'] ?? '' }}</p><p>{{ $settings['address'] }} · {{ $settings['hotline'] }}. {{ $copy['about']['visit_note'] }}</p><a class="ks-text-link" href="{{ $copy['about']['contact_url'] }}">{{ $copy['about']['contact_label'] }} </a></div></div></section>

@endsection


