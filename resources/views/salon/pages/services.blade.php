@extends('salon.layouts.site')
@section('content')
@include('salon.partials.page-hero')
@php($activeCategory = $salon['categories']->contains('slug',request('category')) ? request('category') : 'nails')
@php($menuCategories = $salon['categories']->sortBy(fn($category)=>array_search($category->slug,['nails','pedicures','brows-lashes','waxing','kids'])))
<section class="ks-menu-section ks-compact-menu" id="treatment-menu" data-service-menu data-active-category="{{ $activeCategory }}">
    <div class="ks-menu-toolbar"><div class="ks-container">
        <nav class="ks-menu-tabs" aria-label="Service categories">@foreach($menuCategories as $category)<a href="/services?category={{ $category->slug }}#treatment-menu" data-service-category="{{ $category->slug }}" @if($activeCategory===$category->slug) aria-current="true" @endif>{{ $category->name }} <small>{{ $category->services->count() }}</small></a>@endforeach</nav>
    </div></div>
    <div class="ks-container ks-menu-content">
        @foreach($menuCategories as $category)
        <section class="ks-menu-category" id="menu-{{ $category->slug }}" data-menu-category="{{ $category->slug }}" @if($activeCategory!==$category->slug) hidden @endif>
            <div class="ks-menu-category-heading"><div><h2>{{ $category->name }}</h2><p>{{ $category->description }}</p></div><a class="ks-text-link" href="/contact-us">Contact Us <span aria-hidden="true">↗</span></a></div>
            @if($category->slug === 'pedicures')
            <div class="ks-pedicure-menu">
                @php($seenServiceImages = [])
                @foreach($category->services as $service)
                <article class="ks-treatment" data-service-row>
                    <div class="ks-treatment-top">@if($service->image && !in_array($service->image_id,$seenServiceImages,true))@php($seenServiceImages[]=$service->image_id)<div class="ks-treatment-photo"><x-salon.picture :image="$service->image" sizes="140px"/></div>@endif<div class="ks-treatment-main"><div class="ks-treatment-title"><h3>{{ $service->name }}</h3><span>{{ $service->priceLabel() }}</span></div>@if($service->gel_price)<p class="ks-gel-price">With gel polish ${{ number_format((float)$service->gel_price,0) }}</p>@endif@if($service->massage_minutes)<span class="ks-massage-time"><x-salon.icon name="clock"/> {{ $service->massage_minutes }}-minute massage</span>@endif</div></div>
                    <details class="ks-treatment-includes"><summary>Explore this ritual <span aria-hidden="true">+</span></summary><div class="ks-treatment-extra"><p>{{ $service->description }}</p>@if(count($service->includes ?? []))<ul>@foreach($service->includes as $include)<li><x-salon.icon name="check"/>{{ $include }}</li>@endforeach</ul>@endif@if($service->price_note)<p>{{ $service->price_note }}</p>@endif</div></details>
                    <a href="/contact-us" class="ks-treatment-book" aria-label="Ask about {{ $service->name }}">Ask About This Ritual <x-salon.icon name="arrow"/></a>
                </article>
                @endforeach
            </div>
            <div class="ks-menu-more"><button class="ks-button ks-button-outline" type="button" data-menu-more hidden>Show More Rituals <x-salon.icon name="plus"/></button></div>
            @else
            <div class="ks-price-groups">
                @foreach($category->services->groupBy('group') as $group=>$services)
                <details class="ks-price-group" open><summary><h3>{{ $group ?: $category->name }}</h3><span aria-hidden="true">+</span></summary><div class="ks-price-group-body">
                    @foreach($services as $service)
                    <article class="ks-price-row" data-service-row>
                        <div class="ks-price-line"><h4>{{ $service->name }}</h4><span class="ks-price-dots"></span><strong>{{ $service->priceLabel() }}</strong></div>
                        <div class="ks-row-actions">@if($service->description || $service->duration_label || ($service->price_note && $service->price !== null))<details class="ks-service-details"><summary>Details</summary><div>@if($service->description)<p>{{ $service->description }}</p>@endif@if($service->duration_label)<small>{{ $service->duration_label }}</small>@endif@if($service->price_note && $service->price !== null)<small>{{ $service->price_note }}</small>@endif</div></details>@endif<a href="/contact-us" class="ks-row-book" aria-label="Ask about {{ $service->name }}">Ask <x-salon.icon name="arrow"/></a></div>
                    </article>
                    @endforeach
                </div></details>
                @endforeach
            </div>
            @endif
        </section>
        @endforeach
        <p class="ks-menu-empty" data-menu-empty hidden>No treatments are currently listed in this category. Please call the salon for details.</p>
    </div>
</section>

@endsection

