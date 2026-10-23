@props(['section','page','media','settings','isDemo'=>false,'first'=>false])
@php($type = $section['type'])
@php($items = $section['items'] ?? [])
@php($image = $media->get($section['media_id'] ?? null) ?? ($type === 'hero' ? $page->heroMedia : null))
@if($type === 'hero')
<section class="hero hero-{{ $section['variant'] }}" aria-label="{{ $section['heading'] ?? $page->title }}">
    <div class="container hero-grid">
        <div class="hero-copy"><div class="availability"><span></span>{{ $section['eyebrow'] ?? '' }}</div>
            @if($first)<h1 id="hero-title">{{ $section['heading'] ?? $page->title }}</h1>@else<h2>{{ $section['heading'] ?? $page->title }}</h2>@endif<p class="hero-description">{{ $section['body'] ?? '' }}</p>
            <div class="hero-actions"><x-ui.button :href="$section['button_url'] ?? '#lien-he'">{{ $section['button_label'] ?? 'Cùng bắt đầu' }} <span aria-hidden="true">↗</span></x-ui.button><a class="text-link" href="/#dich-vu">Khám phá giải pháp <span aria-hidden="true">↓</span></a></div>
            <div class="hero-note"><span class="small-orbit" aria-hidden="true">✳</span><p>Mỗi thương hiệu có một câu chuyện.<br><strong>Chúng tôi giúp bạn kể theo cách riêng.</strong></p></div>
        </div>
        <div class="hero-art">
            @if($image)<x-media.picture :media="$image" variant="hero" :alt="$image->alt" sizes="(max-width: 800px) 100vw, 55vw" />@else<img src="/assets/studio-space.svg" alt="Minh họa không gian sáng tạo với các khối hình học màu xanh" width="800" height="850" fetchpriority="high">@endif
            <div class="art-caption"><span>CHIẾN LƯỢC × THIẾT KẾ × CÔNG NGHỆ</span><span aria-hidden="true">↗</span></div>
        </div>
    </div>
    <div class="container hero-bottom"><span>Ý tưởng tốt xứng đáng với một khởi đầu tốt.</span><a href="/#dich-vu">Cuộn để khám phá <span aria-hidden="true">↓</span></a></div>
</section>
@elseif($type === 'contact')
    @if($settings['features']['contact'])<section class="section contact-section" id="{{ $section['id'] ?? 'lien-he' }}"><div class="container contact-grid"><div data-animate="fade-up"><p class="eyebrow">{{ $section['eyebrow'] ?? 'Kết nối' }}</p><h2>{{ $section['heading'] ?? 'Bắt đầu một điều ý nghĩa.' }}</h2><p>{{ $section['body'] ?? '' }}</p>@if($settings['email'])<a class="contact-email" href="mailto:{{ $settings['email'] }}">{{ $settings['email'] }} ↗</a>@endif</div><x-forms.contact :page="$page" :is-demo="$isDemo" /></div></section>@endif
@elseif($type === 'cta')
<section class="section" id="{{ $section['id'] ?? 'cta' }}"><div class="container"><div class="cta-panel" data-animate="fade-up"><span class="eyebrow">{{ $section['eyebrow'] ?? '' }}</span><h2>{{ $section['heading'] ?? '' }}</h2><p>{{ $section['body'] ?? '' }}</p><x-ui.button :href="$section['button_url'] ?? '#lien-he'" variant="dark">{{ $section['button_label'] ?? 'Kết nối ngay' }} ↗</x-ui.button><span class="cta-orbit" aria-hidden="true">✳</span></div></div></section>
@else
<section class="section section-{{ $type }} variant-{{ $section['variant'] }}" id="{{ $section['id'] ?? $type }}"><div class="container">
    <div class="section-heading" data-animate="fade-up"><p class="eyebrow"><span class="eyebrow-dot"></span>{{ $section['eyebrow'] ?? '' }}</p><div><h2>{{ $section['heading'] ?? '' }}</h2>@if($section['body'] ?? null)<p class="section-intro">{{ $section['body'] }}</p>@endif</div></div>
    @switch($type)
        @case('faq')<div class="faq-list">@foreach($items as $item)<x-ui.accordion :title="$item['title'] ?? ''">{{ $item['text'] ?? '' }}</x-ui.accordion>@endforeach</div>@break
        @case('statistics')<div class="statistics-grid">@foreach($items as $item)<div><strong data-count="{{ is_numeric($item['value'] ?? '') ? $item['value'] : '' }}">{{ $item['value'] ?? '' }}</strong><p>{{ $item['label'] ?? $item['title'] ?? '' }}</p></div>@endforeach</div>@break
        @case('gallery') @case('products')
            <div class="project-grid">@foreach($items as $item)<article class="project-card" data-animate="fade-up">@if($photo = $media->get($item['media_id'] ?? null))<button type="button" class="gallery-trigger" data-lightbox="{{ $photo->url() }}" data-caption="{{ $photo->alt }}"><x-media.picture :media="$photo" :alt="$photo->alt" /></button>@else<div class="project-art project-art-{{ $loop->index % 2 }}"><span>{{ $item['value'] ?? 'forma' }}</span><span class="project-star" aria-hidden="true">✳</span><small>Ý TƯỞNG THIẾT KẾ · DEMO</small></div>@endif<div class="project-meta"><div><h3>{{ $item['title'] ?? '' }}</h3><p>{{ $item['text'] ?? '' }}</p></div><span aria-hidden="true">↗</span></div></article>@endforeach</div>@break
        @case('testimonials')<div class="cards-grid">@foreach($items as $item)<figure class="testimonial" data-animate="fade-up"><span class="demo-label">DỮ LIỆU MINH HỌA</span><blockquote>“{{ $item['text'] ?? '' }}”</blockquote><figcaption>{{ $item['title'] ?? '' }}<small>{{ $item['label'] ?? '' }}</small></figcaption></figure>@endforeach</div>@break
        @case('about')<div class="about-grid">@if($image)<x-media.picture :media="$image" :alt="$image->alt" />@else<div class="about-art" aria-hidden="true"><span>f.</span><div>GÓC NHÌN MỚI<br>GIÁ TRỊ DÀI LÂU</div></div>@endif<div>@foreach($items as $item)<div class="about-point"><h3>{{ $item['title'] ?? '' }}</h3><p>{{ $item['text'] ?? '' }}</p></div>@endforeach</div></div>@break
        @case('logos')<div class="logo-cloud">@foreach($items as $item)<span>{{ $item['title'] ?? '' }}</span>@endforeach</div>@break
        @case('map')<div class="map-card"><p>{{ $settings['address'] }}</p>@if($settings['maps_url'])<x-ui.button :href="$settings['maps_url']" variant="outline">Xem trên Google Maps ↗</x-ui.button>@endif</div>@break
        @default
            <div class="cards-grid">@foreach($items as $item)<article class="service-card {{ $type === 'pricing' && $loop->index === 1 ? 'featured' : '' }}" data-animate="fade-up"><div class="card-top"><span class="card-number">{{ str_pad($loop->iteration,2,'0',STR_PAD_LEFT) }}</span><x-ui.icon name="arrow" /></div><h3>{{ $item['title'] ?? '' }}</h3>@if($item['value'] ?? null)<strong class="price">{{ $item['value'] }}</strong>@endif<p>{{ $item['text'] ?? '' }}</p>@if($item['label'] ?? null)<span class="card-tag">{{ $item['label'] }}</span>@endif</article>@endforeach</div>
    @endswitch
    @if($section['button_label'] ?? null)<div class="section-action"><x-ui.button :href="$section['button_url'] ?? '#lien-he'" variant="outline">{{ $section['button_label'] }} ↗</x-ui.button></div>@endif
</div></section>
@endif
