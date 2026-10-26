<?php

namespace App\Filament\Pages;

use App\Models\Media;
use App\Services\CmsAccess;
use App\Services\MediaService;
use Filament\Actions\Action;
use Filament\Forms\Components as F;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;
use Livewire\WithPagination;

class MediaLibrary extends Page
{
    use WithPagination;

    protected static ?string $title = 'Thư viện ảnh';

    protected string $view = 'filament.pages.media-library';

    public string $search = '';

    public static function canAccess(): bool
    {
        return CmsAccess::allows('content');
    }

    public function images()
    {
        return Media::query()->when($this->search !== '', fn ($query) => $query->where(fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')->orWhere('alt', 'like', '%'.$this->search.'%')))->latest('id')->paginate(24);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    protected function getHeaderActions(): array
    {
        return [$this->uploadAction()];
    }

    public function uploadAction(): Action
    {
        return Action::make('upload')->label('Tải ảnh lên')->icon('heroicon-o-cloud-arrow-up')->authorize(fn () => static::canAccess())->schema([
            F\FileUpload::make('files')->label('Kéo thả ảnh vào đây hoặc nhấn để chọn')->multiple()->maxFiles(20)->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])->maxSize(8192)->storeFiles(false)->required()->helperText('JPG, PNG, WebP, AVIF — tối đa 8 MB và 25 megapixel mỗi ảnh.'),
            F\TextInput::make('alt')->label('Mô tả thay thế (tùy chọn)')->maxLength(500),
        ])->action(function (array $data) {
            abort_unless(static::canAccess(), 403);
            foreach ($data['files'] as $file) {
                app(MediaService::class)->upload($file, $data['alt'] ?? '');
            }
            Notification::make()->title('Đã tải ảnh lên')->success()->send();
        });
    }

    public function detailsAction(): Action
    {
        return Action::make('details')->label('Chi tiết ảnh')->authorize(fn () => static::canAccess())
            ->record(fn (array $arguments) => Media::findOrFail($arguments['id'] ?? 0))->fillForm(fn (Media $record) => ['name' => $record->name, 'alt' => $record->alt, 'url' => $record->url()])
            ->modalHeading('Chi tiết ảnh')->slideOver()->schema([
                F\Placeholder::make('preview')->label('')->content(fn (Media $record) => new HtmlString('<img src="'.e($record->url()).'" alt="'.e($record->alt).'" class="sample-media-detail-image">')),
                F\TextInput::make('name')->label('Tên ảnh')->required()->maxLength(200), F\TextInput::make('alt')->label('Văn bản thay thế (Alt)')->maxLength(500),
                F\TextInput::make('url')->label('URL ảnh')->readOnly()->dehydrated(false),
            ])->action(function (Media $record, array $data) {
                abort_unless(static::canAccess(), 403);
                $record->update(collect($data)->only(['name', 'alt'])->all());
                Notification::make()->title('Đã lưu thông tin ảnh')->success()->send();
            });
    }

    public function deleteMediaAction(): Action
    {
        return Action::make('deleteMedia')->label('Xóa ảnh')->color('danger')->authorize(fn () => static::canAccess())
            ->record(fn (array $arguments) => Media::findOrFail($arguments['id'] ?? 0))->requiresConfirmation()->modalHeading('Xóa ảnh này?')->modalDescription('Ảnh đang được dùng hoặc được lưu trong lịch sử nội dung sẽ được bảo vệ, không thể xóa.')
            ->action(function (Media $record) {
                abort_unless(static::canAccess(), 403);
                app(MediaService::class)->delete($record);
                Notification::make()->title('Đã xóa ảnh')->success()->send();
            });
    }
}
