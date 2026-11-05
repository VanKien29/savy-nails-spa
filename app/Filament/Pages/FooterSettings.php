<?php

namespace App\Filament\Pages;

use App\Models\Media;
use App\Models\SalonImage;
use App\Models\SalonProfile;
use App\Services\CmsAccess;
use App\Services\SalonContent;
use App\Services\SavyContent;
use App\Services\SiteSettingsService;
use Filament\Forms\Components as F;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FooterSettings extends Page
{
    protected static ?string $title = 'Cài đặt Footer';

    protected string $view = 'filament.pages.sample-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return CmsAccess::allows('settings');
    }

    public function mount(): void
    {
        $profile = app(SalonContent::class)->all()['profile'];
        $profile['savy'] = app(SavyContent::class)->profile($profile);
        $this->form->fill(['settings' => app(SiteSettingsService::class)->all(), 'profile' => $profile]);
    }

    public static function socialLinksField(string $name): F\Repeater
    {
        return F\Repeater::make($name)->label('Các kênh liên hệ')->schema([
            F\ViewField::make('label')->label('Kênh / icon')->view('filament.forms.social-icon-picker')
                ->required()->rules([Rule::in(array_keys(SiteSettingsService::SOCIAL_CHANNELS))]),
            F\TextInput::make('url')->label('Đường dẫn')->placeholder('https://...')->url()->required(),
        ])->columns(2)->maxItems(20)->defaultItems(0)->reorderable()
            ->itemLabel(fn (array $state) => SiteSettingsService::SOCIAL_CHANNELS[$state['label'] ?? ''] ?? 'Kênh mới')
            ->addActionLabel('Thêm kênh liên hệ')
            ->helperText('Nhấn Chọn icon để mở thư viện, chọn mạng xã hội rồi dán đường dẫn. Kéo thả các mục để sắp xếp icon trên website.');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Nội dung chân trang')->description('Logo dùng chung với Header và được quản lý tại Header kéo thả.')->schema([
                F\TextInput::make('profile.savy.footer.contact_label')->label('Tiêu đề thông tin liên hệ')->maxLength(180),
                F\TextInput::make('profile.savy.footer.hours_label')->label('Tiêu đề giờ mở cửa')->maxLength(180),
                F\TextInput::make('profile.savy.footer.map_label')->label('Chữ liên kết mở bản đồ')->maxLength(180)->columnSpanFull(),
                F\TextInput::make('settings.company_name')->label('Tên doanh nghiệp')->required()->maxLength(160),
                F\TextInput::make('settings.copyright')->label('Bản quyền')->maxLength(200),
                WebsiteContent::imagePicker('profile.savy.images.footer_background', 'Ảnh nền Footer'),
            ])->columns(2),
            Section::make('Thông tin liên hệ & bản đồ')->schema([
                F\TextInput::make('settings.hotline')->label('Số điện thoại')->maxLength(24),
                F\Textarea::make('settings.address')->label('Địa chỉ salon')->maxLength(500)->columnSpanFull(),
                F\TextInput::make('settings.maps_url')->label('Liên kết Google Maps')->url()->maxLength(1000),
                F\Textarea::make('settings.maps_embed_url')->label('URL bản đồ nhúng')->rows(3)->maxLength(4000)->helperText('Dán URL src từ Google Maps → Share → Embed a map.'),
            ])->columns(2),
            Section::make('Giờ mở cửa')->schema([
                F\Repeater::make('profile.hours')->label('Lịch mở cửa')->schema([F\TextInput::make('days')->label('Ngày')->required()->maxLength(80), F\TextInput::make('time')->label('Giờ')->required()->maxLength(100)])->columns(2)->maxItems(14)->defaultItems(0),
                F\Textarea::make('profile.hours_note')->label('Lưu ý lịch mở cửa')->maxLength(1000),
            ]),
            Section::make('Mạng xã hội')->schema([self::socialLinksField('settings.social_links')]),
        ])->statePath('data');
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);
        $state = $this->form->getState();
        DB::transaction(function () use ($state) {
            app(SiteSettingsService::class)->update(array_replace(app(SiteSettingsService::class)->all(), $state['settings']));
            $profile = SalonProfile::whereKey(1)->lockForUpdate()->firstOrFail();
            $data = $profile->data;
            $data['hours'] = $state['profile']['hours'] ?? [];
            $data['hours_note'] = $state['profile']['hours_note'] ?? '';
            $data['savy'] = app(SavyContent::class)->profile($data);
            $data['savy']['footer'] = array_replace($data['savy']['footer'], $state['profile']['savy']['footer'] ?? []);
            foreach ($state['profile']['savy']['images'] ?? [] as $name => $key) {
                if (preg_match('/^uploaded-media-([0-9]+)$/', (string) $key, $match)) {
                    $upload = Media::whereKey($match[1])->where('status', 'ready')->firstOrFail();
                    SalonImage::firstOrCreate(['key' => $key], ['media_id' => $upload->id, 'variants' => [], 'caption' => $upload->name, 'alt' => mb_substr($upload->alt ?: $upload->name, 0, 255), 'category' => 'savy', 'sort_order' => 0]);
                }
                $data['savy']['images'][$name] = $key;
            }
            $profile->update(['data' => $data]);
        });
        Notification::make()->title('Đã lưu Footer')->success()->send();
    }
}
