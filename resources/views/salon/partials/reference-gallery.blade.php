<div class="{{ $rail ? 'av-gallery-rail' : 'av-gallery-grid' }}" @if($rail) aria-label="Nail inspiration carousel" tabindex="0" @endif>
    @foreach($galleryImages as $key)
        @if($salon['images']->has($key))
        <button class="av-gallery-photo" type="button" data-lightbox-file="{{ $key }}" aria-label="View {{ $salon['images']->get($key)->alt }}" @unless($rail) data-reveal @endunless>
            @include('salon.partials.reference-image', ['key' => $key, 'sizes' => '(max-width:767px) 90vw, 33vw'])
        </button>
        @endif
    @endforeach
</div>
