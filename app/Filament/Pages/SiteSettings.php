<?php

namespace App\Filament\Pages;

use App\Filament\Resources\PageResource;
use App\Services\SiteSettingsService;
use Filament\Forms\Components as F;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class SiteSettings extends Page
{
    protected static ?string $title = 'Cấu hình website';

    protected static ?string $navigationLabel = 'Cấu hình website';

    protected string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->is_admin;
    }

    public function mount(): void
    {
        $this->form->fill(app(SiteSettingsService::class)->all());
    }

    public function form(Schema $schema): Schema
    {
        $fields = [
            F\TextInput::make('site_name')->label('Tên website')->required()->maxLength(100),
            F\TextInput::make('company_name')->label('Tên công ty')->required()->maxLength(160), F\TextInput::make('slogan')->maxLength(240),
            F\ColorPicker::make('primary_color')->label('Màu nhấn')->required(), F\ColorPicker::make('secondary_color')->label('Màu nền đậm')->required(),
            F\Select::make('heading_font')->options(['sans' => 'DM Sans / Be Vietnam Pro', 'serif' => 'Georgia', 'system' => 'System'])->required(),
            F\Select::make('body_font')->options(['sans' => 'Be Vietnam Pro', 'serif' => 'Georgia', 'system' => 'System'])->required(),
            F\TextInput::make('email')->email(), F\TextInput::make('hotline'), F\Textarea::make('address'),
            F\TextInput::make('maps_url')->label('Liên kết Google Maps')->url()->helperText('Dùng URL maps an toàn; không nhận HTML embed.'),
            F\TextInput::make('maps_embed_url')->label('URL bản đồ Google Maps nhúng')->url()->maxLength(4000)->helperText('Dán URL src https://www.google.com/maps/embed?pb=... từ mục Share > Embed a map; không dán HTML iframe.'),
            F\TextInput::make('copyright')->maxLength(200), F\Textarea::make('legal')->maxLength(2000),
            F\TextInput::make('default_title')->required()->maxLength(160), F\Textarea::make('default_description')->required()->maxLength(320),
            F\TextInput::make('ga4_id')->helperText('G-... Chỉ tải sau consent.'), F\TextInput::make('gtm_id')->helperText('GTM-... Tránh cài trùng GA qua GTM.'), F\TextInput::make('meta_pixel_id'),
            F\Repeater::make('social_links')->schema([F\TextInput::make('label')->required(), F\TextInput::make('url')->url()->required()])->defaultItems(0),
            F\Repeater::make('contacts')->label('Liên hệ nổi (kéo để sắp xếp)')->schema([
                F\Select::make('type')->options(['phone' => 'Điện thoại', 'zalo' => 'Zalo', 'messenger' => 'Messenger', 'whatsapp' => 'WhatsApp'])->required(),
                F\TextInput::make('url')->required()->helperText('tel:+84... hoặc https://...'), F\ColorPicker::make('color')->default('#153c35')->required(),
                F\Toggle::make('enabled')->default(true), F\Toggle::make('desktop')->default(true), F\Toggle::make('mobile')->default(true),
            ])->defaultItems(0)->maxItems(8),
            F\Select::make('contact_position')->options(['left' => 'Trái', 'right' => 'Phải'])->required(),
            F\Toggle::make('back_to_top'), F\Toggle::make('cookie_consent'), F\Toggle::make('features.contact')->label('Form liên hệ'), F\Toggle::make('features.animations')->label('Animation'), F\Toggle::make('features.multilingual')->label('Hreflang'),
        ];
        foreach (['logo_id' => 'Logo desktop', 'mobile_logo_id' => 'Logo mobile', 'favicon_id' => 'Favicon', 'og_media_id' => 'Ảnh OG mặc định'] as $name => $label) {
            $fields[] = F\Select::make($name)->label($label)->options(fn () => PageResource::mediaOptions())->searchable()
                ->helperText(match ($name) {
                    'logo_id' => 'Tải ảnh trong Thư viện ảnh, rồi chọn tại đây. Dùng cho header desktop và footer.',
                    'mobile_logo_id' => 'Logo cho màn hình nhỏ; để trống để dùng logo desktop. Ưu tiên PNG/WebP nền trong suốt.',
                    default => null,
                });
        }

        return $schema->components($fields)->statePath('data')->columns(2);
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);
        app(SiteSettingsService::class)->update($this->form->getState());
        Notification::make()->title('Đã lưu cấu hình')->success()->send();
    }
}
