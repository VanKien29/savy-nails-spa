@php
    $uploadedImages = \App\Models\Media::where('status', 'ready')->latest()->get();
    $salonImages = ($mediaLibrary ?? false) ? collect() : \App\Models\SalonImage::with('media')->orderBy('sort_order')->get();
    $imageOptions = ($mediaLibrary ?? false)
        ? $uploadedImages->map(fn ($image) => ['key' => $image->id, 'caption' => $image->name, 'url' => $image->url()])->values()->all()
        : $salonImages->map(fn ($image) => ['key' => $image->key, 'caption' => $image->caption, 'url' => $image->url()])
            ->concat($uploadedImages->whereNotIn('id', $salonImages->pluck('media_id')->filter())->map(fn ($image) => ['key' => 'uploaded-media-'.$image->id, 'caption' => $image->name, 'url' => $image->url()]))->values()->all();
@endphp
<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{ value: $wire.$entangle('{{ $getStatePath() }}'), open: false, search: '', dragKey: null, multiple: @js($multiple ?? false), images: @js($imageOptions),
        selected(key){ return this.multiple ? (this.value || []).includes(key) : String(this.value) === String(key) },
        choose(key){ if(this.multiple){this.value = this.selected(key) ? this.value.filter(v => v !== key) : [...(this.value || []), key]}else{this.value = key;this.open = false} },
        move(key, direction){let items = [...(this.value || [])];let index = items.indexOf(key);let target = index + direction;if(index < 0 || target < 0 || target >= items.length)return;items.splice(target,0,items.splice(index,1)[0]);this.value = items},
        dropOn(key){if(!this.multiple || this.dragKey === null)return;let items = [...(this.value || [])];let from = items.indexOf(this.dragKey);let to = items.indexOf(key);if(from >= 0 && to >= 0){items.splice(to,0,items.splice(from,1)[0]);this.value = items}this.dragKey = null}
    }" x-on:keydown.escape.window="open = false" class="sample-image-field {{ ($compact ?? false) ? 'is-compact' : '' }}">
        <div class="sample-selected-images">
            <template x-for="key in (multiple ? (value || []) : (value ? [value] : []))" :key="key">
                <figure :draggable="multiple" x-on:dragstart="dragKey = key" x-on:dragover.prevent x-on:drop.prevent="dropOn(key)" x-on:dragend="dragKey = null">
                    <img :src="images.find(image => image.key === key)?.url" :alt="images.find(image => image.key === key)?.caption || ''" draggable="false">
                    @unless($compact ?? false)<figcaption x-text="images.find(image => image.key === key)?.caption"></figcaption>@endunless
                    <button x-show="multiple" type="button" x-on:click="value = value.filter(v => v !== key)" aria-label="Gỡ ảnh">×</button>
                    <div x-show="multiple" class="sample-image-order-tools">
                        <button type="button" x-on:click="move(key, -1)" :disabled="!multiple || !Array.isArray(value) || value.indexOf(key) === 0" aria-label="Đưa ảnh về trước">←</button>
                        @unless($compact ?? false)<span>Kéo thả để sắp xếp</span>@endunless
                        <button type="button" x-on:click="move(key, 1)" :disabled="!multiple || !Array.isArray(value) || value.indexOf(key) === value.length - 1" aria-label="Đưa ảnh ra sau">→</button>
                    </div>
                </figure>
            </template>
        </div>
        <button type="button" class="sample-image-trigger" x-on:click="open = true"><x-filament::icon icon="heroicon-o-photo"/> Chọn ảnh từ Thư viện Media</button>
        <div x-cloak x-show="open" class="sample-media-overlay" x-trap.inert.noscroll="open" x-on:click.self="open = false" role="dialog" aria-modal="true" aria-label="Chọn ảnh">
            <div class="sample-media-picker">
                <header><div><h2>Thư viện Media</h2><p>Nhấn vào ảnh để chọn. <span x-show="multiple">Thứ tự chọn là thứ tự hiển thị.</span></p></div><button type="button" x-on:click="open = false" aria-label="Đóng bộ chọn ảnh">×</button></header>
                <div class="sample-media-toolbar"><input type="search" x-model="search" placeholder="Tìm kiếm ảnh..." aria-label="Tìm kiếm ảnh"><a href="{{ \App\Filament\Pages\MediaLibrary::getUrl() }}" target="_blank" rel="noopener noreferrer" class="sample-button-outline">Thêm / quản lý ảnh ↗</a></div>
                <div class="sample-media-grid"><template x-for="image in images.filter(item => (item.caption + ' ' + item.key).toLowerCase().includes(search.toLowerCase()))" :key="image.key"><button type="button" x-on:click="choose(image.key)" :class="{'is-selected': selected(image.key)}" :aria-pressed="selected(image.key)"><img :src="image.url" :alt="image.caption" loading="lazy"><span x-text="image.caption"></span><b x-show="selected(image.key)" x-text="multiple ? (value || []).indexOf(image.key) + 1 : '✓'"></b></button></template></div>
                <footer><span x-show="multiple" x-text="(value || []).length + ' ảnh đã chọn'"></span><button type="button" class="sample-button-dark" x-on:click="open = false">Xong</button></footer>
            </div>
        </div>
    </div>
</x-dynamic-component>
