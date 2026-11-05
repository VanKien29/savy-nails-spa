<section class="av-menu-section">
    <div class="av-container">
        <h2 class="av-section-title" data-reveal>Services</h2>
        @foreach($home ? $salon['categories']->take(1) : $salon['categories'] as $category)
        <section class="av-menu-panel" id="menu-{{ $category->slug }}" data-menu-category="{{ $category->slug }}">
            <h3>{{ $category->name }}</h3><div class="av-rule"></div>
            <div class="av-menu-grid">
                @foreach($category->services as $service)
                <article class="av-service" data-service-row>
                    <div class="av-service-heading"><h4>{{ $service->name }}</h4><span class="av-dots"></span><strong>{{ $service->priceLabel() }}</strong></div>
                    @if($service->description)<p>{{ $service->description }}</p>@endif
                    @if($service->includes)<ul>@foreach($service->includes as $step)<li>{{ $step }}</li>@endforeach</ul>@endif
                </article>
                @endforeach
            </div>
        </section>
        @endforeach
        @if($home)<div class="av-center"><a class="av-button" href="/services">Show More</a></div>@endif
    </div>
</section>
