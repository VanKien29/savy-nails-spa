<section class="ks-section kl-collection"><div class="ks-container"><div class="ks-heading-row"><x-salon.section-heading :eyebrow="($collection['eyebrow'] ?? '')" :heading="($collection['heading'] ?? '')" :body="($collection['body'] ?? '')"/><a href="{{ $copy['home']['full_menu_url'] }}" class="ks-text-link">{{ $copy['home']['full_menu_label'] }} </a></div><div class="kl-collection-grid">
@foreach($salon['categories'] as $category)
<a href="/services#menu-{{ $category->slug }}" class="kl-collection-card" data-animate="fade-up"><span class="kl-card-number">0{{ $loop->iteration }}</span><div><h3>{{ $category->name }}</h3><p>{{ $category->description }}</p></div><footer><span>@if($category->services->whereNotNull('price')->isNotEmpty()){{ $copy['home']['price_prefix'] }} {{ \App\Models\SalonService::moneyLabel($category->services->whereNotNull('price')->min('price')) }}@else{{ $copy['menu']['inquire_label'] }}@endif</span></footer></a>
@endforeach
</div></div></section>
