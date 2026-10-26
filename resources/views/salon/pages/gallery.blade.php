@extends('salon.layouts.site')
@section('content')
@include('salon.partials.page-hero')
<section class="ks-section ks-gallery-page" data-gallery>
    <div class="ks-container">
        <div class="ks-gallery-grid ks-gallery-masonry">
            @foreach($salon['images']->where('show_in_gallery',true) as $image)
                <figure class="ks-gallery-item" data-animate="fade-up">
                    <x-salon.picture :image="$image" sizes="(max-width: 730px) 45vw, (max-width: 1100px) 30vw, 344px" :lcp="$loop->first"/>
                </figure>
            @endforeach
        </div>
        @if($instagram = collect($settings['social_links'])->firstWhere('label','Instagram'))
            <div class="ks-instagram-callout">
                <x-salon.icon name="instagram"/>
                <h2>{{ $copy['gallery']['social_heading'] }}</h2>
                <p>{{ $copy['gallery']['social_body'] }}</p>
                <a href="{{ $instagram['url'] }}" class="ks-button ks-button-outline" target="_blank" rel="noopener noreferrer">{{ $copy['gallery']['social_label'] }} </a>
            </div>
        @endif
    </div>
</section>

@endsection
