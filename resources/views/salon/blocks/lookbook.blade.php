<section class="ks-section kl-lookbook"><div class="ks-container"><x-salon.section-heading :eyebrow="($edit['eyebrow'] ?? '')" :heading="($edit['heading'] ?? '')" :body="($edit['body'] ?? '')" center/><div class="ks-gallery-preview kl-look-rail" data-inspiration-slider tabindex="0" aria-label="The signature nail edit" data-native-scroll>
@foreach($salon['profile']['home_lookbook'] ?? [] as $key)
@php($image=$salon['images']->get($key))
@continue(!$image)
<figure class="kl-look" data-animate="fade-up"><button type="button" class="ks-gallery-tile" data-lightbox="{{ $image->url() }}" data-caption="{{ $image->caption }}" aria-label="View {{ $image->caption }}"><x-salon.picture :image="$image" sizes="(max-width:730px) 75vw, 30vw"/><span class="ks-gallery-overlay"><x-salon.icon name="plus"/></span></button></figure>
@endforeach
</div><div class="ks-slider-controls"><div><button type="button" data-slide="-1" aria-label="Previous inspiration"><x-salon.icon name="chevron-left"/></button><button type="button" data-slide="1" aria-label="Next inspiration"><x-salon.icon name="chevron-right"/></button></div></div></div></section>
