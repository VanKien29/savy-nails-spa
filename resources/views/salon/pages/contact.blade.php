@extends('salon.layouts.site')
@section('content')
@include('salon.partials.page-hero')
<section class="ks-section">
    <div class="ks-container ks-contact-grid">
        <div class="ks-contact-details" data-animate="fade-up">
            <h2>{{ $copy['contact']['heading'] }}<br><em>{{ $copy['contact']['heading_accent'] }}</em></h2>
            <div class="ks-contact-item"><x-salon.icon name="pin"/><div><h3>{{ $copy['contact']['visit_label'] }}</h3><p>{{ $settings['address'] }}</p><a href="{{ $settings['maps_url'] }}" target="_blank" rel="noopener noreferrer">{{ $copy['contact']['directions_label'] }}</a></div></div>
            <div class="ks-contact-item"><x-salon.icon name="phone"/><div><h3>{{ $copy['contact']['phone_label'] }}</h3><a class="ks-contact-number" href="tel:{{ preg_replace('/[^0-9+]/','',$settings['hotline']) }}">{{ $settings['hotline'] }}</a></div></div>
            <div class="ks-contact-item"><x-salon.icon name="clock"/><div><h3>{{ $copy['contact']['hours_label'] }}</h3>@forelse($salon['profile']['hours'] as $hour)<p>{{ $hour['days'] }} <span>{{ $hour['time'] }}</span></p>@empty<p>{{ $salon['profile']['hours_note'] }}</p>@endforelse</div></div>
            <div class="ks-contact-item ks-contact-social-row"><x-salon.icon name="sparkle"/><div><h3>{{ $copy['contact']['social_label'] }}</h3><div class="ks-contact-social-pills">@foreach($settings['social_links'] as $social)<a href="{{ $social['url'] }}" class="ks-social-pill" target="_blank" rel="noopener noreferrer" aria-label="Visit THE K on {{ $social['label'] }}"><x-salon.icon :name="strtolower($social['label'])"/><span>{{ $social['label'] }}</span></a>@endforeach</div></div></div>
        </div>
        <div class="ks-contact-visual" data-animate="fade-up">
            <div class="ks-contact-photo"><x-salon.picture :image="$salon['images']->get($salon['profile']['contact_image'] ?? 'contact-welcome')" sizes="(max-width:730px) 100vw, 480px"/></div>
        </div>
    </div>
</section>
<section class="ks-location-band"><div class="ks-container"><div><h2>{{ $copy['contact']['map_heading'] }}<br><em>{{ $copy['contact']['map_accent'] }}</em></h2><p>{{ $settings['address'] }}</p><a class="ks-button" href="{{ $settings['maps_url'] }}" target="_blank" rel="noopener noreferrer">{{ $copy['contact']['map_label'] }}</a></div>@if($settings['maps_embed_url'])
    <div class="ks-location-map" data-lenis-prevent><iframe src="{{ $settings['maps_embed_url'] }}" title="Google Maps — {{ $settings['address'] }}" width="600" height="450" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div>
@endif</div></section>
@endsection
