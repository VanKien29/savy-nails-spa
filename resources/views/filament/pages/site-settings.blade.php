<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}
        <x-filament::button type="submit" wire:loading.attr="disabled">Lưu cấu hình</x-filament::button>
    </form>
</x-filament-panels::page>
