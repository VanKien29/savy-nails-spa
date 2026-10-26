<?php

namespace App\Filament\Pages;

use App\Models\SalonProfile;
use App\Services\CmsAccess;
use App\Services\SalonContent;
use App\Services\SiteSettingsService;
use Filament\Forms\Components as F;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class SeoSettings extends Page
{
    protected static ?string $title = 'Cài đặt SEO';

    protected string $view = 'filament.pages.sample-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return CmsAccess::allows('settings');
    }

    public function mount(): void
    {
        $this->form->fill(app(SiteSettingsService::class)->all() + ['profile' => app(SalonContent::class)->all()['profile']]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('SEO mặc định')->description('Áp dụng khi trang chưa có thiết lập SEO riêng.')->schema([
                F\TextInput::make('default_title')->label('Tiêu đề website')->required()->maxLength(160),
                F\Textarea::make('default_description')->label('Mô tả website')->required()->maxLength(320)->rows(4),
                WebsiteContent::mediaPicker('og_media_id', 'Ảnh chia sẻ mặc định'),
            ]),
            Section::make('Địa chỉ doanh nghiệp trên công cụ tìm kiếm')->visible(fn () => SalonProfile::whereKey(1)->exists())->schema([
                F\TextInput::make('profile.postal_address.streetAddress')->label('Số nhà / đường')->required()->maxLength(200),
                F\TextInput::make('profile.postal_address.addressLocality')->label('Thành phố')->required()->maxLength(100),
                F\TextInput::make('profile.postal_address.addressRegion')->label('Bang / tỉnh')->required()->maxLength(100),
                F\TextInput::make('profile.postal_address.postalCode')->label('Mã bưu chính')->required()->maxLength(20),
                F\TextInput::make('profile.postal_address.addressCountry')->label('Mã quốc gia')->required()->length(2)->regex('/^[A-Z]{2}$/'),
            ])->columns(2)->collapsible(),
        ])->statePath('data');
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);
        $state = $this->form->getState();
        DB::transaction(function () use ($state): void {
            $settings = app(SiteSettingsService::class);
            $settings->update(array_replace($settings->all(), collect($state)->except('profile')->all()));
            if ($profile = SalonProfile::whereKey(1)->lockForUpdate()->first()) {
                $data = $profile->data;
                if (isset($state['profile']['postal_address'])) {
                    $data['postal_address'] = $state['profile']['postal_address'];
                }
                $profile->update(['data' => $data]);
            }
        });
        Notification::make()->title('Đã lưu cấu hình SEO')->success()->send();
    }
}
