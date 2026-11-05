<?php

namespace App\Filament\Pages;

use App\Services\CmsAccess;
use App\Services\SiteSettingsService;
use Filament\Forms\Components as F;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteSettings extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Cấu hình website';

    protected string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return CmsAccess::allows('settings');
    }

    public function mount(): void
    {
        $this->form->fill(app(SiteSettingsService::class)->all());
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Giao diện website')->description('Logo và nút liên hệ chỉnh tại Header; liên hệ và giờ mở cửa chỉnh tại Footer; ảnh và câu chữ chỉnh tại Nội dung website.')->schema([
                F\TextInput::make('site_name')->label('Tên website')->required()->maxLength(100)->columnSpanFull(),
                F\Toggle::make('features.animations')->label('Hiệu ứng chuyển động'),
            ])->columns(2),
        ])->statePath('data');
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);
        $settings = app(SiteSettingsService::class);
        $settings->update(array_replace_recursive($settings->all(), $this->form->getState()));
        Notification::make()->title('Đã lưu cấu hình giao diện')->success()->send();
    }
}
