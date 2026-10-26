<x-filament-panels::page>
    <div class="sample-intro">
        <div><h1>Chỉnh mọi chữ và ảnh trên website</h1><p>Chọn trang → chọn section → sửa nội dung và lưu.</p></div>
        <a href="{{ $this->clientUrl() }}" target="_blank" rel="noopener noreferrer" class="sample-button-outline"><x-filament::icon icon="heroicon-o-arrow-top-right-on-square"/> Xem trang {{ $this->groups()[$section] }}</a>
    </div>
    <nav class="sample-content-pages" aria-label="Chọn trang cần chỉnh sửa">
        @foreach($this->groups() as $key => $label)
            <a href="{{ \App\Filament\Pages\WebsiteContent::getUrl(['section' => $key]) }}" @class(['is-current' => $section === $key]) @if($section === $key) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>
    <div class="sample-content-guide">
        <x-filament::icon icon="heroicon-o-rectangle-group"/>
        <p><strong>{{ $this->groups()[$section] }}</strong> · Các section bên dưới đi theo thứ tự từ trên xuống dưới trên trang. Chọn một section để chỉnh chữ, ảnh và nút trong cùng một nơi.@if($section === 'home') Section đang ẩn vẫn xuất hiện trong danh sách để bạn bật lại.@endif</p>
    </div>
    <form wire:submit="save" class="sample-editor-form sample-content-editor">
        {{ $this->form }}
        <div class="sample-save-bar"><span>Lưu các thay đổi của trang {{ $this->groups()[$section] }}.</span><x-filament::button type="submit" icon="heroicon-o-check" wire:loading.attr="disabled">Lưu nội dung</x-filament::button></div>
    </form>
</x-filament-panels::page>
