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
        $colors = [];
        foreach ([
            'primary_color' => 'Màu nhấn', 'secondary_color' => 'Màu chữ chính',
            'accent_text_color' => 'Màu chữ nhấn', 'background_color' => 'Màu nền trang',
            'surface_color' => 'Màu nền phụ', 'home_gold_color' => 'Màu vàng trang chủ',
            'home_dark_color' => 'Màu tối trang chủ',
        ] as $name => $label) {
            $colors[] = F\ColorPicker::make($name)->label($label)->required();
        }

        return $schema->components([
            Section::make('Giao diện website')->description('Logo và Booking chỉnh tại Header; liên hệ chỉnh tại Footer; thông tin tìm kiếm chỉnh tại SEO.')->schema([
                F\TextInput::make('site_name')->label('Tên website')->required()->maxLength(100)->columnSpanFull(),
                ...$colors,
                F\Select::make('heading_font')->label('Font tiêu đề')->options(['sans' => 'Be Vietnam Pro', 'serif' => 'Cormorant Garamond', 'system' => 'Font hệ thống'])->required(),
                F\Select::make('body_font')->label('Font nội dung')->options(['sans' => 'Be Vietnam Pro', 'serif' => 'Cormorant Garamond', 'system' => 'Font hệ thống'])->required(),
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
