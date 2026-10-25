@extends('salon.layouts.site')
@section('content')
@include('salon.partials.page-hero')
@php($menuCategories = $salon['categories']->sortBy(fn($category) => array_search($category->slug, ['nails', 'pedicures', 'brows-lashes', 'waxing', 'kids'])))
<section class="ks-menu-section ks-classic-menu" id="treatment-menu">
    <div class="ks-container ks-menu-content">
        @forelse($menuCategories as $category)
            <section class="ks-menu-panel" id="menu-{{ $category->slug }}" data-menu-category="{{ $category->slug }}" aria-labelledby="menu-title-{{ $category->slug }}">
                <h2 id="menu-title-{{ $category->slug }}">{{ $category->name }}@if($category->slug === 'kids')<span class="ks-menu-subtitle">(For children under 12)</span>@endif</h2>
                @foreach($category->services->groupBy('group') as $group => $services)
                    <section class="ks-menu-group">
                        @if($group && $group !== $category->name)<h3>{{ $group }}</h3>@endif
                        @foreach($services as $service)
                            <x-salon.service-menu-row :service="$service" :heading-level="$group && $group !== $category->name ? 'h4' : 'h3'"/>
                        @endforeach
                    </section>
                @endforeach
            </section>
        @empty
            <p class="ks-menu-empty">No treatments are currently listed. Please call the salon for details.</p>
        @endforelse
    </div>
</section>
@endsection