@php($channels = \App\Services\SiteSettingsService::SOCIAL_CHANNELS)
<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{ value: $wire.$entangle('{{ $getStatePath() }}').live, open: false, search: '', labels: @js($channels) }" class="sample-social-field">
        <button type="button" class="sample-social-trigger" x-on:click="open = true; search = ''" :aria-expanded="open" aria-haspopup="dialog">
            <span class="sample-social-selected-icon" aria-hidden="true">
                @foreach($channels as $key => $label)
                    <span x-cloak x-show="value === @js($key)"><x-salon.icon :name="strtolower($key)" /></span>
                @endforeach
                <span x-cloak x-show="!value"><x-filament::icon icon="heroicon-o-squares-2x2" /></span>
            </span>
            <span x-text="labels[value] || 'Chọn icon mạng xã hội'"></span>
            <span class="sample-social-change">Chọn icon <span aria-hidden="true">↗</span></span>
        </button>
        <template x-teleport="body">
            <div x-cloak x-show="open" class="sample-media-overlay" x-trap.inert.noscroll="open" x-on:click.self="open = false" x-on:keydown.escape.window="open = false" role="dialog" aria-modal="true" aria-label="Chọn icon mạng xã hội">
                <div class="sample-media-picker sample-social-picker">
                    <header>
                        <div><h2>Chọn icon mạng xã hội</h2><p>Chọn một icon để hiển thị trên website.</p></div>
                        <button type="button" x-on:click="open = false" aria-label="Đóng bộ chọn icon">×</button>
                    </header>
                    <div class="sample-social-search"><input type="search" x-model="search" placeholder="Tìm Facebook, TikTok, Zalo..." aria-label="Tìm mạng xã hội"></div>
                    <div class="sample-social-grid" role="radiogroup" aria-label="Các icon mạng xã hội">
                        @foreach($channels as $key => $label)
                            <label class="sample-social-choice" :class="{ 'is-selected': value === @js($key) }" x-show="@js(strtolower($label)).includes(search.trim().toLowerCase())">
                                <input type="radio" name="{{ $getId() }}-channel" value="{{ $key }}" x-model="value" x-on:change="open = false" aria-label="{{ $label }}">
                                <span class="sample-social-choice-icon"><x-salon.icon :name="strtolower($key)" /></span>
                                <span>{{ $label }}</span>
                                <span class="sample-social-choice-check" x-cloak x-show="value === @js($key)" aria-hidden="true">✓</span>
                            </label>
                        @endforeach
                        <p class="sample-social-empty" x-cloak x-show="!Object.values(labels).some(label => label.toLowerCase().includes(search.trim().toLowerCase()))">Không tìm thấy kênh phù hợp.</p>
                    </div>
                    <footer><span>20 icon có sẵn · Chọn icon rồi nhập đường dẫn.</span><button type="button" class="sample-button-outline" x-on:click="open = false">Đóng</button></footer>
                </div>
            </div>
        </template>
    </div>
</x-dynamic-component>
