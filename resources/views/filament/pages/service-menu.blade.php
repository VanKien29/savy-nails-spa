<x-filament-panels::page>
    <div class="sample-intro">
        <div><h1>Menu dịch vụ</h1><p>Chọn danh mục, bấm Sửa ngay tại dịch vụ. Nội dung được cập nhật trực tiếp lên website.</p></div>
        <a href="/services" target="_blank" rel="noopener noreferrer" class="sample-button-outline"><x-filament::icon icon="heroicon-o-arrow-top-right-on-square"/> Xem trang Services</a>
    </div>
    <div class="menu-editor-layout">
        <aside class="menu-editor-categories" aria-label="Danh mục trong menu">
            <div class="menu-editor-nav-heading"><h2>Danh mục</h2><span>{{ $this->menuCategories->count() }}</span></div>
            <nav>
                @foreach($this->menuCategories as $category)
                    <div class="menu-editor-category" wire:key="menu-category-{{ $category->id }}">
                        <button type="button" wire:click="$set('categoryId', {{ $category->id }})" @class(['menu-editor-category-button', 'is-selected' => $this->selectedCategory?->id === $category->id]) @if($this->selectedCategory?->id === $category->id) aria-current="true" @endif>
                            <span>{{ $category->name }}</span><small>{{ $category->services_count }} dịch vụ</small>
                        </button>
                        <div class="menu-editor-category-order">
                            <button type="button" wire:click="moveCategory({{ $category->id }}, 'up')" @disabled($loop->first) aria-label="Đưa {{ $category->name }} lên trước" title="Lên trước"><x-filament::icon icon="heroicon-o-chevron-up"/></button>
                            <button type="button" wire:click="moveCategory({{ $category->id }}, 'down')" @disabled($loop->last) aria-label="Đưa {{ $category->name }} xuống sau" title="Xuống sau"><x-filament::icon icon="heroicon-o-chevron-down"/></button>
                        </div>
                    </div>
                @endforeach
            </nav>
            {{ $this->createCategoryAction }}
        </aside>
        <div class="menu-editor-main">
            @if($selected = $this->selectedCategory)
                <div class="menu-editor-toolbar">
                    <div><span class="menu-editor-eyebrow">Đang chỉnh sửa</span><h2>{{ $selected->name }}</h2></div>
                    <div class="menu-editor-toolbar-actions">{{ ($this->editCategoryAction)(['id' => $selected->id]) }} {{ $this->createServiceAction }}</div>
                </div>
                @if($this->hiddenCount)
                    <label class="menu-editor-hidden-toggle"><input type="checkbox" wire:model.live="showHidden"> Hiện cả {{ $this->hiddenCount }} dịch vụ đang ẩn</label>
                @endif
                <div class="menu-editor-preview" wire:loading.class="is-loading" wire:target="categoryId" aria-label="Nội dung menu {{ $selected->name }}">
                    <h2 @class(['menu-editor-preview-title', 'menu-editor-sr-only' => $selected->slug === 'brows-lashes'])>{{ $selected->name }}@if($selected->slug === 'kids')<small>{{ app(\App\Services\SalonContent::class)->all()['profile']['copy']['menu']['kids_note'] }}</small>@endif</h2>
                    @forelse($this->groupedServices as $group => $services)
                        @php($pairedPrices = $services->contains(fn ($service) => $service->fill_price !== null))
                        <section class="menu-editor-group" wire:key="menu-group-{{ $selected->id }}-{{ md5($group) }}">
                            @if($group && $group !== $selected->name)<h3>{{ $group }}</h3>@endif
                            @if($pairedPrices)<div class="menu-editor-price-headings" aria-hidden="true"><span>Full Set</span><span>Fill-in</span></div>@endif
                            @foreach($services as $service)
                                <div @class(['menu-editor-service', 'is-hidden' => ! $service->active]) wire:key="menu-service-{{ $service->id }}" data-menu-service="{{ $service->id }}">
                                    <div class="menu-editor-service-content">
                                        @if(! $service->active)<span class="menu-editor-hidden-badge">Đang ẩn trên website</span>@endif
                                        <x-salon.service-menu-row :service="$service" :paired-prices="$pairedPrices" :compact="true" :heading-level="$group && $group !== $selected->name ? 'h4' : 'h3'"/>
                                    </div>
                                    <div class="menu-editor-service-actions">
                                        <button type="button" class="menu-editor-edit" wire:click="mountAction('editService', { id: {{ $service->id }} })" aria-label="Sửa {{ $service->name }}"><x-filament::icon icon="heroicon-o-pencil-square"/> Sửa</button>
                                        <button type="button" class="menu-editor-visibility" wire:click="toggleService({{ $service->id }})" aria-label="{{ $service->active ? 'Ẩn' : 'Hiện' }} {{ $service->name }}">{{ $service->active ? 'Ẩn' : 'Hiện lại' }}</button>
                                        <div class="menu-editor-row-order">
                                            <button type="button" wire:click="moveService({{ $service->id }}, 'up')" @disabled($loop->first) aria-label="Đưa {{ $service->name }} lên trước" title="Lên trước"><x-filament::icon icon="heroicon-o-chevron-up"/></button>
                                            <button type="button" wire:click="moveService({{ $service->id }}, 'down')" @disabled($loop->last) aria-label="Đưa {{ $service->name }} xuống sau" title="Xuống sau"><x-filament::icon icon="heroicon-o-chevron-down"/></button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </section>
                    @empty
                        <div class="menu-editor-empty"><x-filament::icon icon="heroicon-o-sparkles"/><h3>Chưa có dịch vụ đang hiển thị</h3><p>Thêm dịch vụ đầu tiên cho {{ $selected->name }} hoặc hiện lại dịch vụ đã ẩn.</p>{{ $this->createServiceAction }}</div>
                    @endforelse
                </div>
            @else
                <div class="menu-editor-empty"><h2>Bắt đầu tạo menu</h2><p>Thêm một danh mục rồi thêm các dịch vụ bên trong.</p>{{ $this->createCategoryAction }}</div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
