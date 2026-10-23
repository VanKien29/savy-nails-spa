@props(['eyebrow','heading','body'=>null,'center'=>false])
<div {{ $attributes->class(['ks-section-heading','is-centered'=>$center]) }} data-animate="fade-up"><h2>{{ $heading }}</h2>@if($body)<p>{{ $body }}</p>@endif</div>
