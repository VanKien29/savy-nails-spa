<section class="ks-section cms-content-section cms-variant-{{ $section['variant'] ?? 'default' }}" @if($section['id'] ?? null) id="{{ $section['id'] }}" @endif>
    <div class="ks-container">
        <x-salon.section-heading :eyebrow="$section['eyebrow'] ?? ''" :heading="$section['heading'] ?? ''" :body="$section['body'] ?? ''" :center="($section['variant'] ?? '') === 'centered'"/>
        @if($section['media_id'] ?? null)
            @if($sectionMedia = $media->get($section['media_id']))<x-media.picture :media="$sectionMedia" :alt="$sectionMedia->alt" class="cms-section-photo"/>@endif
        @endif
        @if(($section['type'] ?? '') === 'faq')
            <div class="kl-faqs">@foreach($section['items'] ?? [] as $item)<details><summary>{{ $item['title'] ?? '' }}<x-salon.icon name="plus"/></summary><p>{{ $item['text'] ?? '' }}</p></details>@endforeach</div>
        @else
            @if($section['items'] ?? [])<div class="cms-content-grid">@foreach($section['items'] as $item)<article>
                @if($itemMedia = $media->get($item['media_id'] ?? null))<x-media.picture :media="$itemMedia" :alt="$itemMedia->alt"/>@endif
                @if($item['value'] ?? '')<span class="cms-content-value">{{ $item['value'] }}</span>@endif
                @if($item['title'] ?? '')<h3>{{ $item['title'] }}</h3>@endif
                @if($item['text'] ?? '')<p>{{ $item['text'] }}</p>@endif
                @if($item['label'] ?? '')<small>{{ $item['label'] }}</small>@endif
            </article>@endforeach</div>@endif
        @endif
        @if(($section['button_label'] ?? '') && ($section['button_url'] ?? ''))<a href="{{ $section['button_url'] }}" class="ks-button">{{ $section['button_label'] }} </a>@endif
    </div>
</section>
