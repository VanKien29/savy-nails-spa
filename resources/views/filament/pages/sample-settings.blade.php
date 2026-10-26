<x-filament-panels::page>
    <div class="sample-intro"><div><p class="sample-brand-label">{{ app(\App\Services\SiteSettingsService::class)->get('site_name') }}</p><h1>{{ $this->getTitle() }}</h1><p>Quản lý thông tin hiển thị trên website.</p></div><a href="/" target="_blank" rel="noopener noreferrer" class="sample-button-outline"><x-filament::icon icon="heroicon-o-arrow-top-right-on-square"/> Xem website</a></div>
    <form wire:submit="save" class="sample-editor-form sample-settings-form">
        {{ $this->form }}
        <div class="sample-save-bar"><span>Kiểm tra nội dung trước khi lưu.</span><x-filament::button type="submit" icon="heroicon-o-check" wire:loading.attr="disabled">Lưu thay đổi</x-filament::button></div>
    </form>
</x-filament-panels::page>
