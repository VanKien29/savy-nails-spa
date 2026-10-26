<?php

namespace App\Filament\Pages;

use App\Models\NavigationItem;
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
        $this->form->fill(['settings' => app(SiteSettingsService::class)->all(), 'profile' => app(SalonContent::class)->all()['profile'], 'links' => NavigationItem::where('location', 'footer')->orderBy('sort_order')->get(['label', 'url', 'enabled'])->toArray()]);
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
                ...WebsiteContent::copyFields('footer'),
                F\TextInput::make('settings.company_name')->label('Tên doanh nghiệp')->required()->maxLength(160),
                F\TextInput::make('settings.copyright')->label('Bản quyền')->maxLength(200),
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
            Section::make('Chính sách chân trang')->schema(WebsiteContent::copyFields('global', ['policy_label', 'policy_title', 'policy_body', 'policy_note']))->columns(2)->collapsible(),
            Section::make('Menu chân trang')->schema([F\Repeater::make('links')->label('Các liên kết')->schema([F\TextInput::make('label')->label('Tên liên kết')->required()->maxLength(80), F\TextInput::make('url')->label('Đường dẫn')->required()->maxLength(500), F\Toggle::make('enabled')->label('Hiển thị')->default(true)])->columns(2)->maxItems(24)->defaultItems(0)->deleteAction(fn ($action) => $action->requiresConfirmation())]),
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
            $data['copy']['footer'] = array_replace($data['copy']['footer'] ?? [], $state['profile']['copy']['footer'] ?? []);
            $data['copy']['global'] = array_replace($data['copy']['global'] ?? [], $state['profile']['copy']['global'] ?? []);
            $profile->update(['data' => $data]);
            foreach (NavigationItem::where('location', 'footer')->get() as $item) {
                $item->delete();
            }
            foreach ($state['links'] ?? [] as $order => $link) {
                NavigationItem::create($link + ['location' => 'footer', 'sort_order' => $order]);
            }
        });
        Notification::make()->title('Đã lưu Footer')->success()->send();
    }
}
