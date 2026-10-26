@props(['service', 'headingLevel' => 'h3', 'pairedPrices' => false, 'compact' => false])
@php($heading = in_array($headingLevel, ['h3', 'h4'], true) ? $headingLevel : 'h3')
@php($menuCopy = app(\App\Services\SalonContent::class)->all()['profile']['copy']['menu'])
<article class="ks-menu-item" data-service-row>
    <div class="ks-menu-item-line">
        <{{ $heading }}>{{ $service->name }}</{{ $heading }}>
        <span class="ks-menu-item-dots" aria-hidden="true"></span>
        @if($pairedPrices)
            <div class="ks-menu-price-pair">
                <strong><span class="ks-visually-hidden">{{ $menuCopy['full_set_label'] }}: </span>{{ $service->priceLabel() }}</strong>
                <strong>@if($service->fill_price !== null)<span class="ks-visually-hidden">{{ $menuCopy['fill_label'] }}: </span>{{ $service::moneyLabel($service->fill_price) }}{{ $service->price_from ? '+' : '' }}@endif</strong>
            </div>
        @else
            <strong>{{ $service->priceLabel() }}</strong>
        @endif
    </div>
    @if($service->gel_price !== null)<p class="ks-menu-item-option">{{ $menuCopy['gel_label'] }} {{ $service::moneyLabel($service->gel_price) }}</p>@endif
    @if($compact && ($service->description || count($service->includes ?? []) || $service->duration_label || ($service->price_note && $service->price !== null)))<details class="menu-editor-details"><summary>Xem chi tiết dịch vụ</summary>@endif
    @if($service->description)
        @foreach(preg_split('/\R\s*\R/u', $service->description) as $paragraph)<p>{{ $paragraph }}</p>@endforeach
    @endif
    @if(count($service->includes ?? []))
        <div class="ks-menu-item-includes">
            <p>{{ $service->includes_label ?: $menuCopy['includes_label'] }}</p>
            <ul>@foreach($service->includes as $item)<li>{{ $item }}</li>@endforeach</ul>
        </div>
    @endif
    @if($service->duration_label)<p class="ks-menu-item-note">{{ $service->duration_label }}</p>@endif
    @if($service->price_note && $service->price !== null)<p class="ks-menu-item-note">{{ $service->price_note }}</p>@endif
    @if($compact && ($service->description || count($service->includes ?? []) || $service->duration_label || ($service->price_note && $service->price !== null)))</details>@endif
</article>
