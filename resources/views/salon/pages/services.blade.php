@extends('salon.layouts.site')
@section('content')
@include('salon.partials.page-hero')
@php($activeCategory = $salon['categories']->contains('slug', request('category')) ? request('category') : 'nails')
@php($menuCategories = $salon['categories']->sortBy(fn($category) => array_search($category->slug, ['nails', 'pedicures', 'brows-lashes', 'waxing', 'kids'])))
<section class="ks-menu-section ks-classic-menu" id="treatment-menu" data-service-menu data-active-category="{{ $activeCategory }}">
    <div class="ks-menu-toolbar"><div class="ks-container">
        <nav class="ks-menu-tabs" aria-label="Service categories">
            @foreach($menuCategories as $category)
                <a href="/services?category={{ $category->slug }}#treatment-menu" data-service-category="{{ $category->slug }}" @if($activeCategory === $category->slug) aria-current="true" @endif>{{ $category->name }} <small>{{ $category->services->count() }}</small></a>
            @endforeach
        </nav>
    </div></div>
    <div class="ks-container ks-menu-content">
        @foreach($menuCategories as $category)
            <section class="ks-menu-panel" id="menu-{{ $category->slug }}" data-menu-category="{{ $category->slug }}" aria-labelledby="menu-title-{{ $category->slug }}" @if($activeCategory !== $category->slug) hidden @endif>
                <h2 id="menu-title-{{ $category->slug }}">{{ $category->name }}</h2>
                @foreach($category->services->groupBy('group') as $group => $services)
                    <section class="ks-menu-group">
                        @if($group && $group !== $category->name)<h3>{{ $group }}</h3>@endif
                        @foreach($services as $service)
                            <x-salon.service-menu-row :service="$service" :heading-level="$group && $group !== $category->name ? 'h4' : 'h3'"/>
                        @endforeach
                    </section>
                @endforeach
            </section>
        @endforeach
        <p class="ks-menu-empty" data-menu-empty hidden>No treatments are currently listed in this category. Please call the salon for details.</p>
    </div>
</section>
@endsection