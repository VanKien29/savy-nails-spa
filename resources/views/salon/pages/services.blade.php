@extends('salon.layouts.site')
@section('content')
@include('salon.partials.page-hero')
@php($menuCategories = $salon['categories'])
<section class="ks-menu-section ks-classic-menu" id="treatment-menu">
    <div class="ks-container ks-menu-content">
        @forelse($menuCategories as $category)
            <section class="ks-menu-panel" id="menu-{{ $category->slug }}" data-menu-category="{{ $category->slug }}" aria-labelledby="menu-title-{{ $category->slug }}">
                <h2 id="menu-title-{{ $category->slug }}" @class(['ks-visually-hidden' => $category->slug === 'brows-lashes'])>{{ $category->name }}@if($category->slug === 'kids')<span class="ks-menu-subtitle">{{ $copy['menu']['kids_note'] }}</span>@endif</h2>
                @foreach($category->services->groupBy('group') as $group => $services)
                    @php($pairedPrices = $services->contains(fn ($service) => $service->fill_price !== null))
                    <section @class(['ks-menu-group', 'ks-menu-group-paired' => $pairedPrices, 'ks-menu-beauty-group' => $category->slug === 'brows-lashes'])>
                        @if($group && $group !== $category->name)<h3>{{ $group }}</h3>@endif
                        @if($pairedPrices)
                            <div class="ks-menu-price-headings" aria-hidden="true"><span>{{ $copy['menu']['full_set_label'] }}</span><span>{{ $copy['menu']['fill_label'] }}</span></div>
                        @endif
                        @foreach($services as $service)
                            <x-salon.service-menu-row :service="$service" :paired-prices="$pairedPrices" :heading-level="$group && $group !== $category->name ? 'h4' : 'h3'"/>
                        @endforeach
                        @if($category->slug === 'brows-lashes' && $loop->first && count($salon['profile']['menu_brow_images'] ?? []))
                            <div class="ks-menu-brow-photos">
                                @foreach($salon['profile']['menu_brow_images'] as $key)
                                    <x-salon.picture :image="$salon['images']->get($key)" sizes="(max-width: 730px) 28vw, 280px"/>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endforeach
            </section>
        @empty
            <p class="ks-menu-empty">{{ $copy['contact']['menu_empty'] }}</p>
        @endforelse
    </div>
</section>
@endsection
