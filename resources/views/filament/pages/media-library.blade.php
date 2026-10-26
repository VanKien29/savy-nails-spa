<x-filament-panels::page>
    @php($images = $this->images())
    <div class="sample-media-search"><input type="search" wire:model.live.debounce.300ms="search" placeholder="Tìm kiếm ảnh..." aria-label="Tìm kiếm ảnh"><span>{{ $images->total() }} ảnh trong thư viện</span></div>
    <div class="sample-library-grid">
        @forelse($images as $image)
        <article wire:key="media-{{ $image->id }}"><button type="button" wire:click="mountAction('details', { id: {{ $image->id }} })" class="sample-library-image">@if($image->url())<img src="{{ $image->url() }}" alt="{{ $image->alt ?: $image->name }}" loading="lazy">@else<span>Đang xử lý ảnh</span>@endif</button><div><button type="button" wire:click="mountAction('details', { id: {{ $image->id }} })">{{ $image->name }}</button><small>{{ $image->width }} × {{ $image->height }} · {{ number_format($image->size / 1024) }} KB</small><button type="button" class="sample-media-delete" wire:click="mountAction('deleteMedia', { id: {{ $image->id }} })" aria-label="Xóa {{ $image->name }}"><x-filament::icon icon="heroicon-o-trash"/></button></div></article>
        @empty
        <div class="sample-empty-media"><x-filament::icon icon="heroicon-o-photo"/><h2>Chưa có ảnh phù hợp</h2><p>Tải ảnh mới lên hoặc thử từ khóa khác.</p></div>
        @endforelse
    </div>
    {{ $images->links() }}
</x-filament-panels::page>
