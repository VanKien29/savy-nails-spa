@props(['service', 'headingLevel' => 'h3'])
@php($heading = in_array($headingLevel, ['h3', 'h4'], true) ? $headingLevel : 'h3')
<article class="ks-menu-item" data-service-row>
    <div class="ks-menu-item-line">
        <{{ $heading }}>{{ $service->name }}</{{ $heading }}>
        <span class="ks-menu-item-dots" aria-hidden="true"></span>
        <strong>{{ $service->priceLabel() }}</strong>
    </div>
    @if($service->gel_price)<p class="ks-menu-item-option">With gel polish ${{ number_format((float) $service->gel_price, 0) }}</p>@endif
    @if($service->description)<p>{{ $service->description }}</p>@endif
    @if(count($service->includes ?? []))<p class="ks-menu-item-includes"><span>Includes:</span> {{ implode(', ', $service->includes) }}.</p>@endif
    @if($service->duration_label)<p class="ks-menu-item-note">{{ $service->duration_label }}</p>@endif
    @if($service->price_note && $service->price !== null)<p class="ks-menu-item-note">{{ $service->price_note }}</p>@endif
</article>