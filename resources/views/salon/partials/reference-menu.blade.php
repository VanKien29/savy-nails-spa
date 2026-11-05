<section class="av-menu-section">
    <div class="av-container">
        <h2 class="av-section-title" data-reveal>{{ $menuBlock['heading'] }}</h2>
        @if($salon['categories']->isEmpty())<p class="av-center">{{ $copy['contact']['menu_empty'] }}</p>@endif
        @foreach($home ? $salon['categories']->take(1) : $salon['categories'] as $category)
        <section class="av-menu-panel" id="menu-{{ $category->slug }}" data-menu-category="{{ $category->slug }}">
            <h3>{{ $category->name }}</h3><div class="av-rule"></div>
            <div class="av-menu-grid">
                @foreach($category->services->groupBy('group') as $group => $services)
                @if($group && $group !== $category->name)<h4 class="av-menu-group">{{ $group }}</h4>@endif
                @foreach($services as $service)
                <article class="av-service" data-service-row>
                    <div class="av-service-heading"><h4>{{ $service->name }}</h4><span class="av-dots"></span><strong>{{ $service->priceLabel() }}</strong></div>
                    @if($service->description)<p>{{ $service->description }}</p>@endif
                    @if($service->fill_price !== null)<p>{{ $copy['menu']['fill_label'] }}: {{ $service::moneyLabel($service->fill_price) }}</p>@endif
                    @if($service->gel_price !== null)<p>{{ $copy['menu']['gel_label'] }}: {{ $service::moneyLabel($service->gel_price) }}</p>@endif
                    @if($service->price !== null && $service->price_note)<p>{{ $service->price_note }}</p>@endif
                    @if($service->duration_label)<p>{{ $service->duration_label }}</p>@endif
                    @if(count($service->includes ?? []))<p>{{ $service->includes_label ?: $copy['menu']['includes_label'] }}</p><ul>@foreach($service->includes as $step)<li>{{ $step }}</li>@endforeach</ul>@endif
                </article>
                @endforeach
                @endforeach
            </div>
        </section>
        @endforeach
        @if($home && filled($menuBlock['button_label']))<div class="av-center"><a class="av-button" href="{{ $menuBlock['button_url'] }}">{{ $menuBlock['button_label'] }}</a></div>@endif
    </div>
</section>
